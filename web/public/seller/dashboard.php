<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../lib/AdRepository.php';
require_once __DIR__ . '/../../lib/RatingRepository.php';

require_role(ROLE_SELLER);
$assetBase = '../';

$adRepo = new AdRepository($pdo);
$ratingRepo = new RatingRepository($pdo);
$sellerId = current_user_id();

$approved = $adRepo->countBySellerStatus($sellerId, AD_STATUS_APPROVED);
$pending = $adRepo->countBySellerStatus($sellerId, AD_STATUS_PENDING);
$rejected = $adRepo->countBySellerStatus($sellerId, AD_STATUS_REJECTED);
$rating = $ratingRepo->summary($sellerId);
$recent = array_slice($adRepo->bySeller($sellerId), 0, 5);

$pageTitle = 'Seller Dashboard';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="section">
    <span class="eyebrow">Seller console</span>
    <div class="flex-between flex-wrap">
        <h1 class="mb-0">Hello, <?php echo e(current_name()); ?></h1>
        <a href="post-ad.php" class="btn primary"><i class="fa-solid fa-plus"></i> Post a vehicle</a>
    </div>
</section>

<section class="section grid grid-4">
    <div class="card tight stat-card">
        <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-body"><strong><?php echo $approved; ?></strong><span>Approved ads</span></div>
    </div>
    <div class="card tight stat-card">
        <div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div>
        <div class="stat-body"><strong><?php echo $pending; ?></strong><span>Pending review</span></div>
    </div>
    <div class="card tight stat-card">
        <div class="stat-icon"><i class="fa-solid fa-ban"></i></div>
        <div class="stat-body"><strong><?php echo $rejected; ?></strong><span>Rejected</span></div>
    </div>
    <div class="card tight stat-card">
        <div class="stat-icon"><i class="fa-solid fa-star"></i></div>
        <div class="stat-body"><strong><?php echo number_format($rating['avg_rating'], 1); ?></strong><span><?php echo $rating['rating_count']; ?> ratings</span></div>
    </div>
</section>

<section class="section grid grid-sidebar">
    <div class="card">
        <span class="eyebrow">How it works</span>
        <h3>Fair pricing, automatically</h3>
        <p>When you post a vehicle, our AI valuation model checks your asking price against the fair market range. Fair-priced ads go live after admin approval; over- or under-priced ads are flagged so you can adjust.</p>
        <div class="stack" style="margin-top:8px">
            <div class="spec-chip"><i class="fa-solid fa-1"></i> Enter vehicle details</div>
            <div class="spec-chip"><i class="fa-solid fa-2"></i> Get instant AI valuation</div>
            <div class="spec-chip"><i class="fa-solid fa-3"></i> Submit for approval</div>
        </div>
    </div>
    <div class="card">
        <div class="flex-between">
            <h3 class="mb-0">Recent listings</h3>
            <a href="my-ads.php" class="btn secondary small">View all</a>
        </div>
        <div class="divider"></div>
        <?php if (!$recent): ?>
            <div class="empty-state">
                <i class="fa-solid fa-car"></i>
                <p>You haven't posted any vehicles yet.</p>
                <a href="post-ad.php" class="btn primary small">Post your first ad</a>
            </div>
        <?php else: ?>
            <div class="stack">
                <?php foreach ($recent as $ad): ?>
                    <div class="flex-between" style="padding:12px;border:1px solid var(--line);border-radius:14px">
                        <div>
                            <strong><?php echo e($ad['title']); ?></strong>
                            <div class="muted" style="font-size:13px"><?php echo format_money($ad['asking_price']); ?> · <?php echo time_ago($ad['created_at']); ?></div>
                        </div>
                        <span class="badge <?php echo status_badge_class($ad['status']); ?>"><?php echo ucfirst($ad['status']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
