<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$match_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($match_id <= 0) {
    die("Utilisateur invalide");
}

/* =============================
   VERIFIER LE MATCH
============================= */
$sql = "
SELECT *
FROM Matcher m1
JOIN Matcher m2
ON m1.id_user = m2.id_user_1
AND m1.id_user_1 = m2.id_user
WHERE m1.id_user = ?
AND m1.id_user_1 = ?
AND m1.avis='like'
AND m2.avis='like'
";

$stmt = mysqli_prepare($conn,$sql);
mysqli_stmt_bind_param($stmt,"ii",$user_id,$match_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0){
    die("Vous ne pouvez pas parler à cet utilisateur");
}


/* =============================
   ENVOI MESSAGE
============================= */
if($_SERVER["REQUEST_METHOD"]==="POST" && !empty($_POST['message'])){

    $message = trim($_POST['message']);

    $insert = mysqli_prepare($conn,"
        INSERT INTO Message(contenu,id_user,id_user_1)
        VALUES(?,?,?)
    ");

    mysqli_stmt_bind_param(
        $insert,
        "sii",
        $message,
        $user_id,
        $match_id
    );

    mysqli_stmt_execute($insert);

    header("Location: chat.php?id=".$match_id);
    exit();
}


/* récupérer pseudo */
$getUser = mysqli_prepare($conn,"
SELECT pseudo
FROM Utilisateur
WHERE id_user=?
");

mysqli_stmt_bind_param($getUser,"i",$match_id);
mysqli_stmt_execute($getUser);
$resUser = mysqli_stmt_get_result($getUser);
$matchUser = mysqli_fetch_assoc($resUser);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Chat</title>

<style>/* ===== GLOBAL ===== */
body, html {
  margin:0;
  padding:0;
  width:100%;
  height:100%;
  font-family:'Georgia', serif;
}

/* ===== PAGE ===== */
.chat-page{
  position:relative;
  width:100%;
  height:100vh;
  background: radial-gradient(circle at 50% 50%, #00111f 0%, #000814 100%);
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  color:white;
  overflow:hidden;
  padding:20px;
  box-sizing:border-box;
}

/* ===== FOND ANIMÉ ===== */
.chat-page::before{
  content:"";
  position:absolute;
  inset:0;
  background:
      radial-gradient(circle at 30% 30%, rgba(0,170,255,0.25), transparent 70%),
      radial-gradient(circle at 70% 70%, rgba(0,255,255,0.25), transparent 70%);
  animation: glowPulse 6s infinite alternate;
}

.chat-page::after{
  content:"";
  position:absolute;
  inset:0;
  background-image:
    radial-gradient(2px 2px at 10% 20%, #00f0ff, transparent),
    radial-gradient(2px 2px at 50% 10%, #00ffff, transparent),
    radial-gradient(2px 2px at 80% 70%, #00ccff, transparent),
    radial-gradient(2px 2px at 20% 80%, #66ffff, transparent);
  animation: starsFlow 12s linear infinite;
}

/* ===== TITRE ===== */
h2{
  position:relative;
  z-index:5;
  color:#00f0ff;
  text-shadow:0 0 20px #00ffff;
  text-align:center;
  font-size:1.4rem;
  margin-bottom:10px;
}

/* ===== CHAT BOX ===== */
#chat-box{
  position:relative;
  z-index:5;
  width:100%;
  max-width:900px;
  height:60vh;
  overflow-y:auto;
  background:rgba(0,0,0,0.65);
  border-radius:20px;
  padding:15px;
  box-shadow:0 0 20px #00f0ff;
}

/* messages */
.message{
  padding:10px 14px;
  margin:8px 0;
  border-radius:18px;
  max-width:75%;
  word-wrap:break-word;
  font-size:0.95rem;
}

.mine{
  background:#00f0ff;
  color:black;
  margin-left:auto;
}

.theirs{
  background:#222;
  color:white;
}

/* ===== FORM ===== */
#chat-form{
  position:relative;
  z-index:5;
  width:100%;
  max-width:900px;
  display:flex;
  gap:10px;
  margin-top:15px;
}

#chat-form input{
  flex:1;
  padding:12px;
  border-radius:25px;
  border:1px solid #00f0ff;
  background:rgba(0,0,0,0.6);
  color:white;
  outline:none;
}

#chat-form button{
  padding:12px 18px;
  border:none;
  border-radius:25px;
  background:#00f0ff;
  color:black;
  font-weight:bold;
  cursor:pointer;
}

/* ===== BACK ===== */
.back{
  z-index:5;
  margin-top:15px;
  color:#00f0ff;
  text-decoration:none;
}

/* ===== ANIMATIONS ===== */
@keyframes starsFlow{
  from { background-position:0 0; }
  to { background-position:200% 200%; }
}

@keyframes glowPulse{
  from { transform:scale(1); }
  to { transform:scale(1.2); }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px){

  h2{
    font-size:1.1rem;
  }

  #chat-box{
    height:65vh;
    padding:12px;
  }

  .message{
    font-size:0.85rem;
    max-width:85%;
  }

  #chat-form{
    flex-direction:column;
  }

  #chat-form button{
    width:100%;
  }
}

@media (max-width: 480px){

  #chat-box{
    height:70vh;
  }

  .message{
    font-size:0.8rem;
  }
}
</style>
</head>

<body>

<div class="chat-page">

<h2>Chat avec <?= htmlspecialchars($matchUser['pseudo']) ?></h2>

<div id="chat-box"></div>

<form id="chat-form" method="POST">
    <input
        type="text"
        name="message"
        placeholder="Écris ton message..."
        required
    >

    <button type="submit">
        Envoyer
    </button>
</form>

<a class="back" href="matches.php">Voir mes matches</a>

</div>

<script>
function chargerMessages(){
    fetch("load_messages.php?id=<?= $match_id ?>")
        .then(r => r.text())
        .then(data => {
            const box = document.getElementById("chat-box");
            box.innerHTML = data;
            box.scrollTop = box.scrollHeight;
        });
}

setInterval(chargerMessages, 2000);
window.onload = chargerMessages;
</script>

</body>
</html>
