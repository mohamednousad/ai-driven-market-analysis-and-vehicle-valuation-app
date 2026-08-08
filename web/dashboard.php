<?php
require_once __DIR__ . '/app/bootstrap.php';
Auth::requireLogin();
$pageTitle = 'Seller Dashboard';
$userId = (int)Auth::id();
$adModel = new AdModel();
$stats = $adModel->statsForSeller($userId);
$myAds = array_slice($adModel->bySeller($userId), 0, 5);
$sellerProfile = (new SellerProfileModel())->findByUserId($userId);
$sellerType = $sellerProfile['seller_type'] ?? 'non_member';
$subModel = new SubscriptionModel();
$activeSub = $subModel->activeForUser($userId);
$promoUsage = $activeSub ? $subModel->promotionUsage((int)$activeSub['id']) : null;
require __DIR__ . '/partials/header.php';
?>
<div class="dash-shell">
  <?php require __DIR__ . '/partials/dash-side.php'; ?>
  <main class="dash-main">
    <div class="dash-top">
      <h2>Overview</h2>
      <a class="btn btn-gold btn-sm" href="<?= Config::baseUrl('post-ad.php') ?>"><i class="fa-solid fa-plus"></i> Post New Ad</a>
    </div>
    <div class="stat-cards">
      <div class="stat-card"><div class="top-row"><span>Active Ads</span><div class="icon"><i class="fa-solid fa-car"></i></div></div><b><?= $stats['active'] ?></b></div>
      <div class="stat-card"><div class="top-row"><span>Total Views</span><div class="icon"><i class="fa-regular fa-eye"></i></div></div><b><?= number_format($stats['views']) ?></b></div>
      <div class="stat-card"><div class="top-row"><span>Buyer Chats</span><div class="icon"><i class="fa-solid fa-comments"></i></div></div><b><?= $stats['chats'] ?></b></div>
      <div class="stat-card"><div class="top-row"><span>Seller Rating</span><div class="icon"><i class="fa-solid fa-star"></i></div></div><b><?= number_format($stats['rating'], 1) ?></b></div>
    </div>
    <?php if ($activeSub): ?>
    <div class="plan-usage-card">
      <div>
        <h4><i class="fa-solid fa-crown"></i> <?= Helpers::e($activeSub['plan_name']) ?></h4>
        <p>Active until <?= Helpers::e(date('d M Y', strtotime($activeSub['end_date']))) ?></p>
      </div>
      <?php if ($promoUsage): $pct = $promoUsage['total_limit'] > 0 ? round($promoUsage['used_count'] / $promoUsage['total_limit'] * 100) : 0; ?>
      <div class="promo-meter">
        <div class="bar-bg"><div class="bar-fill" style="width:<?= $pct ?>%"></div></div>
        <span><?= (int)$promoUsage['remaining_count'] ?> of <?= (int)$promoUsage['total_limit'] ?> free promotions left</span>
      </div>
      <?php endif; ?>
      <a class="btn btn-outline btn-sm" href="<?= Config::baseUrl('subscription.php') ?>">Manage Plan</a>
    </div>
    <?php else: ?>
    <div class="plan-usage-card">
      <div>
        <h4><i class="fa-regular fa-star"></i> No active plan</h4>
        <p>Unlock analytics, the Member badge, and free ad promotions.</p>
      </div>
      <a class="btn btn-gold btn-sm" href="<?= Config::baseUrl('subscription.php') ?>">View Plans</a>
    </div>
    <?php endif; ?>
    <div class="panel">
      <div class="panel-head"><h3>Recent Ads</h3><a class="link-action" href="<?= Config::baseUrl('my-ads.php') ?>">View all</a></div>
      <?php if (!$myAds): ?>
      <div class="empty-state"><i class="fa-solid fa-car"></i><h4>No ads yet</h4><p>Post your first vehicle and let the AI verify your price.</p></div>
      <?php else: ?>
      <div class="table-wrap">
      <table>
        <thead><tr><th>Vehicle</th><th>Price</th><th>AI Verdict</th><th>Status</th><th>Views</th></tr></thead>
        <tbody>
          <?php foreach ($myAds as $row): ?>
          <tr>
            <td><div class="td-car"><img src="<?= Helpers::e(Helpers::img($row['primary_image'], 'https://images.unsplash.com/photo-1493238792000-8113da705763?auto=format&fit=crop&w=200&q=60')) ?>" alt=""><span><?= Helpers::e($row['title']) ?></span></div></td>
            <td style="font-family:var(--mono)"><?= Helpers::e(Helpers::price($row['price'])) ?></td>
            <td><?php if ($row['fair_price_status'] === 'fair'): ?><span class="status-pill status-approved">FAIR</span><?php elseif ($row['fair_price_status'] === 'not_fair'): ?><span class="status-pill status-rejected">NOT FAIR</span><?php else: ?><span class="status-pill status-pending">WAITING</span><?php endif; ?></td>
            <td><span class="status-pill status-<?= Helpers::e($row['status']) ?>"><?= strtoupper(Helpers::e($row['status'])) ?></span></td>
            <td style="font-family:var(--mono)"><?= number_format((int)$row['views']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
      <?php endif; ?>
    </div>
  </main>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
