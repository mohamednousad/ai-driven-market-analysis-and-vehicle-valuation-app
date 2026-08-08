<?php
require_once __DIR__ . '/app/bootstrap.php';
if (Auth::check()) {
    Helpers::redirect('dashboard.php');
}
$pageTitle = 'Create Account';
$errors = [];
$old = ['username' => '', 'email' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireCsrf();
    $old['username'] = Helpers::post('username');
    $old['email'] = Helpers::post('email');
    $password = Helpers::post('password');
    $confirm = Helpers::post('password_confirm');
    $v = new Validator();
    $v->required('username', $old['username'], 'Username')->min('username', $old['username'], 3, 'Username')
      ->required('email', $old['email'], 'Email')->email('email', $old['email'])
      ->required('password', $password, 'Password')->min('password', $password, 8, 'Password');
    if ($password !== $confirm) {
        $errors['password_confirm'] = 'Passwords do not match.';
    }
    $userModel = new UserModel();
    if ($v->passes() && !$errors) {
        if ($userModel->findByEmail($old['email'])) {
            $errors['email'] = 'An account with this email already exists.';
        } else {
            $userId = $userModel->create($old['username'], $old['email'], $password);
            Session::regenerate();
            Session::set('user_id', $userId);
            Session::set('user_role', 'user');
            Session::set('user_name', $old['username']);
            Flash::success('Account created. Welcome to AutoValue!');
            Helpers::redirect('dashboard.php');
        }
    }
    $errors = array_merge($v->errors(), $errors);
}
require __DIR__ . '/partials/header.php';
?>
<div class="auth-wrap">
  <div class="auth-card">
    <span class="eyebrow">Join the marketplace</span>
    <h2>Create your account</h2>
    <p class="sub">One account lets you buy, sell, and chat.</p>
    <form method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?= Helpers::e(Session::csrfToken()) ?>">
      <div class="form-group <?= isset($errors['username']) ? 'has-error' : '' ?>">
        <label>Username</label>
        <input type="text" name="username" value="<?= Helpers::e($old['username']) ?>" placeholder="e.g. nabeel_s">
        <span class="field-error"><?= Helpers::e($errors['username'] ?? '') ?></span>
      </div>
      <div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
        <label>Email address</label>
        <input type="email" name="email" value="<?= Helpers::e($old['email']) ?>" placeholder="you@example.com">
        <span class="field-error"><?= Helpers::e($errors['email'] ?? '') ?></span>
      </div>
      <div class="form-row">
        <div class="form-group <?= isset($errors['password']) ? 'has-error' : '' ?>">
          <label>Password</label>
          <input type="password" name="password" placeholder="Min 8 characters">
          <span class="field-error"><?= Helpers::e($errors['password'] ?? '') ?></span>
        </div>
        <div class="form-group <?= isset($errors['password_confirm']) ? 'has-error' : '' ?>">
          <label>Confirm password</label>
          <input type="password" name="password_confirm" placeholder="Repeat password">
          <span class="field-error"><?= Helpers::e($errors['password_confirm'] ?? '') ?></span>
        </div>
      </div>
      <button class="btn btn-gold btn-block" type="submit"><i class="fa-solid fa-user-plus"></i> Create Account</button>
    </form>
    <div class="form-foot-link">Already have an account? <a href="<?= Config::baseUrl('login.php') ?>">Sign in</a></div>
  </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
