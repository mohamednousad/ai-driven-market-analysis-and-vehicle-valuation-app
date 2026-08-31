<?php $dashCurrent = basename($_SERVER['SCRIPT_NAME']); ?>
<aside class="dash-side">
  <div class="who">
    <img src="<?= Helpers::e(Helpers::avatar($authUser['profile_image'] ?? null)) ?>" alt="">
    <div>
      <b><?= Helpers::e($authUser['username'] ?? '') ?></b>
      <span><?= Helpers::e(strtoupper(str_replace('_', ' ', $sellerType ?? 'BUYER'))) ?></span>
    </div>
  </div>
  <nav class="dash-nav">
    <a href="<?= Config::baseUrl('dashboard.php') ?>" class="<?= $dashCurrent === 'dashboard.php' ? 'active' : '' ?>"><i class="fa-solid fa-gauge-high"></i> Overview</a>
    <a href="<?= Config::baseUrl('my-ads.php') ?>" class="<?= $dashCurrent === 'my-ads.php' ? 'active' : '' ?>"><i class="fa-solid fa-car"></i> My Ads</a>
    <a href="<?= Config::baseUrl('post-ad.php') ?>"><i class="fa-solid fa-plus"></i> Post New Ad</a>
    <a href="<?= Config::baseUrl('favourites.php') ?>" class="<?= $dashCurrent === 'favourites.php' ? 'active' : '' ?>"><i class="fa-solid fa-heart"></i> Favourites</a>
    <a href="<?= Config::baseUrl('chats.php') ?>"><i class="fa-solid fa-comments"></i> Chats</a>
    <a href="<?= Config::baseUrl('notifications.php') ?>" class="<?= $dashCurrent === 'notifications.php' ? 'active' : '' ?>"><i class="fa-regular fa-bell"></i> Notifications</a>
    <div class="divider"></div>
    <a href="<?= Config::baseUrl('subscription.php') ?>" class="<?= $dashCurrent === 'subscription.php' ? 'active' : '' ?>"><i class="fa-solid fa-crown"></i> My Plan</a>
    <a href="<?= Config::baseUrl('promote.php') ?>" class="<?= $dashCurrent === 'promote.php' ? 'active' : '' ?>"><i class="fa-solid fa-bolt"></i> Promote Ads</a>
    <a href="<?= Config::baseUrl('become-authorized.php') ?>" class="<?= $dashCurrent === 'become-authorized.php' ? 'active' : '' ?>"><i class="fa-solid fa-shield-halved"></i> Get Authorized</a>
    <a href="<?= Config::baseUrl('profile.php') ?>" class="<?= $dashCurrent === 'profile.php' ? 'active' : '' ?>"><i class="fa-solid fa-user-gear"></i> Profile</a>
  </nav>
</aside>
