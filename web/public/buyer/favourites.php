<?php
require_once dirname(dirname(__DIR__)) . '/includes/bootstrap.php';
Auth::requireRole('buyer');

$favIds = (new FavouriteRepository($pdo))->idsForBuyer((int)Auth::id());
$ads = [];
if ($favIds) {
    $adRepo = new AdRepository($pdo);
    foreach ($favIds as $id) {
        $ad = $adRepo->findDetail($id);
        if ($ad && in_array($ad['status'], ['approved', 'sold'], true)) {
            $ads[] = $ad;
        }
    }
}

$pageTitle = 'My Favourites';
require dirname(dirname(__DIR__)) . '/includes/header.php';
?>
<div class="container" style="padding-top:18px;padding-bottom:40px">
  <h1 style="font-size:20px;margin-bottom:14px">My Favourites (<?= count($ads) ?>)</h1>
  <?php if (!$ads): ?><div class="card">No saved ads yet. Tap "Save ad" on any listing.</div><?php endif; ?>
  <?php foreach ($ads as $ad): ?>
    <article class="ad-card">
      <a class="ad-thumb" href="/ad.php?id=<?= (int)$ad['ad_id'] ?>">
        <?php if ($ad['images']): ?>
          <img src="/<?= e(UPLOAD_URL . '/' . $ad['images'][0]) ?>" alt="<?= e($ad['title']) ?>">
        <?php else: ?>
          <span class="no-img"><i class="fa-solid fa-car"></i></span>
        <?php endif; ?>
      </a>
      <div class="ad-body">
        <a class="ad-title" href="/ad.php?id=<?= (int)$ad['ad_id'] ?>"><?= e($ad['title']) ?></a>
        <div class="ad-specline">
          <?= number_format((int)$ad['mileage_km']) ?> km<span class="sep">|</span><?= e(ucfirst($ad['fuel_type'])) ?>
          <?php if ($ad['status'] === 'sold'): ?><span class="sep">|</span><?= statusChip('sold') ?><?php endif; ?>
        </div>
        <div class="ad-locline"><?= e($ad['district']) ?>, Cars</div>
        <div class="ad-price"><?= money((float)$ad['price']) ?></div>
      </div>
    </article>
  <?php endforeach; ?>
</div>
<?php require dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
