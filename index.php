<?php
session_start();
require_once 'auth.php';

// Vérifie si l'utilisateur est connecté
$loggedIn = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>PlayMate - Accueil</title>
  <link rel="stylesheet" href="style.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="home-page">
  <iframe src="musique.php" style="display:none;" id="music-frame"></iframe>

  <!-- Barre latérale -->
  <nav class="sidebar">
      <h1>PlayMate</h1>
      <?php if ($loggedIn): ?>
          <a href="interface.php">Trouver des joueurs</a>
          <a href="profil.php">Mon profil</a>
          <a href="logout.php">Déconnexion</a>
      <?php else: ?>
          <a href="login.php">Connexion</a>
          <a href="inscrire.php">Inscription</a>
      <?php endif; ?>
  </nav>

  <!-- Contenu principal -->
  <main class="img-background">
    <h1 class="main-title">PlayMate</h1>
    <div class="text-container">
        <h2>Bienvenue sur PlayMate</h2>
        <p>Connecte-toi pour rencontrer des gamers qui partagent ta passion et des amis !</p>
          </div>
    </div>
  </main>
</body>
</html>