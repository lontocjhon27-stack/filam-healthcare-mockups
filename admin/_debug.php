<?php
// Temporary diagnostic page. Delete after use.
ini_set('display_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/plain');

echo "PHP version: " . PHP_VERSION . "\n";
echo "__FILE__: " . __FILE__ . "\n";
echo "realpath up 4: " . realpath(__DIR__ . '/../../../../') . "\n\n";

foreach ([
    '/home/u536536872',
    realpath(__DIR__ . '/../../../../'),
    realpath(__DIR__ . '/../..'),
    realpath(__DIR__ . '/../../..'),
] as $dir) {
    if ($dir === false || $dir === null) {
        continue;
    }
    echo "Listing $dir:\n";
    $entries = @scandir($dir);
    if ($entries === false) {
        echo "  (cannot read)\n";
    } else {
        foreach ($entries as $e) {
            if ($e === '.' || $e === '..') continue;
            echo "  - $e\n";
        }
    }
    echo "\n";
}

$path = '/home/u536536872/secure-config.php';
echo "Config file exists: " . (is_file($path) ? 'yes' : 'no') . "\n";
echo "Config file readable: " . (is_readable($path) ? 'yes' : 'no') . "\n";

try {
    require_once $path;
    echo "Config loaded OK\n";
    echo "DB_HOST defined: " . (defined('DB_HOST') ? 'yes (' . DB_HOST . ')' : 'no') . "\n";
    echo "DB_NAME defined: " . (defined('DB_NAME') ? 'yes (' . DB_NAME . ')' : 'no') . "\n";
    echo "DB_USER defined: " . (defined('DB_USER') ? 'yes (' . DB_USER . ')' : 'no') . "\n";
    echo "SETUP_TOKEN defined: " . (defined('SETUP_TOKEN') ? 'yes' : 'no') . "\n";
    echo "UPLOAD_DIR defined: " . (defined('UPLOAD_DIR') ? 'yes (' . UPLOAD_DIR . ')' : 'no') . "\n";
} catch (Throwable $e) {
    echo "ERROR loading config: " . $e->getMessage() . "\n";
}

try {
    require_once __DIR__ . '/../api/db.php';
    $db = get_db();
    echo "DB connection: OK\n";
    $count = $db->query('SELECT COUNT(*) AS c FROM admin_users')->fetch();
    echo "admin_users row count: " . $count['c'] . "\n";
} catch (Throwable $e) {
    echo "ERROR connecting to DB: " . $e->getMessage() . "\n";
}
