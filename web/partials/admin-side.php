<?php $adminCurrent = basename($_SERVER['SCRIPT_NAME']); ?>
<aside class="dash-side">
  <div class="who">
    <img src="<?= Helpers::e(Helpers::avatar($authUser['profile_image'] ?? null)) ?>" alt="">
    <div><b>Admin Panel</b><span>SUPER USER</span></div>
  </div>
  <nav class="dash-nav">
    <a href="<?= Config::baseUrl('admin/index.php') ?>" class="<?= $adminCurrent === 'index.php' ? 'active' : '' ?>"><i class="fa-solid fa-gauge-high"></i> Overview</a>
    <a href="<?= Config::baseUrl('admin/ads.php') ?>" class="<?= $adminCurrent === 'ads.php' ? 'active' : '' ?>"><i class="fa-solid fa-car"></i> Ads Moderation</a>
    <a href="<?= Config::baseUrl('admin/sellers.php') ?>" class="<?= $adminCurrent === 'sellers.php' ? 'active' : '' ?>"><i class="fa-solid fa-shield-halved"></i> Authorized Requests</a>
    <a href="<?= Config::baseUrl('admin/users.php') ?>" class="<?= $adminCurrent === 'users.php' ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> Users</a>
    <a href="<?= Config::baseUrl('admin/reports.php') ?>" class="<?= $adminCurrent === 'reports.php' ? 'active' : '' ?>"><i class="fa-regular fa-flag"></i> Reports</a>
    <a href="<?= Config::baseUrl('admin/payments.php') ?>" class="<?= $adminCurrent === 'payments.php' ? 'active' : '' ?>"><i class="fa-solid fa-credit-card"></i> Payments</a>
    <div class="divider"></div>
    <a href="<?= Config::baseUrl('index.php') ?>"><i class="fa-solid fa-arrow-left"></i> Back to site</a>
    <a href="<?= Config::baseUrl('logout.php') ?>"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
  </nav>
</aside>
