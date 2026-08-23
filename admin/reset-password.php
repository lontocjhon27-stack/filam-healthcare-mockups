<?php
declare(strict_types=1);

// One-time password reset tool, gated by the same SETUP_TOKEN used for
// initial admin setup. Delete this file after use.

require_once __DIR__ . '/../api/db.php';

$db = get_db();
$message = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['token'] ?? '');
    $username = trim((string)($_POST['username'] ?? 'admin'));
    $newPassword = (string)($_POST['password'] ?? '');

    if (!hash_equals(SETUP_TOKEN, $token)) {
        $message = 'Incorrect token.';
    } elseif ($username === '' || strlen($newPassword) < 8) {
        $message = 'Username is required and password must be at least 8 characters.';
    } else {
        $stmt = $db->prepare('UPDATE admin_users SET password_hash = :p WHERE username = :u');
        $stmt->execute([
            'p' => password_hash($newPassword, PASSWORD_DEFAULT),
            'u' => $username,
        ]);
        if ($stmt->rowCount() > 0) {
            $success = true;
            $message = "Password updated for \"$username\". Delete this file now.";
        } else {
            $message = "No admin account found with username \"$username\".";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Admin Password (One-Time)</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-stone-50 px-5">
  <div class="w-full max-w-sm rounded-3xl bg-white p-8 shadow-xl ring-1 ring-slate-100">
    <h1 class="text-center text-xl font-bold text-blue-900">Reset Admin Password</h1>

    <?php if ($message): ?>
      <p class="mt-5 rounded-xl px-4 py-2.5 text-sm font-semibold <?= $success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700' ?>">
        <?= htmlspecialchars($message) ?>
      </p>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="post" class="mt-6 space-y-4">
      <div>
        <label class="mb-1 block text-sm font-semibold">Setup Token</label>
        <input type="text" name="token" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5">
      </div>
      <div>
        <label class="mb-1 block text-sm font-semibold">Username</label>
        <input type="text" name="username" value="admin" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5">
      </div>
      <div>
        <label class="mb-1 block text-sm font-semibold">New Password</label>
        <input type="password" name="password" required minlength="8" class="w-full rounded-xl border border-slate-300 px-4 py-2.5">
      </div>
      <button type="submit" class="w-full rounded-full bg-blue-900 py-3 font-semibold text-white cursor-pointer">Update Password</button>
    </form>
    <?php endif; ?>
  </div>
</body>
</html>
