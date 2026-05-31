<?php
include 'db.php';
include 'auth.php';
requireLogin();

$me = getUserId();
$to_user = (int)$_POST['to_user'];
$action = $_POST['action'] ?? '';

if ($action === 'like') {
    $pdo->prepare("INSERT IGNORE INTO likes (from_user, to_user) VALUES (?, ?)")->execute([$me, $to_user]);
} elseif ($action === 'dislike') {
    $pdo->prepare("INSERT IGNORE INTO dislikes (from_user, to_user) VALUES (?, ?)")->execute([$me, $to_user]);
}

// Revenir à la page suggestions pour voir la suivante
header("Location: suggestions.php");
exit;
?>
