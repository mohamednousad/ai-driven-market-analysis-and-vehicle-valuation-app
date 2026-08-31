<?php
require_once __DIR__ . '/app/bootstrap.php';
Auth::requireLogin();
$pageTitle = 'My Favourites';
$userId = (int)Auth::id();
$adIds = (new FavouriteModel())->adIdsForUser($userId);
$adModel = new AdModel();
$cards = [];
foreach ($adIds as $favId) {
    $card = $adModel->findFull((int)$favId);
    if ($card && $card['status'] === 'approved') {
        $cards[] = $card;
    }
}
$sellerProfile = (new SellerProfileModel())->findByUserId($userId);
$sellerType = $sellerProfile['seller_type'] ?? 'non_member';
require __DIR__ . '/partials/header.php';
?>
<div class="dash-shell">
  <?php require __DIR__ . '/partials/dash-side.php'; ?>
  <main class="dash-main">
    <div class="dash-top"><h2>My Favourites</h2></div>
    <?php if (!$cards): ?>
    <div class="panel"><div class="empty-state"><i class="fa-regular fa-heart"></i><h4>No favourites yet</h4><p>Tap the heart on any listing to save it here.</p></div></div>
    <?php else: ?>
    <div class="car-grid" style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr))">
      <?php foreach ($cards as $card) { include __DIR__ . '/partials/ad-card.php'; } ?>
    </div>
    <?php endif; ?>
  </main>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
