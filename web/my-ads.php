<?php
require_once __DIR__ . '/app/bootstrap.php';
Auth::requireLogin();
$pageTitle = 'My Ads';
$userId = (int)Auth::id();
$adModel = new AdModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireCsrf();
    $action = Helpers::post('action');
    $adId = (int)Helpers::post('ad_id');
    $owned = $adModel->findFull($adId);
    if (!$owned || (int)$owned['seller_id'] !== $userId) {
        Flash::error('That ad could not be found in your account.');
        Helpers::redirect('my-ads.php');
    }
    if ($action === 'delete') {
        $adModel->delete($adId, $userId);
        Flash::success('Ad deleted successfully.');
    } elseif ($action === 'mark_sold') {
        $adModel->changeStatus($adId, 'sold', $userId, (string)$owned['title']);
        Flash::success('Congratulations on the sale! Ad marked as sold.');
    } elseif ($action === 'update_price') {
        $newPrice = (float)Helpers::post('price');
        if ($newPrice <= 0) {
            Flash::error('Enter a valid price before resubmitting.');
        } else {
            $adModel->updatePrice($adId, $newPrice);
            Flash::info('Price updated. The ad has been queued for a fresh AI review.');
        }
    }
    Helpers::redirect('my-ads.php');
}

$myAds = $adModel->bySeller($userId);
$sellerProfile = (new SellerProfileModel())->findByUserId($userId);
$sellerType = $sellerProfile['seller_type'] ?? 'non_member';
require __DIR__ . '/partials/header.php';
?>
<div class="dash-shell">
  <?php require __DIR__ . '/partials/dash-side.php'; ?>
  <main class="dash-main">
    <div class="dash-top">
      <h2>My Ads</h2>
      <a class="btn btn-gold btn-sm" href="<?= Config::baseUrl('post-ad.php') ?>"><i class="fa-solid fa-plus"></i> Post New Ad</a>
    </div>
    <div class="panel">
      <?php if (!$myAds): ?>
      <div class="empty-state"><i class="fa-solid fa-car"></i><h4>You have not posted any ads</h4><p>Your listings and their AI verdicts will appear here.</p></div>
      <?php else: ?>
      <div class="table-wrap">
      <table>
        <thead><tr><th>Vehicle</th><th>Price</th><th>AI Verdict</th><th>Status</th><th>Views</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($myAds as $row): ?>
          <tr>
            <td><div class="td-car"><img src="<?= Helpers::e(Helpers::img($row['primary_image'], 'https://images.unsplash.com/photo-1493238792000-8113da705763?auto=format&fit=crop&w=200&q=60')) ?>" alt=""><div><a href="<?= Config::baseUrl('ad.php?id=' . (int)$row['id']) ?>" style="font-weight:600"><?= Helpers::e($row['title']) ?></a><div style="font-size:11px;color:var(--metallic-grey)"><?= Helpers::e(Helpers::timeAgo($row['created_at'])) ?></div></div></div></td>
            <td style="font-family:var(--mono)"><?= Helpers::e(Helpers::price($row['price'])) ?></td>
            <td><?php if ($row['fair_price_status'] === 'fair'): ?><span class="status-pill status-approved">FAIR</span><?php elseif ($row['fair_price_status'] === 'not_fair'): ?><span class="status-pill status-rejected">NOT FAIR</span><?php else: ?><span class="status-pill status-pending">WAITING</span><?php endif; ?></td>
            <td><span class="status-pill status-<?= Helpers::e($row['status']) ?>"><?= strtoupper(Helpers::e($row['status'])) ?></span></td>
            <td style="font-family:var(--mono)"><?= number_format((int)$row['views']) ?></td>
            <td>
              <div style="display:flex;gap:14px;flex-wrap:wrap">
                <?php if ($row['status'] === 'rejected' || $row['status'] === 'pending'): ?>
                <button type="button" class="link-action js-reprice" data-ad="<?= (int)$row['id'] ?>" data-price="<?= (float)$row['price'] ?>">Fix Price</button>
                <button type="button" class="link-action ok js-reanalyze" data-ad="<?= (int)$row['id'] ?>">Re-run AI</button>
                <?php endif; ?>
                <?php if ($row['status'] === 'approved'): ?>
                <a class="link-action" href="<?= Config::baseUrl('promote.php?ad=' . (int)$row['id']) ?>">Promote</a>
                <form method="post" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?= Helpers::e(Session::csrfToken()) ?>">
                  <input type="hidden" name="action" value="mark_sold">
                  <input type="hidden" name="ad_id" value="<?= (int)$row['id'] ?>">
                  <button type="button" class="link-action js-confirm" data-title="Mark as sold?" data-text="The ad will no longer appear in search results." data-yes="Yes, mark sold">Mark Sold</button>
                </form>
                <?php endif; ?>
                <form method="post" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?= Helpers::e(Session::csrfToken()) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="ad_id" value="<?= (int)$row['id'] ?>">
                  <button type="button" class="link-action danger js-confirm" data-title="Delete this ad?" data-text="This permanently removes the ad, its photos, chats, and analysis." data-yes="Yes, delete" data-danger="1">Delete</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
      <?php endif; ?>
    </div>
  </main>
</div>
<?php
$csrfJs = Session::csrfToken();
$pageScript = <<<JS
$('.js-reprice').on('click', function () {
  var adId = $(this).data('ad');
  var current = $(this).data('price');
  AV.modal({
    type: 'info',
    title: 'Update your asking price',
    html: '<input id="newPriceInput" type="number" value="' + current + '" style="width:100%;padding:12px;border:1.5px solid var(--platinum-2);border-radius:10px;font-size:15px;font-family:var(--mono)">',
    buttons: [
      { label: 'Cancel', cls: 'btn-outline-dark' },
      { label: 'Save and Re-review', cls: 'btn-gold', keepOpen: true, onClick: function () {
          var price = parseFloat($('#newPriceInput').val());
          if (!price || price <= 0) { AV.toast('warning', 'Enter a valid price.'); return; }
          var form = $('<form method="post"><input type="hidden" name="csrf_token" value="{$csrfJs}"><input type="hidden" name="action" value="update_price"><input type="hidden" name="ad_id" value="' + adId + '"><input type="hidden" name="price" value="' + price + '"></form>');
          $('body').append(form);
          form.trigger('submit');
      } }
    ]
  });
});
$('.js-reanalyze').on('click', function () {
  var adId = $(this).data('ad');
  var loading = AV.modal({ loader: true, title: 'AI is reviewing your price', text: 'Hold on a few seconds while the market comparison runs.', buttons: [] });
  AV.ajax('api/analyze.php', { ad_id: adId }, function (res) {
    loading.close();
    AV.modal({
      type: res.verdict === 'fair' ? 'success' : 'error',
      title: res.verdict === 'fair' ? 'Approved. Your ad is live!' : 'Ad Rejected: Price Not Fair',
      text: res.message,
      buttons: [{ label: 'Refresh', cls: 'btn-gold', onClick: function () { window.location.reload(); } }]
    });
  }, function (msg) {
    loading.close();
    AV.toast('error', msg);
  });
});
JS;
require __DIR__ . '/partials/footer.php';
?>
