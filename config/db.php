<?php
// config/db.php
$host = 'localhost';
$user = 'root';
$pass = ''; // thay bằng password MySQL của bạn
$db_name = 'da_web';

$mysqli = new mysqli($host, $user, $pass, $db_name);
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");
