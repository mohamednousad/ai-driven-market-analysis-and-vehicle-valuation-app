<?php
require_once __DIR__ . '/app/bootstrap.php';
$adModel = new AdModel();
$adId = (int)Helpers::get('id', '0');
$ad = $adModel->findFull($adId);
if (!$ad || ($ad['status'] !== 'approved' && Auth::id() !== (int)$ad['seller_id'] && !Auth::isAdmin())) {
    Flash::error('That listing is not available.');
    Helpers::redirect('browse.php');
}
$adModel->incrementViews($adId);
$vehicle = $adModel->vehicleByAd($adId);
$images = $vehicle ? $adModel->imagesByVehicle((int)$vehicle['id']) : [];
$analysis = $adModel->analysisByAd($adId);
$sellerProfile = (new SellerProfileModel())->findByUserId((int)$ad['seller_id']);
$reviews = (new RatingModel())->forSeller((int)$ad['seller_id']);
$feedback = (new FeedbackModel())->forAd($adId);
$isFav = Auth::check() ? (new FavouriteModel())->isFavourite((int)Auth::id(), $adId) : false;
$isOwner = Auth::id() === (int)$ad['seller_id'];
$fallback = 'https://images.unsplash.com/photo-1493238792000-8113da705763?auto=format&fit=crop&w=1200&q=80';
$mainImg = $images ? Helpers::img($images[0]['image_path'], $fallback) : $fallback;
$isFair = ($analysis['fair_price_status'] ?? '') === 'fair';
$pageTitle = $ad['title'];
require __DIR__ . '/partials/header.php';
?>
<div class="page-head">
  <div class="container">
    <div class="breadcrumb"><a href="<?= Config::baseUrl('index.php') ?>">Home</a> / <a href="<?= Config::baseUrl('browse.php') ?>">Browse</a> / <?= Helpers::e($ad['make'] . ' ' . $ad['model']) ?></div>
    <h1><?= Helpers::e($ad['title']) ?></h1>
  </div>
