<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireAdmin();
$pageTitle = 'Payments';
$payments = (new PaymentModel())->all();
require dirname(__DIR__) . '/partials/header.php';
?>
<div class="dash-shell">
  <?php require dirname(__DIR__) . '/partials/admin-side.php'; ?>
  <main class="dash-main">
    <div class="dash-top"><h2>Payments Ledger</h2></div>
    <div class="panel">
      <?php if (!$payments): ?>
      <div class="empty-state"><i class="fa-solid fa-credit-card"></i><h4>No payments yet</h4></div>
      <?php else: ?>
      <div class="table-wrap"><table>
        <thead><tr><th>Date</th><th>User</th><th>Category</th><th>Amount</th><th>Method</th><th>Transaction</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($payments as $p): ?>
          <tr>
            <td><?= Helpers::e($p['paid_at'] ? date('d M Y H:i', strtotime($p['paid_at'])) : date('d M Y H:i', strtotime($p['created_at']))) ?></td>
            <td><?= Helpers::e($p['username']) ?></td>
            <td><?= Helpers::e(ucfirst((string)$p['category'])) ?></td>
            <td style="font-family:var(--mono)"><?= Helpers::e(Helpers::price($p['amount'])) ?></td>
            <td><?= Helpers::e(ucfirst((string)$p['method'])) ?></td>
            <td style="font-family:var(--mono);font-size:11.5px"><?= Helpers::e((string)$p['transaction_id']) ?></td>
            <td><span class="status-pill status-<?= Helpers::e($p['status']) ?>"><?= strtoupper(Helpers::e($p['status'])) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
      <?php endif; ?>
    </div>
  </main>
</div>
<?php require dirname(__DIR__) . '/partials/footer.php'; ?>
