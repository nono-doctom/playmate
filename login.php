<?php
include 'db.php';
session_start();

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($email === '' || $password === '') {
        $error = "Veuillez remplir tous les champs.";
    } else {

        $stmt = mysqli_prepare($conn,"
            SELECT id_user, mot_de_passe, pseudo
            FROM Utilisateur
            WHERE email = ?
        ");

        mysqli_stmt_bind_param($stmt,"s",$email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['mot_de_passe'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = (int)$user['id_user'];
            $_SESSION['pseudo'] = $user['pseudo'];

            header("Location: profil.php");
            exit();

        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Connexion - PlayMate</title>
<link rel="stylesheet" href="style.css">
</head>

<body class="body-form">

<form method="POST" class="form-container">

    <h2 class="form-title">
        Connexion
    </h2>

    <?php if($error): ?>
        <div class="error-msg">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <input
        type="email"
        name="email"
        placeholder="Email"
        class="form-input"
        required
    >

    <input
        type="password"
        name="password"
        placeholder="Mot de passe"
        class="form-input"
        required
    >

    <button
        type="submit"
        class="form-button"
    >
        Se connecter
    </button>

    <p class="form-text">
        Pas encore inscrit ?
        <a href="inscrire.php" class="form-link">
            Créer un compte
        </a>
    </p>

</form>

</body>
</html>