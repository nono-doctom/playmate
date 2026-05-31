document.addEventListener("DOMContentLoaded", () => {
  const chatBox = document.getElementById("chat-box");
  const form = document.getElementById("chat-form");
  const input = document.getElementById("msg"); // 🔥 FIX ICI
  const toUser = chatBox.dataset.to;

  function loadMessages() {
    fetch(`messages_load.php?to=${toUser}`) // 🔥 FIX ICI
      .then(r => r.text())
      .then(html => {
        chatBox.innerHTML = html;
        chatBox.scrollTop = chatBox.scrollHeight;
      });
  }

  setInterval(loadMessages, 2000);
  loadMessages();

  form.addEventListener("submit", e => {
    e.preventDefault();

    const msg = input.value.trim();
    if(msg === "") return;

    fetch("send_message.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `to=${toUser}&content=${encodeURIComponent(msg)}`
    }).then(() => {
      input.value = "";
      loadMessages();
    });
  });
});