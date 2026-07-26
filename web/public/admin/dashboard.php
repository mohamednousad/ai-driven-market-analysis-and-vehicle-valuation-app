<?php
require_once dirname(dirname(__DIR__)) . '/includes/bootstrap.php';
Auth::requireRole('admin');

$adCounts = (new AdRepository($pdo))->counts();
$userCounts = (new UserRepository($pdo))->counts();
$pendingReports = (new ReportRepository($pdo))->pendingCount();

$pageTitle = 'Admin Dashboard';
require dirname(dirname(__DIR__)) . '/includes/header.php';
?>
<div class="container" style="padding-top:18px;padding-bottom:40px">
  <h1 style="font-size:20px;margin-bottom:14px">Admin Dashboard</h1>

  <div class="stat-grid">
    <div class="stat"><div class="num"><?= $adCounts['pending_review'] ?></div><div class="lbl">Ads pending review</div></div>
    <div class="stat"><div class="num"><?= $adCounts['approved'] ?></div><div class="lbl">Live ads</div></div>
    <div class="stat"><div class="num"><?= $adCounts['sold'] ?></div><div class="lbl">Sold</div></div>
    <div class="stat"><div class="num"><?= $adCounts['rejected'] ?></div><div class="lbl">Rejected</div></div>
    <div class="stat"><div class="num"><?= $pendingReports ?></div><div class="lbl">Open reports</div></div>
  </div>
  <div class="stat-grid">
    <div class="stat"><div class="num"><?= $userCounts['seller'] ?></div><div class="lbl">Sellers</div></div>
    <div class="stat"><div class="num"><?= $userCounts['buyer'] ?></div><div class="lbl">Buyers</div></div>
    <div class="stat"><div class="num"><?= $userCounts['admin'] ?></div><div class="lbl">Admins</div></div>
  </div>

  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <a class="btn btn-primary" href="/admin/ads.php"><i class="fa-solid fa-list-check"></i> Review ads</a>
    <a class="btn btn-outline" href="/admin/users.php"><i class="fa-solid fa-users"></i> Manage users</a>
    <a class="btn btn-outline" href="/admin/reports.php"><i class="fa-regular fa-flag"></i> Reports</a>
  </div>
</div>
<?php require dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
