<?php
require_once __DIR__ . '/app/bootstrap.php';
Auth::requireLogin();
$pageTitle = 'Become an Authorized Seller';
$userId = (int)Auth::id();
$reqModel = new AuthorizedRequestModel();
$sellerModel = new SellerProfileModel();
$sellerProfile = $sellerModel->findByUserId($userId);
$sellerType = $sellerProfile['seller_type'] ?? 'non_member';
$latest = $reqModel->latestForUser($userId);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireCsrf();
    if ($sellerType !== 'member') {
        Flash::warning('Only Member sellers can request Authorized status. Upgrade your plan first.');
        Helpers::redirect('subscription.php');
    }
    if ($reqModel->pendingForUser($userId)) {
        Flash::warning('You already have a pending request. Please wait for admin review.');
        Helpers::redirect('become-authorized.php');
    }
    $name = Helpers::post('business_name');
    $description = Helpers::post('business_description');
    $v = new Validator();
    $v->required('business_name', $name, 'Business name')->min('business_name', $name, 3, 'Business name')
      ->required('business_description', $description, 'Business description')->min('business_description', $description, 20, 'Business description');
    if (empty($_FILES['business_document']['name'])) {
        $errors['business_document'] = 'Please attach your business registration document as a PDF.';
    }
    $errors = array_merge($v->errors(), $errors);
    if (!$errors) {
        $requestId = $reqModel->create($userId, $name, $description);
        $upload = FileUploader::saveDocument($_FILES['business_document'], 'uploads/sellers/' . $userId . '/verification/' . $requestId, 'business_document');
        if (!$upload['ok']) {
            $errors['business_document'] = $upload['error'];
        } else {
            $reqModel->attachDocument($requestId, $upload['path']);
            (new NotificationModel())->push($userId, 'Request Submitted', 'Your Authorized Seller request is now pending admin review.', 'system');
            Flash::success('Request submitted. Our team will review your documents shortly.');
            Helpers::redirect('become-authorized.php');
        }
    }
}
require __DIR__ . '/partials/header.php';
$authUser = $authUser ?? Auth::user();
?>
<div class="dash-shell">
  <?php require __DIR__ . '/partials/dash-side.php'; ?>
  <main class="dash-main">
    <div class="dash-top"><h2>Authorized Seller Verification</h2></div>
    <?php if ($sellerType === 'authorized'): ?>
    <div class="panel"><div class="panel-body" style="text-align:center;padding:52px 24px">
      <div class="modal-icon green" style="margin:0 auto 16px"><i class="fa-solid fa-shield-halved"></i></div>
      <h3 style="margin-bottom:8px">You are an Authorized Seller</h3>
      <p style="color:var(--metallic-grey)">Your listings carry the gold AUTHORIZED badge that buyers trust most.</p>
    </div></div>
    <?php elseif ($latest && $latest['status'] === 'pending'): ?>
    <div class="panel"><div class="panel-body" style="text-align:center;padding:52px 24px">
      <div class="modal-icon gold" style="margin:0 auto 16px"><i class="fa-regular fa-clock"></i></div>
      <h3 style="margin-bottom:8px">Request under review</h3>
      <p style="color:var(--metallic-grey)">Submitted <?= Helpers::e(Helpers::timeAgo($latest['created_at'])) ?>. We will notify you once an admin reviews your business documents.</p>
    </div></div>
    <?php else: ?>
      <?php if ($latest && $latest['status'] === 'rejected'): ?>
      <div class="panel" style="border-color:var(--red-flag)">
        <div class="panel-body">
          <b style="color:var(--red-flag)"><i class="fa-solid fa-circle-xmark"></i> Your previous request was rejected</b>
          <p style="color:var(--metallic-grey);font-size:13.5px;margin-top:6px">Reason: <?= Helpers::e($latest['rejection_reason'] ?: 'Not specified.') ?> You can fix the issue and submit again below.</p>
        </div>
      </div>
      <?php endif; ?>
      <?php if ($sellerType !== 'member'): ?>
      <div class="panel"><div class="panel-body" style="text-align:center;padding:52px 24px">
        <div class="modal-icon gold" style="margin:0 auto 16px"><i class="fa-solid fa-medal"></i></div>
        <h3 style="margin-bottom:8px">Membership required</h3>
        <p style="color:var(--metallic-grey);margin-bottom:20px">Authorized status is reserved for Member or Premium plan sellers. Upgrade first, then verify your business.</p>
        <a class="btn btn-gold" href="<?= Config::baseUrl('subscription.php') ?>"><i class="fa-solid fa-crown"></i> View Plans</a>
      </div></div>
      <?php else: ?>
      <div class="panel">
        <div class="panel-head"><h3>Submit your business for verification</h3></div>
        <div class="panel-body">
          <form method="post" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf_token" value="<?= Helpers::e(Session::csrfToken()) ?>">
            <div class="form-group <?= isset($errors['business_name']) ? 'has-error' : '' ?>">
              <label>Business name</label>
              <input type="text" name="business_name" value="<?= Helpers::e(Helpers::post('business_name')) ?>" placeholder="e.g. Nabeel Auto Traders">
              <span class="field-error"><?= Helpers::e($errors['business_name'] ?? '') ?></span>
            </div>
            <div class="form-group <?= isset($errors['business_description']) ? 'has-error' : '' ?>">
              <label>What does your business do?</label>
              <textarea name="business_description" rows="3" placeholder="Registered dealer since 2020, specializing in..."><?= Helpers::e(Helpers::post('business_description')) ?></textarea>
              <span class="field-error"><?= Helpers::e($errors['business_description'] ?? '') ?></span>
            </div>
            <div class="form-group <?= isset($errors['business_document']) ? 'has-error' : '' ?>">
              <label>Business registration document (PDF, max 10MB)</label>
              <input type="file" name="business_document" accept="application/pdf">
              <span class="field-error"><?= Helpers::e($errors['business_document'] ?? '') ?></span>
            </div>
            <button class="btn btn-gold" type="submit"><i class="fa-solid fa-shield-halved"></i> Submit for Review</button>
          </form>
        </div>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </main>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
