<?php
require_once __DIR__ . '/auth.php';
$flash = get_flash();
$pageTitle = $pageTitle ?? APP_NAME;
$assetBase = $assetBase ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> · <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $assetBase; ?>assets/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
<header class="topbar">
    <div class="topbar-inner">
        <a class="brand" href="<?php echo $assetBase . role_dashboard(current_role()); ?>">
            <span class="brand-mark"><i class="fa-solid fa-car-side"></i></span>
            <span class="brand-text"><?php echo APP_NAME; ?></span>
        </a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle menu"><i class="fa-solid fa-bars"></i></button>
        <nav class="navlinks" id="navLinks">
            <?php if (is_logged_in()): ?>
                <?php if (has_role(ROLE_BUYER)): ?>
                    <a href="<?php echo $assetBase; ?>buyer/dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                    <a href="<?php echo $assetBase; ?>buyer/browse.php"><i class="fa-solid fa-magnifying-glass"></i> Browse</a>
                    <a href="<?php echo $assetBase; ?>buyer/assistant.php"><i class="fa-solid fa-robot"></i> AI Assistant</a>
                <?php elseif (has_role(ROLE_SELLER)): ?>
                    <a href="<?php echo $assetBase; ?>seller/dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                    <a href="<?php echo $assetBase; ?>seller/post-ad.php"><i class="fa-solid fa-plus"></i> Post Ad</a>
                    <a href="<?php echo $assetBase; ?>seller/my-ads.php"><i class="fa-solid fa-rectangle-list"></i> My Ads</a>
                    <a href="<?php echo $assetBase; ?>seller/ratings.php"><i class="fa-solid fa-star"></i> Ratings</a>
                <?php elseif (has_role(ROLE_ADMIN)): ?>
                    <a href="<?php echo $assetBase; ?>admin/dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                    <a href="<?php echo $assetBase; ?>admin/ads.php"><i class="fa-solid fa-rectangle-list"></i> Ads</a>
                    <a href="<?php echo $assetBase; ?>admin/users.php"><i class="fa-solid fa-users"></i> Users</a>
                    <a href="<?php echo $assetBase; ?>admin/settings.php"><i class="fa-solid fa-gear"></i> Settings</a>
                <?php endif; ?>
                <span class="nav-user"><i class="fa-solid fa-circle-user"></i> <?php echo e(current_name()); ?></span>
                <a class="nav-logout" href="<?php echo $assetBase; ?>logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            <?php else: ?>
                <a href="<?php echo $assetBase; ?>login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
                <a class="btn primary small" href="<?php echo $assetBase; ?>register.php">Get Started</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<?php if ($flash): ?>
    <div class="flash-wrap">
        <div class="alert <?php echo e($flash['type']); ?>">
            <i class="fa-solid <?php echo $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
            <span><?php echo e($flash['message']); ?></span>
        </div>
    </div>
<?php endif; ?>
<main class="page">
