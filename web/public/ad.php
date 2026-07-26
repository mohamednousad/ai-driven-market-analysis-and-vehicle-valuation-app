<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$adRepo = new AdRepository($pdo);
$adId = (int)($_GET['id'] ?? 0);
$ad = $adRepo->findDetail($adId);

if (!$ad) {
    http_response_code(404);
    flash('error', 'That ad could not be found.');
    redirect('/index.php');
}

$viewerId = Auth::id();
$isOwner = $viewerId !== null && $viewerId === (int)$ad['seller_id'];
$isAdmin = Auth::role() === 'admin';

if ($ad['status'] !== 'approved' && $ad['status'] !== 'sold' && !$isOwner && !$isAdmin) {
    flash('error', 'This ad is not published.');
    redirect('/index.php');
}

if (!$isOwner) {
    $adRepo->incrementViews($adId);
    $ad['views_count']++;
}

$analysis = (new AnalysisRepository($pdo))->latestForAd($adId);
$ratingRepo = new RatingRepository($pdo);
$sellerRating = $ratingRepo->sellerSummary((int)$ad['seller_id']);
$recentRatings = $ratingRepo->recentForSeller((int)$ad['seller_id']);

$favRepo = new FavouriteRepository($pdo);
$isFav = $viewerId && Auth::role() === 'buyer' ? $favRepo->isFavourite($viewerId, $adId) : false;
$myRating = $viewerId && Auth::role() === 'buyer' ? $ratingRepo->forAdByBuyer($adId, $viewerId) : null;

$features = $ad['features'] ? (json_decode($ad['features'], true) ?: []) : [];

