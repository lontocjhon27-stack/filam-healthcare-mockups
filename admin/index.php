<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../api/db.php';

require_login();

$db = get_db();
$applications = $db->query('SELECT id, first_name, last_name, email, profession, status, submitted_at FROM applications ORDER BY submitted_at DESC LIMIT 100')->fetchAll();
$inquiries = $db->query('SELECT id, company_name, hiring_position, email, status, submitted_at FROM employer_inquiries ORDER BY submitted_at DESC LIMIT 100')->fetchAll();
$messages = $db->query('SELECT id, name, email, subject, status, submitted_at FROM contact_messages ORDER BY submitted_at DESC LIMIT 100')->fetchAll();

function status_badge(string $status): string {
    $colors = [
        'new' => 'bg-sky/15 text-navy',
        'reviewed' => 'bg-amber-100 text-amber-800',
        'contacted' => 'bg-emerald-100 text-emerald-800',
        'closed' => 'bg-slate-100 text-slate-600',
    ];
    $cls = $colors[$status] ?? $colors['new'];
    return '<span class="inline-block rounded-full px-3 py-1 text-xs font-bold ' . $cls . '">' . htmlspecialchars(ucfirst($status)) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard | Fil-Am Healthcare Solutions</title>
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
  <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-8">
    <div class="flex items-center gap-3">
      <img src="https://fahs.us/assets/logo-v2.png" alt="Fil-Am Healthcare Solutions" class="h-10 w-auto">
      <span class="font-sans text-lg font-bold text-navy">Admin Portal</span>
    </div>
    <div class="flex items-center gap-4 text-sm">
      <span class="text-slate-600">Signed in as <strong><?= htmlspecialchars($_SESSION['admin_username'] ?? '') ?></strong></span>
      <a href="logout.php" class="rounded-full border border-navy/20 px-4 py-2 font-semibold text-navy cursor-pointer">Log Out</a>
    </div>
  </div>
</header>

<main class="mx-auto max-w-7xl px-5 py-10 sm:px-8">

  <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
      <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Applications</p>
      <p class="mt-2 font-sans text-3xl font-extrabold text-navy"><?= count($applications) ?></p>
    </div>
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
      <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Employer Inquiries</p>
      <p class="mt-2 font-sans text-3xl font-extrabold text-navy"><?= count($inquiries) ?></p>
    </div>
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
      <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Contact Messages</p>
      <p class="mt-2 font-sans text-3xl font-extrabold text-navy"><?= count($messages) ?></p>
    </div>
  </div>

  <!-- Candidate Applications -->
  <section class="mt-10">
    <h2 class="font-sans text-xl font-bold text-navy">Candidate Applications</h2>
    <div class="mt-4 overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
      <table class="w-full min-w-[600px] text-left text-sm">
        <thead class="border-b border-slate-100 text-xs font-bold uppercase tracking-wide text-slate-500">
          <tr><th class="px-5 py-3">Name</th><th class="px-5 py-3">Email</th><th class="px-5 py-3">Profession</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Submitted</th><th class="px-5 py-3"></th></tr>
        </thead>
        <tbody>
          <?php if (!$applications): ?>
            <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">No applications yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($applications as $a): ?>
          <tr class="border-b border-slate-50 last:border-0">
            <td class="px-5 py-3 font-semibold"><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></td>
            <td class="px-5 py-3 text-slate-600"><?= htmlspecialchars($a['email']) ?></td>
            <td class="px-5 py-3 text-slate-600"><?= htmlspecialchars($a['profession'] ?? '') ?></td>
            <td class="px-5 py-3"><?= status_badge($a['status']) ?></td>
            <td class="px-5 py-3 text-slate-500"><?= htmlspecialchars($a['submitted_at']) ?></td>
            <td class="px-5 py-3"><a href="view.php?type=application&id=<?= (int)$a['id'] ?>" class="font-bold text-navy cursor-pointer">View</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Employer Inquiries -->
  <section class="mt-10">
    <h2 class="font-sans text-xl font-bold text-navy">Employer Inquiries</h2>
    <div class="mt-4 overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
      <table class="w-full min-w-[600px] text-left text-sm">
        <thead class="border-b border-slate-100 text-xs font-bold uppercase tracking-wide text-slate-500">
          <tr><th class="px-5 py-3">Company</th><th class="px-5 py-3">Position</th><th class="px-5 py-3">Email</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Submitted</th><th class="px-5 py-3"></th></tr>
        </thead>
        <tbody>
          <?php if (!$inquiries): ?>
            <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">No inquiries yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($inquiries as $i): ?>
          <tr class="border-b border-slate-50 last:border-0">
            <td class="px-5 py-3 font-semibold"><?= htmlspecialchars($i['company_name']) ?></td>
            <td class="px-5 py-3 text-slate-600"><?= htmlspecialchars($i['hiring_position'] ?? '') ?></td>
            <td class="px-5 py-3 text-slate-600"><?= htmlspecialchars($i['email']) ?></td>
            <td class="px-5 py-3"><?= status_badge($i['status']) ?></td>
            <td class="px-5 py-3 text-slate-500"><?= htmlspecialchars($i['submitted_at']) ?></td>
            <td class="px-5 py-3"><a href="view.php?type=inquiry&id=<?= (int)$i['id'] ?>" class="font-bold text-navy cursor-pointer">View</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Contact Messages -->
  <section class="mt-10 mb-16">
    <h2 class="font-sans text-xl font-bold text-navy">Contact Messages</h2>
    <div class="mt-4 overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
      <table class="w-full min-w-[600px] text-left text-sm">
        <thead class="border-b border-slate-100 text-xs font-bold uppercase tracking-wide text-slate-500">
          <tr><th class="px-5 py-3">Name</th><th class="px-5 py-3">Email</th><th class="px-5 py-3">Subject</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Submitted</th><th class="px-5 py-3"></th></tr>
        </thead>
        <tbody>
          <?php if (!$messages): ?>
            <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">No messages yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($messages as $m): ?>
          <tr class="border-b border-slate-50 last:border-0">
            <td class="px-5 py-3 font-semibold"><?= htmlspecialchars($m['name']) ?></td>
            <td class="px-5 py-3 text-slate-600"><?= htmlspecialchars($m['email']) ?></td>
            <td class="px-5 py-3 text-slate-600"><?= htmlspecialchars($m['subject'] ?? '') ?></td>
            <td class="px-5 py-3"><?= status_badge($m['status']) ?></td>
            <td class="px-5 py-3 text-slate-500"><?= htmlspecialchars($m['submitted_at']) ?></td>
            <td class="px-5 py-3"><a href="view.php?type=message&id=<?= (int)$m['id'] ?>" class="font-bold text-navy cursor-pointer">View</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

</main>
</body>
</html>
