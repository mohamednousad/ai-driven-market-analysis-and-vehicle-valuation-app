<?php
require_once __DIR__ . '/app/bootstrap.php';
Auth::requireLogin();
$pageTitle = 'Promote Ads';
$userId = (int)Auth::id();
$adModel = new AdModel();
$approvedAds = array_values(array_filter($adModel->bySeller($userId), fn($a) => $a['status'] === 'approved'));
$subModel = new SubscriptionModel();
$activeSub = $subModel->activeForUser($userId);
$promoUsage = $activeSub ? $subModel->promotionUsage((int)$activeSub['id']) : null;
$freeLeft = (int)($promoUsage['remaining_count'] ?? 0);
$settings = Database::getInstance()->fetchAll('SELECT * FROM settings');
$settingMap = array_column($settings, 'value', 'key');
$promoTypes = [
    'featured' => ['label' => 'Featured', 'icon' => 'fa-bolt', 'desc' => 'Gold FEATURED badge plus priority in the featured row on the homepage.', 'price' => (float)($settingMap['promo_price_featured'] ?? 2000)],
    'top_listing' => ['label' => 'Top Listing', 'icon' => 'fa-arrow-up', 'desc' => 'Your ad stays pinned above regular results in browse and search.', 'price' => (float)($settingMap['promo_price_top_listing'] ?? 3500)],
    'highlight' => ['label' => 'Highlight', 'icon' => 'fa-star', 'desc' => 'A gold highlight ring that makes your card pop in every list.', 'price' => (float)($settingMap['promo_price_highlight'] ?? 1500)],
];
$sellerProfile = (new SellerProfileModel())->findByUserId($userId);
$sellerType = $sellerProfile['seller_type'] ?? 'non_member';
$preselect = (int)Helpers::get('ad', '0');
require __DIR__ . '/partials/header.php';
?>
<div class="dash-shell">
  <?php require __DIR__ . '/partials/dash-side.php'; ?>
  <main class="dash-main">
    <div class="dash-top"><h2>Promote Your Ads</h2></div>
    <div class="plan-usage-card">
      <div>
        <h4><i class="fa-solid fa-bolt"></i> Free promotions</h4>
        <p><?= $activeSub ? 'Included with your ' . Helpers::e($activeSub['plan_name']) . '.' : 'Subscribe to Member or Premium to earn free promotions.' ?></p>
      </div>
      <div style="font-family:var(--mono);font-size:26px;color:var(--gold-bright)"><?= $freeLeft ?> <span style="font-size:12px;color:rgba(255,255,255,.6)">remaining</span></div>
    </div>
    <?php if (!$approvedAds): ?>
    <div class="panel"><div class="empty-state"><i class="fa-solid fa-bolt"></i><h4>No approved ads to promote</h4><p>Only live, AI-approved ads can be promoted.</p></div></div>
    <?php else: ?>
    <div class="panel">
      <div class="panel-head"><h3>Choose an ad and a boost</h3></div>
      <div class="panel-body">
        <div class="form-group">
          <label>Select your ad</label>
          <select id="promoAd">
            <?php foreach ($approvedAds as $a): ?>
            <option value="<?= (int)$a['id'] ?>" <?= $preselect === (int)$a['id'] ? 'selected' : '' ?>><?= Helpers::e($a['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="tier-grid" style="margin-top:18px">
          <?php foreach ($promoTypes as $key => $pt): ?>
          <div class="tier-card">
            <div class="tier-icon"><i class="fa-solid <?= $pt['icon'] ?>"></i></div>
            <h3><?= Helpers::e($pt['label']) ?></h3>
            <div class="price"><?= Helpers::e(Helpers::price($pt['price'])) ?> <span>/ <?= (int)($settingMap['promo_duration_days'] ?? 7) ?> days</span></div>
            <ul><li><i class="fa-solid fa-check"></i> <?= Helpers::e($pt['desc']) ?></li></ul>
            <?php if ($freeLeft > 0): ?>
            <button class="btn btn-gold btn-block js-promo" data-type="<?= $key ?>" data-mode="free" data-label="<?= Helpers::e($pt['label']) ?>" type="button"><span class="btn-label">Use 1 Free Promotion</span></button>
            <?php else: ?>
            <button class="btn btn-outline-dark btn-block js-promo" data-type="<?= $key ?>" data-mode="paid" data-price="<?= $pt['price'] ?>" data-label="<?= Helpers::e($pt['label']) ?>" type="button"><span class="btn-label">Pay <?= Helpers::e(Helpers::price($pt['price'])) ?></span></button>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </main>
</div>
<?php
$pageScript = <<<'JS'
$('.js-promo').on('click', function () {
  var btn = $(this);
  var adId = $('#promoAd').val();
  var mode = btn.data('mode');
  var label = btn.data('label');
  var confirmText = mode === 'free'
    ? 'This will use 1 of your free promotions to apply the ' + label + ' boost.'
    : 'You will be charged for the ' + label + ' boost on this ad.';
  AV.confirm('Apply ' + label + ' boost?', confirmText, function () {
    AV.setLoading(btn, true);
    AV.ajax('api/pay.php', { type: 'promotion', ad_id: adId, promo_type: btn.data('type'), mode: mode }, function (res) {
      if (res.payhere) { AV.gatewaySubmit(res.payhere); return; }
      if (res.redirect) { window.location = res.redirect; return; }
      AV.setLoading(btn, false);
      AV.toast('success', res.message || 'Promotion applied.');
      setTimeout(function () { window.location.reload(); }, 1200);
    }, function (msg) {
      AV.setLoading(btn, false);
      AV.toast('error', msg);
    });
  }, mode === 'free' ? 'Use Free Promotion' : 'Proceed to Payment');
});
JS;
require __DIR__ . '/partials/footer.php';
?>
