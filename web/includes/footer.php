</main>
<footer class="site-footer">
    <div class="footer-inner">
        <div>
            <span class="brand-mark small"><i class="fa-solid fa-car-side"></i></span>
            <strong><?php echo APP_NAME; ?></strong>
            <p><?php echo APP_TAGLINE; ?> — fair pricing, verified sellers, smart search.</p>
        </div>
        <p class="muted">&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Built for CSE6035 Development Project.</p>
    </div>
</footer>
<script src="<?php echo $assetBase ?? ''; ?>assets/js/app.js"></script>
<?php if (!empty($pageScripts) && is_array($pageScripts)): ?>
    <?php foreach ($pageScripts as $script): ?>
        <script src="<?php echo ($assetBase ?? '') . $script; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
