<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../lib/UserRepository.php';

require_role(ROLE_ADMIN);
$assetBase = '../';

$userRepo = new UserRepository($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = post('action');
    $userId = (int)post('user_id');
    if ($action === 'suspend') {
        $userRepo->setActive($userId, 0);
        set_flash('success', 'User suspended.');
    } elseif ($action === 'activate') {
        $userRepo->setActive($userId, 1);
        set_flash('success', 'User activated.');
    } elseif ($action === 'delete') {
        $userRepo->delete($userId);
        set_flash('success', 'User deleted.');
    }
    redirect('users.php');
}

$users = $userRepo->all();
$pageTitle = 'Manage Users';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="section">
    <span class="eyebrow">User management</span>
    <h1 class="mb-0">Users</h1>
</section>

<div class="table-card">
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><strong><?php echo e($u['full_name']); ?></strong></td>
                <td class="muted"><?php echo e($u['email']); ?></td>
                <td><span class="pill"><?php echo ucfirst($u['role']); ?></span></td>
                <td>
                    <?php if ((int)$u['is_active'] === 1): ?>
                        <span class="badge badge-approved">Active</span>
                    <?php else: ?>
                        <span class="badge badge-rejected">Suspended</span>
                    <?php endif; ?>
                </td>
                <td class="muted" style="font-size:13px"><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                <td>
                    <?php if ($u['role'] !== ROLE_ADMIN): ?>
                        <div class="flex" style="gap:6px">
                            <?php if ((int)$u['is_active'] === 1): ?>
                                <form method="POST" style="margin:0">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="suspend">
                                    <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                    <button class="btn secondary small" title="Suspend"><i class="fa-solid fa-user-slash"></i></button>
                                </form>
                            <?php else: ?>
                                <form method="POST" style="margin:0">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="activate">
                                    <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                    <button class="btn success small" title="Activate"><i class="fa-solid fa-user-check"></i></button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" style="margin:0" onsubmit="return confirm('Delete this user and all their data?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                <button class="btn danger small" title="Delete"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    <?php else: ?>
                        <span class="muted" style="font-size:13px">—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
