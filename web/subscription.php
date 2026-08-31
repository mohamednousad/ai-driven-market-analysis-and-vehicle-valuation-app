<?php
require_once __DIR__ . '/app/bootstrap.php';
$pageTitle = 'Seller Plans';
$subModel = new SubscriptionModel();
$plans = $subModel->plans();
$activeSub = Auth::check() ? $subModel->activeForUser((int)Auth::id()) : null;
$promoUsage = $activeSub ? $subModel->promotionUsage((int)$activeSub['id']) : null;
$payments = Auth::check() ? (new PaymentModel())->forUser((int)Auth::id()) : [];
require __DIR__ . '/partials/header.php';
?>
<div class="page-head">
  <div class="container">
    <div class="breadcrumb"><a href="<?= Config::baseUrl('index.php') ?>">Home</a> / Plans</div>
    <h1>Seller Plans</h1>
  </div>
</div>
<section class="section">
  <div class="container">
    <?php if ($activeSub): ?>
    <div class="plan-usage-card" style="margin-bottom:34px">
      <div>
        <h4><i class="fa-solid fa-crown"></i> Current plan: <?= Helpers::e($activeSub['plan_name']) ?></h4>
        <p>Active until <?= Helpers::e(date('d M Y', strtotime($activeSub['end_date']))) ?></p>
      </div>
      <?php if ($promoUsage): ?>
      <div class="promo-meter">
        <div class="bar-bg"><div class="bar-fill" style="width:<?= $promoUsage['total_limit'] > 0 ? round($promoUsage['used_count'] / $promoUsage['total_limit'] * 100) : 0 ?>%"></div></div>
        <span><?= (int)$promoUsage['remaining_count'] ?> of <?= (int)$promoUsage['total_limit'] ?> free promotions left</span>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <div class="tier-grid">
      <?php foreach ($plans as $i => $plan): $featuredPlan = (int)$plan['free_promotions'] === 8; ?>
      <div class="tier-card <?= $featuredPlan ? 'featured' : '' ?>">
        <?php if ($featuredPlan): ?><span class="ribbon">MOST POWERFUL</span><?php endif; ?>
        <div class="tier-icon"><i class="fa-solid <?= ['fa-chart-line', 'fa-medal', 'fa-crown'][$i] ?? 'fa-star' ?>"></i></div>
        <h3><?= Helpers::e($plan['name']) ?></h3>
        <div class="price"><?= Helpers::e(Helpers::price($plan['price'])) ?> <span>/ <?= (int)$plan['duration'] ?> days</span></div>
        <ul>
          <?php foreach (explode('|', (string)$plan['features']) as $feat): ?>
          <li><i class="fa-solid fa-check"></i> <?= Helpers::e($feat) ?></li>
          <?php endforeach; ?>
        </ul>
        <?php if ($activeSub && (int)$activeSub['plan_id'] === (int)$plan['id']): ?>
        <button class="btn btn-outline-dark btn-block" disabled>Current Plan</button>
        <?php else: ?>
        <button class="btn <?= $featuredPlan ? 'btn-gold' : 'btn-outline-dark' ?> btn-block js-buy-plan" data-plan="<?= (int)$plan['id'] ?>" data-name="<?= Helpers::e($plan['name']) ?>" data-price="<?= Helpers::e(Helpers::price($plan['price'])) ?>" type="button"><span class="btn-label">Get <?= Helpers::e($plan['name']) ?></span></button>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if ($payments): ?>
    <div class="panel" style="margin-top:44px">
      <div class="panel-head"><h3>Payment History</h3></div>
      <div class="table-wrap">
      <table>
        <thead><tr><th>Date</th><th>Category</th><th>Amount</th><th>Method</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($payments as $pay): ?>
          <tr>
            <td><?= Helpers::e($pay['paid_at'] ? date('d M Y', strtotime($pay['paid_at'])) : date('d M Y', strtotime($pay['created_at']))) ?></td>
            <td><?= Helpers::e(ucfirst($pay['category'])) ?></td>
            <td style="font-family:var(--mono)"><?= Helpers::e(Helpers::price($pay['amount'])) ?></td>
            <td><?= Helpers::e(ucfirst((string)$pay['method'])) ?></td>
            <td><span class="status-pill status-<?= Helpers::e($pay['status']) ?>"><?= strtoupper(Helpers::e($pay['status'])) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php
$loggedIn = Auth::check() ? 'true' : 'false';
$pageScript = <<<JS
$('.js-buy-plan').on('click', function () {
  if (!{$loggedIn}) { window.location = AV.base + '/login.php'; return; }
  var btn = $(this);
  var planName = btn.data('name');
  var planPrice = btn.data('price');
  AV.confirm('Subscribe to ' + planName + '?', 'You will be charged ' + planPrice + ' for 30 days of benefits.', function () {
    AV.setLoading(btn, true);
    AV.ajax('api/pay.php', { type: 'subscription', plan_id: btn.data('plan') }, function (res) {
      if (res.payhere) { AV.gatewaySubmit(res.payhere); return; }
      if (res.redirect) { window.location = res.redirect; return; }
      AV.setLoading(btn, false);
    }, function (msg) {
      AV.setLoading(btn, false);
      AV.toast('error', msg);
    });
  }, 'Proceed to Payment');
});
JS;
require __DIR__ . '/partials/footer.php';
?>
