<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

require_post();

header('Access-Control-Allow-Origin: https://fahs.us');

$company  = clean_str($_POST['company'] ?? '', 150);
$position = clean_str($_POST['position'] ?? '', 150);
$staff    = clean_str($_POST['staffCount'] ?? '', 20);
$location = clean_str($_POST['location'] ?? '', 150);
$email    = clean_email($_POST['email'] ?? '');
$phone    = clean_str($_POST['phone'] ?? '', 30);

if ($company === '' || $email === null) {
    json_out(['ok' => false, 'error' => 'Company name and a valid email are required.'], 422);
}

$db = get_db();
$stmt = $db->prepare(
    'INSERT INTO employer_inquiries (company_name, hiring_position, staff_needed, location, email, phone)
     VALUES (:company_name, :hiring_position, :staff_needed, :location, :email, :phone)'
);
$stmt->execute([
    'company_name' => $company,
    'hiring_position' => $position,
    'staff_needed' => $staff,
    'location' => $location,
    'email' => $email,
    'phone' => $phone,
]);

json_out(['ok' => true]);