</div>
<div class="container detail-layout">
  <div>
    <div class="gallery-main"><img id="galleryMain" src="<?= Helpers::e($mainImg) ?>" alt="<?= Helpers::e($ad['title']) ?>"></div>
    <?php if (count($images) > 1): ?>
    <div class="gallery-thumbs">
      <?php foreach ($images as $i => $img): $src = Helpers::img($img['image_path'], $fallback); ?>
      <img src="<?= Helpers::e($src) ?>" data-full="<?= Helpers::e($src) ?>" class="<?= $i === 0 ? 'active' : '' ?>" alt="Photo <?= $i + 1 ?>">
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="detail-title-row">
      <div>
        <div class="detail-loc"><i class="fa-solid fa-location-dot"></i> <?= Helpers::e($ad['city'] . ', ' . $ad['district']) ?> <span>&bull;</span> <i class="fa-regular fa-eye"></i> <?= number_format((int)$ad['views']) ?> views <span>&bull;</span> <?= Helpers::e(Helpers::timeAgo($ad['published_at'] ?? $ad['created_at'])) ?></div>
        <div class="price-row">
          <div class="price-big"><?= Helpers::e(Helpers::price($ad['price'])) ?></div>
          <?php if ($ad['is_negotiable']): ?><span class="tag-negotiable">Negotiable</span><?php endif; ?>
          <span class="status-pill status-<?= Helpers::e($ad['status']) ?>"><?= strtoupper(Helpers::e($ad['status'])) ?></span>
        </div>
      </div>
      <button class="icon-btn js-fav <?= $isFav ? 'active' : '' ?>" data-ad="<?= $adId ?>" type="button" aria-label="Favourite"><i class="<?= $isFav ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i></button>
    </div>
    <div class="spec-table">
      <div class="spec-item"><span>Make / Model</span><b><?= Helpers::e($ad['make'] . ' ' . $ad['model']) ?></b></div>
      <div class="spec-item"><span>Year</span><b><?= (int)$vehicle['manufacture_year'] ?></b></div>
      <div class="spec-item"><span>Mileage</span><b><?= number_format((int)$vehicle['mileage']) ?> km</b></div>
      <div class="spec-item"><span>Engine</span><b><?= number_format((int)$vehicle['engine_cc']) ?> cc</b></div>
      <div class="spec-item"><span>Transmission</span><b><?= Helpers::e(ucfirst((string)$vehicle['transmission'])) ?></b></div>
      <div class="spec-item"><span>Fuel</span><b><?= Helpers::e(ucfirst((string)$vehicle['fuel_type'])) ?></b></div>
      <div class="spec-item"><span>Body</span><b><?= Helpers::e($vehicle['body_type']) ?></b></div>
      <div class="spec-item"><span>Colour</span><b><?= Helpers::e($vehicle['colour']) ?></b></div>
      <div class="spec-item"><span>Owners</span><b><?= (int)$vehicle['number_of_owners'] ?></b></div>
      <div class="spec-item"><span>Finance</span><b><?= Helpers::e($vehicle['finance_status'] ?: 'Clear') ?></b></div>
    </div>
    <div class="desc-block">
      <h3>Description</h3>
      <p><?= nl2br(Helpers::e($ad['description'])) ?></p>
    </div>
    <?php if (!empty($vehicle['features'])): ?>
    <div class="desc-block">
      <h3>Features</h3>
      <div class="feature-tags">
        <?php foreach (explode('|', (string)$vehicle['features']) as $feat): ?><span><?= Helpers::e(trim($feat)) ?></span><?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
    <div class="desc-block">
      <h3>Buyer Feedback</h3>
      <?php if (!$feedback): ?><p style="color:var(--metallic-grey)">No feedback yet on this listing.</p><?php endif; ?>
      <?php foreach ($feedback as $fb): ?>
      <div class="review-item">
        <div class="rv-head"><b><?= Helpers::e($fb['username']) ?></b><span><?= Helpers::e(Helpers::timeAgo($fb['created_at'])) ?></span></div>
        <p><?= Helpers::e($fb['comment']) ?></p>
      </div>
      <?php endforeach; ?>
      <?php if (Auth::check() && !$isOwner): ?>
      <form id="feedbackForm" style="margin-top:16px">
        <div class="form-group">
          <label>Leave feedback on this ad</label>
          <textarea name="comment" rows="2" placeholder="Was the listing accurate?"></textarea>
          <span class="field-error"></span>
        </div>
        <button class="btn btn-outline-dark btn-sm" type="submit"><span class="btn-label">Post Feedback</span></button>
      </form>
      <?php endif; ?>
    </div>
  </div>
  <aside>
    <div class="gauge-card">
      <span class="eyebrow">AI Fair Price Engine</span>
      <div class="gauge-svg-wrap">
        <svg viewBox="0 0 210 130" width="210" height="130">
          <path d="M 20 115 A 85 85 0 0 1 190 115" fill="none" stroke="rgba(255,255,255,.12)" stroke-width="13" stroke-linecap="round"/>
          <path d="M 20 115 A 85 85 0 0 1 190 115" fill="none" stroke="<?= $isFair ? '#D4AF37' : '#6B7280' ?>" stroke-width="13" stroke-linecap="round" stroke-dasharray="267" stroke-dashoffset="<?= $isFair ? '40' : '150' ?>"/>
          <circle cx="105" cy="115" r="6" fill="#D4AF37"/>
        </svg>
      </div>
      <div class="gauge-readout">
        <div class="verdict <?= $isFair ? '' : 'bad' ?>"><?= $isFair ? 'FAIR PRICE' : 'PRICE FLAGGED' ?></div>
        <p><?= Helpers::e($analysis['result'] ?? 'This listing has not been analyzed yet.') ?></p>
      </div>
    </div>
    <div class="sidebar-card">
      <div class="seller-card-head">
        <img src="<?= Helpers::e(Helpers::avatar($sellerProfile['profile_image'] ?? $ad['seller_avatar'])) ?>" alt="">
        <div>
          <h4><?= Helpers::e($ad['seller_name']) ?></h4>
          <div class="badge-row">
            <?php if (($ad['seller_type'] ?? '') === 'authorized'): ?><span class="badge badge-gold"><i class="fa-solid fa-shield-halved"></i> AUTHORIZED SELLER</span>
            <?php elseif (($ad['seller_type'] ?? '') === 'member'): ?><span class="badge badge-gold"><i class="fa-solid fa-medal"></i> MEMBER</span>
            <?php else: ?><span class="badge badge-grey">SELLER</span><?php endif; ?>
          </div>
        </div>
      </div>
      <div class="seller-stats">
        <div><b><?= number_format((float)($ad['avg_rating'] ?? 0), 1) ?></b><span>Rating</span></div>
        <div><b><?= count($reviews) ?></b><span>Reviews</span></div>
        <div><b><?= number_format((float)($sellerProfile['response_rate'] ?? 0)) ?>%</b><span>Response</span></div>
      </div>
      <?php if (!$isOwner): ?>
      <button class="btn btn-gold btn-block" id="chatStartBtn" type="button"><i class="fa-solid fa-comments"></i><span class="btn-label"> Chat with Seller</span></button>
      <button class="btn btn-outline-dark btn-block btn-sm" id="reportBtn" type="button" style="margin-top:9px"><i class="fa-regular fa-flag"></i> Report this ad</button>
      <?php else: ?>
      <a class="btn btn-dark btn-block" href="<?= Config::baseUrl('my-ads.php') ?>"><i class="fa-solid fa-list"></i> Manage My Ads</a>
      <?php endif; ?>
    </div>
    <?php if (Auth::check() && !$isOwner): ?>
    <div class="sidebar-card">
      <h4>Rate this seller</h4>
      <div class="rating-stars" id="ratingStars" data-value="0">
        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
      </div>
      <div class="form-group" style="margin-top:12px">
        <textarea id="ratingComment" rows="2" placeholder="How was your experience?"></textarea>
      </div>
      <button class="btn btn-dark btn-block btn-sm" id="ratingSubmit" type="button"><span class="btn-label">Submit Rating</span></button>
    </div>
    <?php endif; ?>
  </aside>
