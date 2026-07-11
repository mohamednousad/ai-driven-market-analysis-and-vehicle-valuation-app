<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../lib/AuthService.php';
redirect_if_logged_in();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $message = 'Your session expired. Please try again.';
    } else {
        $service = new AuthService($pdo);
        $result = $service->login(trim(post('email')), post('password'));
        if ($result['success']) {
            set_flash('success', 'Welcome back, ' . current_name() . '.');
            redirect(role_dashboard(current_role()));
        }
        $message = $result['message'];
    }
}
$pageTitle = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="auth-layout">
    <div class="auth-visual">
        <div class="brand" style="color:#fff">
            <span class="brand-mark"><i class="fa-solid fa-car-side"></i></span>
            <span class="brand-text"><?php echo APP_NAME; ?></span>
        </div>
        <div>
            <h1>Welcome back.</h1>
            <p>Login to browse fair-priced vehicles, manage your listings, or run the admin console.</p>
            <div class="auth-points">
                <div class="auth-point"><i class="fa-solid fa-gauge"></i> Role-based dashboards for buyers, sellers and admins</div>
                <div class="auth-point"><i class="fa-solid fa-bolt"></i> Instant AI price checks before ads go live</div>
                <div class="auth-point"><i class="fa-solid fa-comments"></i> Smart AI search assistant for buyers</div>
            </div>
        </div>
        <p class="muted" style="color:rgba(255,255,255,.75)">CSE6035 Development Project</p>
    </div>
    <div class="auth-form-wrap">
        <div class="card auth-card">
            <span class="eyebrow">Secure login</span>
            <h2>Login to <?php echo APP_NAME; ?></h2>
            <?php if ($message): ?>
                <div class="alert error"><i class="fa-solid fa-circle-exclamation"></i><span><?php echo e($message); ?></span></div>
            <?php endif; ?>
            <form method="POST" class="form-grid">
                <?php echo csrf_field(); ?>
                <div class="field">
                    <label>Email</label>
                    <div class="input-icon"><i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" required>
                    </div>
                </div>
                <div class="field">
                    <label>Password</label>
                    <div class="input-icon"><i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" required>
                    </div>
                </div>
                <button type="submit" class="btn primary full">Login <i class="fa-solid fa-arrow-right"></i></button>
            </form>
            <p class="muted" style="margin-top:16px">No account yet? <a href="register.php" style="color:var(--orange-dark);font-weight:700">Create one</a></p>
            <div class="divider"></div>
            <p class="muted" style="font-size:13px;margin-bottom:6px"><strong>Demo logins</strong> (password: <code>password123</code>)</p>
            <div class="tag-row">
                <span class="spec-chip"><i class="fa-solid fa-user-shield"></i> admin@autovalue.lk</span>
                <span class="spec-chip"><i class="fa-solid fa-tag"></i> seller@autovalue.lk</span>
                <span class="spec-chip"><i class="fa-solid fa-magnifying-glass"></i> buyer@autovalue.lk</span>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
