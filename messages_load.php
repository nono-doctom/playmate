<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    exit();
}

$me = $_SESSION['user_id'];
$other = $_SESSION['chat_with'];

$stmt = mysqli_prepare($conn,
"
SELECT *
FROM Message
WHERE
(id_user = ? AND id_user_1 = ?)
OR
(id_user = ? AND id_user_1 = ?)
ORDER BY date_mess ASC
");

mysqli_stmt_bind_param($stmt, "iiii",
    $me,
    $other,
    $other,
    $me
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while($msg = mysqli_fetch_assoc($result)) {

    $class = ($msg['id_user'] == $me)
        ? "mine"
        : "theirs";

    echo '
    <div class="message '.$class.'">
        '.htmlspecialchars($msg['contenu']).'
        <br>
        <small style="font-size:11px;opacity:0.7;">
            '.date("H:i", strtotime($msg['date_mess'])).'
        </small>
    </div>
    ';
}
?>