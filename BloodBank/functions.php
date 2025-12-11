<?php
require_once 'config.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user() {
    global $pdo;
    if (!is_logged_in()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function require_role($role) {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
    $user = current_user();
    if (!$user || $user['role'] !== $role) {
        die("Access denied. This page is for $role only.");
    }
}

/**
 * can_receive_from(receiver_blood, donor_blood)
 * Returns true if a receiver with blood group $receiver can receive from donor $donor.
 */
function can_receive_from($receiver, $donor) {
    $r = strtoupper(trim($receiver));
    $d = strtoupper(trim($donor));
    if ($r === $d) return true;

    $r_rh = (strpos($r, '+') !== false) ? '+' : ((strpos($r, '-') !== false) ? '-' : null);
    $d_rh = (strpos($d, '+') !== false) ? '+' : ((strpos($d, '-') !== false) ? '-' : null);

    if ($r_rh === '-' && $d_rh === '+') return false;

    $map = [
        'O' => ['O'],
        'A' => ['A','O'],
        'B' => ['B','O'],
        'AB'=> ['A','B','AB','O']
    ];

    $r_abo = str_replace(['+','-'],'',$r);
    $d_abo = str_replace(['+','-'],'',$d);

    if (!isset($map[$r_abo])) return false;
    if (!in_array($d_abo, $map[$r_abo])) return false;

    return true;
}
