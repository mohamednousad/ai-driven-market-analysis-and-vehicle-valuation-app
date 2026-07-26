<?php
require_once dirname(dirname(__DIR__)) . '/includes/bootstrap.php';
Auth::requireRole('admin');

$userRepo = new UserRepository($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $userId = (int)($_POST['user_id'] ?? 0);
    if (isset($_POST['status'])) {
        $userRepo->setStatus($userId, $_POST['status']);
        flash('success', 'User status updated.');
    }
    if (isset($_POST['poster_type'])) {
        $userRepo->setPosterType($userId, $_POST['poster_type']);
        flash('success', 'Poster type updated.');
    }
    redirect('/admin/users.php');
}

$users = $userRepo->all();

$pageTitle = 'Manage Users';
require dirname(dirname(__DIR__)) . '/includes/header.php';
?>
<div class="container" style="padding-top:18px;padding-bottom:40px">
  <h1 style="font-size:20px;margin-bottom:14px">Manage Users (<?= count($users) ?>)</h1>
  <table class="table">
    <tr><th>Name</th><th>Email / Phone</th><th>Role</th><th>Poster type</th><th>Status</th><th>Actions</th></tr>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= e($u['full_name']) ?></td>
        <td><?= e($u['email']) ?><br><span style="color:var(--muted);font-size:12px"><?= e($u['phone'] ?? '-') ?></span></td>
        <td><?= e(ucfirst($u['role'])) ?></td>
        <td>
          <?php if ($u['role'] === 'seller'): ?>
            <form method="post"><?php echo csrfField(); ?>
              <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
              <select name="poster_type" onchange="this.form.submit()">
                <option value="non_member" <?= $u['poster_type'] === 'non_member' ? 'selected' : '' ?>>Non-member</option>
                <option value="member" <?= $u['poster_type'] === 'member' ? 'selected' : '' ?>>Member</option>
                <option value="authorized_agent" <?= $u['poster_type'] === 'authorized_agent' ? 'selected' : '' ?>>Authorized Agent</option>
              </select>
            </form>
          <?php else: ?>-<?php endif; ?>
        </td>
        <td><span class="chip <?= $u['status'] === 'active' ? 'chip-approved' : 'chip-rejected' ?>"><?= e(ucfirst($u['status'])) ?></span></td>
        <td>
          <div class="actions">
            <?php if ($u['role'] !== 'admin'): ?>
              <?php if ($u['status'] !== 'active'): ?>
                <form method="post"><?php echo csrfField(); ?>
                  <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
                  <input type="hidden" name="status" value="active">
                  <button class="btn btn-green btn-sm" type="submit">Activate</button>
                </form>
              <?php endif; ?>
              <?php if ($u['status'] !== 'suspended'): ?>
                <form method="post"><?php echo csrfField(); ?>
                  <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
                  <input type="hidden" name="status" value="suspended">
                  <button class="btn btn-outline btn-sm" type="submit">Suspend</button>
                </form>
              <?php endif; ?>
              <?php if ($u['status'] !== 'banned'): ?>
                <form method="post"><?php echo csrfField(); ?>
                  <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
                  <input type="hidden" name="status" value="banned">
                  <button class="btn btn-danger btn-sm" type="submit">Ban</button>
                </form>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php require dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
