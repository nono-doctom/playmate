<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    exit();
}

$from = $_SESSION['user_id'];
$to = intval($_POST['to']);
$content = trim($_POST['content']);

if ($content == "") {
    exit();
}

$stmt = mysqli_prepare($conn,
"INSERT INTO Message(contenu, id_user, id_user_1)
 VALUES (?, ?, ?)");

mysqli_stmt_bind_param($stmt, "sii",
    $content,
    $from,
    $to
);

mysqli_stmt_execute($stmt);

echo "ok";
?>