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
<title>Connexion</title>

<style>

/* ===== RESET IDENTIQUE LOCAL ===== */
body {
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: Georgia, serif;
    background: radial-gradient(circle at 50% 50%, #00111f, #000814);
    color: white;
}

/* ===== FORM EXACTEMENT MÊME TAILLE QUE LOCAL ===== */
.form-container {
    background: rgba(0, 20, 40, 0.85);
    padding: 40px 30px;
    border-radius: 20px;

    width: 340px;        /* IMPORTANT : même largeur que Vue local */
    max-width: 340px;    /* verrouille la taille */
    
    box-shadow: 0 0 40px rgba(0, 255, 255, 0.4);
    border: 1px solid rgba(0, 255, 255, 0.6);
    backdrop-filter: blur(10px);
}

/* TITRE */
.form-title {
    text-align: center;
    color: #00f0ff;
    margin-bottom: 25px;
    text-shadow: 0 0 10px #00ffff;
}

/* INPUT */
.form-input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 12px;
    border: 1px solid #00eaff;
    background: rgba(0, 60, 100, 0.4);
    color: white;
    outline: none;
    box-sizing: border-box;
}

/* BUTTON */
.form-button {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 12px;
    background: #00f0ff;
    color: black;
    font-weight: bold;
    cursor: pointer;
}

.form-button:hover {
    background: #00c0cc;
    color: white;
}

/* ERROR */
.error-msg {
    color: red;
    text-align: center;
    margin-bottom: 10px;
}

/* TEXT */
.form-text {
    text-align: center;
    margin-top: 15px;
    font-size: 14px;
}

.form-link {
    color: #00ffff;
    text-decoration: none;
}

.form-link:hover {
    text-decoration: underline;
}
/* ===================== RESPONSIVE ===================== */
@media screen and (max-width: 1024px) {
  .form-container {
    width: 380px;
  }
}

@media screen and (max-width: 768px) {

  body {
    padding: 15px;
  }

  .form-container {
    width: 100%;
    max-width: 380px;
    padding: 30px 20px;
    border-radius: 16px;
  }

  .form-title {
    font-size: 1.5rem;
  }

  .form-input {
    font-size: 0.95rem;
    padding: 11px;
  }

  .form-button {
    font-size: 0.95rem;
    padding: 11px;
  }

  .form-text {
    font-size: 0.9rem;
  }
}

@media screen and (max-width: 480px) {

  .form-container {
    width: 95%;
    padding: 22px 15px;
  }

  .form-title {
    font-size: 1.3rem;
  }

  .form-input {
    font-size: 0.9rem;
  }
}
</style>

</head>

<body>

<div class="form-container">

    <h2 class="form-title">Connexion</h2>

    <?php if($error): ?>
        <div class="error-msg">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <input class="form-input" type="email" name="email" placeholder="Email" required>

        <input class="form-input" type="password" name="password" placeholder="Mot de passe" required>

        <button class="form-button" type="submit">
            Se connecter
        </button>

    </form>

    <p class="form-text">
        Pas encore inscrit ?
        <a class="form-link" href="inscrire.php">Créer un compte</a>
    </p>

</div>

</body>
</html>
