<?php
require_once __DIR__ . '/app/bootstrap.php';
Auth::requireLogin();
$pageTitle = 'Notifications';
$userId = (int)Auth::id();
$notifModel = new NotificationModel();
$items = $notifModel->forUser($userId, 60);
$notifModel->markAllRead($userId);
$sellerProfile = (new SellerProfileModel())->findByUserId($userId);
$sellerType = $sellerProfile['seller_type'] ?? 'non_member';
$icons = ['system' => 'fa-gear', 'ad' => 'fa-car', 'payment' => 'fa-credit-card', 'message' => 'fa-comment'];
require __DIR__ . '/partials/header.php';
?>
<div class="dash-shell">
  <?php require __DIR__ . '/partials/dash-side.php'; ?>
  <main class="dash-main">
    <div class="dash-top"><h2>Notifications</h2></div>
    <div class="panel">
      <?php if (!$items): ?>
      <div class="empty-state"><i class="fa-regular fa-bell"></i><h4>Nothing here yet</h4><p>Ad approvals, payments, and messages will show up here.</p></div>
      <?php else: ?>
      <?php foreach ($items as $n): ?>
      <div class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
        <div class="ni-icon"><i class="fa-solid <?= $icons[$n['type']] ?? 'fa-bell' ?>"></i></div>
        <div>
          <b><?= Helpers::e($n['title']) ?></b>
          <p><?= Helpers::e($n['message']) ?></p>
          <span class="ni-time"><?= Helpers::e(Helpers::timeAgo($n['created_at'])) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
