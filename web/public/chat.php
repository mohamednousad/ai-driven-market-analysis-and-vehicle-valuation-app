<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireLogin();

$chatRepo = new ChatRepository($pdo);
$adRepo = new AdRepository($pdo);

$chatId = (int)($_GET['chat_id'] ?? 0);
$adId = (int)($_GET['ad_id'] ?? 0);

if ($chatId === 0 && $adId > 0) {
    if (Auth::role() !== 'buyer') {
        flash('error', 'Only buyers can start a chat from an ad.');
        redirect('/ad.php?id=' . $adId);
    }
    if ($adRepo->ownerOf($adId) === null) {
        flash('error', 'Ad not found.');
        redirect('/index.php');
    }
    $chatId = $chatRepo->openOrCreate($adId, (int)Auth::id());
}

$chat = $chatRepo->find($chatId);
if (!$chat || !$chatRepo->canAccess($chat, (int)Auth::id())) {
    flash('error', 'Chat not found.');
    redirect('/chats.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $text = trim($_POST['message'] ?? '');
    if ($text !== '' && mb_strlen($text) <= 2000) {
        $chatRepo->addMessage($chatId, (int)Auth::id(), $text);
        $recipient = (int)Auth::id() === (int)$chat['buyer_id'] ? (int)$chat['seller_id'] : (int)$chat['buyer_id'];
        (new NotificationRepository($pdo))->push(
            $recipient, (int)$chat['ad_id'],
            'New message',
            mb_substr($text, 0, 120),
            'chat_message'
        );
    }
    redirect('/chat.php?chat_id=' . $chatId);
}

$messages = $chatRepo->messages($chatId);
$other = (int)$chat['buyer_id'] === (int)Auth::id() ? $chat['seller_name'] : $chat['buyer_name'];

$pageTitle = 'Chat: ' . $chat['ad_title'];
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="container" style="max-width:720px;padding-top:18px;padding-bottom:40px">
  <div class="card" style="display:flex;justify-content:space-between;align-items:center">
    <div>
      <strong><?= e($chat['ad_title']) ?></strong>
      <div style="color:var(--muted);font-size:13px">Chatting with <?= e($other) ?> · <?= money((float)$chat['price']) ?></div>
    </div>
    <a class="btn btn-outline btn-sm" href="/ad.php?id=<?= (int)$chat['ad_id'] ?>">View ad</a>
  </div>

  <div class="chat-box">
    <div class="chat-messages">
      <?php if (!$messages): ?><div class="msg-typing">Say hello to start the conversation.</div><?php endif; ?>
      <?php foreach ($messages as $m): ?>
        <div class="msg <?= (int)$m['sender_id'] === (int)Auth::id() ? 'msg-mine' : 'msg-theirs' ?>">
          <div class="m-who"><?= e($m['sender_name']) ?> · <?= e(timeAgo($m['created_at'])) ?></div>
          <?= e($m['message_text']) ?>
        </div>
      <?php endforeach; ?>
    </div>
    <form class="chat-input" method="post">
      <?= csrfField() ?>
      <input type="text" name="message" maxlength="2000" placeholder="Write a message..." autofocus autocomplete="off">
      <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
    </form>
  </div>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
