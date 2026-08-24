<?php
declare(strict_types=1);

/**
 * Sends mail via Microsoft Graph (app-only, client credentials) instead of
 * SMTP AUTH — the tenant has Security Defaults enabled, which blocks basic
 * SMTP auth entirely. Graph uses OAuth2 client credentials instead, so it
 * works without weakening the tenant's baseline security.
 */
function graph_send_mail(string $to, string $subject, string $body, ?string $replyTo = null): bool {
    if (!defined('GRAPH_TENANT_ID') || !defined('GRAPH_CLIENT_ID') || !defined('GRAPH_CLIENT_SECRET')) {
        return false;
    }

    $token = graph_get_token();
    if ($token === null) {
        return false;
    }

    $mailbox = defined('SMTP_USER') ? SMTP_USER : 'careers@fahs.us';

    $message = [
        'message' => [
            'subject' => $subject,
            'body' => ['contentType' => 'Text', 'content' => $body],
            'toRecipients' => [['emailAddress' => ['address' => $to]]],
        ],
        'saveToSentItems' => 'true',
    ];
    if ($replyTo !== null) {
        $message['message']['replyTo'] = [['emailAddress' => ['address' => $replyTo]]];
    }

    $ch = curl_init("https://graph.microsoft.com/v1.0/users/$mailbox/sendMail");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($message),
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $token",
            'Content-Type: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $status === 202;
}

function graph_get_token(): ?string {
    $tenant = GRAPH_TENANT_ID;
    $ch = curl_init("https://login.microsoftonline.com/$tenant/oauth2/v2.0/token");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type' => 'client_credentials',
            'client_id' => GRAPH_CLIENT_ID,
            'client_secret' => GRAPH_CLIENT_SECRET,
            'scope' => 'https://graph.microsoft.com/.default',
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);

    if ($resp === false) {
        return null;
    }
    $data = json_decode($resp, true);
    return $data['access_token'] ?? null;
}
