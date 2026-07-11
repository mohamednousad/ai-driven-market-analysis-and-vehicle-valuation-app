<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../lib/AdRepository.php';

require_role(ROLE_ADMIN);
$assetBase = '../';

$adRepo = new AdRepository($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = post('action');
    $adId = (int)post('ad_id');
    if ($action === 'approve') {
        $adRepo->updateStatus($adId, AD_STATUS_APPROVED);
        set_flash('success', 'Ad approved and published.');
    } elseif ($action === 'reject') {
        $adRepo->updateStatus($adId, AD_STATUS_REJECTED);
        set_flash('success', 'Ad rejected.');
    } elseif ($action === 'delete') {
        $adRepo->delete($adId);
        set_flash('success', 'Ad deleted.');
    }
    redirect('ads.php' . (query('status') ? '?status=' . urlencode(query('status')) : ''));
}

$status = query('status');
$ads = $adRepo->allForAdmin($status);
$statuses = ['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'];

$pageTitle = 'Manage Ads';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="section">
    <span class="eyebrow">Moderation</span>
    <h1 class="mb-0">Manage vehicle ads</h1>
</section>

<div class="tag-row" style="margin-bottom:20px">
    <?php foreach ($statuses as $key => $label): ?>
        <a href="ads.php<?php echo $key ? '?status=' . $key : ''; ?>"
           class="btn <?php echo $status === $key ? 'primary' : 'secondary'; ?> small"><?php echo $label; ?></a>
    <?php endforeach; ?>
</div>

<?php if (!$ads): ?>
    <div class="card empty-state"><i class="fa-solid fa-inbox"></i><p>No ads in this category.</p></div>
<?php else: ?>
    <div class="table-card">
        <table>
            <thead><tr><th>Vehicle</th><th>Seller</th><th>Asking</th><th>Fair range</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($ads as $ad): ?>
                <tr>
                    <td>
                        <strong><?php echo e($ad['title']); ?></strong>
                        <div class="muted" style="font-size:13px"><?php echo e($ad['brand'] . ' · ' . $ad['model_year']); ?></div>
                    </td>
                    <td><?php echo e($ad['seller_name']); ?></td>
                    <td><strong><?php echo format_money($ad['asking_price']); ?></strong></td>
                    <td class="muted" style="font-size:13px"><?php echo $ad['lower_bound'] ? format_money($ad['lower_bound']) . ' – ' . format_money($ad['upper_bound']) : 'N/A'; ?></td>
                    <td><span class="badge <?php echo status_badge_class($ad['status']); ?>"><?php echo ucfirst($ad['status']); ?></span></td>
                    <td>
                        <div class="flex" style="gap:6px">
                            <?php if ($ad['status'] !== AD_STATUS_APPROVED): ?>
                                <form method="POST" style="margin:0">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="ad_id" value="<?php echo (int)$ad['id']; ?>">
                                    <button class="btn success small" title="Approve"><i class="fa-solid fa-check"></i></button>
                                </form>
                            <?php endif; ?>
                            <?php if ($ad['status'] !== AD_STATUS_REJECTED): ?>
                                <form method="POST" style="margin:0">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="reject">
                                    <input type="hidden" name="ad_id" value="<?php echo (int)$ad['id']; ?>">
                                    <button class="btn secondary small" title="Reject"><i class="fa-solid fa-xmark"></i></button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" style="margin:0" onsubmit="return confirm('Delete this ad permanently?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="ad_id" value="<?php echo (int)$ad['id']; ?>">
                                <button class="btn danger small" title="Delete"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
