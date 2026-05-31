<?php
include 'auth.php';
requireLogin();
include 'db.php';

$current = getUserId();
$to_user = (int)($_GET['id'] ?? 0);

if ($to_user > 0) {
    $stmt = mysqli_prepare($conn, "
        INSERT INTO Matcher (id_user, id_user_1, avis) 
        VALUES (?, ?, 'dislike')
        ON DUPLICATE KEY UPDATE avis = 'dislike'
    ");
    mysqli_stmt_bind_param($stmt, "ii", $current, $to_user);
    mysqli_stmt_execute($stmt);
}

header('Location: interface.php');
exit;
?>
