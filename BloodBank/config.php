<?php
// config.php - update DB credentials if needed
session_start();

$db_host = '127.0.0.1';
$db_name = 'bloodbank';
$db_user = 'root';
$db_pass = ''; // set your MySQL password

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
