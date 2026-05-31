<?php
session_start();
include 'db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUser($id = null) {
    global $conn;
    if (!$id) $id = getUserId();
    $stmt = mysqli_prepare($conn, "SELECT * FROM Utilisateur WHERE id_user = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}
?>
