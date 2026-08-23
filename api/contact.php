<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

require_post();

header('Access-Control-Allow-Origin: https://fahs.us');

$name    = clean_str($_POST['name'] ?? '', 150);
$email   = clean_email($_POST['email'] ?? '');
$subject = clean_str($_POST['subject'] ?? '', 100);
$message = clean_str($_POST['message'] ?? '', 5000);

if ($name === '' || $email === null) {
    json_out(['ok' => false, 'error' => 'Name and a valid email are required.'], 422);
}

$db = get_db();
$stmt = $db->prepare(
    'INSERT INTO contact_messages (name, email, subject, message)
     VALUES (:name, :email, :subject, :message)'
);
$stmt->execute([
    'name' => $name,
    'email' => $email,
    'subject' => $subject,
    'message' => $message,
]);

json_out(['ok' => true]);
