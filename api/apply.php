<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

require_post();

header('Access-Control-Allow-Origin: https://fahs.us');

$firstName = clean_str($_POST['firstName'] ?? '', 100);
$lastName  = clean_str($_POST['lastName'] ?? '', 100);
$email     = clean_email($_POST['email'] ?? '');
$phone     = clean_str($_POST['phone'] ?? '', 30);
$location  = clean_str($_POST['currentLocation'] ?? '', 150);
$profession = clean_str($_POST['profession'] ?? '', 100);
$specialty  = clean_str($_POST['specialty'] ?? '', 150);
$experience = clean_str($_POST['experience'] ?? '', 50);
$nclex      = clean_str($_POST['nclexStatus'] ?? '', 50);
$education  = clean_str($_POST['education'] ?? '', 255);
$license    = clean_str($_POST['license'] ?? '', 255);

if ($firstName === '' || $lastName === '' || $email === null) {
    json_out(['ok' => false, 'error' => 'Name and a valid email are required.'], 422);
}

try {
    $resumePath   = store_upload('resume', 'applications');
    $passportPath = store_upload('passport', 'applications');
    $diplomaPath  = store_upload('diploma', 'applications');
    $torPath      = store_upload('transcriptOfRecords', 'applications');
    $certPath     = store_upload('employmentCertificate', 'applications');
} catch (RuntimeException $e) {
    json_out(['ok' => false, 'error' => $e->getMessage()], 422);
}

$db = get_db();
$stmt = $db->prepare(
    'INSERT INTO applications
     (first_name, last_name, email, phone, current_location, profession, specialty,
      experience, nclex_status, education, license_credentials,
      resume_path, passport_path, diploma_path, transcript_path, employment_cert_path)
     VALUES
     (:first_name, :last_name, :email, :phone, :current_location, :profession, :specialty,
      :experience, :nclex_status, :education, :license_credentials,
      :resume_path, :passport_path, :diploma_path, :transcript_path, :employment_cert_path)'
);
$stmt->execute([
    'first_name' => $firstName,
    'last_name' => $lastName,
    'email' => $email,
    'phone' => $phone,
    'current_location' => $location,
    'profession' => $profession,
    'specialty' => $specialty,
    'experience' => $experience,
    'nclex_status' => $nclex,
    'education' => $education,
    'license_credentials' => $license,
    'resume_path' => $resumePath,
    'passport_path' => $passportPath,
    'diploma_path' => $diplomaPath,
    'transcript_path' => $torPath,
    'employment_cert_path' => $certPath,
]);

notify_admin(
    "New Candidate Application - $firstName $lastName",
    "New candidate application received.\n\n"
    . "Name: $firstName $lastName\n"
    . "Email: $email\n"
    . "Phone: $phone\n"
    . "Profession: $profession\n"
    . "Specialty: $specialty\n"
    . "Experience: $experience\n"
    . "Current location: $location\n\n"
    . "View in admin portal: https://admin.fahs.us/index.php",
    $email
);

json_out(['ok' => true]);
