<?php
require_once 'functions.php';
require_role('hospital');
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: hospital_view_requests.php');
    exit;
}

$request_id = intval($_POST['request_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$request_id || !in_array($action, ['approve','reject'])) {
    die('Invalid.');
}

$stmt = $pdo->prepare('SELECT * FROM requests WHERE id = ? AND hospital_id = ?');
$stmt->execute([$request_id, $user['id']]);
$r = $stmt->fetch();
if (!$r) die('Request not found.');

if ($action === 'approve') {
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT units FROM blood_samples WHERE id = ? FOR UPDATE');
        $stmt->execute([$r['sample_id']]);
        $s = $stmt->fetch();
        if (!$s || $s['units'] <= 0) {
            throw new Exception('No units left.');
        }
        $stmt = $pdo->prepare('UPDATE blood_samples SET units = units - 1 WHERE id = ?');
        $stmt->execute([$r['sample_id']]);

        $stmt = $pdo->prepare('UPDATE requests SET status = "approved" WHERE id = ?');
        $stmt->execute([$r['id']]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die('Error: ' . $e->getMessage());
    }
} else {
    $stmt = $pdo->prepare('UPDATE requests SET status = "rejected" WHERE id = ?');
    $stmt->execute([$r['id']]);
}

header('Location: hospital_view_requests.php');
exit;
