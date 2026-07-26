<?php
require_once dirname(dirname(__DIR__)) . '/includes/bootstrap.php';
Auth::requireRole('seller');

$adRepo = new AdRepository($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $adId = (int)($_POST['ad_id'] ?? 0);
    if (($_POST['action'] ?? '') === 'mark_sold') {
        $ok = $adRepo->markSold($adId, (int)Auth::id());
        flash($ok ? 'success' : 'error', $ok ? 'Ad marked as sold.' : 'Only live ads you own can be marked as sold.');
    }
    redirect('/seller/my-ads.php');
}

$ads = $adRepo->bySeller((int)Auth::id());

$pageTitle = 'My Ads';
require dirname(dirname(__DIR__)) . '/includes/header.php';
?>
<div class="container" style="padding-top:18px;padding-bottom:40px">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
    <h1 style="font-size:20px">My Ads (<?= count($ads) ?>)</h1>
    <a class="btn btn-primary" href="/seller/post-ad.php"><i class="fa-solid fa-plus"></i> Post new ad</a>
  </div>

  <?php if (!$ads): ?><div class="card">You have not posted any ads yet.</div><?php endif; ?>

  <?php foreach ($ads as $ad): ?>
    <article class="ad-card <?= $ad['promo_type'] ? 'is-featured' : '' ?>">
      <?= promoTag($ad['promo_type']) ?>
      <a class="ad-thumb" href="/ad.php?id=<?= (int)$ad['ad_id'] ?>">
        <?php if ($ad['thumb']): ?>
          <img src="/<?= e(UPLOAD_URL . '/' . $ad['thumb']) ?>" alt="<?= e($ad['title']) ?>">
        <?php else: ?>
          <span class="no-img"><i class="fa-solid fa-car"></i></span>
        <?php endif; ?>
      </a>
      <div class="ad-body">
        <a class="ad-title" href="/ad.php?id=<?= (int)$ad['ad_id'] ?>"><?= e($ad['title']) ?></a>
        <div class="ad-specline"><?= statusChip($ad['status']) ?> · <?= number_format((int)$ad['views_count']) ?> views</div>
        <div class="ad-price"><?= money((float)$ad['price']) ?></div>
        <div style="display:flex;gap:8px;margin-top:9px;flex-wrap:wrap">
          <?php if ($ad['status'] === 'approved'): ?>
            <form method="post">
              <?php echo csrfField(); ?>
              <input type="hidden" name="ad_id" value="<?= (int)$ad['ad_id'] ?>">
              <input type="hidden" name="action" value="mark_sold">
              <button class="btn btn-outline btn-sm" type="submit">Mark as sold</button>
            </form>
            <a class="btn btn-outline btn-sm" href="/seller/promote.php?ad_id=<?= (int)$ad['ad_id'] ?>"><i class="fa-solid fa-bolt"></i> Promote</a>
          <?php endif; ?>
        </div>
      </div>
      <span class="ad-time"><?= e(timeAgo($ad['created_at'])) ?></span>
    </article>
  <?php endforeach; ?>
</div>
<?php require dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
