<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../api/db.php';

require_login();

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$field = $_GET['field'] ?? '';

$allowedFields = ['resume_path', 'passport_path', 'diploma_path', 'transcript_path', 'employment_cert_path'];

if ($type !== 'application' || $id <= 0 || !in_array($field, $allowedFields, true)) {
    http_response_code(400);
    exit('Invalid request.');
}

$stmt = get_db()->prepare("SELECT $field AS fname FROM applications WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$row = $stmt->fetch();

if (!$row || empty($row['fname'])) {
    http_response_code(404);
    exit('File not found.');
}

$path = rtrim(UPLOAD_DIR, '/') . '/applications/' . basename($row['fname']);

if (!is_file($path)) {
    http_response_code(404);
    exit('File not found on disk.');
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $path);
finfo_close($finfo);

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($row['fname']) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
