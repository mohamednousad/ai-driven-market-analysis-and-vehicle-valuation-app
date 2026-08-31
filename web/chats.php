<?php
require_once __DIR__ . '/app/bootstrap.php';
Auth::requireLogin();
$pageTitle = 'Chats';
$userId = (int)Auth::id();
$chatModel = new ChatModel();
$chatList = $chatModel->listForUser($userId);
$activeChatId = (int)Helpers::get('chat', '0');
$activeChat = $activeChatId ? $chatModel->find($activeChatId, $userId) : null;
if ($activeChat) {
    $chatModel->markRead($activeChatId, $userId);
}
$activeMessages = $activeChat ? $chatModel->messages($activeChatId) : [];
$otherId = $activeChat ? ((int)$activeChat['buyer_id'] === $userId ? (int)$activeChat['seller_id'] : (int)$activeChat['buyer_id']) : 0;
$otherUser = $otherId ? (new UserModel())->findById($otherId) : null;
require __DIR__ . '/partials/header.php';
?>
<div class="chat-shell <?= $activeChat ? 'viewing' : '' ?>">
  <div class="chat-list">
    <div class="chat-list-head"><h3>Messages</h3></div>
    <?php if (!$chatList): ?>
    <div class="empty-state"><i class="fa-regular fa-comments"></i><h4>No chats yet</h4><p>Start a chat from any car listing.</p></div>
    <?php endif; ?>
    <?php foreach ($chatList as $c): ?>
    <a class="chat-item <?= (int)$c['id'] === $activeChatId ? 'active' : '' ?>" href="<?= Config::baseUrl('chats.php?chat=' . (int)$c['id']) ?>">
      <img src="<?= Helpers::e(Helpers::avatar($c['other_avatar'])) ?>" alt="">
      <div class="meta">
        <div class="row1"><span><?= Helpers::e($c['other_name']) ?></span><span class="time"><?= Helpers::e(Helpers::timeAgo($c['last_time'] ?? $c['created_at'])) ?></span></div>
        <div class="row2"><?= Helpers::e($c['last_message'] ?? 'Say hello') ?></div>
      </div>
      <?php if ((int)$c['unread'] > 0): ?><span class="unread-dot"><?= (int)$c['unread'] ?></span><?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>
  <div class="chat-window">
    <?php if (!$activeChat): ?>
    <div class="empty-state" style="margin:auto"><i class="fa-regular fa-comment-dots"></i><h4>Select a conversation</h4><p>Pick a chat from the left to start messaging.</p></div>
    <?php else: ?>
    <div class="chat-window-head">
      <button class="chat-back" type="button" onclick="window.location='<?= Config::baseUrl('chats.php') ?>'"><i class="fa-solid fa-arrow-left"></i></button>
      <img src="<?= Helpers::e(Helpers::avatar($otherUser['profile_image'] ?? null)) ?>" alt="">
      <div><b><?= Helpers::e($otherUser['username'] ?? 'User') ?></b><span style="font-size:11px;color:var(--metallic-grey)"><?= (int)$activeChat['ad_seller'] === $otherId ? 'Seller' : 'Buyer' ?></span></div>
    </div>
    <a class="chat-ad-strip" href="<?= Config::baseUrl('ad.php?id=' . (int)$activeChat['ad_id']) ?>">
      <img src="<?= Helpers::e(Helpers::img($activeChat['ad_image'], 'https://images.unsplash.com/photo-1493238792000-8113da705763?auto=format&fit=crop&w=200&q=60')) ?>" alt="">
      <div><?= Helpers::e($activeChat['ad_title']) ?><b><?= Helpers::e(Helpers::price($activeChat['ad_price'])) ?></b></div>
    </a>
    <div class="chat-body" id="chatBody">
      <?php foreach ($activeMessages as $m): ?>
      <div class="msg <?= (int)$m['sender_id'] === $userId ? 'out' : 'in' ?>">
        <?= Helpers::e($m['message']) ?>
        <span class="msg-time"><?= Helpers::e(Helpers::timeAgo($m['created_at'])) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <form class="chat-input-row" id="chatForm">
      <input type="text" id="chatMessage" placeholder="Type your message..." autocomplete="off">
      <button class="btn btn-gold" type="submit"><i class="fa-solid fa-paper-plane"></i></button>
    </form>
    <?php endif; ?>
  </div>
</div>
<?php
if ($activeChat) {
    $pageScript = 'var chatId = ' . (int)$activeChatId . ";\n" . <<<'JS'
var lastCount = $('#chatBody .msg').length;
$('#chatBody').scrollTop($('#chatBody')[0].scrollHeight);
$('#chatForm').on('submit', function (e) {
  e.preventDefault();
  var text = $('#chatMessage').val().trim();
  if (!text) return;
  $('#chatMessage').val('');
  AV.ajax('api/messages.php', { action: 'send', chat_id: chatId, message: text }, function () {
    poll(true);
  });
});
function poll(force) {
  AV.ajax('api/messages.php', { action: 'fetch', chat_id: chatId }, function (res) {
    if (res.items.length !== lastCount || force) {
      lastCount = res.items.length;
      var $body = $('#chatBody').empty();
      res.items.forEach(function (m) {
        var $m = $('<div class="msg ' + (m.mine ? 'out' : 'in') + '"><span class="m-text"></span><span class="msg-time"></span></div>');
        $m.find('.m-text').text(m.message);
        $m.find('.msg-time').text(m.time_ago);
        $body.append($m);
      });
      $body.scrollTop($body[0].scrollHeight);
    }
  });
}
setInterval(poll, 4000);
JS;
}
require __DIR__ . '/partials/footer.php';
?>
