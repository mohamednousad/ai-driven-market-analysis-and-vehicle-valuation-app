<?php
require_once dirname(dirname(__DIR__)) . '/includes/bootstrap.php';
Auth::requireRole('seller');

$adRepo = new AdRepository($pdo);
$promoRepo = new PromotionRepository($pdo);
$adId = (int)($_REQUEST['ad_id'] ?? 0);

if ($adRepo->ownerOf($adId) !== (int)Auth::id()) {
    flash('error', 'You can only promote your own ads.');
    redirect('/seller/my-ads.php');
}

$prices = unserialize(PROMO_PRICES);
$active = $promoRepo->activeForAd($adId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $type = $_POST['promotion_type'] ?? '';
    $days = (int)($_POST['days'] ?? 0);
    if ($promoRepo->purchase($adId, $type, $days)) {
        (new NotificationRepository($pdo))->push(
            (int)Auth::id(), $adId,
            'Promotion activated',
            'Your ' . str_replace('_', ' ', $type) . ' promotion is live for ' . $days . ' day(s).',
            'promotion'
        );
        flash('success', 'Payment simulated and promotion activated.');
        redirect('/seller/my-ads.php');
    }
    flash('error', 'Invalid promotion selection.');
    redirect('/seller/promote.php?ad_id=' . $adId);
}

$pageTitle = 'Promote ad';
require dirname(dirname(__DIR__)) . '/includes/header.php';
?>
<div class="form-card">
  <h1>Promote your ad</h1>
  <?php if ($active): ?>
    <div class="alert alert-success">
      This ad already has an active <?= e(str_replace('_', ' ', $active['promotion_type'])) ?> promotion
      until <?= e(date('d M Y', strtotime($active['ends_at']))) ?>.
    </div>
  <?php endif; ?>
  <form method="post">
    <?= csrfField() ?>
    <input type="hidden" name="ad_id" value="<?= $adId ?>">
    <div class="form-group">
      <label>Promotion type</label>
      <select name="promotion_type">
        <option value="top_ad">Top Ad — <?= money($prices['top_ad']) ?>/day</option>
        <option value="featured">Featured — <?= money($prices['featured']) ?>/day</option>
        <option value="urgent">Urgent — <?= money($prices['urgent']) ?>/day</option>
      </select>
    </div>
    <div class="form-group">
      <label>Duration (days)</label>
      <select name="days">
        <option value="3">3 days</option>
        <option value="7">7 days</option>
        <option value="14">14 days</option>
        <option value="30">30 days</option>
      </select>
    </div>
    <div class="form-hint" style="margin-bottom:12px">Payment is simulated for this academic project. No real money moves.</div>
    <button class="btn btn-primary btn-block" type="submit"><i class="fa-solid fa-credit-card"></i> Pay and activate</button>
  </form>
</div>
<?php require dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
