<?php
require_once dirname(dirname(__DIR__)) . '/includes/bootstrap.php';
Auth::requireRole('buyer');

$gemini = new GeminiChat();

$pageTitle = 'AI Assistant';
require dirname(dirname(__DIR__)) . '/includes/header.php';
?>
<div class="container" style="max-width:720px;padding-top:18px;padding-bottom:40px">
  <h1 style="font-size:20px;margin-bottom:6px">AutoValue AI Assistant</h1>
  <p style="color:var(--muted);font-size:13px;margin-bottom:14px">
    Tell the assistant your budget and needs. It will help you decide which vehicle to search for.
  </p>
  <?php if (!$gemini->isConfigured()): ?>
    <div class="alert alert-error">The assistant is not configured yet. Add your GEMINI_API_KEY in web/config/config.php.</div>
  <?php endif; ?>
  <div class="chat-box assistant-box">
    <div class="chat-messages" id="assistant-messages">
      <div class="msg msg-theirs">Hi! I am the AutoValue assistant. What is your budget in rupees, and how will you use the vehicle?</div>
    </div>
    <form class="chat-input" id="assistant-form">
      <input type="text" id="assistant-input" maxlength="1000" placeholder="e.g. I have 8 million for a family hybrid" autocomplete="off">
      <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
    </form>
  </div>
</div>
<script>
(function () {
  var form = document.getElementById('assistant-form');
  var input = document.getElementById('assistant-input');
  var box = document.getElementById('assistant-messages');
  var history = [];

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var text = input.value.trim();
    if (!text) return;
    append('msg-mine', text);
    history.push({ role: 'user', text: text });
    input.value = '';
    var typing = document.createElement('div');
    typing.className = 'msg-typing';
    typing.textContent = 'Assistant is thinking...';
    box.appendChild(typing);
    box.scrollTop = box.scrollHeight;

    fetch('/api/chat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ history: history.slice(0, -1), message: text })
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        typing.remove();
        var reply = data.success ? data.text : data.message;
        append('msg-theirs', reply);
        if (data.success) history.push({ role: 'model', text: reply });
      })
      .catch(function () {
        typing.remove();
        append('msg-theirs', 'Could not reach the assistant. Try again.');
      });
  });

  function append(cls, text) {
    var div = document.createElement('div');
    div.className = 'msg ' + cls;
    div.textContent = text;
    box.appendChild(div);
    box.scrollTop = box.scrollHeight;
  }
})();
</script>
<?php require dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
