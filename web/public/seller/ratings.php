<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../lib/RatingRepository.php';

require_role(ROLE_SELLER);
$assetBase = '../';

$ratingRepo = new RatingRepository($pdo);
$summary = $ratingRepo->summary(current_user_id());
$ratings = $ratingRepo->forSeller(current_user_id());

$pageTitle = 'My Ratings';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="section">
    <span class="eyebrow">Reputation</span>
    <h1 class="mb-0">Buyer ratings</h1>
</section>

<section class="section grid grid-sidebar">
    <div class="card text-center">
        <div style="font-size:52px;font-weight:800;color:var(--orange-dark);letter-spacing:-.03em"><?php echo number_format($summary['avg_rating'], 1); ?></div>
        <?php echo star_rating_html($summary['avg_rating']); ?>
        <p class="muted" style="margin-top:10px"><?php echo $summary['rating_count']; ?> verified <?php echo $summary['rating_count'] === 1 ? 'rating' : 'ratings'; ?></p>
    </div>
    <div class="card">
        <h3>What buyers say</h3>
        <div class="divider"></div>
        <?php if (!$ratings): ?>
            <div class="empty-state">
                <i class="fa-regular fa-star"></i>
                <p>No ratings yet. Ratings appear here after buyers rate you.</p>
            </div>
        <?php else: ?>
            <div class="stack">
                <?php foreach ($ratings as $r): ?>
                    <div style="padding:14px;border:1px solid var(--line);border-radius:14px">
                        <div class="flex-between">
                            <strong><?php echo e($r['buyer_name']); ?></strong>
                            <?php echo star_rating_html((float)$r['rating']); ?>
                        </div>
                        <?php if ($r['comment']): ?>
                            <p class="muted" style="margin:8px 0 0"><?php echo e($r['comment']); ?></p>
                        <?php endif; ?>
                        <div class="muted" style="font-size:12px;margin-top:6px"><?php echo time_ago($r['created_at']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
