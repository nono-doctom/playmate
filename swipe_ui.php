<?php
include 'auth.php';
requireLogin();
?>
<?php include 'musique.php'; ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Swipe</title>
  <style>
    body { font-family: Arial, sans-serif; display:flex; justify-content:center; margin-top:40px; }
    #card { width:320px; border:1px solid #ddd; padding:16px; text-align:center; }
    img.avatar { width:120px; height:120px; border-radius:50%; object-fit:cover; }
    .buttons { margin-top:12px; display:flex; justify-content:space-around; }
    button { padding:10px 14px; font-size:16px; border-radius:6px; cursor:pointer; }
    .like { background:#28a745; color:white; border:none; }
    .dislike { background:#dc3545; color:white; border:none; }
  </style>
</head>
<body>
    <iframe src="musique.php" style="display:none;" id="music-frame"></iframe>
  <div id="card">
    <div id="profile">
      <p>Chargement...</p>
    </div>
    <div class="buttons" id="actions" style="display:none;">
      <button class="dislike" id="btnDislike">Dislike</button>
      <button class="like" id="btnLike">Like</button>
    </div>
  </div>

<script>
let users = [];
let index = 0;

async function fetchUsers() {
  const res = await fetch('suggestions.php?limit=20');
  const data = await res.json();
  if (data.success) {
    users = data.users;
    index = 0;
    showNext();
  } else {
    document.getElementById('profile').innerText = 'Erreur chargement';
  }
}

function showNext() {
  const el = document.getElementById('profile');
  if (index >= users.length) {
    el.innerHTML = '<p>Plus de profils pour le moment.</p>';
    document.getElementById('actions').style.display = 'none';
    return;
  }
  const u = users[index];
  el.innerHTML = `
    <img class="avatar" src="${u.avatar || 'https://via.placeholder.com/120'}" alt="">
    <h3>${u.username || 'Utilisateur'}</h3>
    <p>${u.bio || ''}</p>
  `;
  document.getElementById('actions').style.display = 'flex';
}

async function sendAction(to_user, action) {
  const res = await fetch('action_swipe.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({to_user, action})
  });
  return await res.json();
}

document.getElementById('btnLike').addEventListener('click', async () => {
  if (!users[index]) return;
  const u = users[index];
  const r = await sendAction(u.id, 'like');
  if (r.success && r.match) {
    alert('C’est un MATCH ! 🎉');
  }
  // retire la personne de l'affichage (ne reviendra pas)
  index++;
  showNext();
});

document.getElementById('btnDislike').addEventListener('click', async () => {
  if (!users[index]) return;
  const u = users[index];
  const r = await sendAction(u.id, 'dislike');
  if (r.success) {
    // retire la personne
    index++;
    showNext();
  }
});

fetchUsers();
</script>
</body>
</html>
