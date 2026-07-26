<?php
require_once dirname(dirname(__DIR__)) . '/includes/bootstrap.php';
Auth::requireRole('admin');

$adRepo = new AdRepository($pdo);
$analysisRepo = new AnalysisRepository($pdo);
$notifRepo = new NotificationRepository($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $adId = (int)($_POST['ad_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $sellerId = $adRepo->ownerOf($adId);

    if ($sellerId !== null && in_array($action, ['approve', 'reject'], true)) {
        if ($action === 'approve') {
            $adRepo->setStatus($adId, 'approved');
            $notifRepo->push($sellerId, $adId, 'Ad approved', 'Your ad is now live on AutoValue.', 'ad_approved');
            flash('success', 'Ad approved and published.');
        } else {
            $adRepo->setStatus($adId, 'rejected');
            $notifRepo->push($sellerId, $adId, 'Ad rejected', 'Your ad was rejected by the admin team.', 'ad_rejected');
            flash('success', 'Ad rejected.');
        }
    }
    redirect('/admin/ads.php');
}

$showAll = isset($_GET['all']);
$ads = $showAll ? $adRepo->allForAdmin() : $adRepo->pendingForAdmin();

$pageTitle = 'Review Ads';
require dirname(dirname(__DIR__)) . '/includes/header.php';
?>
<div class="container" style="padding-top:18px;padding-bottom:40px">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:8px">
    <h1 style="font-size:20px"><?= $showAll ? 'All Ads' : 'Ads Pending Review' ?> (<?= count($ads) ?>)</h1>
    <div>
      <a class="btn btn-outline btn-sm" href="/admin/ads.php">Pending</a>
      <a class="btn btn-outline btn-sm" href="/admin/ads.php?all=1">All ads</a>
    </div>
  </div>

  <?php if (!$ads): ?><div class="card">Nothing here right now.</div><?php endif; ?>

  <table class="table">
    <?php if ($ads): ?>
    <tr><th>Ad</th><th>Seller</th><th>Price</th><th>AI analysis</th><th>Status</th><th>Actions</th></tr>
    <?php endif; ?>
    <?php foreach ($ads as $ad): ?>
      <?php $an = $analysisRepo->latestForAd((int)$ad['ad_id']); ?>
      <tr>
        <td><a href="/ad.php?id=<?= (int)$ad['ad_id'] ?>"><?= e($ad['title']) ?></a><br>
            <span style="color:var(--muted);font-size:12px"><?= e($ad['make']) ?> <?= e($ad['model']) ?> · <?= (int)$ad['manufacture_year'] ?> · <?= e($ad['district']) ?></span></td>
        <td><?= e($ad['seller_name']) ?><br><?= posterBadge($ad['poster_type']) ?></td>
        <td style="color:var(--price-green);font-weight:700"><?= money((float)$ad['price']) ?></td>
        <td>
          <?php if ($an): ?>
            <span class="chip chip-approved"><?= e(ucfirst($an['result'])) ?></span><br>
            <span style="font-size:12px;color:var(--muted)">
              Predicted <?= money((float)$an['predicted_price']) ?><br>
              Range <?= money((float)$an['lower_bound']) ?> - <?= money((float)$an['upper_bound']) ?><br>
              Confidence <?= round((float)$an['confidence_score'] * 100) ?>%
            </span>
          <?php else: ?>
            <span style="color:var(--muted)">No analysis</span>
          <?php endif; ?>
        </td>
        <td><?= statusChip($ad['status']) ?></td>
        <td>
          <div class="actions">
            <?php if ($ad['status'] === 'pending_review'): ?>
              <form method="post"><?php echo csrfField(); ?>
                <input type="hidden" name="ad_id" value="<?= (int)$ad['ad_id'] ?>">
                <input type="hidden" name="action" value="approve">
                <button class="btn btn-green btn-sm" type="submit">Approve</button>
              </form>
              <form method="post"><?php echo csrfField(); ?>
                <input type="hidden" name="ad_id" value="<?= (int)$ad['ad_id'] ?>">
                <input type="hidden" name="action" value="reject">
                <button class="btn btn-danger btn-sm" type="submit">Reject</button>
              </form>
            <?php endif; ?>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php require dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
