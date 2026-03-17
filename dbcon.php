<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lab-automation');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die('<div style="font-family:sans-serif;padding:30px;background:#fff5f5;color:#b91c1c;border-radius:8px;margin:20px;">
        <strong>Database Connection Failed:</strong> ' . mysqli_connect_error() . '
        <br><small>Check credentials in dbcon.php &mdash; DB name should be <strong>lab_automation</strong></small></div>');
}
mysqli_set_charset($conn, 'utf8mb4');
?>
