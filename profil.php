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

$result = $conn->query("
    SELECT j.nom
    FROM jouer jo
    JOIN jeu j
    ON jo.id_jeu=j.id_jeu
    WHERE jo.id_user=$id
");

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

<link rel="stylesheet" href="style.css">

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
<div style="text-align:center;margin-bottom:15px;">
    <img
        src="<?= htmlspecialchars($user['photo']) ?>"
        width="150"
        height="150"
        style="border-radius:50%;object-fit:cover;"
    >
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
const choixJeu = new Choices('#jeu',{
    removeItemButton:true,
    placeholderValue:'Choisis tes jeux...',
    searchPlaceholderValue:'Rechercher un jeu...',
    itemSelectText:''
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
document
.querySelector('input[name="photo"]')
.addEventListener('change', function(e){

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