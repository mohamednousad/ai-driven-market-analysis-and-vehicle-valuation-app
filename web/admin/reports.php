<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireAdmin();
$pageTitle = 'Reports';
$reportModel = new ReportModel();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireCsrf();
    $id = (int)Helpers::post('report_id');
    $status = Helpers::post('status');
    if (in_array($status, ['reviewed', 'resolved'], true)) {
        $reportModel->review($id, (int)Auth::id(), $status);
        Flash::success('Report marked as ' . $status . '.');
    }
    Helpers::redirect('admin/reports.php');
}
$all = $reportModel->all();
require dirname(__DIR__) . '/partials/header.php';
?>
<div class="dash-shell">
  <?php require dirname(__DIR__) . '/partials/admin-side.php'; ?>
  <main class="dash-main">
    <div class="dash-top"><h2>Reports</h2></div>
    <div class="panel">
      <?php if (!$all): ?>
      <div class="empty-state"><i class="fa-regular fa-flag"></i><h4>No reports yet</h4></div>
      <?php else: ?>
      <div class="table-wrap"><table>
        <thead><tr><th>Reporter</th><th>Reported User</th><th>Ad</th><th>Reason</th><th>Status</th><th>Submitted</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($all as $r): ?>
          <tr>
            <td><?= Helpers::e($r['reporter_name']) ?></td>
            <td><?= Helpers::e($r['reported_name']) ?></td>
            <td><?php if ($r['ad_id']): ?><a class="link-action" href="<?= Config::baseUrl('ad.php?id=' . (int)$r['ad_id']) ?>"><?= Helpers::e($r['ad_title']) ?></a><?php else: ?>&mdash;<?php endif; ?></td>
            <td style="max-width:280px"><?= Helpers::e($r['reason']) ?></td>
            <td><span class="status-pill status-<?= Helpers::e($r['status']) ?>"><?= strtoupper(Helpers::e($r['status'])) ?></span></td>
            <td><?= Helpers::e(date('d M Y', strtotime($r['created_at']))) ?></td>
            <td>
              <?php if ($r['status'] === 'pending'): ?>
              <div style="display:flex;gap:12px;flex-wrap:wrap">
                <form method="post" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?= Helpers::e(Session::csrfToken()) ?>">
                  <input type="hidden" name="report_id" value="<?= (int)$r['id'] ?>">
                  <input type="hidden" name="status" value="reviewed">
                  <button type="submit" class="link-action">Mark Reviewed</button>
                </form>
                <form method="post" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?= Helpers::e(Session::csrfToken()) ?>">
                  <input type="hidden" name="report_id" value="<?= (int)$r['id'] ?>">
                  <input type="hidden" name="status" value="resolved">
                  <button type="submit" class="link-action ok">Mark Resolved</button>
                </form>
              </div>
              <?php else: ?>&mdash;<?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
      <?php endif; ?>
    </div>
  </main>
</div>
<?php require dirname(__DIR__) . '/partials/footer.php'; ?>
