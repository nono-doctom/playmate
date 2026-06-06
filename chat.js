document.addEventListener("DOMContentLoaded", () => {
  // Attend que toute la page HTML soit chargée avant d’exécuter le code

  const chatBox = document.getElementById("chat-box");
  // Zone où s’affichent les messages

  const form = document.getElementById("chat-form");
  // Formulaire d’envoi de message

  const input = document.getElementById("msg");
  // Champ de texte du message

  const toUser = chatBox.dataset.to;
  // Récupère l’ID de l’utilisateur destinataire depuis data-to dans le HTML

  function loadMessages() {
    // Fonction qui récupère les messages depuis le serveur

    fetch(`messages_load.php?to=${toUser}`)
      .then(r => r.text())
      .then(html => {
        // On reçoit du HTML généré par PHP

        chatBox.innerHTML = html;
        // On affiche les messages dans la zone de chat

        chatBox.scrollTop = chatBox.scrollHeight;
        // Scroll automatique vers le bas (dernier message)
      });
  }

  // Recharge les messages toutes les 2 secondes
  setInterval(loadMessages, 2000);

  // Chargement initial des messages dès l’ouverture
  loadMessages();

  form.addEventListener("submit", e => {
    // Quand l’utilisateur envoie un message

    e.preventDefault();
    // Empêche le rechargement de la page

    const msg = input.value.trim();
    // Récupère le message sans espaces inutiles

    if (msg === "") return;
    // Empêche l’envoi de message vide

    fetch("send_message.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },

      body: `to=${toUser}&content=${encodeURIComponent(msg)}`
      // Envoie le message au serveur (destinataire + contenu)
    }).then(() => {
      input.value = "";
      // Vide le champ après envoi

      loadMessages();
      // Recharge immédiatement les messages
    });
  });
});