<?php
declare(strict_types=1);

/**
 * Minimal authenticated SMTP client (no external libraries) for sending
 * notification mail through Microsoft 365, since Hostinger's server can't
 * send as @fahs.us once SPF locks sending to Microsoft's servers only.
 */
function smtp_send(string $to, string $subject, string $body, ?string $replyTo = null): bool {
    $host = 'smtp.office365.com';
    $port = 587;
    $user = defined('SMTP_USER') ? SMTP_USER : '';
    $pass = defined('SMTP_PASS') ? SMTP_PASS : '';

    if ($user === '' || $pass === '') {
        return false;
    }

    $read = function ($sock) {
        $data = '';
        while (($line = fgets($sock, 515)) !== false) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };

    $expect = function ($sock, string $code) use ($read): bool {
        $resp = $read($sock);
        return strpos($resp, $code) === 0 || substr($resp, 0, 3) === $code;
    };

    $sock = @stream_socket_client("tcp://$host:$port", $errno, $errstr, 15);
    if (!$sock) {
        return false;
    }

    try {
        $read($sock); // 220 greeting

        fwrite($sock, "EHLO fahs.us\r\n");
        $read($sock);

        fwrite($sock, "STARTTLS\r\n");
        if (!$expect($sock, '220')) return false;

        if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            return false;
        }

        fwrite($sock, "EHLO fahs.us\r\n");
        $read($sock);

        fwrite($sock, "AUTH LOGIN\r\n");
        if (!$expect($sock, '334')) return false;

        fwrite($sock, base64_encode($user) . "\r\n");
        if (!$expect($sock, '334')) return false;

        fwrite($sock, base64_encode($pass) . "\r\n");
        if (!$expect($sock, '235')) return false;

        fwrite($sock, "MAIL FROM:<$user>\r\n");
        if (!$expect($sock, '250')) return false;

        fwrite($sock, "RCPT TO:<$to>\r\n");
        if (!$expect($sock, '250')) return false;

        fwrite($sock, "DATA\r\n");
        if (!$expect($sock, '354')) return false;

        $headers = "From: Fil-Am Healthcare Solutions <$user>\r\n";
        $headers .= "To: <$to>\r\n";
        $headers .= "Subject: " . mb_encode_mimeheader($subject, 'UTF-8') . "\r\n";
        if ($replyTo !== null) {
            $headers .= "Reply-To: <$replyTo>\r\n";
        }
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        $escapedBody = preg_replace('/^\./m', '..', $body);
        $message = $headers . "\r\n" . $escapedBody . "\r\n.\r\n";

        fwrite($sock, $message);
        if (!$expect($sock, '250')) return false;

        fwrite($sock, "QUIT\r\n");
        return true;
    } finally {
        fclose($sock);
    }
}