</div>
<?php
$pageScript = <<<JS
$('#chatStartBtn').on('click', function () {
  var btn = $(this);
  AV.setLoading(btn, true);
  AV.ajax('api/messages.php', { action: 'start', ad_id: {$adId} }, function (res) {
    window.location = AV.base + '/chats.php?chat=' + res.chat_id;
  }, function (msg) { AV.setLoading(btn, false); AV.toast('error', msg); });
});
$('#reportBtn').on('click', function () {
  AV.modal({
    type: 'warning',
    title: 'Report this ad',
    html: '<textarea id="reportReason" rows="3" placeholder="Tell us what is wrong with this listing" style="width:100%;padding:11px;border:1.5px solid var(--platinum-2);border-radius:10px;font-size:13.5px"></textarea>',
    buttons: [
      { label: 'Cancel', cls: 'btn-outline-dark' },
      { label: 'Submit Report', cls: 'btn-danger', keepOpen: true, onClick: function () {
          var reason = $('#reportReason').val().trim();
          if (reason.length < 10) { AV.toast('warning', 'Please describe the issue in at least 10 characters.'); return; }
          AV.ajax('api/report.php', { ad_id: {$adId}, reason: reason }, function () {
            $('#appModal').removeClass('open');
            AV.toast('success', 'Report submitted. Our team will review it shortly.');
          });
      } }
    ]
  });
});
$('#ratingSubmit').on('click', function () {
  var value = parseInt($('#ratingStars').attr('data-value') || '0', 10);
  if (!value) { AV.toast('warning', 'Tap the stars to pick a rating first.'); return; }
  var btn = $(this);
  AV.setLoading(btn, true);
  AV.ajax('api/rate.php', { ad_id: {$adId}, rating: value, comment: $('#ratingComment').val() }, function () {
    AV.setLoading(btn, false);
    AV.toast('success', 'Thanks, your rating has been recorded.');
  }, function (msg) { AV.setLoading(btn, false); AV.toast('error', msg); });
});
$('#feedbackForm').on('submit', function (e) {
  e.preventDefault();
  var comment = $(this).find('[name=comment]').val().trim();
  if (comment.length < 5) { AV.toast('warning', 'Feedback is a little too short.'); return; }
  var btn = $(this).find('button');
  AV.setLoading(btn, true);
  AV.ajax('api/feedback.php', { ad_id: {$adId}, comment: comment }, function () {
    window.location.reload();
  }, function (msg) { AV.setLoading(btn, false); AV.toast('error', msg); });
});
JS;
require __DIR__ . '/partials/footer.php';
?>
