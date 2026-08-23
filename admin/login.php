<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../api/db.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $error = 'Session expired. Please try again.';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        $stmt = get_db()->prepare('SELECT id, password_hash FROM admin_users WHERE username = :u LIMIT 1');
        $stmt->execute(['u' => $username]);
        $row = $stmt->fetch();

        if ($row && password_verify($password, $row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_username'] = $username;
            header('Location: index.php');
            exit;
        }
        $error = 'Incorrect username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login | Fil-Am Healthcare Solutions</title>
<link rel="icon" href="../favicon.ico">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = { theme: { extend: { fontFamily: { sans: ['Poppins','sans-serif'] }, colors: { navy: '#0B3B91', red: '#B91C1C', sky: '#4FA3FF' } } } };
</script>
<style>body{font-family:'Poppins',sans-serif;}</style>
</head>
<body class="flex min-h-screen items-center justify-center bg-stone-50 px-5">
  <div class="w-full max-w-sm rounded-3xl bg-white p-8 shadow-xl ring-1 ring-slate-100">
    <img src="../assets/logo.png" alt="Fil-Am Healthcare Solutions" class="mx-auto h-14 w-auto">
    <h1 class="mt-6 text-center font-sans text-xl font-bold text-navy">Admin Login</h1>
    <p class="mt-1 text-center text-sm text-slate-500">Internal team access only.</p>

    <?php if ($error): ?>
      <p class="mt-5 rounded-xl bg-red/10 px-4 py-2.5 text-sm font-semibold text-red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" class="mt-6 space-y-4">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <div>
        <label class="mb-1.5 block text-sm font-semibold text-slate-800">Username</label>
        <input type="text" name="username" required autofocus class="w-full min-h-[44px] rounded-xl border border-navy/15 px-4 py-2.5 text-slate-900">
      </div>
      <div>
        <label class="mb-1.5 block text-sm font-semibold text-slate-800">Password</label>
        <input type="password" name="password" required class="w-full min-h-[44px] rounded-xl border border-navy/15 px-4 py-2.5 text-slate-900">
      </div>
      <button type="submit" class="mt-2 w-full min-h-[44px] rounded-full bg-navy text-base font-semibold text-white shadow-lg shadow-navy/30 cursor-pointer">Log In</button>
    </form>
  </div>
</body>
</html>
