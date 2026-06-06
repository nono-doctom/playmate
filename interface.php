<?php
// ================== SESSION ==================
// Démarre la session pour savoir si l'utilisateur est connecté
if (session_status() == PHP_SESSION_NONE) session_start();

// On vérifie que l'utilisateur est connecté
require_once 'auth.php';
requireLogin();

// Puis on fait la connexion à la base de données
require_once 'db.php';

// Et je récupère l'ID de l'utilisateur connecté
$id = getUserId();


// UTILISATEUR 
// On prépare une requête pour récupérer les infos du joueur connecté
//Les points d’interrogation servent à mettre des valeurs de façon sécurisée 
// dans une requête SQL pour éviter les injections.
$stmt = mysqli_prepare($conn, "SELECT * FROM Utilisateur WHERE id_user = ?");

// Mais après on remplace le ? par l'ID utilisateur
mysqli_stmt_bind_param($stmt, "i", $id);
//c’est un objet qui contient la requête SQL préparée sans avoir donnée de valeur puis a^pres on
// met les valeur donc ici on a i qui veut dire int car l'id de l'utilsateur est en int.
// Exécute la requête
mysqli_stmt_execute($stmt);

// Récupère le résultat
$res = mysqli_stmt_get_result($stmt);

// Transforme en tableau PHP avec la méthide fetch
$user = mysqli_fetch_assoc($res);


