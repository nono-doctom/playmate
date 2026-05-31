<?php
include 'db.php';
include 'auth.php';
requireLogin();

$current = getUserId();
$to_user = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($to_user > 0 && $to_user !== $current) {
    // Marquer le profil comme "dislike"
    $stmt = mysqli_prepare($conn, "
        INSERT INTO Matcher (id_user, id_user_1, avis) 
        VALUES (?, ?, 'dislike')
        ON DUPLICATE KEY UPDATE avis = 'dislike'
    ");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $current, $to_user);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// 🔹 Récupérer le prochain profil disponible
$stmt = mysqli_prepare($conn, "
    SELECT u.*, 
           GROUP_CONCAT(DISTINCT j.nom) AS games_list,
           GROUP_CONCAT(DISTINCT p.libelle) AS plateformes
    FROM Utilisateur u
    LEFT JOIN jouer jo ON jo.id_user = u.id_user
    LEFT JOIN jeu j ON jo.id_jeu = j.id_jeu
    LEFT JOIN utiliser ut ON ut.id_user = u.id_user
    LEFT JOIN plateforme p ON ut.id_plateforme = p.id_plateforme
    WHERE u.id_user != ? 
      AND u.id_user NOT IN (SELECT id_user_1 FROM Matcher WHERE id_user = ?)
    GROUP BY u.id_user
    ORDER BY u.id_user DESC
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "ii", $current, $current);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$profil = mysqli_fetch_assoc($result);

// 🔹 Redirection vers interface pour afficher le prochain profil
if ($profil) {
    // Passe au prochain profil automatiquement
    $_SESSION['next_profil_id'] = $profil['id_user']; // optionnel si tu veux stocker
    header("Location: interface.php");
} else {
    echo "Aucun autre joueur disponible.";
}
exit;
?>