<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireAdmin();
$pageTitle = 'Ads Moderation';
$adModel = new AdModel();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireCsrf();
    $adId = (int)Helpers::post('ad_id');
    $action = Helpers::post('action');
    $ad = $adModel->findFull($adId);
    if ($ad) {
        if ($action === 'approve') {
            $adModel->changeStatus($adId, 'approved', (int)$ad['seller_id'], (string)$ad['title']);
            Flash::success('Ad approved.');
        } elseif ($action === 'reject') {
            $adModel->changeStatus($adId, 'rejected', (int)$ad['seller_id'], (string)$ad['title']);
            Flash::warning('Ad rejected.');
        } elseif ($action === 'delete') {
            $adModel->delete($adId, (int)$ad['seller_id']);
            Flash::success('Ad deleted permanently.');
        }
    }
    Helpers::redirect('admin/ads.php');
}
$filter = Helpers::get('status', 'pending');
if (!in_array($filter, ['pending', 'rejected', 'approved', 'sold'], true)) {
    $filter = 'pending';
}
$db = Database::getInstance();
$ids = array_column($db->fetchAll('SELECT id FROM ads WHERE status = ? ORDER BY created_at DESC LIMIT 100', [$filter]), 'id');
$all = [];
foreach ($ids as $id) {
    $row = $adModel->findFull((int)$id);
    if ($row) { $all[] = $row; }
}
require dirname(__DIR__) . '/partials/header.php';
?>
<div class="dash-shell">
  <?php require dirname(__DIR__) . '/partials/admin-side.php'; ?>
  <main class="dash-main">
    <div class="dash-top"><h2>Ads Moderation</h2></div>
    <div class="admin-tabs">
      <a href="?status=pending" class="<?= $filter === 'pending' ? 'active' : '' ?>">Pending</a>
      <a href="?status=rejected" class="<?= $filter === 'rejected' ? 'active' : '' ?>">Rejected</a>
      <a href="?status=approved" class="<?= $filter === 'approved' ? 'active' : '' ?>">Approved</a>
      <a href="?status=sold" class="<?= $filter === 'sold' ? 'active' : '' ?>">Sold</a>
    </div>
    <div class="panel">
      <?php if (!$all): ?>
      <div class="empty-state"><i class="fa-solid fa-check-double"></i><h4>Nothing here</h4><p>No ads with this status.</p></div>
      <?php else: ?>
      <div class="table-wrap"><table>
        <thead><tr><th>Vehicle</th><th>Seller</th><th>Price</th><th>AI Verdict</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($all as $r): ?>
          <tr>
            <td><div class="td-car"><img src="<?= Helpers::e(Helpers::img($r['primary_image'], 'https://images.unsplash.com/photo-1493238792000-8113da705763?auto=format&fit=crop&w=200&q=60')) ?>" alt=""><a href="<?= Config::baseUrl('ad.php?id=' . (int)$r['id']) ?>"><?= Helpers::e($r['title']) ?></a></div></td>
            <td><?= Helpers::e($r['seller_name']) ?></td>
            <td style="font-family:var(--mono)"><?= Helpers::e(Helpers::price($r['price'])) ?></td>
            <td><?php if ($r['fair_price_status'] === 'fair'): ?><span class="status-pill status-approved">FAIR</span><?php elseif ($r['fair_price_status'] === 'not_fair'): ?><span class="status-pill status-rejected">NOT FAIR</span><?php else: ?><span class="status-pill status-pending">NONE</span><?php endif; ?></td>
            <td><span class="status-pill status-<?= Helpers::e($r['status']) ?>"><?= strtoupper(Helpers::e($r['status'])) ?></span></td>
            <td>
              <div style="display:flex;gap:12px;flex-wrap:wrap">
                <?php if ($r['status'] !== 'approved'): ?>
                <form method="post" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?= Helpers::e(Session::csrfToken()) ?>">
                  <input type="hidden" name="action" value="approve">
                  <input type="hidden" name="ad_id" value="<?= (int)$r['id'] ?>">
                  <button type="button" class="link-action ok js-confirm" data-title="Approve this ad?" data-text="It will override the AI verdict and go live now." data-yes="Yes, approve">Approve</button>
                </form>
                <?php endif; ?>
                <?php if ($r['status'] !== 'rejected'): ?>
                <form method="post" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?= Helpers::e(Session::csrfToken()) ?>">
                  <input type="hidden" name="action" value="reject">
                  <input type="hidden" name="ad_id" value="<?= (int)$r['id'] ?>">
                  <button type="button" class="link-action danger js-confirm" data-title="Reject this ad?" data-text="The ad will be hidden from buyers." data-yes="Yes, reject" data-danger="1">Reject</button>
                </form>
                <?php endif; ?>
                <form method="post" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?= Helpers::e(Session::csrfToken()) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="ad_id" value="<?= (int)$r['id'] ?>">
                  <button type="button" class="link-action danger js-confirm" data-title="Permanently delete this ad?" data-text="Deletes photos, chats, and analysis. Cannot be undone." data-yes="Yes, delete" data-danger="1">Delete</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
      <?php endif; ?>
    </div>
  </main>
</div>
<?php require dirname(__DIR__) . '/partials/footer.php'; ?>
