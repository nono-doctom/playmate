<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $pseudo = trim($_POST['pseudo']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if ($pseudo === '' || $email === '' || $_POST['password'] === '') {
        $error = "Tous les champs sont obligatoires";
    } else {

        // PERSONNAGE PAR DÉFAUT
        $defaultPersonnage = "Une Lionnette";

        // 1. Vérifie si le personnage existe
        $checkPerso = mysqli_query(
            $conn,
            "SELECT nom_personnage FROM personnage WHERE nom_personnage='$defaultPersonnage'"
        );

        // 2. S’il n’existe pas → on le crée
        if (mysqli_num_rows($checkPerso) === 0) {
            mysqli_query(
                $conn,
                "INSERT INTO personnage (nom_personnage, description)
                 VALUES ('$defaultPersonnage', 'Je tryhard ici c’est la victoire')"
            );
        }

        // 3. Vérifie si email existe déjà
        $checkUser = mysqli_query(
            $conn,
            "SELECT id_user FROM Utilisateur WHERE email='$email'"
        );

        if (mysqli_num_rows($checkUser) > 0) {
            $error = "Email déjà utilisé";
        } else {

            // 4. INSERT utilisateur avec personnage garanti existant
            mysqli_query(
                $conn,
                "INSERT INTO Utilisateur (pseudo, email, mot_de_passe, nom_personnage)
                 VALUES ('$pseudo', '$email', '$password', '$defaultPersonnage')"
            );

            header("Location: login.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Inscription</title>

<style>
body {
  margin: 0;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  font-family: Georgia, serif;
  background: radial-gradient(circle at 50% 50%, #00111f, #000814);
  color: white;
}

.form-container {
  width: 340px;
  padding: 30px;
  border-radius: 18px;
  background: rgba(0, 20, 40, 0.85);
  box-shadow: 0 0 30px rgba(0, 255, 255, 0.3);
  display: flex;
  flex-direction: column;
}

.form-title {
  text-align: center;
  color: #00f0ff;
  margin-bottom: 20px;
}

.form-input {
  padding: 10px;
  margin-bottom: 12px;
  border-radius: 10px;
  border: 1px solid #00eaff;
  background: rgba(0,60,100,0.4);
  color: white;
  outline: none;
}

.form-button {
  padding: 10px;
  border: none;
  border-radius: 10px;
  background: #00f0ff;
  font-weight: bold;
  cursor: pointer;
}

.form-button:hover {
  background: #00c0cc;
}

.form-text {
  text-align: center;
  margin-top: 10px;
}

.form-link {
  color: #00ffff;
  text-decoration: none;
}

.form-link:hover {
  text-decoration: underline;
}

.error {
  color: #ff6b6b;
  text-align: center;
  margin-bottom: 10px;
}
/* ===================== RESPONSIVE ===================== */

/* TABLETTE + PETIT ÉCRAN */
@media screen and (max-width: 1024px) {

  .main-content {
    margin-left: 0;
    padding: 15px;
  }

  .sidebar {
    width: 240px;
  }

  .profil-card,
  .matches-page .card,
  .interface-page .card {
    width: 90%;
    max-width: 500px;
  }
}

/* MOBILE */
@media screen and (max-width: 768px) {

  /* Sidebar passe en haut */
  .sidebar {
    position: relative;
    width: 100%;
    height: auto;
    flex-direction: row;
    justify-content: center;
    flex-wrap: wrap;
    padding: 10px;
  }

  .main-content {
    margin-left: 0;
    padding: 10px;
  }

  /* TITRES */
  body.home-page .main-title {
    font-size: 2rem;
    top: 10px;
  }

  .interface-title {
    font-size: 1.6rem;
    position: relative;
    top: 0;
    left: 0;
    transform: none;
    margin: 10px 0;
  }

  /* CARDS */
  .card {
    width: 95%;
    padding: 15px;
  }

  .profil-card {
    width: 95%;
    padding: 20px;
  }

  /* TEXTE */
  .text-container h1 {
    font-size: 1.4rem;
  }

  .text-container p {
    font-size: 1rem;
  }

  /* BOUTONS */
  .btn,
  .button-slide,
  .wp-block-button__link {
    font-size: 0.9rem;
    padding: 10px 18px;
  }

  /* BACKGROUND FIX */
  .interface-page,
  .matches-page,
  .img-background {
    background-position: center;
    background-size: cover;
  }
}

/* PETITS TÉLÉPHONES */
@media screen and (max-width: 480px) {

  .sidebar {
    flex-direction: column;
    align-items: center;
  }

  .btn {
    width: 100%;
    text-align: center;
  }

  .card {
    width: 98%;
  }

  .profil-card {
    width: 98%;
  }
}
</style>

</head>

<body>

<form method="POST" class="form-container">

  <h2 class="form-title">Créer un compte</h2>

  <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

  <input class="form-input" name="pseudo" placeholder="Pseudo">
  <input class="form-input" name="email" placeholder="Email">
  <input class="form-input" type="password" name="password" placeholder="Mot de passe">

  <button class="form-button">S’inscrire</button>

  <p class="form-text">
    Déjà inscrit ? <a class="form-link" href="login.php">Connexion</a>
  </p>

</form>

</body>
</html>
