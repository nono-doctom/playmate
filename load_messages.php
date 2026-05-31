<?php
include 'db.php';
session_start();

$user_id = (int)$_SESSION['user_id'];
$match_id = (int)$_GET['id'];

$get = mysqli_prepare($conn,"
SELECT *
FROM Message
WHERE
(id_user=? AND id_user_1=?)
OR
(id_user=? AND id_user_1=?)
ORDER BY date_mess ASC
");

mysqli_stmt_bind_param(
    $get,
    "iiii",
    $user_id,
    $match_id,
    $match_id,
    $user_id
);

mysqli_stmt_execute($get);
$result = mysqli_stmt_get_result($get);

while($msg = mysqli_fetch_assoc($result)){

    $class = ($msg['id_user']==$user_id)
        ? 'mine'
        : 'theirs';

    echo '<div class="message '.$class.'">';
    echo htmlspecialchars($msg['contenu']);
    echo '</div>';
}
?>