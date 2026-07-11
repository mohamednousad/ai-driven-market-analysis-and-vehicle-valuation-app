<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../lib/AuthService.php';
redirect_if_logged_in();

$message = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $message = 'Your session expired. Please try again.';
    } else {
        $service = new AuthService($pdo);
        $result = $service->register(
            trim(post('full_name')),
            trim(post('email')),
            post('password'),
            post('role', ROLE_BUYER),
            trim(post('phone'))
        );
        $message = $result['message'];
        $success = $result['success'];
        if ($success) {
            set_flash('success', $message);
            redirect('login.php');
        }
        set_old(['full_name' => post('full_name'), 'email' => post('email'), 'phone' => post('phone'), 'role' => post('role')]);
    }
}
$pageTitle = 'Create account';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="auth-layout">
    <div class="auth-visual">
        <div class="brand" style="color:#fff">
            <span class="brand-mark"><i class="fa-solid fa-car-side"></i></span>
            <span class="brand-text"><?php echo APP_NAME; ?></span>
        </div>
        <div>
            <h1>Buy and sell vehicles the smart way.</h1>
            <p>Join Sri Lanka's AI-assisted vehicle marketplace where prices are validated, sellers are rated, and buyers get real guidance.</p>
            <div class="auth-points">
                <div class="auth-point"><i class="fa-solid fa-shield-halved"></i> AI-validated fair pricing on every listing</div>
                <div class="auth-point"><i class="fa-solid fa-robot"></i> Chat with an AI assistant to find your match</div>
                <div class="auth-point"><i class="fa-solid fa-star"></i> Verified seller ratings you can trust</div>
            </div>
        </div>
        <p class="muted" style="color:rgba(255,255,255,.75)">CSE6035 Development Project</p>
    </div>
    <div class="auth-form-wrap">
        <div class="card auth-card">
            <span class="eyebrow">Create account</span>
            <h2>Start your account</h2>
            <?php if ($message): ?>
                <div class="alert <?php echo $success ? 'success' : 'error'; ?>">
                    <i class="fa-solid fa-circle-exclamation"></i><span><?php echo e($message); ?></span>
                </div>
            <?php endif; ?>
            <form method="POST" class="form-grid">
                <?php echo csrf_field(); ?>
                <div class="role-toggle">
                    <label class="role-option">
                        <input type="radio" name="role" value="buyer" <?php echo old('role', 'buyer') === 'seller' ? '' : 'checked'; ?>>
                        <span><i class="fa-solid fa-magnifying-glass"></i> Buyer</span>
                    </label>
                    <label class="role-option">
                        <input type="radio" name="role" value="seller" <?php echo old('role') === 'seller' ? 'checked' : ''; ?>>
                        <span><i class="fa-solid fa-tag"></i> Seller</span>
                    </label>
                </div>
                <div class="field">
                    <label>Full name</label>
                    <div class="input-icon"><i class="fa-solid fa-user"></i>
                        <input type="text" name="full_name" value="<?php echo old('full_name'); ?>" required>
                    </div>
                </div>
                <div class="field">
                    <label>Email</label>
                    <div class="input-icon"><i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" value="<?php echo old('email'); ?>" required>
                    </div>
                </div>
                <div class="field">
                    <label>Phone (optional)</label>
                    <div class="input-icon"><i class="fa-solid fa-phone"></i>
                        <input type="text" name="phone" value="<?php echo old('phone'); ?>">
                    </div>
                </div>
                <div class="field">
                    <label>Password</label>
                    <div class="input-icon"><i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" minlength="6" required>
                    </div>
                </div>
                <button type="submit" class="btn primary full">Create account <i class="fa-solid fa-arrow-right"></i></button>
            </form>
            <p class="muted" style="margin-top:16px">Already registered? <a href="login.php" style="color:var(--orange-dark);font-weight:700">Login here</a></p>
        </div>
    </div>
</section>
<?php clear_old(); require_once __DIR__ . '/../includes/footer.php'; ?>
