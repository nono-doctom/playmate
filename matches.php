<?php
include 'db.php';
session_start();

/* =========================
   MUSIQUE (OPTIONNEL)
========================= */
include 'musique.php';

/* =========================
   VÉRIF CONNEXION
========================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* =========================
   ID UTILISATEUR CONNECTÉ
========================= */
$user_id = (int)$_SESSION['user_id'];

/* =========================
   REQUÊTE : MATCHS RÉCIPROQUES
   (LIKE MUTUEL = MATCH)
========================= */

/*
   On cherche les utilisateurs :
   - que tu as likés (m1)
   - ET qui t'ont liké aussi (m2)
   => donc un match réciproque
*/
$stmt = mysqli_prepare($conn, "
    SELECT u.id_user, u.pseudo, u.bio
    FROM Utilisateur u

    -- tu as liké cette personne
    INNER JOIN Matcher m1 
        ON u.id_user = m1.id_user_1

    -- cette personne t’a liké
    INNER JOIN Matcher m2 
        ON u.id_user = m2.id_user

    WHERE m1.id_user = ?
      AND m1.avis = 'like'
      AND m2.id_user_1 = ?
      AND m2.avis = 'like'
      AND u.id_user != ?
");

/* injection sécurisée des paramètres */
mysqli_stmt_bind_param($stmt, "iii", $user_id, $user_id, $user_id);

/* exécution */
mysqli_stmt_execute($stmt);

/* récupération résultat */
$result = mysqli_stmt_get_result($stmt);

/* stockage des matchs */
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
body {
    font-family: Georgia, serif;
    background: radial-gradient(circle at top, #00111f, #000814);
    color: white;
    margin: 0;
    padding: 20px;
}

/* TITRE */
h2 {
    text-align: center;
    color: #00f0ff;
    font-size: clamp(1.4rem, 3vw, 2rem);
}

/* CARD MATCH */
.card {
    background: rgba(0,20,40,0.85);
    padding: 20px;
    margin: 15px auto;
    border-radius: 15px;

    width: 100%;
    max-width: 520px;

    box-shadow: 0 0 15px rgba(0, 240, 255, 0.4);
    transition: transform 0.2s ease;
}

.card:hover {
    transform: translateY(-3px);
}

/* BOUTON */
.btn {
    display: inline-block;
    padding: 10px 16px;

    background: #00f0ff;
    color: black;

    text-decoration: none;
    border-radius: 10px;

    margin-top: 10px;
    transition: 0.3s;
}

.btn:hover {
    background: #00c0cc;
    color: white;
}

/* MESSAGE VIDE */
.empty {
    text-align: center;
    opacity: 0.7;
    margin-top: 50px;
}

/* RETOUR */
a.back {
    display: block;
    text-align: center;
    margin-top: 30px;
    color: #00ffff;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .card {
        width: 90%;
    }

    .btn {
        display: block;
        width: 100%;
        text-align: center;
    }
}
</style>
</head>

<body>

<h2>Mes matchs</h2>

<?php if (!empty($matches)): ?>

    <!-- boucle des matchs -->
    <?php foreach ($matches as $match): ?>

        <div class="card">

            <!-- pseudo utilisateur -->
            <h3><?= htmlspecialchars($match['pseudo']) ?></h3>

            <!-- bio utilisateur -->
            <p><?= htmlspecialchars($match['bio'] ?? 'Pas de bio') ?></p>

            <!-- bouton chat -->
            <a class="btn" href="chat.php?id=<?= (int)$match['id_user'] ?>">
                Parler
            </a>

        </div>

    <?php endforeach; ?>

<?php else: ?>

    <!-- aucun match -->
    <p class="empty">Vous n'avez pas encore de matchs.</p>

<?php endif; ?>

<!-- retour -->
<a class="back" href="interface.php">Trouvez d'autres joueur</a>

</body>
</html>
