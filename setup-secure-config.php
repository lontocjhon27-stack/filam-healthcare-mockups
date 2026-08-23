<?php
declare(strict_types=1);

// One-time helper: writes /home/u536536872/secure-config.php from a form,
// so we don't depend on the file manager UI. Self-disables once that file
// exists — refuses to overwrite it. Safe to delete after use.

const WRITER_TOKEN = 'tAc4aKJeoax0fDhoO5Gg0VaAvptRUQvb';
const TARGET_PATH = '/home/u536536872/secure-config.php';

$alreadyExists = is_file(TARGET_PATH);
$message = null;
$success = false;

if ($alreadyExists) {
    $message = 'secure-config.php already exists on the server. This tool refuses to overwrite it — delete the existing file first if you really need to redo this.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['token'] ?? '');
    if (!hash_equals(WRITER_TOKEN, $token)) {
        $message = 'Incorrect token.';
    } else {
        $dbHost = trim((string)($_POST['db_host'] ?? 'localhost'));
        $dbName = trim((string)($_POST['db_name'] ?? ''));
        $dbUser = trim((string)($_POST['db_user'] ?? ''));
        $dbPass = (string)($_POST['db_pass'] ?? '');
        $setupToken = trim((string)($_POST['setup_token'] ?? ''));
        $uploadDir = trim((string)($_POST['upload_dir'] ?? '/home/u536536872/secure-uploads'));

        if ($dbName === '' || $dbUser === '' || $dbPass === '' || $setupToken === '') {
            $message = 'All fields are required.';
        } else {
            $php = "<?php\n"
                . "define('DB_HOST', " . var_export($dbHost, true) . ");\n"
                . "define('DB_NAME', " . var_export($dbName, true) . ");\n"
                . "define('DB_USER', " . var_export($dbUser, true) . ");\n"
                . "define('DB_PASS', " . var_export($dbPass, true) . ");\n"
                . "define('SETUP_TOKEN', " . var_export($setupToken, true) . ");\n"
                . "define('UPLOAD_DIR', " . var_export($uploadDir, true) . ");\n";

            if (file_put_contents(TARGET_PATH, $php) === false) {
                $message = 'Failed to write the file. The web server user may not have write access to ' . dirname(TARGET_PATH) . '.';
            } else {
                @chmod(TARGET_PATH, 0600);
                $success = true;
                $message = 'secure-config.php created successfully. You can now delete this setup-secure-config.php file.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Config Writer (One-Time Setup)</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-stone-50 px-5">
  <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-xl ring-1 ring-slate-100">
    <h1 class="text-center text-xl font-bold text-blue-900">Create secure-config.php</h1>
    <p class="mt-1 text-center text-xs text-slate-500">Writes to <?= htmlspecialchars(TARGET_PATH) ?></p>

    <?php if ($message): ?>
      <p class="mt-5 rounded-xl px-4 py-2.5 text-sm font-semibold <?= $success || $alreadyExists ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700' ?>">
        <?= htmlspecialchars($message) ?>
      </p>
    <?php endif; ?>

    <?php if (!$alreadyExists && !$success): ?>
    <form method="post" class="mt-6 space-y-4">
      <div>
        <label class="mb-1 block text-sm font-semibold">Writer Token</label>
        <input type="text" name="token" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5">
      </div>
      <div>
        <label class="mb-1 block text-sm font-semibold">DB Host</label>
        <input type="text" name="db_host" value="localhost" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5">
      </div>
      <div>
        <label class="mb-1 block text-sm font-semibold">DB Name</label>
        <input type="text" name="db_name" value="u536536872_admin" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5">
      </div>
      <div>
        <label class="mb-1 block text-sm font-semibold">DB User</label>
        <input type="text" name="db_user" value="u536536872_admin" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5">
      </div>
      <div>
        <label class="mb-1 block text-sm font-semibold">DB Password</label>
        <input type="password" name="db_pass" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5">
      </div>
      <div>
        <label class="mb-1 block text-sm font-semibold">Admin Setup Token (for /admin/setup.php)</label>
        <input type="text" name="setup_token" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5">
      </div>
      <div>
        <label class="mb-1 block text-sm font-semibold">Upload Directory</label>
        <input type="text" name="upload_dir" value="/home/u536536872/secure-uploads" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5">
      </div>
      <button type="submit" class="w-full rounded-full bg-blue-900 py-3 font-semibold text-white cursor-pointer">Write Config File</button>
    </form>
    <?php endif; ?>
  </div>
</body>
</html>
