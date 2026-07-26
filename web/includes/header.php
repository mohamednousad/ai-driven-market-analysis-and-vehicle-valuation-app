<?php
$authUser = Auth::user();
$notifCount = 0;
if ($authUser) {
    $notifCount = (new NotificationRepository($pdo))->unreadCount((int)$authUser['user_id']);
}
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
<header class="topbar">
  <div class="container topbar-inner">
    <a class="brand" href="/index.php">
      <i class="fa-solid fa-car-side"></i> <span>Auto<strong>Value</strong></span>
    </a>
    <form class="topbar-search" action="/index.php" method="get">
      <input type="text" name="q" placeholder="Search vehicles, e.g. Toyota Aqua" value="<?= e($_GET['q'] ?? '') ?>">
      <button type="submit" aria-label="Search"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
    <nav class="topbar-nav">
      <?php if ($authUser): ?>
        <a href="/notifications.php" class="nav-icon" title="Notifications">
          <i class="fa-regular fa-bell"></i>
          <?php if ($notifCount > 0): ?><span class="nav-dot"><?= $notifCount > 9 ? '9+' : $notifCount ?></span><?php endif; ?>
        </a>
        <a href="/chats.php" class="nav-icon" title="Chats"><i class="fa-regular fa-comment-dots"></i></a>
        <?php if ($authUser['role'] === 'buyer'): ?>
          <a href="/buyer/favourites.php" class="nav-icon" title="Favourites"><i class="fa-regular fa-heart"></i></a>
          <a href="/buyer/assistant.php" class="nav-link">AI Assistant</a>
        <?php endif; ?>
        <?php if ($authUser['role'] === 'seller'): ?>
          <a href="/seller/my-ads.php" class="nav-link">My Ads</a>
        <?php endif; ?>
        <?php if ($authUser['role'] === 'admin'): ?>
          <a href="/admin/dashboard.php" class="nav-link">Admin</a>
        <?php endif; ?>
        <span class="nav-user"><i class="fa-regular fa-user"></i> <?= e($authUser['full_name']) ?></span>
        <a href="/logout.php" class="nav-link">Log out</a>
      <?php else: ?>
        <a href="/login.php" class="nav-link">Log in</a>
        <a href="/register.php" class="nav-link">Register</a>
      <?php endif; ?>
      <?php if (!$authUser || $authUser['role'] === 'seller'): ?>
        <a href="/seller/post-ad.php" class="btn-post"><i class="fa-solid fa-plus"></i> POST AD</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main>
<?php if ($msg = flash('success')): ?><div class="container"><div class="alert alert-success"><?= e($msg) ?></div></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="container"><div class="alert alert-error"><?= e($msg) ?></div></div><?php endif; ?>
