<?php
$cardImg = Helpers::img($card['primary_image'] ?? null, 'https://images.unsplash.com/photo-1493238792000-8113da705763?auto=format&fit=crop&w=1200&q=80');
$isFair = ($card['fair_price_status'] ?? '') === 'fair';
?>
<a class="car-card" href="<?= Config::baseUrl('ad.php?id=' . (int)$card['id']) ?>">
  <div class="car-media">
    <img src="<?= Helpers::e($cardImg) ?>" alt="<?= Helpers::e($card['title']) ?>" loading="lazy">
    <div class="car-badge-row">
      <div style="display:flex;gap:6px;flex-wrap:wrap">
        <?php if (!empty($card['is_promoted'])): ?><span class="chip chip-promo"><i class="fa-solid fa-bolt"></i> FEATURED</span><?php endif; ?>
        <?php if (($card['seller_type'] ?? '') === 'authorized'): ?><span class="chip chip-tier"><i class="fa-solid fa-shield-halved"></i> AUTHORIZED</span>
        <?php elseif (($card['seller_type'] ?? '') === 'member'): ?><span class="chip chip-tier"><i class="fa-solid fa-medal"></i> MEMBER</span><?php endif; ?>
      </div>
      <div class="gauge-mini <?= $isFair ? '' : 'not-fair' ?>">
        <b><?= $isFair ? 'FAIR' : 'CHECK' ?></b>
        <span>AI PRICE</span>
      </div>
    </div>
  </div>
  <div class="car-body">
    <div class="car-loc"><i class="fa-solid fa-location-dot"></i> <?= Helpers::e(($card['city'] ?? '') . ', ' . ($card['district'] ?? '')) ?> &bull; <?= Helpers::e(Helpers::timeAgo($card['published_at'] ?? $card['created_at'])) ?></div>
    <h3><?= Helpers::e($card['title']) ?></h3>
    <div class="car-price"><?= Helpers::e(Helpers::price($card['price'])) ?><span><?= !empty($card['is_negotiable']) ? 'Negotiable' : 'Fixed' ?></span></div>
    <div class="car-specs">
      <span><?= (int)$card['manufacture_year'] ?></span>
      <span><?= number_format((int)$card['mileage']) ?> km</span>
      <span><?= Helpers::e(ucfirst((string)$card['transmission'])) ?></span>
      <span><?= Helpers::e(ucfirst((string)$card['fuel_type'])) ?></span>
    </div>
    <div class="car-foot">
      <div class="seller-mini">
        <img src="<?= Helpers::e(Helpers::avatar($card['seller_avatar'] ?? null)) ?>" alt="">
        <?= Helpers::e($card['seller_name'] ?? '') ?>
      </div>
      <div class="star-line"><i class="fa-solid fa-star"></i> <?= number_format((float)($card['avg_rating'] ?? 0), 1) ?></div>
    </div>
  </div>
</a>
