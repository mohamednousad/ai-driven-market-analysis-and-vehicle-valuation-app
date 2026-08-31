<?php
$authUser = Auth::check() ? Auth::user() : null;
$navNotifCount = 0;
$navChatCount = 0;
if ($authUser) {
    $navNotifCount = (new NotificationModel())->unreadCount((int)$authUser['id']);
    $navChatCount = (new ChatModel())->unreadCount((int)$authUser['id']);
}
$current = basename($_SERVER['SCRIPT_NAME']);
$flashItems = Flash::pull();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="base-url" content="<?= Helpers::e(Config::baseUrl()) ?>">
<meta name="csrf-token" content="<?= Helpers::e(Session::csrfToken()) ?>">
<title><?= Helpers::e($pageTitle ?? Config::get('APP_NAME')) ?> | AutoValue</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="<?= Config::baseUrl('assets/css/app.css') ?>">
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<nav class="navbar">
  <div class="container nav-inner">
    <a class="logo" href="<?= Config::baseUrl('index.php') ?>"><span class="mark">A</span>Auto<span class="accent">Value</span></a>
    <div class="nav-links" id="navLinks">
      <a href="<?= Config::baseUrl('index.php') ?>" class="<?= $current === 'index.php' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Home</a>
      <a href="<?= Config::baseUrl('browse.php') ?>" class="<?= $current === 'browse.php' ? 'active' : '' ?>"><i class="fa-solid fa-car"></i> Browse Cars</a>
      <a href="<?= Config::baseUrl('subscription.php') ?>" class="<?= $current === 'subscription.php' ? 'active' : '' ?>"><i class="fa-solid fa-crown"></i> Plans</a>
      <?php if ($authUser): ?>
        <a href="<?= Config::baseUrl('post-ad.php') ?>" class="<?= $current === 'post-ad.php' ? 'active' : '' ?>"><i class="fa-solid fa-plus"></i> Post Ad</a>
        <a href="<?= Config::baseUrl('dashboard.php') ?>" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
        <a href="<?= Config::baseUrl('chats.php') ?>" class="<?= $current === 'chats.php' ? 'active' : '' ?>"><i class="fa-solid fa-comments"></i> Chats<?= $navChatCount ? ' (' . $navChatCount . ')' : '' ?></a>
        <?php if (Auth::isAdmin()): ?>
          <a href="<?= Config::baseUrl('admin/index.php') ?>"><i class="fa-solid fa-shield-halved"></i> Admin</a>
        <?php endif; ?>
        <a href="<?= Config::baseUrl('logout.php') ?>"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
      <?php else: ?>
        <a href="<?= Config::baseUrl('login.php') ?>" class="<?= $current === 'login.php' ? 'active' : '' ?>"><i class="fa-solid fa-arrow-right-to-bracket"></i> Login</a>
        <a href="<?= Config::baseUrl('register.php') ?>" class="<?= $current === 'register.php' ? 'active' : '' ?>"><i class="fa-solid fa-user-plus"></i> Register</a>
      <?php endif; ?>
    </div>
    <div class="nav-actions">
      <?php if ($authUser): ?>
        <div style="position:relative">
          <button class="nav-icon-btn" id="notifBtn" type="button" aria-label="Notifications">
            <i class="fa-regular fa-bell"></i>
            <?php if ($navNotifCount): ?><span class="nav-badge" id="notifBadge"><?= $navNotifCount ?></span><?php endif; ?>
          </button>
          <div class="notif-drop" id="notifDrop">
            <div class="nd-head"><b>Notifications</b><a class="link-action" href="<?= Config::baseUrl('notifications.php') ?>">View all</a></div>
            <div class="nd-list"></div>
          </div>
        </div>
        <a class="user-chip" href="<?= Config::baseUrl('profile.php') ?>">
          <img src="<?= Helpers::e(Helpers::avatar($authUser['profile_image'])) ?>" alt="Profile">
          <span><?= Helpers::e($authUser['username']) ?></span>
        </a>
      <?php else: ?>
        <a class="btn btn-gold btn-sm" href="<?= Config::baseUrl('post-ad.php') ?>"><i class="fa-solid fa-plus"></i> Sell Your Car</a>
      <?php endif; ?>
      <button class="nav-toggle" id="navToggle" type="button" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
    </div>
  </div>
</nav>
<script>window.AV_FLASH = <?= json_encode($flashItems) ?>;</script>
