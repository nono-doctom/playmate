<?php
include 'db.php';
session_start();

// Nombre de joueurs à créer
$nb_joueurs = 30;

// Définir les options possibles
$plateformes = ["PC", "PlayStation", "Xbox", "Switch", "Mobile"];
$styles_jeu = ["FPS", "MOBA", "RPG", "Simulation", "Aventure", "Chill"];
$humeurs = ["Joueur sérieux", "Relax", "Compétitif", "Fun", "Explorateur"];
$styles_personnage = [
    "Je dépense beaucoup dans les jeux" => "pigeon",
    "Je cherche l'amour dans les jeux" => "tigre",
    "J'aime glisser et m'amuser sur les jeux" => "pinguino"
];
$jeux_possibles = ["Fortnite", "League of Legends", "Valorant", "Minecraft", "FIFA", "Chill", "Apex Legends", "Among Us", "Overwatch"];

// Fonction pour générer une chaîne aléatoire
function random_string($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for ($i=0; $i<$length; $i++) {
        $str .= $chars[rand(0, strlen($chars)-1)];
    }
    return $str;
}

// Générer des joueurs
for ($i=0; $i<$nb_joueurs; $i++) {
    $pseudo = "Player_" . random_string(5);
    $email = strtolower($pseudo) . "@mail.com";
    $mot_de_passe = password_hash("password", PASSWORD_DEFAULT);
    $tranche_age = rand(12, 40);
    $bio = "Salut, je suis $pseudo !";
    $platform = $plateformes[array_rand($plateformes)];
    $style = $styles_jeu[array_rand($styles_jeu)];
    $mood = $humeurs[array_rand($humeurs)];
    $style_choice_phrase = array_rand($styles_personnage);
    $personnage = $styles_personnage[$style_choice_phrase];
    
    // Choisir 1 à 3 jeux aléatoires
    shuffle($jeux_possibles);
    $games = implode(", ", array_slice($jeux_possibles, 0, rand(1,3)));

    // Préparer l'insertion
    $stmt = mysqli_prepare($conn, "
        INSERT INTO Utilisateur (pseudo, email, mot_de_passe, tranche_age, bio, platform, style, mood, games, style_choice, personnage) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt, "sssisssssss", $pseudo, $email, $mot_de_passe, $tranche_age, $bio, $platform, $style, $mood, $games, $style_choice_phrase, $personnage);
    mysqli_stmt_execute($stmt);
}

echo "<p style='color:white; font-family:Georgia;'>$nb_joueurs joueurs générés avec succès !</p>";
echo "<a href='interface.php' style='color:#00f0ff; text-decoration:none;'>Voir les joueurs</a>";
?>
