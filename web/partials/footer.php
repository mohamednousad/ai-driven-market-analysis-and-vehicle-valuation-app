<footer>
  <div class="container">
    <div class="footer-grid">
      <div>
        <a class="logo" href="<?= Config::baseUrl('index.php') ?>" style="margin-bottom:14px"><span class="mark">A</span>Auto<span class="accent">Value</span></a>
        <p style="font-size:13px;max-width:300px;margin-top:12px">Sri Lanka's AI-driven used car marketplace. Every listing is analyzed for fair pricing before it goes live.</p>
      </div>
      <div>
        <h5>Marketplace</h5>
        <ul>
          <li><a href="<?= Config::baseUrl('browse.php') ?>">Browse Cars</a></li>
          <li><a href="<?= Config::baseUrl('post-ad.php') ?>">Post an Ad</a></li>
          <li><a href="<?= Config::baseUrl('subscription.php') ?>">Seller Plans</a></li>
        </ul>
      </div>
      <div>
        <h5>Sellers</h5>
        <ul>
          <li><a href="<?= Config::baseUrl('dashboard.php') ?>">Seller Dashboard</a></li>
          <li><a href="<?= Config::baseUrl('become-authorized.php') ?>">Become Authorized</a></li>
          <li><a href="<?= Config::baseUrl('promote.php') ?>">Promote Ads</a></li>
        </ul>
      </div>
      <div>
        <h5>Account</h5>
        <ul>
          <li><a href="<?= Config::baseUrl('login.php') ?>">Login</a></li>
          <li><a href="<?= Config::baseUrl('register.php') ?>">Register</a></li>
          <li><a href="<?= Config::baseUrl('favourites.php') ?>">My Favourites</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>AutoValue. All rights reserved.</span>
      <span>AI Fair Price Engine v1</span>
    </div>
  </div>
</footer>
<div class="toast-stack" id="toastStack"></div>
<div class="modal-overlay" id="appModal"><div class="modal"></div></div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= Config::baseUrl('assets/js/app.js') ?>"></script>
<?php if (!empty($pageScript)): ?><script><?= $pageScript ?></script><?php endif; ?>
</body>
</html>
