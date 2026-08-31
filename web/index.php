<?php
require_once __DIR__ . '/app/bootstrap.php';
$pageTitle = 'AI-Driven Used Car Marketplace';
$adModel = new AdModel();
$featured = $adModel->featured(6);
$makes = $adModel->distinctMakes();
$districts = (new LocationModel())->districts();
$plans = (new SubscriptionModel())->plans();
require __DIR__ . '/partials/header.php';
?>
<header class="hero">
  <div class="hero-bg" style="background-image:url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=2000&q=80')"></div>
  <div class="hero-fade"></div>
  <div class="container hero-content on-dark">
    <span class="eyebrow">AI-Verified Fair Pricing</span>
    <h1>Buy and sell cars at prices the market actually agrees with.</h1>
    <p class="lead">Every ad on AutoValue passes through our AI fair price engine before it goes live. No inflated listings, no guesswork, no wasted calls.</p>
    <div class="hero-cta">
      <a class="btn btn-gold" href="<?= Config::baseUrl('browse.php') ?>"><i class="fa-solid fa-magnifying-glass"></i> Browse Cars</a>
      <a class="btn btn-outline" href="<?= Config::baseUrl('post-ad.php') ?>"><i class="fa-solid fa-plus"></i> Sell Your Car</a>
    </div>
    <div class="hero-stats">
      <div class="stat"><b>9,700+</b><span>MARKET RECORDS ANALYZED</span></div>
      <div class="stat"><b>100%</b><span>ADS AI-REVIEWED</span></div>
      <div class="stat"><b>60 sec</b><span>AVG APPROVAL TIME</span></div>
    </div>
  </div>
</header>

<div class="container">
  <form class="search-bar" action="<?= Config::baseUrl('browse.php') ?>" method="get">
    <div class="field">
      <label>Search</label>
      <input type="text" name="q" placeholder="Toyota Aqua, Vezel...">
    </div>
    <div class="field">
      <label>Make</label>
      <select name="make">
        <option value="">Any make</option>
        <?php foreach ($makes as $m): ?><option value="<?= Helpers::e($m['make']) ?>"><?= Helpers::e($m['make']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label>District</label>
      <select name="district">
        <option value="">All districts</option>
        <?php foreach ($districts as $d): ?><option value="<?= Helpers::e($d['district']) ?>"><?= Helpers::e($d['district']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label>Max Price</label>
      <input type="number" name="max_price" placeholder="10,000,000">
    </div>
    <button class="btn btn-dark" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
  </form>
</div>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div>
        <span class="eyebrow">Handpicked by the market</span>
        <h2>Featured &amp; Trending Listings</h2>
      </div>
      <a class="btn btn-outline-dark btn-sm" href="<?= Config::baseUrl('browse.php') ?>">View all cars <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="car-grid">
      <?php foreach ($featured as $card) { include __DIR__ . '/partials/ad-card.php'; } ?>
    </div>
  </div>
</section>

<section class="section grey">
  <div class="container">
    <div class="section-head">
      <div>
        <span class="eyebrow">How it works</span>
        <h2>From listing to sold, with AI in the middle</h2>
      </div>
    </div>
    <div class="steps">
      <div class="step"><div class="num">01</div><h4>Create your ad</h4><p>Add your vehicle details, photos, and your asking price in a few minutes.</p></div>
      <div class="step"><div class="num">02</div><h4>AI fair price check</h4><p>Our engine compares your price against thousands of real market records instantly.</p></div>
      <div class="step"><div class="num">03</div><h4>Instant decision</h4><p>Fairly priced ads go live immediately. Overpriced ads get a clear rejection with the expected range.</p></div>
      <div class="step"><div class="num">04</div><h4>Chat and close</h4><p>Buyers message you directly. Rate each other after the deal is done.</p></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div>
        <span class="eyebrow">Seller plans</span>
        <h2>Choose how seriously you want to sell</h2>
      </div>
    </div>
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
        <a class="btn <?= $featuredPlan ? 'btn-gold' : 'btn-outline-dark' ?> btn-block" href="<?= Config::baseUrl('subscription.php') ?>">Get <?= Helpers::e($plan['name']) ?></a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
