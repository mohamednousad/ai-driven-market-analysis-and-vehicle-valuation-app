<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../lib/AdRepository.php';
require_once __DIR__ . '/../../lib/RatingRepository.php';

require_role(ROLE_BUYER);
$assetBase = '../';

$adRepo = new AdRepository($pdo);
$ratingRepo = new RatingRepository($pdo);

$id = (int)query('id');
$ad = $adRepo->find($id);

if (!$ad || $ad['status'] !== AD_STATUS_APPROVED) {
    set_flash('error', 'That vehicle is not available.');
    redirect('browse.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'rate' && verify_csrf()) {
    $result = $ratingRepo->rate(
        (int)$ad['seller_id'],
        current_user_id(),
        $id,
        (int)post('rating'),
        trim(post('comment'))
    );
    set_flash($result['success'] ? 'success' : 'error', $result['message']);
    redirect('vehicle.php?id=' . $id);
}

$sellerSummary = $ratingRepo->summary((int)$ad['seller_id']);

$pageTitle = $ad['title'];
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="section">
    <a href="browse.php" class="btn secondary small"><i class="fa-solid fa-arrow-left"></i> Back to browse</a>
</section>

<div class="grid grid-2" style="align-items:start">
    <div class="stack">
        <div class="detail-media">
            <?php if (!empty($ad['image_path'])): ?>
                <img src="<?php echo $assetBase . UPLOAD_URL . '/' . e($ad['image_path']); ?>" alt="<?php echo e($ad['title']); ?>" style="width:100%;height:100%;object-fit:cover">
            <?php else: ?>
                <i class="fa-solid fa-car-side"></i>
            <?php endif; ?>
        </div>
        <div class="card">
            <h3>Specifications</h3>
            <div class="spec-list">
                <div class="spec-item"><i class="fa-solid fa-tag"></i><div><span>Brand</span><strong><?php echo e($ad['brand']); ?></strong></div></div>
                <div class="spec-item"><i class="fa-solid fa-car"></i><div><span>Model</span><strong><?php echo e($ad['vehicle_model'] ?: '—'); ?></strong></div></div>
                <div class="spec-item"><i class="fa-solid fa-calendar"></i><div><span>Year</span><strong><?php echo e($ad['model_year']); ?></strong></div></div>
                <div class="spec-item"><i class="fa-solid fa-gauge-high"></i><div><span>Mileage</span><strong><?php echo number_format($ad['mileage']); ?> km</strong></div></div>
                <div class="spec-item"><i class="fa-solid fa-oil-can"></i><div><span>Engine</span><strong><?php echo number_format($ad['engine_capacity']); ?> cc</strong></div></div>
                <div class="spec-item"><i class="fa-solid fa-gas-pump"></i><div><span>Fuel</span><strong><?php echo e($ad['fuel_type']); ?></strong></div></div>
                <div class="spec-item"><i class="fa-solid fa-gears"></i><div><span>Transmission</span><strong><?php echo e($ad['transmission']); ?></strong></div></div>
                <div class="spec-item"><i class="fa-solid fa-certificate"></i><div><span>Condition</span><strong><?php echo e($ad['condition_grade']); ?></strong></div></div>
            </div>
            <?php if ($ad['description']): ?>
                <div class="divider"></div>
                <h3>Description</h3>
                <p><?php echo nl2br(e($ad['description'])); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="stack">
        <div class="card">
            <span class="badge badge-approved"><i class="fa-solid fa-shield-halved"></i> AI fair price verified</span>
            <h1 style="margin-top:14px"><?php echo e($ad['title']); ?></h1>
            <div class="detail-price"><?php echo format_money($ad['asking_price']); ?></div>
            <?php if ($ad['predicted_price']): ?>
                <p class="muted" style="margin-top:8px">AI estimated fair range: <strong><?php echo format_money($ad['lower_bound']); ?> – <?php echo format_money($ad['upper_bound']); ?></strong></p>
            <?php endif; ?>
            <div class="spec-chip" style="margin-top:6px"><i class="fa-solid fa-location-dot"></i> <?php echo e($ad['location'] ?: 'Sri Lanka'); ?></div>
        </div>

        <div class="card">
            <h3>Seller</h3>
            <div class="flex-between">
                <div>
                    <strong><?php echo e($ad['seller_name']); ?></strong>
                    <div class="rating-line" style="margin-top:4px">
                        <?php echo star_rating_html($sellerSummary['avg_rating']); ?>
                        <?php echo $sellerSummary['rating_count'] > 0 ? number_format($sellerSummary['avg_rating'], 1) . ' (' . $sellerSummary['rating_count'] . ')' : 'No ratings yet'; ?>
                    </div>
                </div>
            </div>
            <div class="divider"></div>
            <div class="stack">
                <a href="tel:<?php echo e($ad['seller_phone']); ?>" class="btn primary full"><i class="fa-solid fa-phone"></i> <?php echo e($ad['seller_phone'] ?: 'Contact seller'); ?></a>
                <a href="mailto:<?php echo e($ad['seller_email']); ?>" class="btn secondary full"><i class="fa-solid fa-envelope"></i> Email seller</a>
            </div>
        </div>

        <div class="card">
            <h3>Rate this seller</h3>
            <p class="muted">Share your experience to help other buyers.</p>
            <form method="POST" class="form-grid">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="rate">
                <div class="field">
                    <label>Your rating</label>
                    <select name="rating" required>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Good</option>
                        <option value="3">3 - Average</option>
                        <option value="2">2 - Poor</option>
                        <option value="1">1 - Very poor</option>
                    </select>
                </div>
                <div class="field">
                    <label>Comment (optional)</label>
                    <textarea name="comment" placeholder="How was your experience?"></textarea>
                </div>
                <button type="submit" class="btn primary full"><i class="fa-solid fa-star"></i> Submit rating</button>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