// PLATEFORME
// Récupère la plateforme de l'utilisateur (PC, PS5, Xbox,ect )
$stmtPlatUser = mysqli_prepare($conn, "
SELECT p.libelle 
FROM utiliser u 
JOIN plateforme p ON u.id_plateforme = p.id_plateforme 
WHERE u.id_user = ?
");

$stmtPlatUser->bind_param("i", $id);
// Ici on remplace le "?" dans la requête SQL par la valeur de $id
// "i" veut dire int
// donc on veux chercher les infos de l’utilisateur avec l'ID en paramètre

$stmtPlatUser->execute();
// On exécute ensuite la requête SQL
// Donc la base de données fait la recherche avec la valeur donnée

$resPlatUser = $stmtPlatUser->get_result();
// On récupère le résultat de la requête
// C’est la réponse envoyée par la base de données tout cela pour avoir 
// la plateforme de l'utilisateur qui a été rentré en paramètre

$userPlat = $resPlatUser->fetch_assoc();
//  On transforme le résultat en tableau PHP
// On récupère une seule ligne (la plateforme de l’utilisateur)

// Si aucune plateforme n'est sélectionnner alors on met le libelle PC par défaut
$userPlatLibelle = $userPlat['libelle'] ?? 'PC';


// AGE UTILISATEUR
// Variable âge vide au départ
$ageUser = null;

// Vérifie si la date de naissance existe
if (!empty($user['date_naissance']) && $user['date_naissance'] != '0000-00-00') {
    //empty Ça vérifie si une variable est vide ou sans valeur utile donc !empty 
    // cest 'linverse donc je verifie si la variable n'est pas vide donc après 

    // Transforme la date en objet
    $birthDate = new DateTime($user['date_naissance']);

    // Date actuelle
    $today = new DateTime();

    // Calcul de l'âge on fait la date d'aujourdhui moins l'age rentre dans la likste déroulante.
    $ageUser = $today->diff($birthDate)->y;
}


// JEUX UTILISATEUR
// Récupère les jeux du joueur connecté
$resGames = mysqli_query($conn,"
SELECT j.nom 
FROM jouer jo 
JOIN jeu j ON jo.id_jeu = j.id_jeu 
WHERE jo.id_user = $id
");

// Tableau des jeux
$gamesUser = [];

// On ajoute chaque jeu dans le tableau avec une boucle while.
while($row = mysqli_fetch_assoc($resGames)) {
    $gamesUser[] = $row['nom'];
}


// FILTRE AGE
// Récupère l'âge choisi dans la liste déroulante
$selectedAge = isset($_GET['age']) ? (int)$_GET['age'] : null;

//  isset($_GET['age']) : vérifie si un âge a été envoyé dans l’URL et existe =(isset)
// Exemple : interface.php?age=20

// 
// Si la condition est vraie  on prend alors la valeur après le ?
//(int)$_GET['age'] : transforme la valeur en nombre entier
// Exemple : "20" devient 20

// null : signifie "il n'y aucune valeur"
// Donc si aucun âge n’est choisi, selectedAge = vide

// REQUETE JOUEURS 
// On récupère tous les autres joueurs
$sql = "
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
";
//On récupère tous les jeux du joueur

//GROUP_CONCAT = regroupe plusieurs lignes en une seule
//DISTINCT = évite les doublons
//AS games_list = on appelle ça "games_list"
//GROUP_CONCAT(DISTINCT p.libelle) AS plateformes On récupère toutes les plateformes :
//et après on fait les jointures
// Si un âge est sélectionné alors on filtre
if ($selectedAge) {

    // La requête permet de garder seulement les utilisateurs qui ont 
    // l'âge sélectionner grâce a la liste déroulante.
    $sql .= " AND TIMESTAMPDIFF(YEAR, u.date_naissance, CURDATE()) = $selectedAge ";
}

// Tri des résultats
$sql .= " GROUP BY u.id_user ORDER BY u.id_user DESC";


// Prépare la requête
$stmt = mysqli_prepare($conn, $sql);

// Remplace les ? par l'ID utilisateur
mysqli_stmt_bind_param($stmt, "ii", $id, $id);

// Exécute la requête
mysqli_stmt_execute($stmt);

// Récupère les résultats
$profilResult = mysqli_stmt_get_result($stmt);


// LISTE des profils
// On a donc un tableau qui contient tous les joueurs.
$profils = [];

// On ajoute chaque joueur dans le tableau
while ($row = mysqli_fetch_assoc($profilResult)) {
    $profils[] = $row;
}


// La fonction compatibilité permet au joueur de savoir 
// si ils ont des centres d'interêts commun tel que les jeux en commun jouer, la même plateforme,
// ou la même humeur ect.
function calculerCompatibilite($user1, $user2) {

    // Score de départ
    $score = 50;

    //PLATEFORME
    // Vérifie si même plateforme
    if (!empty($user1['plateforme']) && !empty($user2['plateformes'])) {

        $plateformes2 = array_map('trim', explode(',', strtolower($user2['plateformes'])));

        if (in_array(strtolower($user1['plateforme']), $plateformes2)) {
            $score += 20;
        } else {
            $score -= 15;
        }
    }

    //JEUX 
    // Compare les jeux en commun
    if (!empty($user1['games_list']) && !empty($user2['games_list'])) {

        $jeux1 = array_map('trim', explode(',', strtolower($user1['games_list'])));
        $jeux2 = array_map('trim', explode(',', strtolower($user2['games_list'])));

        $commun = count(array_intersect($jeux1, $jeux2));

        if ($commun >= 3) $score += 25;
        elseif ($commun == 2) $score += 18;
        elseif ($commun == 1) $score += 10;
        else $score -= 20;
    }

   //HUMEUR
    if (!empty($user1['humeur']) && !empty($user2['humeur'])) {
        $score += ($user1['humeur'] === $user2['humeur']) ? 5 : -3;
    }

    // AGE
    if (!empty($user1['date_naissance']) && !empty($user2['date_naissance'])) {
    //Ça vérifie si une variable est vide ou sans valeur utile donc ! 
    // cest 'linverse donc je verifie si la variable n'est pas vide donc après 
    // ca me permet d'augmenter la compatibilité ou de la baisser
        $d1 = new DateTime($user1['date_naissance']);
        $d2 = new DateTime($user2['date_naissance']);

        $difference = abs($d1->diff($d2)->y);

        if ($difference <= 3) $score += 10;
        elseif ($difference <= 6) $score += 5;
        else $score -= 8;
    }

    // Donc le résultat final est compris entre 0 et 100.
    return max(0, min(100, $score));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Interface - PlayMate</title>
<style>

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body {
  width: 100%;
  min-height: 100%;
  height: 100%;
  font-family: Georgia, serif;
  color: white;
}

/* =========================
   PAGE BACKGROUND FIX MOBILE
========================= */
.interface-page {
  width: 100%;
  min-height: 100vh;
  min-height: 100dvh;

  position: relative;

  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;

  padding: 100px 15px 40px;
  text-align: center;

  background-image: url('jeux_video.png');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}

/* overlay */
.interface-page::before {
  content: "";
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  z-index: 0;
}

/* contenu au-dessus */
.interface-page > * {
  position: relative;
  z-index: 1;
}

/* =========================
   TITRE
========================= */
.interface-title {
  position: fixed;
  top: 40px;
  left: 50%;
  transform: translateX(-50%);
  font-size: clamp(1.5rem, 4vw, 3rem);
  font-weight: bold;
  color: white;
  z-index: 10;
}

/* =========================
   BACK BUTTON
========================= */
.back-btn {
  position: fixed;
  top: 10px;
  left: 20px;
  z-index: 11;

  padding: 8px 15px;
  background: #00f0ff;
  color: black;
  text-decoration: none;
  border-radius: 10px;
  font-weight: bold;
}

.back-btn:hover {
  background: #00c0cc;
  color: white;
}

/* =========================
   CONTENT
========================= */
.main-content {
  position: relative;
  z-index: 1;

  width: 100%;
  max-width: 600px;

  padding: 15px;
  text-align: center;
}

/* =========================
   CARD STYLE (GAMER / NETFLIX)
========================= */
.card {
  background: rgba(17, 17, 17, 0.85);
  border-radius: 16px;
  padding: 20px;
  margin: 15px auto;

  box-shadow: 0 0 20px rgba(0, 255, 255, 0.2);

  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 0 30px rgba(0, 255, 255, 0.4);
}

/* =========================
   BUTTONS
========================= */
.btn {
  display: inline-block;
  padding: 10px 20px;
  margin: 10px;

  background: #00f0ff;
  color: black;

  text-decoration: none;
  border-radius: 10px;

  transition: 0.3s;
}

.btn:hover {
  background: #00c0cc;
  color: white;
}

/* =========================
   PROGRESS BAR
========================= */
.progress-bar {
  height: 20px;
  border-radius: 10px;
  background: #111;
  overflow: hidden;
  margin-top: 10px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #00f0ff, #00c0cc);
}

/* =========================
   BASE RESET
========================= */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body {
  width: 100%;
  min-height: 100%;
  font-family: Georgia, serif;
  color: white;
}

/* =========================
   PAGE BACKGROUND
========================= */
.interface-page {
  width: 100%;
  min-height: 100vh;
  min-height: 100dvh;

  display: flex;
  flex-direction: column;
  align-items: center;

  padding: 120px 20px 40px;
  text-align: center;

  background-image: url('jeux_video.png');
  background-size: cover;
  background-position: center;
}

/* overlay */
.interface-page::before {
  content: "";
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.55);
  z-index: 0;
}

.interface-page > * {
  position: relative;
  z-index: 1;
}

/* =========================
   TITLE
========================= */
.interface-title {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  font-size: clamp(1.2rem, 3vw, 2.5rem);
  text-align: center;
  width: 100%;
  padding: 0 10px;
}

/* =========================
   BACK BUTTON
========================= */
.back-btn {
  position: fixed;
  top: 10px;
  left: 10px;

  padding: 8px 12px;
  font-size: 14px;

  background: #00f0ff;
  color: black;
  text-decoration: none;
  border-radius: 8px;
}

/* =========================
   MAIN CONTENT
========================= */
.main-content {
  width: 100%;
  max-width: 650px;
}

/* =========================
   CARD
========================= */
.card {
  width: 100%;
  background: rgba(17,17,17,0.85);
  border-radius: 16px;
  padding: 20px;
  margin: 15px 0;

  box-shadow: 0 0 20px rgba(0,255,255,0.2);
}

/* =========================
   BUTTON
========================= */
.btn {
  display: inline-block;
  padding: 10px 16px;
  margin: 8px;

  background: #00f0ff;
  color: black;

  text-decoration: none;
  border-radius: 10px;

  font-size: 14px;
}

/* =========================
   PROGRESS BAR
========================= */
.progress-bar {
  height: 18px;
  border-radius: 10px;
  background: #111;
  overflow: hidden;
  margin-top: 10px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #00f0ff, #00c0cc);
}

/* =========================
   RESPONSIVE TABLET
========================= */
@media (max-width: 1024px) {

  .main-content {
    max-width: 90%;
  }
}

/* =========================
   MOBILE
========================= */
@media (max-width: 768px) {

  .interface-page {
    padding: 100px 10px 30px;
  }

  .card {
    padding: 15px;
  }

  .btn {
    display: block;
    width: 100%;
    margin: 10px 0;
  }

  .interface-title {
    font-size: 1.4rem;
    top: 15px;
  }
}

/* =========================
   SMALL PHONE
========================= */
@media (max-width: 480px) {

  .card {
    padding: 12px;
  }

  .back-btn {
    font-size: 12px;
    padding: 6px 10px;
  }

  .interface-title {
    font-size: 1.2rem;
  }
}
</style>
</head>

<body>
<div class="interface-page">

<a href="index.php" class="back-btn">Retour</a>
<h1 class="interface-title">Découvre un joueur</h1>

<div class="main-content">

<!--  LISTE DÉROULANTE -->
<form method="GET"> <!-- la méthode GET permet de recupérer-->
    <select name="age" class="btn" onchange="this.form.submit()">
        <option value="">Choisir un âge</option>

        <?php for($i = 18; $i <= 99; $i++): ?>
            <option value="<?= $i ?>" <?= ($selectedAge == $i) ? 'selected' : '' ?>>
                <?= $i ?> ans
            </option>
        <?php endfor; ?>
    </select>
</form>

<?php if(!empty($profils)): ?>

<?php foreach($profils as $profil): 

    // compatibilité pour chaque profil
    $compatibility = calculerCompatibilite(
        [
            'plateforme' => $userPlatLibelle,
            'games_list' => implode(',', $gamesUser),
            'humeur' => $user['humeur'],
            'date_naissance' => $user['date_naissance']
        ],
        [
    'plateforme' => $profil['plateformes'] ?? '',
            'games_list' => $profil['games_list'] ?? '',
            'humeur' => $profil['humeur'] ?? '',
            'date_naissance' => $profil['date_naissance'] ?? ''
        ]
    );
?>

<div class="card">
    <h2><?= htmlspecialchars($profil['pseudo']) ?></h2>

    <p><strong>Âge :</strong>
    <?= !empty($profil['date_naissance']) 
        ? (new DateTime($profil['date_naissance']))->diff(new DateTime())->y 
        : 'Non précisé' ?>
    </p>

    <p><strong>Plateforme :</strong> <?= htmlspecialchars($profil['plateformes'] ?? 'Non précisé') ?></p>
    <p><strong>Personnage :</strong> <?= htmlspecialchars($profil['nom_personnage'] ?? 'Non précisé') ?></p>
    <p><strong>Bio :</strong> <?= htmlspecialchars($profil['bio'] ?? 'Non précisé') ?></p>
    <p><strong>Jeux :</strong> <?= htmlspecialchars($profil['games_list'] ?? 'Non précisé') ?></p>

    <p><strong>Compatibilité : <?= $compatibility ?>%</strong></p>

    <div class="progress-bar">
        <div class="progress-fill" style="width:<?= $compatibility ?>%;"></div>
    </div>

    <a class="btn" href="like.php?id=<?= $profil['id_user'] ?>">J’aime</a>
    <a class="btn" href="dislike.php?id=<?= $profil['id_user'] ?>">Je passe</a>
</div>

<?php endforeach; ?>

<?php else: ?>
<p>Aucun joueur trouvé avec cet âge.</p>
<?php endif; ?>

<a class="btn" href="matches.php">Voir mes matchs</a>

</div>
</div>
</body>
</html>