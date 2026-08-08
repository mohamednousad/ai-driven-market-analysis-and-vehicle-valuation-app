<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireAdmin();
$pageTitle = 'Authorized Seller Requests';
$reqModel = new AuthorizedRequestModel();
$sellerModel = new SellerProfileModel();
$notif = new NotificationModel();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireCsrf();
    $id = (int)Helpers::post('request_id');
    $action = Helpers::post('action');
    $reason = trim(Helpers::post('rejection_reason'));
    $updated = null;
    if ($action === 'approve') {
        $updated = $reqModel->review($id, (int)Auth::id(), 'approved');
        if ($updated) {
            $sellerModel->updateSellerType((int)$updated['user_id'], 'authorized');
            $notif->push((int)$updated['user_id'], 'Authorized Seller Approved', 'Congratulations! You are now an Authorized Seller. The gold badge will show on all your ads.', 'system');
            Flash::success('Seller approved as Authorized.');
        }
    } elseif ($action === 'reject') {
        if (mb_strlen($reason) < 5) {
            Flash::error('Please give the seller a clear rejection reason.');
            Helpers::redirect('admin/sellers.php');
        }
        $updated = $reqModel->review($id, (int)Auth::id(), 'rejected', $reason);
        if ($updated) {
            $notif->push((int)$updated['user_id'], 'Authorized Request Rejected', 'Your Authorized Seller request was not approved. Reason: ' . $reason, 'system');
            Flash::warning('Request rejected. Seller has been notified.');
        }
    }
    Helpers::redirect('admin/sellers.php');
}
$all = $reqModel->all();
require dirname(__DIR__) . '/partials/header.php';
?>
<div class="dash-shell">
  <?php require dirname(__DIR__) . '/partials/admin-side.php'; ?>
  <main class="dash-main">
    <div class="dash-top"><h2>Authorized Seller Requests</h2></div>
    <div class="panel">
      <?php if (!$all): ?>
      <div class="empty-state"><i class="fa-solid fa-shield-halved"></i><h4>No requests submitted</h4></div>
      <?php else: ?>
      <div class="table-wrap"><table>
        <thead><tr><th>Applicant</th><th>Business</th><th>Description</th><th>Document</th><th>Status</th><th>Submitted</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($all as $r): ?>
          <tr>
            <td><b><?= Helpers::e($r['username']) ?></b><br><span style="font-size:11px;color:var(--metallic-grey)"><?= Helpers::e($r['email']) ?></span></td>
            <td><?= Helpers::e($r['business_name']) ?></td>
            <td style="max-width:260px"><?= Helpers::e(mb_strimwidth((string)$r['business_description'], 0, 130, '...')) ?></td>
            <td>
              <?php if ($r['business_document']): ?>
              <a class="link-action" href="<?= Config::baseUrl($r['business_document']) ?>" target="_blank"><i class="fa-regular fa-file-pdf"></i> View PDF</a>
              <?php else: ?>&mdash;<?php endif; ?>
            </td>
            <td><span class="status-pill status-<?= Helpers::e($r['status']) ?>"><?= strtoupper(Helpers::e($r['status'])) ?></span>
              <?php if ($r['status'] === 'rejected' && $r['rejection_reason']): ?><br><span style="font-size:11px;color:var(--red-flag)"><?= Helpers::e($r['rejection_reason']) ?></span><?php endif; ?></td>
            <td><?= Helpers::e(date('d M Y', strtotime($r['created_at']))) ?></td>
            <td>
              <?php if ($r['status'] === 'pending'): ?>
              <div style="display:flex;gap:12px;flex-wrap:wrap">
                <form method="post" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?= Helpers::e(Session::csrfToken()) ?>">
                  <input type="hidden" name="action" value="approve">
                  <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                  <button type="button" class="link-action ok js-confirm" data-title="Approve this Authorized request?" data-text="The seller will be upgraded to Authorized immediately." data-yes="Yes, approve">Approve</button>
                </form>
                <button type="button" class="link-action danger js-reject" data-id="<?= (int)$r['id'] ?>">Reject</button>
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
<?php
$csrfJs = Session::csrfToken();
$pageScript = <<<JS
$('.js-reject').on('click', function () {
  var reqId = \$(this).data('id');
  AV.modal({
    type: 'error',
    title: 'Reject Authorized Seller request',
    html: '<textarea id="rejReason" rows="3" placeholder="Explain what needs to change (visible to the seller)" style="width:100%;padding:11px;border:1.5px solid var(--platinum-2);border-radius:10px;font-size:13.5px"></textarea>',
    buttons: [
      { label: 'Cancel', cls: 'btn-outline-dark' },
      { label: 'Send Rejection', cls: 'btn-danger', keepOpen: true, onClick: function () {
          var reason = \$('#rejReason').val().trim();
          if (reason.length < 5) { AV.toast('warning', 'Please write a clearer reason.'); return; }
          var form = \$('<form method="post"><input type="hidden" name="csrf_token" value="{$csrfJs}"><input type="hidden" name="action" value="reject"><input type="hidden" name="request_id" value="' + reqId + '"><input type="hidden" name="rejection_reason" value=""></form>');
          form.find('[name=rejection_reason]').val(reason);
          \$('body').append(form);
          form.trigger('submit');
      } }
    ]
  });
});
JS;
require dirname(__DIR__) . '/partials/footer.php';
?>
