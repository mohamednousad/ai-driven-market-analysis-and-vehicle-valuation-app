<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../lib/AdRepository.php';
require_once __DIR__ . '/../../lib/UserRepository.php';

require_role(ROLE_ADMIN);
$assetBase = '../';

$adRepo = new AdRepository($pdo);
$userRepo = new UserRepository($pdo);

$pendingAds = $adRepo->allForAdmin(AD_STATUS_PENDING);
$approvedCount = count($adRepo->allForAdmin(AD_STATUS_APPROVED));
$sellers = $userRepo->countByRole(ROLE_SELLER);
$buyers = $userRepo->countByRole(ROLE_BUYER);

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="section">
    <span class="eyebrow">Admin console</span>
    <h1 class="mb-0">Platform overview</h1>
</section>

<section class="section grid grid-4">
    <div class="card tight stat-card">
        <div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div>
        <div class="stat-body"><strong><?php echo count($pendingAds); ?></strong><span>Ads pending</span></div>
    </div>
    <div class="card tight stat-card">
        <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-body"><strong><?php echo $approvedCount; ?></strong><span>Ads live</span></div>
    </div>
    <div class="card tight stat-card">
        <div class="stat-icon"><i class="fa-solid fa-tag"></i></div>
        <div class="stat-body"><strong><?php echo $sellers; ?></strong><span>Sellers</span></div>
    </div>
    <div class="card tight stat-card">
        <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
        <div class="stat-body"><strong><?php echo $buyers; ?></strong><span>Buyers</span></div>
    </div>
</section>

<section class="section">
    <div class="flex-between">
        <h2 class="mb-0">Ads awaiting review</h2>
        <a href="ads.php?status=pending" class="btn secondary small">Go to moderation</a>
    </div>
    <div class="divider"></div>
    <?php if (!$pendingAds): ?>
        <div class="card empty-state"><i class="fa-solid fa-inbox"></i><p>Nothing waiting for review. You're all caught up.</p></div>
    <?php else: ?>
        <div class="table-card">
            <table>
                <thead><tr><th>Vehicle</th><th>Seller</th><th>Asking</th><th>Fair range</th><th>Posted</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($pendingAds, 0, 8) as $ad): ?>
                    <tr>
                        <td><strong><?php echo e($ad['title']); ?></strong></td>
                        <td><?php echo e($ad['seller_name']); ?></td>
                        <td><?php echo format_money($ad['asking_price']); ?></td>
                        <td class="muted" style="font-size:13px"><?php echo $ad['lower_bound'] ? format_money($ad['lower_bound']) . ' – ' . format_money($ad['upper_bound']) : 'N/A'; ?></td>
                        <td class="muted" style="font-size:13px"><?php echo time_ago($ad['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
