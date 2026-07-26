<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireLogin();

$chats = (new ChatRepository($pdo))->inboxFor((int)Auth::id());

$pageTitle = 'My Chats';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="container" style="max-width:720px;padding-top:18px;padding-bottom:40px">
  <h1 style="font-size:20px;margin-bottom:14px">My Chats</h1>
  <?php if (!$chats): ?><div class="card">No conversations yet. Open an ad and press "Chat with seller".</div><?php endif; ?>
  <div class="chat-list">
    <?php foreach ($chats as $c): ?>
      <?php $other = (int)$c['buyer_id'] === (int)Auth::id() ? $c['seller_name'] : $c['buyer_name']; ?>
      <a href="/chat.php?chat_id=<?= (int)$c['chat_id'] ?>">
        <div class="cl-title"><?= e($c['ad_title']) ?> · with <?= e($other) ?></div>
        <div class="cl-last"><?= e($c['last_message'] ?? 'No messages yet') ?></div>
      </a>
    <?php endforeach; ?>
  </div>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
