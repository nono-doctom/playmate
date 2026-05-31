<?php
// auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Démarre la session si nécessaire
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}
?>