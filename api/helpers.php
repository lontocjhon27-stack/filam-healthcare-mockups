<?php
declare(strict_types=1);

require_once '/home/u536536872/domains/fahs.us/secure-config.php';

const MAX_UPLOAD_BYTES = 8 * 1024 * 1024; // 8MB per file
const ALLOWED_MIME = [
    'application/pdf'    => 'pdf',
    'application/msword'  => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'image/jpeg'          => 'jpg',
    'image/png'           => 'png',
];

function json_out(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function require_post(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_out(['ok' => false, 'error' => 'Method not allowed'], 405);
    }
}

function clean_str(?string $v, int $maxLen = 255): string {
    $v = trim((string)$v);
    $v = strip_tags($v);
    return mb_substr($v, 0, $maxLen);
}

function clean_email(?string $v): ?string {
    $v = trim((string)$v);
    $v = filter_var($v, FILTER_VALIDATE_EMAIL);
    return $v ?: null;
}

/**
 * Validates and stores an uploaded file outside the web root.
 * Returns the stored (random) filename, or null if no file was provided.
 * Throws RuntimeException on validation failure.
 */
function store_upload(string $fieldName, string $subfolder): ?string {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Upload error for $fieldName (code {$file['error']})");
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        throw new RuntimeException("$fieldName exceeds the 8MB limit");
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset(ALLOWED_MIME[$mime])) {
        throw new RuntimeException("$fieldName has an unsupported file type ($mime)");
    }

    $ext = ALLOWED_MIME[$mime];
    $dir = rtrim(UPLOAD_DIR, '/') . '/' . $subfolder . '/';
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }

    $storedName = bin2hex(random_bytes(16)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . $storedName)) {
        throw new RuntimeException("Failed to save $fieldName");
    }

    return $storedName;
}
