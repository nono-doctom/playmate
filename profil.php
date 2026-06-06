<?php

// Démarre la session si elle n'est pas déjà active
if (session_status() == PHP_SESSION_NONE) session_start();

// Sécurité : fonctions d'authentification
require_once 'auth.php';
requireLogin(); // bloque l'accès si non connecté

require_once 'db.php';

// Récupération de l'ID utilisateur connecté
$id = getUserId();


/* =========================
   UTILISATEUR
========================= */

// On récupère toutes les infos de l'utilisateur
$stmt = $conn->prepare("SELECT * FROM Utilisateur WHERE id_user = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();


/* =========================
   PLATEFORME UTILISATEUR
========================= */

// On récupère la plateforme liée à l'utilisateur
$stmtPlatUser = $conn->prepare("
    SELECT id_plateforme
    FROM utiliser
    WHERE id_user=?
");

$stmtPlatUser->bind_param("i", $id);
$stmtPlatUser->execute();
$resPlatUser = $stmtPlatUser->get_result();
$userPlat = $resPlatUser->fetch_assoc();

// Si aucune plateforme trouvée → valeur par défaut = 1
$userPlatId = $userPlat['id_plateforme'] ?? 1;


/* =========================
   AGE + DATE DE NAISSANCE
========================= */

$age = null;
$year = $month = $day = '';

// Si la date existe et n'est pas vide
if (!empty($user['date_naissance']) && $user['date_naissance'] != '0000-00-00') {

    // On découpe la date (YYYY-MM-DD)
    list($year, $month, $day) = explode('-', $user['date_naissance']);

    // Calcul de l'âge avec DateTime
    $birthDate = new DateTime($user['date_naissance']);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
}


/* =========================
   LISTE DES JEUX
========================= */

$gamesList = [];

// On récupère tous les jeux triés par ordre alphabétique
$result = $conn->query("SELECT id_jeu, nom FROM jeu ORDER BY nom ASC");

while ($row = $result->fetch_assoc()) {
    $gamesList[] = $row;
}


/* =========================
   JEUX DE L'UTILISATEUR
========================= */

$userjeu = [];

$stmt = $conn->prepare("
    SELECT j.nom
    FROM jouer jo
    JOIN jeu j ON jo.id_jeu = j.id_jeu
    WHERE jo.id_user = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

while ($r = $result->fetch_assoc()) {
    $userjeu[] = $r['nom'];
}


/* =========================
   PERSONNAGES (statique)
========================= */

// Liste définie manuellement (pas en base de données)
$personnages = [
    ["nom" => "Une Lionnette", "desc" => "Je tryhard ici c'est la victoire"],
    ["nom" => "Un Pinguin", "desc" => "J'aime glisser et m'amuser sur les jeux"],
    ["nom" => "Un Pijaune", "desc" => "Je dépense beaucoup dans les jeux"],
    ["nom" => "Un Chatou", "desc" => "Je cherche l’amour dans les jeux"]
];


/* =========================
   HUMEURS
========================= */

$humeurs = [
    "Joueur sérieux",
    "Relax",
    "Compétitif",
    "Fun",
    "Explorateur"
];


/* =========================
   PLATEFORMES
========================= */

$plateformes = [];

// Récupère toutes les plateformes disponibles
$res = $conn->query("SELECT id_plateforme, libelle FROM plateforme ORDER BY libelle ASC");

while ($r = $res->fetch_assoc()) {
    $plateformes[] = $r;
}


/* =========================
   PHOTO UTILISATEUR
========================= */

// On récupère la photo stockée en base
$photo = $user['photo'] ?? '';

// Si la photo existe déjà dans uploads/
if ($photo && str_starts_with($photo, 'uploads/')) {
    $photoUrl = $photo;
} else {
    // Sinon on ajoute le chemin uploads/
    $photoUrl = "uploads/" . $photo;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Mon profil - PlayMate</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<style>
    /* =========================
   GLOBAL RESET
========================= */
* {
  box-sizing: border-box;
}

body, html {
  margin: 0;
  padding: 0;
  width: 100%;
  min-height: 100vh;
  font-family: 'Georgia', serif;
  background: radial-gradient(circle at 50% 50%, #00111f, #000814);
  color: #fff;
}

/* =========================
   CENTRAGE FORM PAGES
========================= */
.profil-form-page,
.body-form {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 20px;
}

/* =========================
   FORM CONTAINER (LOGIN + INSCRIPTION + PROFIL)
========================= */
.form-container {
  width: 100%;
  max-width: 420px;
  background: rgba(0, 20, 40, 0.85);
  padding: 40px 30px;
  border-radius: 20px;
  box-shadow: 0 0 40px rgba(0, 255, 255, 0.4);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(0, 255, 255, 0.6);
}

/* TITRE */
.form-title,
.form-container h1 {
  text-align: center;
  color: #00f0ff;
  margin-bottom: 25px;
  text-shadow: 0 0 10px #00ffff, 0 0 20px #00bfff;
}

/* =========================
   INPUTS / SELECT / TEXTAREA
========================= */
.form-input,
.form-select,
.form-textarea {
  width: 100%;
  padding: 12px;
  margin-top: 8px;
  margin-bottom: 15px;
  border-radius: 12px;
  border: 1px solid #00eaff;
  background-color: rgba(0, 60, 100, 0.4);
  color: #fff;
  font-size: 1rem;
  outline: none;
  transition: 0.3s;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  border-color: #00ffff;
  box-shadow: 0 0 10px #00eaff;
}

/* =========================
   BUTTON
========================= */
.form-button {
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  background-color: #00f0ff;
  color: black;
  cursor: pointer;
  transition: 0.3s;
}

.form-button:hover {
  background-color: #00c0cc;
  color: white;
  box-shadow: 0 0 15px #00eaff;
}

/* =========================
   ERROR / MESSAGE
========================= */
.error-msg {
  color: #ff4d4d;
  text-align: center;
  margin-bottom: 10px;
}

.result,
#message {
  text-align: center;
  margin-top: 10px;
  color: #00cfff;
  text-shadow: 0 0 8px #00cfff;
}

/* =========================
   DATE SELECT (PROFIL)
========================= */
.date-wrapper {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.date-select {
  flex: 1;
  padding: 10px;
  border-radius: 12px;
  border: 1px solid #00eaff;
  background-color: rgba(0,60,100,0.4);
  color: #fff;
  text-align: center;
}

/* tailles fixes */
.day-select { flex: 0 0 70px; }
.month-select { flex: 0 0 110px; }
.year-select { flex: 0 0 90px; }

/* =========================
   PERSONNAGES
========================= */
.personnages {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-top: 10px;
}

.personnage-card input {
  display: none;
}

.personnage-content {
  padding: 14px;
  text-align: center;
  border-radius: 12px;
  border: 1px solid rgba(0,255,255,0.2);
  cursor: pointer;
  transition: 0.3s;
}

.personnage-content img {
  width: 70px;
  filter: drop-shadow(0 0 8px #00cfff);
}

.personnage-card input:checked + .personnage-content {
  border-color: #00cfff;
  box-shadow: 0 0 15px rgba(0,255,255,0.3);
  transform: scale(1.03);
}

/* =========================
   CHOICES.JS FIX
========================= */
.choices__inner {
  background: rgba(0, 60, 100, 0.85) !important;
  border: 1px solid #00eaff !important;
  border-radius: 40px;
  color: #fff !important;
}

/* texte sélectionné (tags) */
.choices__list--multiple .choices__item {
  background: #00a6c7 !important;
  color: #00111f !important;
  border-radius: 40px;
}

/* dropdown */
.choices__list--dropdown {
  background: rgba(0, 20, 40, 0.95) !important;
  border: 1px solid #00eaff !important;
  color: #fff !important;
}

/* items dropdown */
.choices__item--selectable {
  color: #fff !important;
}

/* item hover */
.choices__item--selectable.is-highlighted {
  background: #00f0ff !important;
  color: black !important;
}
/* ===================== RESPONSIVE ===================== */
/* =========================
   RESPONSIVE PROFIL FORM FIX
========================= */

/* TABLETTE */
@media screen and (max-width: 1024px) {

.form-container {
  max-width: 500px;
  padding: 30px 20px;
}

.personnages {
  grid-template-columns: 1fr 1fr;
}
}

/* MOBILE */
@media screen and (max-width: 768px) {

body, html {
  font-size: 14px;
}

.form-container {
  width: 95%;
  padding: 25px 15px;
  border-radius: 15px;
}

/* DATE FLEX QUI CASSE MOINS */
.date-wrapper {
  flex-direction: column;
  gap: 8px;
}

.date-select {
  width: 100%;
  flex: unset;
}

/* PERSONNAGES -> 1 colonne */
.personnages {
  grid-template-columns: 1fr;
}

/* PHOTO */
#photoBox img {
  width: 90px;
}

/* TITRE */
h1 {
  font-size: 1.5rem;
}

/* BOUTONS PLUS LISIBLES */
.form-button {
  font-size: 0.95rem;
  padding: 10px;
}
}

/* PETIT MOBILE */
@media screen and (max-width: 480px) {

.form-container {
  width: 98%;
  padding: 20px 12px;
}

h1 {
  font-size: 1.3rem;
}

.form-input,
.form-select,
.form-textarea {
  font-size: 0.9rem;
  padding: 10px;
}

.personnage-content {
  padding: 10px;
}

.personnage-content img {
  width: 55px;
}
}
</style>
</head>

<body class="profil-form-page">

<form class="form-container" id="profilForm" enctype="multipart/form-data">

<h1>Mon profil</h1>

<!-- Champ caché : indique si l'utilisateur veut supprimer la photo -->
<input type="hidden" name="delete_photo" id="delete_photo" value="0">


<!-- PSEUDO -->
<label>Pseudo</label>
<input type="text" name="pseudo" class="form-input"
value="<?= htmlspecialchars($user['pseudo'] ?? '') ?>">


<!-- DATE DE NAISSANCE -->
<label>Date de naissance</label>

<div class="date-wrapper">

<!-- JOUR -->
<select name="day" id="day" class="date-select day-select">
<option value="">Jour</option>
<?php for($d=1;$d<=31;$d++): ?>
<option value="<?= str_pad($d,2,'0',STR_PAD_LEFT) ?>"
<?= $day == str_pad($d,2,'0',STR_PAD_LEFT) ? 'selected' : '' ?>>
<?= $d ?>
</option>
<?php endfor; ?>
</select>

<!-- MOIS -->
<select name="month" id="month" class="date-select month-select">
<option value="">Mois</option>
<?php
$months = ['Janvier','Février','Mars','Avril','Mai','Juin',
'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

for($m=1;$m<=12;$m++): ?>
<option value="<?= str_pad($m,2,'0',STR_PAD_LEFT) ?>"
<?= $month == str_pad($m,2,'0',STR_PAD_LEFT) ? 'selected' : '' ?>>
<?= $months[$m-1] ?>
</option>
<?php endfor; ?>
</select>

<!-- ANNÉE (limite : 18 ans minimum) -->
<select name="year" id="year" class="date-select year-select">
<option value="">Année</option>
<?php
$maxYear = date('Y') - 18; // interdit les -18 ans
for($y=$maxYear; $y>=1920; $y--): ?>
<option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>>
<?= $y ?>
</option>
<?php endfor; ?>
</select>

</div>


<!-- AFFICHAGE DE L'ÂGE -->
<div class="result" id="ageResult">
<?php if ($age !== null): ?>
Votre âge est de <strong><?= $age ?> ans</strong>
<?php endif; ?>
</div>


<!-- PHOTO PROFIL -->
<label>Photo</label>

<?php if(!empty($photoUrl)): ?>
<!-- Photo actuelle -->
<div id="photoBox">
    <img src="<?= htmlspecialchars($photoUrl) ?>" width="120" style="border-radius:50%;">
</div>
<?php endif; ?>

<!-- Upload nouvelle photo -->
<input type="file" name="photo" accept="image/*" class="form-select">

<!-- Preview avant upload -->
<img id="preview" width="150"
style="display:none; border-radius:50%; margin-top:15px;">

<!-- Bouton suppression photo -->
<button type="button" id="deletePhoto" class="form-button" style="margin-top:10px;">
Supprimer la photo
</button>


<!-- PLATEFORME -->
<label>Plateforme</label>
<select name="platform" class="form-select">
<?php foreach($plateformes as $p): ?>
<option value="<?= $p['id_plateforme'] ?>"
<?= $userPlatId == $p['id_plateforme'] ? 'selected' : '' ?>>
<?= htmlspecialchars($p['libelle']) ?>
</option>
<?php endforeach; ?>
</select>


<!-- JEUX FAVORIS (multi-select) -->
<label>Jeux préférés</label>
<select id="jeu" name="jeu[]" multiple class="form-select">
<?php foreach($gamesList as $game): ?>
<option value="<?= htmlspecialchars($game['nom']) ?>"
<?= in_array($game['nom'],$userjeu) ? 'selected' : '' ?>>
<?= htmlspecialchars($game['nom']) ?>
</option>
<?php endforeach; ?>
</select>


<!-- HUMEUR -->
<label>Humeur</label>
<select name="humeur" class="form-select">
<?php foreach($humeurs as $h): ?>
<option <?= ($user['humeur'] ?? '') === $h ? 'selected' : '' ?>>
<?= $h ?>
</option>
<?php endforeach; ?>
</select>


<!-- BIO -->
<label>Bio</label>
<textarea name="bio" class="form-textarea">
<?= htmlspecialchars($user['bio'] ?? '') ?>
</textarea>


<!-- PERSONNAGE -->
<label>Choisis ton personnage</label>

<div class="personnages">
<?php foreach($personnages as $p): ?>
<label class="personnage-card">

<!-- Radio = un seul personnage possible -->
<input type="radio" name="personnage"
value="<?= $p['nom'] ?>"
<?= ($user['nom_personnage'] ?? '') === $p['nom'] ? 'checked' : '' ?>>

<div class="personnage-content">
    <img src="personnages/<?= $p['nom'] ?>.png">
    <div><?= $p['nom'] ?></div>
    <div><?= htmlspecialchars($p['desc']) ?></div>
</div>

</label>
<?php endforeach; ?>
</div>


<!-- SUBMIT -->
<button type="submit" class="form-button">Mettre à jour</button>


<!-- NAVIGATION -->
<div style="text-align:center; margin-top:10px;">
    <a href="interface.php" style="color:#00ffff;">
        Trouver des joueurs
    </a>
</div>


<!-- MESSAGE AJAX -->
<div id="message"></div>

</form>

<script>

/* DOMContentLoaded */
// Attendre que toute la page soit chargée avant d'exécuter le JS
document.addEventListener('DOMContentLoaded', () => {

    // Initialise le plugin Choices.js sur le select #jeu
    // Permet de rendre le select plus stylé + multi-sélection avec suppression
    new Choices('#jeu', { removeItemButton: true });

});


/* PREVIEW IMAGE */
// Quand l'utilisateur choisit une image dans l'input file
document.querySelector('input[name="photo"]')?.addEventListener('change', function (e) {

    // On récupère le premier fichier sélectionné
    const file = e.target.files[0];

    if (file) {

        // FileReader permet de lire un fichier local (image ici)
        const reader = new FileReader();

        // Quand la lecture du fichier est terminée
        reader.onload = e => {

            // On récupère l'élément img de preview
            const img = document.getElementById('preview');

            // On met l'image choisie en source (base64)
            img.src = e.target.result;

            // On affiche l'image (si elle était cachée)
            img.style.display = "block";
        };

        // Lecture du fichier en DataURL (format image affichable dans src)
        reader.readAsDataURL(file);
    }
});


/* DELETE PHOTO */
// On récupère le bouton avec l'id "deletePhoto" et on ajoute un événement click
document.getElementById('deletePhoto')?.addEventListener('click', function () {

// On demande confirmation à l'utilisateur avant suppression
if (!confirm("Supprimer la photo ?")) return;

// On envoie une requête POST au serveur pour supprimer la photo
fetch('deletePhoto.php', { method: 'POST' })

// On transforme la réponse en JSON
.then(r => r.json())

// On traite les données retournées par le serveur
.then(data => {

    // Si la suppression a réussi côté serveur
    if (data.success) {

        // On supprime visuellement le bloc contenant la photo
        document.getElementById('photoBox')?.remove();

        // On récupère l'élément de prévisualisation de l'image
        const preview = document.getElementById('preview');

        if (preview) {
            // On masque l'image
            preview.style.display = "none";

            // On vide la source de l'image
            preview.src = "";
        }

        // On met un champ caché à 1 pour indiquer que la photo doit être supprimée
        document.getElementById('delete_photo').value = "1";

    } else {
        // Si erreur côté serveur, on affiche un message
        alert(data.error || "Erreur");
    }

});

});


/* SUBMIT */
// On écoute la soumission du formulaire "profilForm"
document.getElementById('profilForm').addEventListener('submit', function (e) {

// On empêche le rechargement de la page
e.preventDefault();

// On envoie le formulaire en AJAX (sans recharger la page)
fetch('updateProfil.php', {
    method: 'POST',

    // FormData récupère automatiquement tous les champs du formulaire
    body: new FormData(this)
})

// On convertit la réponse en JSON
.then(r => r.json())

// On traite la réponse du serveur
.then(data => {

    // On affiche un message dans l'élément "message"
    document.getElementById('message').textContent =
        data.success ? "Profil mis à jour" : ("Erreur : " + data.error);

    // On réinitialise le flag de suppression de photo
    document.getElementById('delete_photo').value = "0";
});

});
</script>

</body>
</html>