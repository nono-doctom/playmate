<?php
include 'db.php';
session_start();

// Vérifie si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $defaultPersonnage = 'Une Lionnette';

    // Vérifie si le personnage par défaut existe
    $result = mysqli_query($conn, "SELECT nom_personnage FROM personnage WHERE nom_personnage='$defaultPersonnage'");
    if(mysqli_num_rows($result) == 0){
        // Crée le personnage par défaut
        $desc = "Je tryhard ici c'est la victoire";
        mysqli_query($conn, "INSERT INTO personnage (nom_personnage, description) VALUES ('$defaultPersonnage', '$desc')");
        $nom_personnage = $defaultPersonnage;
    } else {
        $row = mysqli_fetch_assoc($result);
        $nom_personnage = $row['nom_personnage'];
    }

    // Récupère les données du formulaire
    $pseudo = mysqli_real_escape_string($conn, $_POST['pseudo']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Vérifie si l'email existe déjà
    $resCheck = mysqli_query($conn, "SELECT id_user FROM Utilisateur WHERE email='$email'");
    if(mysqli_num_rows($resCheck) > 0){
        $erreur = "Email déjà utilisé";
    } else {
        // Insère l'utilisateur
        mysqli_query($conn, "INSERT INTO Utilisateur (pseudo, email, mot_de_passe, nom_personnage) 
                             VALUES ('$pseudo', '$email', '$password', '$nom_personnage')");
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Inscription - PlayMate</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="body-form">

<form method="POST" class="form-container">
  <h2 class="form-title">Créer un compte</h2>

  <?php if(isset($erreur)) echo "<div class='error-msg'>$erreur</div>"; ?>

  <input type="text" name="pseudo" placeholder="Pseudo" class="form-input" required>
  <input type="email" name="email" placeholder="Email" class="form-input" required>
  <input type="password" name="password" placeholder="Mot de passe" class="form-input" required>

  <button type="submit" class="form-button">S’inscrire</button>

  <p class="form-text">
    Déjà inscrit ? <a href="login.php" class="form-link">Connexion</a>
  </p>
</form>

</body>
</html>