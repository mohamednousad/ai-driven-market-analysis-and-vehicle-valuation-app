<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireLogin();

$repo = new NotificationRepository($pdo);
$items = $repo->forUser((int)Auth::id());
$repo->markAllRead((int)Auth::id());

$pageTitle = 'Notifications';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="container" style="max-width:720px;padding-top:18px;padding-bottom:40px">
  <h1 style="font-size:20px;margin-bottom:14px">Notifications</h1>
  <?php if (!$items): ?><div class="card">Nothing here yet.</div><?php endif; ?>
  <?php foreach ($items as $n): ?>
    <div class="card" style="padding:13px 15px">
      <strong><?= e($n['title']) ?></strong>
      <div style="color:var(--muted);font-size:13px;margin:3px 0"><?= e($n['message']) ?></div>
      <div style="font-size:12px;color:#b3b3b3">
        <?= e(timeAgo($n['created_at'])) ?>
        <?php if ($n['ad_id']): ?> · <a href="/ad.php?id=<?= (int)$n['ad_id'] ?>">View ad</a><?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
