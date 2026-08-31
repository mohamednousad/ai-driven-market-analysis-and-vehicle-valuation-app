<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireAdmin();
$pageTitle = 'Users';
$userModel = new UserModel();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireCsrf();
    $targetId = (int)Helpers::post('user_id');
    $action = Helpers::post('action');
    if ($targetId === (int)Auth::id()) {
        Flash::warning('You cannot change your own status.');
        Helpers::redirect('admin/users.php');
    }
    if ($action === 'suspend') { $userModel->updateStatus($targetId, 'suspended'); Flash::success('User suspended.'); }
    if ($action === 'activate') { $userModel->updateStatus($targetId, 'active'); Flash::success('User activated.'); }
    Helpers::redirect('admin/users.php');
}
$users = $userModel->all();
require dirname(__DIR__) . '/partials/header.php';
?>
<div class="dash-shell">
  <?php require dirname(__DIR__) . '/partials/admin-side.php'; ?>
  <main class="dash-main">
    <div class="dash-top"><h2>Users</h2></div>
    <div class="panel">
      <div class="table-wrap"><table>
        <thead><tr><th>User</th><th>Email</th><th>Role</th><th>Seller Type</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td><div class="td-car"><img src="<?= Helpers::e(Helpers::avatar($u['profile_image'])) ?>" alt="" style="border-radius:50%"><span><?= Helpers::e($u['username']) ?></span></div></td>
            <td><?= Helpers::e($u['email']) ?></td>
            <td><span class="status-pill <?= $u['role'] === 'admin' ? 'status-approved' : 'status-sold' ?>"><?= strtoupper(Helpers::e($u['role'])) ?></span></td>
            <td><?= Helpers::e(strtoupper(str_replace('_', ' ', (string)($u['seller_type'] ?? 'BUYER')))) ?></td>
            <td><span class="status-pill status-<?= Helpers::e($u['status']) ?>"><?= strtoupper(Helpers::e($u['status'])) ?></span></td>
            <td><?= Helpers::e(date('d M Y', strtotime($u['created_at']))) ?></td>
            <td>
              <?php if ((int)$u['id'] !== (int)Auth::id()): ?>
                <?php if ($u['status'] !== 'suspended'): ?>
                <form method="post" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?= Helpers::e(Session::csrfToken()) ?>">
                  <input type="hidden" name="action" value="suspend">
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                  <button type="button" class="link-action danger js-confirm" data-title="Suspend this user?" data-text="They will not be able to log in until reactivated." data-yes="Yes, suspend" data-danger="1">Suspend</button>
                </form>
                <?php else: ?>
                <form method="post" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?= Helpers::e(Session::csrfToken()) ?>">
                  <input type="hidden" name="action" value="activate">
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                  <button type="button" class="link-action ok js-confirm" data-title="Reactivate this user?" data-text="They will be able to log in again immediately." data-yes="Yes, activate">Activate</button>
                </form>
                <?php endif; ?>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>
  </main>
</div>
<?php require dirname(__DIR__) . '/partials/footer.php'; ?>
