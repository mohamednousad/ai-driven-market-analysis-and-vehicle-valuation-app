<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (Auth::check()) {
    redirect('/index.php');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $auth = new Auth($pdo);
    [$ok, $error] = $auth->register(
        trim($_POST['full_name'] ?? ''),
        trim($_POST['email'] ?? ''),
        $_POST['password'] ?? '',
        $_POST['role'] ?? 'buyer',
        trim($_POST['phone'] ?? '')
    );
    if ($ok) {
        flash('success', 'Account created. You can log in now.');
        redirect('/login.php');
    }
}

$pageTitle = 'Register';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="form-card">
  <h1>Create your account</h1>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post">
    <?= csrfField() ?>
    <div class="form-group">
      <label>Full name</label>
      <input type="text" name="full_name" required maxlength="120" value="<?= e($_POST['full_name'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Phone</label>
      <input type="text" name="phone" required maxlength="20" placeholder="07XXXXXXXX" value="<?= e($_POST['phone'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>I want to</label>
      <select name="role">
        <option value="buyer" <?= ($_POST['role'] ?? '') === 'buyer' ? 'selected' : '' ?>>Buy vehicles</option>
        <option value="seller" <?= ($_POST['role'] ?? '') === 'seller' ? 'selected' : '' ?>>Sell vehicles</option>
      </select>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required minlength="8">
      <div class="form-hint">At least 8 characters.</div>
    </div>
    <button class="btn btn-primary btn-block" type="submit">Create account</button>
  </form>
  <div class="form-foot">Already registered? <a href="/login.php">Log in</a></div>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
