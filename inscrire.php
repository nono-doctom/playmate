<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* =========================
       CLEAN INPUTS
    ========================= */
    $pseudo = trim($_POST['pseudo']);
    $email = trim($_POST['email']);
    $plainPassword = $_POST['password'];

    if ($pseudo === '' || $email === '' || $plainPassword === '') {
        $error = "Tous les champs sont obligatoires";
    } else {

        /* =========================
           HASH PASSWORD
        ========================= */
        $password = password_hash($plainPassword, PASSWORD_DEFAULT);

        /* =========================
           PERSONNAGE PAR DÉFAUT
        ========================= */
        $defaultPersonnage = "Une Lionnette";

        // CHECK personnage existe
        $stmt = $conn->prepare("
            SELECT nom_personnage
            FROM personnage
            WHERE nom_personnage = ?
        ");
        $stmt->bind_param("s", $defaultPersonnage);
        $stmt->execute();
        $res = $stmt->get_result();

        // CREATE personnage si absent
        if ($res->num_rows === 0) {

            $stmt = $conn->prepare("
                INSERT INTO personnage (nom_personnage, description)
                VALUES (?, ?)
            ");

            $desc = "Je tryhard ici c’est la victoire";
            $stmt->bind_param("ss", $defaultPersonnage, $desc);
            $stmt->execute();
        }

        /* =========================
           CHECK EMAIL EXISTS
        ========================= */
        $stmt = $conn->prepare("
            SELECT id_user
            FROM Utilisateur
            WHERE email = ?
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $error = "Email déjà utilisé";
        } else {

            /* =========================
               INSERT USER
            ========================= */
            $stmt = $conn->prepare("
                INSERT INTO Utilisateur
                (pseudo, email, mot_de_passe, nom_personnage)
                VALUES (?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "ssss",
                $pseudo,
                $email,
                $password,
                $defaultPersonnage
            );

            $stmt->execute();

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
/* =====================
   RESPONSIVE INSCRIPTION CLEAN
===================== */

/* TABLETTE */
@media screen and (max-width: 1024px) {
  .form-container {
    width: 380px;
  }
}

/* MOBILE */
@media screen and (max-width: 768px) {

  body {
    padding: 15px;
  }

  .form-container {
    width: 100%;
    max-width: 380px;
    padding: 25px;
    border-radius: 14px;
  }

  .form-title {
    font-size: 1.5rem;
  }

  .form-input {
    font-size: 0.95rem;
    padding: 10px;
  }

  .form-button {
    font-size: 0.95rem;
    padding: 10px;
  }
}

/* PETIT MOBILE */
@media screen and (max-width: 480px) {

  .form-container {
    width: 100%;
    max-width: 95%;
    padding: 20px;
  }

  .form-title {
    font-size: 1.3rem;
  }

  .form-input {
    font-size: 0.9rem;
  }

  .form-text {
    font-size: 0.9rem;
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