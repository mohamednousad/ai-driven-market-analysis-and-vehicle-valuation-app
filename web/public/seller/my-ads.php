<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../lib/AdRepository.php';

require_role(ROLE_SELLER);
$assetBase = '../';

$adRepo = new AdRepository($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'delete' && verify_csrf()) {
    $adRepo->delete((int)post('ad_id'), current_user_id());
    set_flash('success', 'Ad deleted.');
    redirect('my-ads.php');
}

$ads = $adRepo->bySeller(current_user_id());
$pageTitle = 'My Ads';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="section flex-between flex-wrap">
    <div>
        <span class="eyebrow">My listings</span>
        <h1 class="mb-0">My vehicle ads</h1>
    </div>
    <a href="post-ad.php" class="btn primary"><i class="fa-solid fa-plus"></i> Post new</a>
</section>

<?php if (!$ads): ?>
    <div class="card empty-state">
        <i class="fa-solid fa-car"></i>
        <p>No ads yet. Post your first vehicle to get started.</p>
        <a href="post-ad.php" class="btn primary small">Post an ad</a>
    </div>
<?php else: ?>
    <div class="table-card">
        <table>
            <thead>
                <tr><th>Vehicle</th><th>Asking</th><th>Fair range</th><th>Status</th><th>Posted</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($ads as $ad): ?>
                <tr>
                    <td>
                        <strong><?php echo e($ad['title']); ?></strong>
                        <div class="muted" style="font-size:13px"><?php echo e($ad['brand'] . ' · ' . $ad['model_year'] . ' · ' . number_format($ad['mileage']) . ' km'); ?></div>
                    </td>
                    <td><strong><?php echo format_money($ad['asking_price']); ?></strong></td>
                    <td class="muted" style="font-size:13px">
                        <?php echo $ad['lower_bound'] ? format_money($ad['lower_bound']) . ' – ' . format_money($ad['upper_bound']) : 'N/A'; ?>
                    </td>
                    <td><span class="badge <?php echo status_badge_class($ad['status']); ?>"><?php echo ucfirst($ad['status']); ?></span></td>
                    <td class="muted" style="font-size:13px"><?php echo time_ago($ad['created_at']); ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Delete this ad?');" style="margin:0">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="ad_id" value="<?php echo (int)$ad['id']; ?>">
                            <button class="btn danger small"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
