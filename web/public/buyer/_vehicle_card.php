<?php
$cardBase = $assetBase ?? '';
$rating = (float)($ad['seller_rating'] ?? 0);
?>
<article class="vehicle-card">
    <div class="vehicle-media">
        <?php if (!empty($ad['image_path'])): ?>
            <img src="<?php echo $cardBase . UPLOAD_URL . '/' . e($ad['image_path']); ?>" alt="<?php echo e($ad['title']); ?>">
        <?php else: ?>
            <i class="fa-solid fa-car-side"></i>
        <?php endif; ?>
        <span class="vehicle-badge"><i class="fa-solid fa-location-dot"></i> <?php echo e($ad['location'] ?: 'Sri Lanka'); ?></span>
        <div class="vehicle-fav"><i class="fa-regular fa-heart"></i></div>
    </div>
    <div class="vehicle-body">
        <div class="vehicle-title"><?php echo e($ad['title']); ?></div>
        <div class="vehicle-price"><?php echo format_money($ad['asking_price']); ?></div>
        <div class="vehicle-specs">
            <span class="spec-chip"><i class="fa-solid fa-calendar"></i> <?php echo e($ad['model_year']); ?></span>
            <span class="spec-chip"><i class="fa-solid fa-gauge-high"></i> <?php echo number_format($ad['mileage']); ?> km</span>
            <span class="spec-chip"><i class="fa-solid fa-gas-pump"></i> <?php echo e($ad['fuel_type']); ?></span>
        </div>
        <div class="vehicle-foot">
            <span class="rating-line"><?php echo star_rating_html($rating); ?> <?php echo $rating > 0 ? number_format($rating, 1) : 'New'; ?></span>
            <a href="vehicle.php?id=<?php echo (int)$ad['id']; ?>" class="btn secondary small">View <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>
</article>
