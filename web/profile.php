<?php
require_once __DIR__ . '/app/bootstrap.php';
Auth::requireLogin();
$pageTitle = 'My Profile';
$userId = (int)Auth::id();
$userModel = new UserModel();
$sellerModel = new SellerProfileModel();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireCsrf();
    $data = [
        'phone_number' => Helpers::post('phone_number'),
        'date_of_birth' => Helpers::post('date_of_birth') ?: null,
        'gender' => Helpers::post('gender') ?: null,
        'bio' => Helpers::post('bio'),
        'address' => Helpers::post('address'),
        'city' => Helpers::post('city'),
        'district' => Helpers::post('district'),
        'province' => Helpers::post('province'),
        'postal_code' => Helpers::post('postal_code'),
    ];
    $sellerModel->createOrUpdate($userId, $data);
    if (!empty($_FILES['profile_image']['name'])) {
        $upload = FileUploader::saveImage($_FILES['profile_image'], 'uploads/users/' . $userId . '/profile', 'profile_image_' . time());
        if ($upload['ok']) {
            $userModel->updateProfileImage($userId, $upload['path']);
        } else {
            $errors['profile_image'] = $upload['error'];
        }
    }
    if (!$errors) {
        Flash::success('Profile updated successfully.');
        Helpers::redirect('profile.php');
    }
}
$authUserRow = $userModel->findById($userId);
$sellerProfile = $sellerModel->findByUserId($userId);
$sellerType = $sellerProfile['seller_type'] ?? 'non_member';
require __DIR__ . '/partials/header.php';
?>
<div class="dash-shell">
  <?php require __DIR__ . '/partials/dash-side.php'; ?>
  <main class="dash-main">
    <div class="dash-top"><h2>My Profile</h2></div>
    <div class="panel">
      <div class="panel-head"><h3>Account and seller details</h3></div>
      <div class="panel-body">
        <form method="post" enctype="multipart/form-data" novalidate>
          <div style="margin-bottom:20px">
            <span style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--metallic-grey)"><i class="fa-solid fa-user" style="margin-right:6px"></i>Account Details</span>
          </div>
          <input type="hidden" name="csrf_token" value="<?= Helpers::e(Session::csrfToken()) ?>">
          <div style="display:flex;align-items:center;gap:18px;margin-bottom:22px;flex-wrap:wrap">
            <img src="<?= Helpers::e(Helpers::avatar($authUserRow['profile_image'])) ?>" alt="" style="width:74px;height:74px;border-radius:50%;object-fit:cover;border:2px solid var(--gold)">
            <div class="form-group <?= isset($errors['profile_image']) ? 'has-error' : '' ?>" style="margin-bottom:0">
              <label>Profile photo</label>
              <input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp">
              <span class="field-error"><?= Helpers::e($errors['profile_image'] ?? '') ?></span>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group"><label>Username</label><input type="text" value="<?= Helpers::e($authUserRow['username']) ?>" disabled></div>
            <div class="form-group"><label>Email</label><input type="email" value="<?= Helpers::e($authUserRow['email']) ?>" disabled></div>
          </div>
          <hr style="border:none;border-top:1.5px solid var(--platinum-2);margin:24px 0 20px">
          <div style="margin-bottom:20px">
            <span style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--metallic-grey)"><i class="fa-solid fa-car" style="margin-right:6px"></i>Seller Details <span style="font-weight:400;text-transform:none;letter-spacing:0">&mdash; Update only if you want to sell your car</span></span>
          </div>
          <div class="form-row">
            <div class="form-group"><label>Phone number</label><input type="text" name="phone_number" value="<?= Helpers::e($sellerProfile['phone_number'] ?? '') ?>" placeholder="+94 7X XXX XXXX"></div>
            <div class="form-group"><label>Date of birth</label><input type="date" name="date_of_birth" value="<?= Helpers::e($sellerProfile['date_of_birth'] ?? '') ?>"></div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Gender</label>
              <select name="gender">
                <option value="">Prefer not to say</option>
                <?php foreach (['male', 'female', 'other'] as $g): ?>
                <option value="<?= $g ?>" <?= ($sellerProfile['gender'] ?? '') === $g ? 'selected' : '' ?>><?= ucfirst($g) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group"><label>City</label><input type="text" name="city" value="<?= Helpers::e($sellerProfile['city'] ?? '') ?>"></div>
          </div>
          <div class="form-group"><label>Seller bio</label><textarea name="bio" rows="3" placeholder="Tell buyers about yourself"><?= Helpers::e($sellerProfile['bio'] ?? '') ?></textarea></div>
          <div class="form-group"><label>Address</label><input type="text" name="address" value="<?= Helpers::e($sellerProfile['address'] ?? '') ?>"></div>
          <div class="form-row">
            <div class="form-group"><label>District</label><input type="text" name="district" value="<?= Helpers::e($sellerProfile['district'] ?? '') ?>"></div>
            <div class="form-group"><label>Province</label><input type="text" name="province" value="<?= Helpers::e($sellerProfile['province'] ?? '') ?>"></div>
          </div>
          <div class="form-group" style="max-width:220px"><label>Postal code</label><input type="text" name="postal_code" value="<?= Helpers::e($sellerProfile['postal_code'] ?? '') ?>"></div>
          <button class="btn btn-gold" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
