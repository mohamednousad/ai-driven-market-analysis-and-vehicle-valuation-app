<?php
require_once dirname(dirname(__DIR__)) . '/includes/bootstrap.php';
Auth::requireRole('admin');

$reportRepo = new ReportRepository($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $reportRepo->review((int)($_POST['report_id'] ?? 0), $_POST['status'] ?? '', (int)Auth::id());
    flash('success', 'Report updated.');
    redirect('/admin/reports.php');
}

$reports = $reportRepo->all();

$pageTitle = 'Reports';
require dirname(dirname(__DIR__)) . '/includes/header.php';
?>
<div class="container" style="padding-top:18px;padding-bottom:40px">
  <h1 style="font-size:20px;margin-bottom:14px">Ad Reports (<?= count($reports) ?>)</h1>
  <?php if (!$reports): ?><div class="card">No reports submitted.</div><?php endif; ?>
  <?php if ($reports): ?>
  <table class="table">
    <tr><th>Ad</th><th>Reported by</th><th>Type</th><th>Reason</th><th>Status</th><th>Actions</th></tr>
    <?php foreach ($reports as $r): ?>
      <tr>
        <td><a href="/ad.php?id=<?= (int)$r['ad_id'] ?>"><?= e($r['ad_title']) ?></a></td>
        <td><?= e($r['reporter_name']) ?><br><span style="color:var(--muted);font-size:12px"><?= e(timeAgo($r['created_at'])) ?></span></td>
        <td><?= e(str_replace('_', ' ', $r['report_type'])) ?></td>
        <td style="max-width:220px"><?= e($r['reason'] ?: '-') ?></td>
        <td><span class="chip <?= $r['status'] === 'pending' ? 'chip-pending' : 'chip-approved' ?>"><?= e(str_replace('_', ' ', $r['status'])) ?></span></td>
        <td>
          <?php if ($r['status'] === 'pending'): ?>
            <div class="actions">
              <form method="post"><?php echo csrfField(); ?>
                <input type="hidden" name="report_id" value="<?= (int)$r['report_id'] ?>">
                <input type="hidden" name="status" value="action_taken">
                <button class="btn btn-danger btn-sm" type="submit">Action taken</button>
              </form>
              <form method="post"><?php echo csrfField(); ?>
                <input type="hidden" name="report_id" value="<?= (int)$r['report_id'] ?>">
                <input type="hidden" name="status" value="dismissed">
                <button class="btn btn-outline btn-sm" type="submit">Dismiss</button>
              </form>
            </div>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
<?php require dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
