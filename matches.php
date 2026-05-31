<?php
include 'db.php';
session_start();

include 'musique.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

/* MATCHS RÉCIPROQUES */
$stmt = mysqli_prepare($conn, "
    SELECT u.id_user, u.pseudo, u.bio
    FROM Utilisateur u
    INNER JOIN Matcher m1 
        ON u.id_user = m1.id_user_1
    INNER JOIN Matcher m2 
        ON u.id_user = m2.id_user
    WHERE m1.id_user = ?
      AND m1.avis = 'like'
      AND m2.id_user_1 = ?
      AND m2.avis = 'like'
      AND u.id_user != ?
");

mysqli_stmt_bind_param($stmt, "iii", $user_id, $user_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$matches = [];
while ($row = mysqli_fetch_assoc($result)) {
    $matches[] = $row;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Mes matchs</title>

<style>
body{
    font-family: Georgia;
    background: radial-gradient(circle at top, #00111f, #000814);
    color: white;
    margin: 0;
    padding: 20px;
}

h2{
    text-align: center;
    color: #00f0ff;
}

.card{
    background: rgba(0,20,40,0.85);
    padding: 20px;
    margin: 15px auto;
    border-radius: 15px;
    max-width: 500px;
    box-shadow: 0 0 15px #00f0ff;
}

.btn{
    display: inline-block;
    padding: 10px 15px;
    background: #00f0ff;
    color: black;
    text-decoration: none;
    border-radius: 10px;
    margin-top: 10px;
}

.btn:hover{
    background: #00c0cc;
    color: white;
}

.empty{
    text-align: center;
    opacity: 0.7;
    margin-top: 50px;
}

a.back{
    display:block;
    text-align:center;
    margin-top:30px;
    color:#00ffff;
}
</style>
</head>

<body>

<h2>Mes matchs</h2>

<?php if (!empty($matches)): ?>

    <?php foreach ($matches as $match): ?>

        <div class="card">
            <h3><?= htmlspecialchars($match['pseudo']) ?></h3>
            <p><?= htmlspecialchars($match['bio'] ?? 'Pas de bio') ?></p>

            <!-- 💬 CHAT CORRECT -->
            <a class="btn" href="chat.php?id=<?= (int)$match['id_user'] ?>">
                Parler
            </a>
        </div>

    <?php endforeach; ?>

<?php else: ?>
    <p class="empty">Vous n'avez pas encore de matchs.</p>
<?php endif; ?>

<a class="back" href="interface.php">Trouvez d'autres joueur</a>

</body>
</html>