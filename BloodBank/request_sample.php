<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: available_samples.php');
    exit;
}

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user = current_user();
if ($user['role'] !== 'receiver') {
    die('Only receivers can request samples.');
}

$sample_id = intval($_POST['sample_id'] ?? 0);
if (!$sample_id) die('Invalid sample.');

$stmt = $pdo->prepare('SELECT s.*, u.name as hospital_name FROM blood_samples s JOIN users u ON s.hospital_id = u.id WHERE s.id = ?');
$stmt->execute([$sample_id]);
$sample = $stmt->fetch();
if (!$sample) die('Sample not found.');

if (!can_receive_from($user['blood_group'], $sample['blood_group'])) {
    die('You are not compatible to receive this blood group.');
}

$stmt = $pdo->prepare('SELECT id FROM requests WHERE sample_id = ? AND receiver_id = ? AND status = "pending"');
$stmt->execute([$sample_id, $user['id']]);
if ($stmt->fetch()) {
    header('Location: available_samples.php?msg=already_requested');
    exit;
}

$stmt = $pdo->prepare('INSERT INTO requests (sample_id, hospital_id, receiver_id, message) VALUES (?,?,?,?)');
$stmt->execute([$sample_id, $sample['hospital_id'], $user['id'], null]);

header('Location: available_samples.php?msg=requested');
exit;
