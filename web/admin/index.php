<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireAdmin();
$pageTitle = 'Admin Overview';
$db = Database::getInstance();
$stats = [
    'users' => (new UserModel())->countAll(),
    'ads' => (int)($db->fetchOne("SELECT COUNT(*) AS c FROM ads WHERE status = 'approved'")['c'] ?? 0),
    'pending' => (int)($db->fetchOne("SELECT COUNT(*) AS c FROM ads WHERE status = 'pending'")['c'] ?? 0),
    'reports' => (new ReportModel())->pendingCount(),
    'authorized' => (new AuthorizedRequestModel())->pendingCount(),
    'revenue' => (new PaymentModel())->revenueThisMonth(),
];
$recentAds = array_slice((new AdModel())->pendingForAdmin(), 0, 6);
require dirname(__DIR__) . '/partials/header.php';
?>
<div class="dash-shell">
  <?php require dirname(__DIR__) . '/partials/admin-side.php'; ?>
  <main class="dash-main">
    <div class="dash-top"><h2>Admin Overview</h2></div>
    <div class="stat-cards">
      <div class="stat-card"><div class="top-row"><span>Total Users</span><div class="icon"><i class="fa-solid fa-users"></i></div></div><b><?= number_format($stats['users']) ?></b></div>
      <div class="stat-card"><div class="top-row"><span>Live Ads</span><div class="icon"><i class="fa-solid fa-car"></i></div></div><b><?= number_format($stats['ads']) ?></b></div>
      <div class="stat-card"><div class="top-row"><span>Pending Ads</span><div class="icon"><i class="fa-solid fa-hourglass-half"></i></div></div><b><?= number_format($stats['pending']) ?></b></div>
      <div class="stat-card"><div class="top-row"><span>Revenue (MTD)</span><div class="icon"><i class="fa-solid fa-money-bill-wave"></i></div></div><b style="font-size:19px"><?= Helpers::e(Helpers::price($stats['revenue'])) ?></b></div>
    </div>
    <div class="stat-cards">
      <div class="stat-card"><div class="top-row"><span>Reports awaiting review</span><div class="icon"><i class="fa-regular fa-flag"></i></div></div><b><?= $stats['reports'] ?></b></div>
      <div class="stat-card"><div class="top-row"><span>Authorized requests pending</span><div class="icon"><i class="fa-solid fa-shield-halved"></i></div></div><b><?= $stats['authorized'] ?></b></div>
    </div>
    <div class="panel">
      <div class="panel-head"><h3>Recent Ads Awaiting or Rejected</h3><a class="link-action" href="<?= Config::baseUrl('admin/ads.php') ?>">Manage all</a></div>
      <?php if (!$recentAds): ?>
      <div class="empty-state"><i class="fa-solid fa-check-double"></i><h4>All caught up</h4><p>No ads in the pending or rejected queue.</p></div>
      <?php else: ?>
      <div class="table-wrap"><table>
        <thead><tr><th>Vehicle</th><th>Seller</th><th>Price</th><th>AI Verdict</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($recentAds as $r): ?>
          <tr>
            <td><div class="td-car"><img src="<?= Helpers::e(Helpers::img($r['primary_image'], 'https://images.unsplash.com/photo-1493238792000-8113da705763?auto=format&fit=crop&w=200&q=60')) ?>" alt=""><a href="<?= Config::baseUrl('ad.php?id=' . (int)$r['id']) ?>"><?= Helpers::e($r['title']) ?></a></div></td>
            <td><?= Helpers::e($r['seller_name']) ?></td>
            <td style="font-family:var(--mono)"><?= Helpers::e(Helpers::price($r['price'])) ?></td>
            <td><?php if ($r['fair_price_status'] === 'fair'): ?><span class="status-pill status-approved">FAIR</span><?php elseif ($r['fair_price_status'] === 'not_fair'): ?><span class="status-pill status-rejected">NOT FAIR</span><?php else: ?><span class="status-pill status-pending">NONE</span><?php endif; ?></td>
            <td><span class="status-pill status-<?= Helpers::e($r['status']) ?>"><?= strtoupper(Helpers::e($r['status'])) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
      <?php endif; ?>
    </div>
  </main>
</div>
<?php require dirname(__DIR__) . '/partials/footer.php'; ?>
