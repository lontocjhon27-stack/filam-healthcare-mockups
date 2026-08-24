<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../api/db.php';

require_login();

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);

$tableMap = [
    'application' => 'applications',
    'inquiry' => 'employer_inquiries',
    'message' => 'contact_messages',
];

if (!isset($tableMap[$type]) || $id <= 0) {
    http_response_code(404);
    exit('Not found.');
}

$table = $tableMap[$type];
$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $newStatus = clean_status($_POST['status'] ?? 'new');
    $stmt = $db->prepare("UPDATE $table SET status = :s WHERE id = :id");
    $stmt->execute(['s' => $newStatus, 'id' => $id]);
}

function clean_status(string $s): string {
    return in_array($s, ['new', 'reviewed', 'contacted', 'closed'], true) ? $s : 'new';
}

$stmt = $db->prepare("SELECT * FROM $table WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$record = $stmt->fetch();

if (!$record) {
    http_response_code(404);
    exit('Record not found.');
}

$fileFields = [
    'resume_path' => 'Resume / CV',
    'passport_path' => 'Passport',
    'diploma_path' => 'Diploma',
    'transcript_path' => 'Transcript of Records',
    'employment_cert_path' => 'Employment Certificate',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Record Detail | Fil-Am Healthcare Solutions Admin</title>
<link rel="icon" href="../favicon.ico">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = { theme: { extend: { fontFamily: { sans: ['Poppins','sans-serif'] }, colors: { navy: '#0B3B91', red: '#B91C1C', sky: '#4FA3FF' } } } };
</script>
<style>body{font-family:'Poppins',sans-serif;}</style>
</head>
<body class="bg-stone-50 text-slate-900">

<header class="border-b border-slate-200 bg-white">
  <div class="mx-auto flex max-w-3xl items-center justify-between px-5 py-4">
    <a href="index.php" class="flex items-center gap-3 cursor-pointer">
      <img src="https://fahs.us/assets/logo-v2.png" alt="Fil-Am Healthcare Solutions" class="h-10 w-auto">
      <span class="font-sans text-lg font-bold text-navy">Admin Portal</span>
    </a>
    <a href="index.php" class="text-sm font-semibold text-navy cursor-pointer">&larr; Back to Dashboard</a>
  </div>
</header>

<main class="mx-auto max-w-3xl px-5 py-10">
  <div class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-100">

    <form method="post" class="mb-6 flex items-center gap-3">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <label class="text-sm font-semibold text-slate-600">Status:</label>
      <select name="status" onchange="this.form.submit()" class="rounded-xl border border-navy/15 px-3 py-2 text-sm font-semibold">
        <?php foreach (['new','reviewed','contacted','closed'] as $s): ?>
          <option value="<?= $s ?>" <?= $record['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
    </form>

    <?php if ($type === 'application'): ?>
      <h1 class="font-sans text-2xl font-bold text-navy"><?= htmlspecialchars($record['first_name'] . ' ' . $record['last_name']) ?></h1>
      <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div><dt class="text-xs font-bold uppercase text-slate-500">Email</dt><dd class="text-slate-800"><?= htmlspecialchars($record['email']) ?></dd></div>
        <div><dt class="text-xs font-bold uppercase text-slate-500">Phone</dt><dd class="text-slate-800"><?= htmlspecialchars($record['phone'] ?? '') ?></dd></div>
        <div><dt class="text-xs font-bold uppercase text-slate-500">Current Location</dt><dd class="text-slate-800"><?= htmlspecialchars($record['current_location'] ?? '') ?></dd></div>
        <div><dt class="text-xs font-bold uppercase text-slate-500">Profession</dt><dd class="text-slate-800"><?= htmlspecialchars($record['profession'] ?? '') ?></dd></div>
        <div><dt class="text-xs font-bold uppercase text-slate-500">Specialty</dt><dd class="text-slate-800"><?= htmlspecialchars($record['specialty'] ?? '') ?></dd></div>
        <div><dt class="text-xs font-bold uppercase text-slate-500">Years of Experience</dt><dd class="text-slate-800"><?= htmlspecialchars($record['experience'] ?? '') ?></dd></div>
        <div><dt class="text-xs font-bold uppercase text-slate-500">NCLEX Status</dt><dd class="text-slate-800"><?= htmlspecialchars($record['nclex_status'] ?? '') ?></dd></div>
        <div class="sm:col-span-2"><dt class="text-xs font-bold uppercase text-slate-500">Education</dt><dd class="text-slate-800"><?= htmlspecialchars($record['education'] ?? '') ?></dd></div>
        <div class="sm:col-span-2"><dt class="text-xs font-bold uppercase text-slate-500">License / Credentials</dt><dd class="text-slate-800"><?= htmlspecialchars($record['license_credentials'] ?? '') ?></dd></div>
      </dl>

      <h2 class="mt-8 font-sans text-lg font-bold text-navy">Documents</h2>
      <ul class="mt-3 space-y-2">
        <?php $anyFile = false; foreach ($fileFields as $field => $label): if (!empty($record[$field])): $anyFile = true; ?>
          <li>
            <a href="download.php?type=application&id=<?= (int)$record['id'] ?>&field=<?= urlencode($field) ?>" class="inline-flex items-center gap-2 font-semibold text-navy cursor-pointer">
              &#128190; <?= htmlspecialchars($label) ?>
            </a>
          </li>
        <?php endif; endforeach; if (!$anyFile): ?>
          <li class="text-slate-400">No documents uploaded.</li>
        <?php endif; ?>
      </ul>

    <?php elseif ($type === 'inquiry'): ?>
      <h1 class="font-sans text-2xl font-bold text-navy"><?= htmlspecialchars($record['company_name']) ?></h1>
      <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div><dt class="text-xs font-bold uppercase text-slate-500">Hiring Position</dt><dd class="text-slate-800"><?= htmlspecialchars($record['hiring_position'] ?? '') ?></dd></div>
        <div><dt class="text-xs font-bold uppercase text-slate-500">Staff Needed</dt><dd class="text-slate-800"><?= htmlspecialchars($record['staff_needed'] ?? '') ?></dd></div>
        <div><dt class="text-xs font-bold uppercase text-slate-500">Location</dt><dd class="text-slate-800"><?= htmlspecialchars($record['location'] ?? '') ?></dd></div>
        <div><dt class="text-xs font-bold uppercase text-slate-500">Email</dt><dd class="text-slate-800"><?= htmlspecialchars($record['email']) ?></dd></div>
        <div><dt class="text-xs font-bold uppercase text-slate-500">Phone</dt><dd class="text-slate-800"><?= htmlspecialchars($record['phone'] ?? '') ?></dd></div>
      </dl>

    <?php elseif ($type === 'message'): ?>
      <h1 class="font-sans text-2xl font-bold text-navy"><?= htmlspecialchars($record['name']) ?></h1>
      <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div><dt class="text-xs font-bold uppercase text-slate-500">Email</dt><dd class="text-slate-800"><?= htmlspecialchars($record['email']) ?></dd></div>
        <div><dt class="text-xs font-bold uppercase text-slate-500">Subject</dt><dd class="text-slate-800"><?= htmlspecialchars($record['subject'] ?? '') ?></dd></div>
        <div class="sm:col-span-2"><dt class="text-xs font-bold uppercase text-slate-500">Message</dt><dd class="whitespace-pre-wrap text-slate-800"><?= htmlspecialchars($record['message'] ?? '') ?></dd></div>
      </dl>
    <?php endif; ?>

    <p class="mt-8 text-xs text-slate-400">Submitted <?= htmlspecialchars($record['submitted_at']) ?></p>
  </div>
</main>
</body>
</html>