$pageTitle = $ad['title'];
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="container detail-grid">
  <div class="detail-main">
    <div class="card">
      <h1 class="detail-title"><?= e($ad['title']) ?> <?= $ad['status'] === 'sold' ? statusChip('sold') : '' ?></h1>
      <div class="detail-sub">
        <span><i class="fa-regular fa-clock"></i> Posted <?= e(timeAgo($ad['published_at'] ?? $ad['created_at'])) ?>, <?= e($ad['district']) ?></span>
        <span><i class="fa-regular fa-eye"></i> <?= number_format((int)$ad['views_count']) ?> views</span>
        <?php if ($isOwner || $isAdmin): ?><span><?= statusChip($ad['status']) ?></span><?php endif; ?>
      </div>

      <?php if ($ad['images']): ?>
        <div class="gallery-main">
          <img id="gallery-main-img" src="/<?= e(UPLOAD_URL . '/' . $ad['images'][0]) ?>" alt="<?= e($ad['title']) ?>">
        </div>
        <?php if (count($ad['images']) > 1): ?>
          <div class="gallery-thumbs">
            <?php foreach ($ad['images'] as $i => $img): ?>
              <img src="/<?= e(UPLOAD_URL . '/' . $img) ?>" data-full="/<?= e(UPLOAD_URL . '/' . $img) ?>"
                   class="<?= $i === 0 ? 'active' : '' ?>" alt="photo <?= $i + 1 ?>">
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <div class="price-line">
        <span class="price-big"><?= money((float)$ad['price']) ?></span>
        <?php if ($ad['is_negotiable']): ?><span class="negotiable">Negotiable</span><?php endif; ?>
      </div>

      <?php if ($analysis && $analysis['result'] === 'fair'): ?>
        <div class="ai-chip"><i class="fa-solid fa-robot"></i> AI verified fair price
          · predicted <?= money((float)$analysis['predicted_price']) ?>
          · confidence <?= round((float)$analysis['confidence_score'] * 100) ?>%</div>
      <?php endif; ?>

      <table class="spec-table">
        <tr><td>Brand</td><td><?= e($ad['make']) ?></td></tr>
        <tr><td>Model</td><td><?= e($ad['model']) ?></td></tr>
        <tr><td>Year of Manufacture</td><td><?= (int)$ad['manufacture_year'] ?></td></tr>
        <?php if ($ad['register_year']): ?><tr><td>Year of Registration</td><td><?= (int)$ad['register_year'] ?></td></tr><?php endif; ?>
        <tr><td>Condition</td><td>Used</td></tr>
        <tr><td>Transmission</td><td><?= e(ucfirst($ad['transmission'])) ?></td></tr>
        <?php if ($ad['body_type']): ?><tr><td>Body type</td><td><?= e($ad['body_type']) ?></td></tr><?php endif; ?>
        <tr><td>Fuel type</td><td><?= e(ucfirst($ad['fuel_type'])) ?></td></tr>
        <?php if ($ad['engine_cc']): ?><tr><td>Engine capacity</td><td><?= number_format((int)$ad['engine_cc']) ?> cc</td></tr><?php endif; ?>
        <tr><td>Mileage</td><td><?= number_format((int)$ad['mileage_km']) ?> km</td></tr>
        <?php if ($features): ?><tr><td>Features</td><td><?= e(implode(', ', $features)) ?></td></tr><?php endif; ?>
      </table>

      <?php if ($ad['description']): ?>
        <h3 style="margin:16px 0 6px">Description</h3>
        <p style="white-space:pre-line;line-height:1.6"><?= e($ad['description']) ?></p>
      <?php endif; ?>
    </div>

    <div class="card">
      <h3 style="margin-bottom:8px">Seller ratings
        <?php if ($sellerRating['total']): ?>
          <span class="stars">
            <?php for ($s = 1; $s <= 5; $s++): ?><i class="fa-<?= $s <= round($sellerRating['average']) ? 'solid' : 'regular' ?> fa-star"></i><?php endfor; ?>
          </span>
          <?= $sellerRating['average'] ?> (<?= $sellerRating['total'] ?>)
        <?php else: ?>
          <span style="color:var(--muted);font-weight:400;font-size:13px">No ratings yet</span>
        <?php endif; ?>
      </h3>
      <?php foreach ($recentRatings as $r): ?>
        <div class="rating-row">
          <span class="stars"><?php for ($s = 1; $s <= 5; $s++): ?><i class="fa-<?= $s <= (int)$r['rating'] ? 'solid' : 'regular' ?> fa-star"></i><?php endfor; ?></span>
          <?= e($r['comment']) ?>
          <div class="who"><?= e($r['buyer_name']) ?> · <?= e(timeAgo($r['created_at'])) ?></div>
        </div>
      <?php endforeach; ?>

      <?php if (Auth::role() === 'buyer'): ?>
        <form method="post" action="/rate.php" style="margin-top:12px">
          <?= csrfField() ?>
          <input type="hidden" name="ad_id" value="<?= $adId ?>">
          <div class="form-row">
            <div class="form-group">
              <label>Rate this seller</label>
              <select name="rating">
                <?php for ($s = 5; $s >= 1; $s--): ?>
                  <option value="<?= $s ?>" <?= $myRating && (int)$myRating['rating'] === $s ? 'selected' : '' ?>><?= $s ?> star<?= $s > 1 ? 's' : '' ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="form-group" style="flex:2">
              <label>Comment</label>
              <input type="text" name="comment" maxlength="500" value="<?= e($myRating['comment'] ?? '') ?>" placeholder="How was the experience?">
            </div>
          </div>
          <button class="btn btn-outline btn-sm" type="submit"><?= $myRating ? 'Update rating' : 'Submit rating' ?></button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <aside class="detail-side">
    <div class="card seller-card">
      <h3>For sale by <?= e($ad['seller_name']) ?> <?= posterBadge($ad['poster_type']) ?></h3>
      <div class="seller-since">Member since <?= date('M Y', strtotime($ad['seller_since'])) ?></div>
      <?php if ($ad['seller_phone']): ?>
        <button class="btn btn-green btn-block" id="reveal-phone" data-phone="<?= e($ad['seller_phone']) ?>">
          <i class="fa-solid fa-phone"></i> <?= e(substr($ad['seller_phone'], 0, 4)) ?>XXXXXX · Click to show
        </button>
      <?php endif; ?>
      <?php if (Auth::role() === 'buyer' && !$isOwner): ?>
        <a class="btn btn-primary btn-block" href="/chat.php?ad_id=<?= $adId ?>"><i class="fa-regular fa-comment-dots"></i> Chat with seller</a>
        <form method="post" action="/favourite.php">
          <?= csrfField() ?>
          <input type="hidden" name="ad_id" value="<?= $adId ?>">
          <button class="btn btn-outline btn-block" type="submit">
            <i class="fa-<?= $isFav ? 'solid' : 'regular' ?> fa-heart"></i> <?= $isFav ? 'Saved to favourites' : 'Save ad' ?>
          </button>
        </form>
      <?php elseif (!Auth::check()): ?>
        <a class="btn btn-primary btn-block" href="/login.php">Log in to chat with seller</a>
      <?php endif; ?>
      <?php if (Auth::check() && !$isOwner): ?>
        <button class="btn btn-outline btn-block btn-sm" data-modal-open="report-modal"><i class="fa-regular fa-flag"></i> Report this ad</button>
      <?php endif; ?>
      <?php if ($isOwner && $ad['status'] === 'approved'): ?>
        <a class="btn btn-outline btn-block" href="/seller/promote.php?ad_id=<?= $adId ?>"><i class="fa-solid fa-bolt"></i> Promote this ad</a>
      <?php endif; ?>
    </div>

    <div class="card safety-card">
      <h3><i class="fa-solid fa-shield-halved"></i> Stay Alert: Avoid Online Scams</h3>
      <ul>
        <li>AutoValue support will never message you in chat asking for payments.</li>
        <li>Never share OTPs or card details, and never pay without seeing the vehicle.</li>
        <li>AutoValue has no delivery service. All payments happen off-platform.</li>
      </ul>
    </div>
  </aside>
</div>

<div class="modal-back" id="report-modal">
  <div class="modal">
    <h3>Report this ad</h3>
    <form method="post" action="/report.php">
      <?= csrfField() ?>
      <input type="hidden" name="ad_id" value="<?= $adId ?>">
      <div class="form-group">
        <label>Reason</label>
        <select name="report_type">
          <option value="spam">Spam</option>
          <option value="fraud">Fraud / scam</option>
          <option value="misleading_price">Misleading price</option>
          <option value="incorrect_info">Incorrect information</option>
          <option value="offensive">Offensive content</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div class="form-group">
        <label>Details (optional)</label>
        <textarea name="reason" rows="3" maxlength="500"></textarea>
      </div>
      <button class="btn btn-danger" type="submit">Submit report</button>
      <button class="btn btn-outline" type="button" data-modal-close>Cancel</button>
    </form>
  </div>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
