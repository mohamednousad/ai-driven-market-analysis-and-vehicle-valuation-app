<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../lib/AdRepository.php';

require_role(ROLE_BUYER);
$assetBase = '../';

$adRepo = new AdRepository($pdo);
$featured = $adRepo->featured(6);

$pageTitle = 'Buyer Dashboard';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="section hero">
    <div class="slider">
        <div class="slides">
            <div class="slide slide-1 active">
                <div class="slide-content">
                    <span class="eyebrow">AI-validated pricing</span>
                    <h2>Every listing checked for a fair price</h2>
                    <p>No more guesswork. Our AI compares each seller's price against real market data, so you only see fair deals.</p>
                    <a href="browse.php" class="btn secondary"><i class="fa-solid fa-magnifying-glass"></i> Browse vehicles</a>
                </div>
            </div>
            <div class="slide slide-2">
                <div class="slide-content">
                    <span class="eyebrow">Smart assistant</span>
                    <h2>Not sure what to buy?</h2>
                    <p>Tell our AI assistant your budget and needs. It asks the right questions and suggests a search plan.</p>
                    <a href="assistant.php" class="btn secondary"><i class="fa-solid fa-robot"></i> Ask the AI assistant</a>
                </div>
            </div>
            <div class="slide slide-3">
                <div class="slide-content">
                    <span class="eyebrow">Trusted sellers</span>
                    <h2>Buy from rated, verified sellers</h2>
                    <p>Check verified buyer ratings before you contact a seller and shop with confidence.</p>
                    <a href="browse.php?sort=rating" class="btn secondary"><i class="fa-solid fa-star"></i> Top-rated listings</a>
                </div>
            </div>
        </div>
        <button class="slider-arrow prev" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
        <button class="slider-arrow next" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
        <div class="slider-dots">
            <button class="active"></button><button></button><button></button>
        </div>
    </div>
</section>

<section class="section grid grid-3">
    <div class="card card-hover">
        <div class="stat-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
        <h3 style="margin-top:14px">Search &amp; filter</h3>
        <p class="muted">Filter by brand, price, mileage, fuel type and seller rating to find your match fast.</p>
        <a href="browse.php" class="btn secondary small">Start browsing</a>
    </div>
    <div class="card card-hover">
        <div class="stat-icon"><i class="fa-solid fa-robot"></i></div>
        <h3 style="margin-top:14px">AI assistant</h3>
        <p class="muted">Chat with our Gemini-powered assistant for personalised vehicle guidance.</p>
        <a href="assistant.php" class="btn secondary small">Open assistant</a>
    </div>
    <div class="card card-hover">
        <div class="stat-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <h3 style="margin-top:14px">Fair prices only</h3>
        <p class="muted">Listings shown here already passed our AI fair-price validation.</p>
        <a href="browse.php" class="btn secondary small">See fair deals</a>
    </div>
</section>

<section class="section">
    <div class="flex-between">
        <h2 class="mb-0">Featured vehicles</h2>
        <a href="browse.php" class="btn secondary small">View all <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="divider"></div>
    <?php if (!$featured): ?>
        <div class="card empty-state"><i class="fa-solid fa-car"></i><p>No vehicles listed yet. Check back soon.</p></div>
    <?php else: ?>
        <div class="vehicle-grid">
            <?php foreach ($featured as $ad): ?>
                <?php include __DIR__ . '/_vehicle_card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
