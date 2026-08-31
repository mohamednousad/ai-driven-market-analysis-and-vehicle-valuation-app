<?php
require_once __DIR__ . '/app/bootstrap.php';
if (Auth::check()) {
    Helpers::redirect('dashboard.php');
}
$pageTitle = 'Login';
$errors = [];
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireCsrf();
    $email = Helpers::post('email');
    $password = Helpers::post('password');
    $v = new Validator();
    $v->required('email', $email, 'Email')->email('email', $email)->required('password', $password, 'Password');
    if ($v->passes()) {
        $result = Auth::attempt($email, $password, $_SERVER['REMOTE_ADDR'] ?? '');
        if ($result['ok']) {
            Flash::success('Welcome back, ' . $result['user']['username'] . '.');
            Helpers::redirect($result['user']['role'] === 'admin' ? 'admin/index.php' : 'dashboard.php');
        }
        $errors['password'] = $result['error'];
    } else {
        $errors = $v->errors();
    }
}
require __DIR__ . '/partials/header.php';
?>
<div class="auth-wrap">
  <div class="auth-card">
    <span class="eyebrow">Welcome back</span>
    <h2>Sign in to AutoValue</h2>
    <p class="sub">Manage your ads, chats, and AI price reports.</p>
    <form method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?= Helpers::e(Session::csrfToken()) ?>">
      <div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
        <label>Email address</label>
        <input type="email" name="email" value="<?= Helpers::e($email) ?>" placeholder="you@example.com">
        <span class="field-error"><?= Helpers::e($errors['email'] ?? '') ?></span>
      </div>
      <div class="form-group <?= isset($errors['password']) ? 'has-error' : '' ?>">
        <label>Password</label>
        <input type="password" name="password" placeholder="Your password">
        <span class="field-error"><?= Helpers::e($errors['password'] ?? '') ?></span>
      </div>
      <button class="btn btn-gold btn-block" type="submit"><i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In</button>
    </form>
    <div class="form-foot-link">New to AutoValue? <a href="<?= Config::baseUrl('register.php') ?>">Create an account</a></div>
  </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
