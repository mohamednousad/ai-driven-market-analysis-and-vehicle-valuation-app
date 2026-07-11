<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../lib/SettingsService.php';
require_once __DIR__ . '/../../lib/GeminiChat.php';

require_role(ROLE_BUYER);
$assetBase = '../';

$settings = new SettingsService($pdo);
$chat = new GeminiChat((string)$settings->get('gemini_api_key', ''), (string)$settings->get('gemini_model', 'gemini-1.5-flash'));
$configured = $chat->isConfigured();

$pageTitle = 'AI Assistant';
$pageScripts = ['assets/js/assistant.js'];
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="section">
    <span class="eyebrow">AI assistant</span>
    <h1 class="mb-0">Your vehicle-buying assistant</h1>
    <p>Tell the assistant your budget and what you need. It will help you shape a smart search.</p>
</section>

<?php if (!$configured): ?>
    <div class="alert warn"><i class="fa-solid fa-triangle-exclamation"></i><span>The AI assistant isn't configured yet. An administrator can add a Gemini API key in Settings to enable it.</span></div>
<?php endif; ?>

<div class="chat-shell">
    <div class="chat-log" id="chatLog">
        <div class="chat-msg assistant">Hi! I'm your AutoValue assistant. Tell me your budget in LKR and what you're looking for — for example, "a fuel-efficient hybrid under 8 million for city driving".</div>
    </div>
    <div class="chat-input">
        <input type="text" id="chatText" placeholder="Type your message..." <?php echo $configured ? '' : 'disabled'; ?>>
        <button class="btn primary" id="chatSend" <?php echo $configured ? '' : 'disabled'; ?>><i class="fa-solid fa-paper-plane"></i></button>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
