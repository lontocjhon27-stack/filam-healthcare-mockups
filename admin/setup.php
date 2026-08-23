<?php
declare(strict_types=1);

// One-time admin account bootstrap. Self-disables once an account exists —
// safe to leave in place, but fine to delete afterward too.

require_once __DIR__ . '/../api/db.php';

$db = get_db();
$existing = (int)$db->query('SELECT COUNT(*) AS c FROM admin_users')->fetch()['c'];

$message = null;
$success = false;

if ($existing > 0) {
    $message = 'An admin account already exists. This setup page is now disabled.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['token'] ?? '');
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if (!hash_equals(SETUP_TOKEN, $token)) {
        $message = 'Incorrect setup token.';
    } elseif ($username === '' || strlen($password) < 8) {
        $message = 'Username is required and password must be at least 8 characters.';
    } else {
        $stmt = $db->prepare('INSERT INTO admin_users (username, password_hash) VALUES (:u, :p)');
        $stmt->execute(['u' => $username, 'p' => password_hash($password, PASSWORD_DEFAULT)]);
        $success = true;
        $message = "Admin account \"$username\" created. You can now log in — and this setup page is disabled.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Setup | Fil-Am Healthcare Solutions</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>body{font-family:sans-serif;}</style>
</head>
<body class="flex min-h-screen items-center justify-center bg-stone-50 px-5">
  <div class="w-full max-w-sm rounded-3xl bg-white p-8 shadow-xl ring-1 ring-slate-100">
    <h1 class="text-center text-xl font-bold text-blue-900">One-Time Admin Setup</h1>

    <?php if ($message): ?>
      <p class="mt-5 rounded-xl px-4 py-2.5 text-sm font-semibold <?= $success || $existing > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700' ?>">
        <?= htmlspecialchars($message) ?>
      </p>
    <?php endif; ?>

    <?php if ($existing === 0 && !$success): ?>
    <form method="post" class="mt-6 space-y-4">
      <div>
        <label class="mb-1.5 block text-sm font-semibold">Setup Token</label>
        <input type="text" name="token" required class="w-full min-h-[44px] rounded-xl border border-slate-300 px-4 py-2.5">
      </div>
      <div>
        <label class="mb-1.5 block text-sm font-semibold">Username</label>
        <input type="text" name="username" required class="w-full min-h-[44px] rounded-xl border border-slate-300 px-4 py-2.5">
      </div>
      <div>
        <label class="mb-1.5 block text-sm font-semibold">Password</label>
        <input type="password" name="password" required minlength="8" class="w-full min-h-[44px] rounded-xl border border-slate-300 px-4 py-2.5">
      </div>
      <button type="submit" class="mt-2 w-full min-h-[44px] rounded-full bg-blue-900 text-base font-semibold text-white cursor-pointer">Create Admin Account</button>
    </form>
    <?php endif; ?>

    <?php if ($success): ?>
      <a href="login.php" class="mt-5 block text-center font-semibold text-blue-900 underline">Go to Login</a>
    <?php endif; ?>
  </div>
</body>
</html>
