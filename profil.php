<?php
if (session_status() == PHP_SESSION_NONE) session_start();

require_once 'auth.php';
requireLogin();
require_once 'db.php';

$id = getUserId();

/* =========================
   Récupérer infos utilisateur
========================= */
$stmt = $conn->prepare("
    SELECT *
    FROM Utilisateur
    WHERE id_user = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* =========================
   plateforme user
========================= */
$stmtPlatUser = $conn->prepare("
    SELECT id_plateforme
    FROM utiliser
    WHERE id_user=?
");
$stmtPlatUser->bind_param("i", $id);
$stmtPlatUser->execute();
$resPlatUser = $stmtPlatUser->get_result();
$userPlat = $resPlatUser->fetch_assoc();
$userPlatId = $userPlat['id_plateforme'] ?? 1;


/* =========================
   âge
========================= */
$age = null;
$year = '';
$month = '';
$day = '';

if (
    !empty($user['date_naissance']) &&
    $user['date_naissance'] != '0000-00-00'
){
    list($year,$month,$day)=explode('-', $user['date_naissance']);

    $birthDate = new DateTime($user['date_naissance']);
    $today = new DateTime();

    $age = $today->diff($birthDate)->y;
}


/* =========================
   liste jeux
========================= */
$gamesList = [];

$result = $conn->query("
    SELECT id_jeu, nom
    FROM jeu
    ORDER BY nom ASC
");

while($row = $result->fetch_assoc()){
    $gamesList[] = $row;
}


/* =========================
   jeux user
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

while($r = $result->fetch_assoc()){
    $userjeu[] = $r['nom'];
}


/* =========================
   personnages
========================= */
$personnages = [
    ["nom"=>"Une Lionnette","desc"=>"Je tryhard ici c'est la victoire"],
    ["nom"=>"Un Pinguin","desc"=>"J'aime glisser et m'amuser sur les jeux"],
    ["nom"=>"Un Pijaune","desc"=>"Je dépense beaucoup dans les jeux"],
    ["nom"=>"Un Chatou","desc"=>"Je cherche l’amour dans les jeux"]
];


/* =========================
   humeurs
========================= */
$humeurs = [
    "Joueur sérieux",
    "Relax",
    "Compétitif",
    "Fun",
    "Explorateur"
];


/* =========================
   plateformes
========================= */
$plateformes = [];

$res = $conn->query("
    SELECT id_plateforme, libelle
    FROM plateforme
    ORDER BY libelle ASC
");

while($r = $res->fetch_assoc()){
    $plateformes[] = $r;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Mon profil - PlayMate</title>

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

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"/>

<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
</head>

<body class="profil-form-page">

<form
class="form-container"
id="profilForm"
enctype="multipart/form-data"
>

<h1>Mon profil</h1>

<!-- DATE -->
<label>Date de naissance</label>

<div class="date-wrapper">

<select name="day" id="day" class="date-select day-select">
<option value="">Jour</option>
<?php for($d=1;$d<=31;$d++): ?>
<option
value="<?= str_pad($d,2,'0',STR_PAD_LEFT) ?>"
<?= $day == str_pad($d,2,'0',STR_PAD_LEFT) ? 'selected' : '' ?>
>
<?= $d ?>
</option>
<?php endfor; ?>
</select>


<select name="month" id="month" class="date-select month-select">
<option value="">Mois</option>

<?php
$months = [
'Janvier','Février','Mars','Avril','Mai','Juin',
'Juillet','Août','Septembre','Octobre','Novembre','Décembre'
];

for($m=1;$m<=12;$m++):
?>

<option
value="<?= str_pad($m,2,'0',STR_PAD_LEFT) ?>"
<?= $month == str_pad($m,2,'0',STR_PAD_LEFT) ? 'selected' : '' ?>
>
<?= $months[$m-1] ?>
</option>

<?php endfor; ?>
</select>


<select name="year" id="year" class="date-select year-select">
<option value="">Année</option>

<?php
$maxYear = date('Y') - 18;

for($y=$maxYear; $y>=1920; $y--):
?>

<option
value="<?= $y ?>"
<?= $year == $y ? 'selected' : '' ?>
>
<?= $y ?>
</option>

<?php endfor; ?>
</select>

</div>


<div class="result" id="ageResult">
<?php if ($age !== null): ?>
Votre âge est de <strong><?= $age ?> ans</strong>
<?php endif; ?>
</div>


<!-- PHOTO -->
<label>Photo de profil</label>

<?php if(!empty($user['photo'])): ?>
<div style="text-align:center;margin-bottom:15px;" id="photoBox">


</div>
<?php endif; ?>

<input
type="file"
name="photo"
accept="image/*"
class="form-select"
>

<img
id="preview"
width="150"
style="display:none; border-radius:50%; margin-top:15px;"
>


    <button type="button" id="deletePhoto" class="form-button" style="margin-top:10px;">
        Supprimer la photo
    </button>

<!-- Plateforme -->
<label>Plateforme</label>
<select name="platform" class="form-select">
<?php foreach($plateformes as $p): ?>
<option
value="<?= $p['id_plateforme'] ?>"
<?= $userPlatId == $p['id_plateforme'] ? 'selected' : '' ?>
>
<?= htmlspecialchars($p['libelle']) ?>
</option>
<?php endforeach; ?>
</select>


<!-- Jeux -->
<label>Jeux préférés</label>
<select id="jeu" name="jeu[]" multiple class="form-select">
<?php foreach($gamesList as $game): ?>
<option
value="<?= htmlspecialchars($game['nom']) ?>"
<?= in_array($game['nom'],$userjeu)?'selected':'' ?>
>
<?= htmlspecialchars($game['nom']) ?>
</option>
<?php endforeach; ?>
</select>


<!-- humeur -->
<label>Humeur</label>
<select name="humeur" class="form-select">
<?php foreach($humeurs as $h): ?>
<option
<?= ($user['humeur'] ?? '') === $h ? 'selected' : '' ?>
>
<?= $h ?>
</option>
<?php endforeach; ?>
</select>


<!-- bio -->
<label>Bio</label>
<textarea name="bio" class="form-textarea"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>


<!-- personnages -->
<label>Choisis ton personnage</label>

<div class="personnages">
<?php foreach($personnages as $p): ?>

<label class="personnage-card">

<input
type="radio"
name="personnage"
value="<?= $p['nom'] ?>"
<?= ($user['nom_personnage'] ?? '') === $p['nom'] ? 'checked' : '' ?>
>

<div class="personnage-content">
<img src="personnages/<?= $p['nom'] ?>.png">

<div><?= $p['nom'] ?></div>

<div class="personnage-desc">
<?= htmlspecialchars($p['desc']) ?>
</div>
</div>

</label>

<?php endforeach; ?>
</div>


<button type="submit" class="form-button">
Mettre à jour
</button>

<div id="message"></div>

<div style="text-align:center;">
<a href="interface.php" style="color:#00ffff;">
Trouver des joueurs
</a>
</div>

</form>


<script>
document.addEventListener('DOMContentLoaded', () => {
    new Choices('#jeu',{
        removeItemButton:true,
        placeholderValue:'Choisis tes jeux...',
        searchPlaceholderValue:'Rechercher un jeu...',
        itemSelectText:''
    });
});


function calculateAge(){
    const day=document.getElementById('day').value;
    const month=document.getElementById('month').value;
    const year=document.getElementById('year').value;

    const resultDiv=document.getElementById('ageResult');

    if(day && month && year){

        const birthDate=new Date(year, month-1, day);
        const today=new Date();

        let age=today.getFullYear()-birthDate.getFullYear();

        const m=today.getMonth()-birthDate.getMonth();

        if(m < 0 || (m===0 && today.getDate()<birthDate.getDate())){
            age--;
        }

        if(age < 18){
            resultDiv.innerHTML =
            "<span style='color:red'>Tu dois avoir au moins 18 ans</span>";
        } else {
            resultDiv.innerHTML =
            `Votre âge est de <strong>${age} ans</strong>`;
        }

    } else {
        resultDiv.innerHTML='';
    }
}

['day','month','year'].forEach(id =>
document.getElementById(id)
.addEventListener('change', calculateAge)
);


/* aperçu image */
const photoInput = document.querySelector('input[name="photo"]');

photoInput?.addEventListener('change', function(e){

    const file = e.target.files[0];

    if(file){
        const reader = new FileReader();

        reader.onload = function(event){
            const img =
            document.getElementById('preview');

            img.src = event.target.result;
            img.style.display = "block";
        }

        reader.readAsDataURL(file);
    }
});
/* =========================
   SUPPRIMER PHOTO
========================= */
document.getElementById('deletePhoto')?.addEventListener('click', function () {

    if(!confirm("Supprimer la photo ?")) return;

    fetch('deletePhoto.php', {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {

        if(data.success){
            const box = document.getElementById('photoBox');
            if(box) box.remove();
        } else {
            alert(data.error);
        }

    });

});

document.getElementById('profilForm')
.addEventListener('submit', function(e){

    e.preventDefault();

    fetch('updateProfil.php',{
        method:'POST',
        body:new FormData(this)
    })
    .then(res=>res.json())
    .then(data=>{
        document.getElementById('message').textContent =
        data.success
        ? "Profil mis à jour"
        : "Erreur : " + data.error;
    });
});
</script>

</body>
</html>
