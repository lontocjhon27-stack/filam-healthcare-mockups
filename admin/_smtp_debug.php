<?php
declare(strict_types=1);
// Temporary diagnostic page. Delete after use.
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain');

require_once '/home/u536536872/domains/fahs.us/secure-config.php';

$host = 'smtp.office365.com';
$port = 587;
$user = SMTP_USER;
$pass = SMTP_PASS;

echo "Connecting to $host:$port...\n";
$sock = @stream_socket_client("tcp://$host:$port", $errno, $errstr, 15);
if (!$sock) {
    echo "CONNECT FAILED: [$errno] $errstr\n";
    exit;
}
echo "Connected.\n\n";

function readAll($sock) {
    $data = '';
    stream_set_timeout($sock, 10);
    while (($line = fgets($sock, 515)) !== false) {
        $data .= $line;
        if (isset($line[3]) && $line[3] === ' ') break;
    }
    return $data;
}

echo "S: " . readAll($sock);

fwrite($sock, "EHLO fahs.us\r\n");
echo "C: EHLO fahs.us\n";
echo "S: " . readAll($sock);

fwrite($sock, "STARTTLS\r\n");
echo "C: STARTTLS\n";
echo "S: " . readAll($sock);

if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
    echo "TLS HANDSHAKE FAILED\n";
    exit;
}
echo "TLS OK.\n\n";

fwrite($sock, "EHLO fahs.us\r\n");
echo "C: EHLO fahs.us\n";
echo "S: " . readAll($sock);

fwrite($sock, "AUTH LOGIN\r\n");
echo "C: AUTH LOGIN\n";
echo "S: " . readAll($sock);

fwrite($sock, base64_encode($user) . "\r\n");
echo "C: [base64 username]\n";
echo "S: " . readAll($sock);

fwrite($sock, base64_encode($pass) . "\r\n");
echo "C: [base64 password]\n";
echo "S: " . readAll($sock);

fwrite($sock, "QUIT\r\n");
fclose($sock);
