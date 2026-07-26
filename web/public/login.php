<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (Auth::check()) {
    redirect('/index.php');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $auth = new Auth($pdo);
    [$ok, $error] = $auth->attempt(trim($_POST['email'] ?? ''), $_POST['password'] ?? '');
    if ($ok) {
        flash('success', 'Welcome back!');
        $role = Auth::role();
        redirect($role === 'admin' ? '/admin/dashboard.php' : '/index.php');
    }
}

$pageTitle = 'Log in';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="form-card">
  <h1>Log in to AutoValue</h1>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post">
    <?= csrfField() ?>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <button class="btn btn-primary btn-block" type="submit">Log in</button>
  </form>
  <div class="form-foot">New to AutoValue? <a href="/register.php">Create an account</a></div>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
