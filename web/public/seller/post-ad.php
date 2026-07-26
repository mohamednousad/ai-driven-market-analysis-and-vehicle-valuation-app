<?php
require_once dirname(dirname(__DIR__)) . '/includes/bootstrap.php';
Auth::requireRole('seller');

$locations = $pdo->query("SELECT location_id, name FROM locations WHERE type = 'district' ORDER BY name")->fetchAll();
$featureOptions = ['Air Condition', 'Power Steering', 'Power Mirror', 'Power Window'];

$errors = [];
$aiVerdict = null;
$old = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();

    $title = trim($_POST['title'] ?? '');
    $make = trim($_POST['make'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $bodyType = trim($_POST['body_type'] ?? '');
    $manufactureYear = (int)($_POST['manufacture_year'] ?? 0);
    $registerYear = (int)($_POST['register_year'] ?? 0) ?: null;
    $chassisNo = trim($_POST['chassis_no'] ?? '') ?: null;
    $engineCc = (int)($_POST['engine_cc'] ?? 0) ?: null;
    $fuelType = $_POST['fuel_type'] ?? '';
    $transmission = $_POST['transmission'] ?? '';
    $mileage = (int)($_POST['mileage_km'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $locationId = (int)($_POST['location_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $negotiable = isset($_POST['is_negotiable']) ? 1 : 0;
    $features = array_values(array_intersect((array)($_POST['features'] ?? []), $featureOptions));

    if ($title === '' || mb_strlen($title) > 150) $errors[] = 'A title up to 150 characters is required.';
    if ($make === '' || $model === '') $errors[] = 'Make and model are required.';
    if ($manufactureYear < 1980 || $manufactureYear > (int)date('Y')) $errors[] = 'Enter a valid year of manufacture.';
    if ($registerYear !== null && $registerYear < $manufactureYear) $errors[] = 'Registration year cannot be before the manufacture year.';
    if (!in_array($fuelType, ['petrol', 'diesel', 'hybrid', 'electric', 'other'], true)) $errors[] = 'Select a fuel type.';
    if (!in_array($transmission, ['manual', 'automatic', 'tiptronic', 'other'], true)) $errors[] = 'Select a transmission.';
    if ($mileage < 0 || $mileage > 1500000) $errors[] = 'Enter a valid mileage in km.';
    if ($price <= 0) $errors[] = 'Enter your asking price in rupees.';
    if ($locationId <= 0) $errors[] = 'Select your district.';

    $uploads = [];
    if (!empty($_FILES['images']['name'][0])) {
        $count = count($_FILES['images']['name']);
        if ($count > MAX_IMAGES_PER_AD) {
            $errors[] = 'You can upload up to ' . MAX_IMAGES_PER_AD . ' photos.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
                    $errors[] = 'One of the photos failed to upload.';
                    break;
                }
                if ($_FILES['images']['size'][$i] > MAX_IMAGE_BYTES) {
                    $errors[] = 'Each photo must be under 5 MB.';
                    break;
                }
                $mime = $finfo->file($_FILES['images']['tmp_name'][$i]);
                if (!isset($allowed[$mime])) {
                    $errors[] = 'Photos must be JPG, PNG or WEBP.';
                    break;
                }
                $uploads[] = ['tmp' => $_FILES['images']['tmp_name'][$i], 'ext' => $allowed[$mime]];
            }
        }
    } else {
        $errors[] = 'Add at least one photo of the vehicle.';
    }

    if (!$errors) {
        $api = new PredictionApi();
        $response = $api->predict([
            'brand'            => $make,
            'model'            => $model,
            'manufacture_year' => $manufactureYear,
            'transmission'     => ucfirst($transmission),
            'fuel_type'        => ucfirst($fuelType),
            'engine_cc'        => $engineCc ?? 0,
            'mileage_km'       => $mileage,
            'feature_count'    => count($features),
        ]);

        if (empty($response['success'])) {
            $errors[] = $response['message'] ?? 'AI valuation failed.';
        } else {
            $prediction = $response['prediction'];
            $validator = new PriceValidator();
            $result = $validator->classify($price, $prediction);
            $aiVerdict = [
                'result'     => $result,
                'message'    => $validator->message($result, $prediction),
                'prediction' => $prediction,
            ];

            if ($result === 'fair') {
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0775, true);
                }
                $paths = [];
                foreach ($uploads as $u) {
                    $name = bin2hex(random_bytes(10)) . '.' . $u['ext'];
                    move_uploaded_file($u['tmp'], UPLOAD_DIR . '/' . $name);
                    $paths[] = $name;
                }

                $adRepo = new AdRepository($pdo);
                $adId = $adRepo->createFull(
                    [
                        'seller_id' => (int)Auth::id(), 'location_id' => $locationId,
                        'title' => $title, 'description' => $description,
                        'price' => $price, 'is_negotiable' => $negotiable,
                    ],
                    [
                        'manufacture_year' => $manufactureYear, 'register_year' => $registerYear,
                        'chassis_no' => $chassisNo, 'engine_cc' => $engineCc, 'fuel_type' => $fuelType,
                    ],
                    [
                        'make' => $make, 'model' => $model, 'body_type' => $bodyType ?: null,
                        'transmission' => $transmission, 'mileage_km' => $mileage,
                        'features' => $features ? json_encode($features) : null,
                    ],
                    $paths
                );

                (new AnalysisRepository($pdo))->save($adId, $price, $prediction, $result);
                (new NotificationRepository($pdo))->push(
                    (int)Auth::id(), $adId,
                    'Ad submitted for review',
                    'Your ad passed the AI fair-price check and is waiting for admin approval.',
                    'ad_submitted'
                );

                flash('success', 'Fair price confirmed by AI. Your ad is now pending admin review.');
                redirect('/seller/my-ads.php');
            }
        }
    }
}

$pageTitle = 'Post your ad';
require dirname(dirname(__DIR__)) . '/includes/header.php';
?>
<div class="form-card form-wide">
  <h1>Post your vehicle ad</h1>
  <p style="color:var(--muted);font-size:13px;margin-bottom:16px">
    Every ad is checked by our AI valuation model against real Sri Lankan market data before it can be published.
  </p>

  <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

  <?php if ($aiVerdict && $aiVerdict['result'] !== 'fair'): ?>
    <div class="ai-verdict ai-bad">
      <strong><i class="fa-solid fa-triangle-exclamation"></i> Price <?= e($aiVerdict['result']) ?> — ad not published</strong>
      <?= e($aiVerdict['message']) ?>
      <div class="ai-range">
        <span>AI predicted: <b><?= money((float)$aiVerdict['prediction']['predicted_price']) ?></b></span>
        <span>Fair range: <b><?= money((float)$aiVerdict['prediction']['lower_bound']) ?> - <?= money((float)$aiVerdict['prediction']['upper_bound']) ?></b></span>
        <span>Confidence: <b><?= round((float)$aiVerdict['prediction']['confidence_score'] * 100) ?>%</b></span>
      </div>
    </div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <?= csrfField() ?>
    <div class="form-group">
      <label>Ad title</label>
      <input type="text" name="title" maxlength="150" required placeholder="e.g. Toyota Aqua G Grade 2017" value="<?= e($old['title'] ?? '') ?>">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Make (brand)</label>
        <input type="text" name="make" required placeholder="Toyota" value="<?= e($old['make'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Model</label>
        <input type="text" name="model" required placeholder="Aqua" value="<?= e($old['model'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Body type</label>
        <select name="body_type">
          <?php foreach (['', 'Sedan', 'Hatchback', 'SUV / 4x4', 'Wagon', 'Van', 'Pickup', 'Coupe'] as $bt): ?>
            <option value="<?= e($bt) ?>" <?= ($old['body_type'] ?? '') === $bt ? 'selected' : '' ?>><?= $bt === '' ? 'Select' : e($bt) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Year of manufacture</label>
        <input type="number" name="manufacture_year" required min="1980" max="<?= date('Y') ?>" value="<?= e($old['manufacture_year'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Year of registration (optional)</label>
        <input type="number" name="register_year" min="1980" max="<?= date('Y') ?>" value="<?= e($old['register_year'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Chassis no (optional)</label>
        <input type="text" name="chassis_no" maxlength="40" value="<?= e($old['chassis_no'] ?? '') ?>">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Engine capacity (cc)</label>
        <input type="number" name="engine_cc" min="0" max="10000" value="<?= e($old['engine_cc'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Fuel type</label>
        <select name="fuel_type" required>
          <?php foreach (['petrol', 'diesel', 'hybrid', 'electric', 'other'] as $ft): ?>
            <option value="<?= $ft ?>" <?= ($old['fuel_type'] ?? '') === $ft ? 'selected' : '' ?>><?= ucfirst($ft) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Transmission</label>
        <select name="transmission" required>
          <?php foreach (['automatic', 'manual', 'tiptronic', 'other'] as $tr): ?>
            <option value="<?= $tr ?>" <?= ($old['transmission'] ?? '') === $tr ? 'selected' : '' ?>><?= ucfirst($tr) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Mileage (km)</label>
        <input type="number" name="mileage_km" required min="0" value="<?= e($old['mileage_km'] ?? '') ?>">
      </div>
    </div>
    <div class="form-group">
      <label>Features</label>
      <div style="display:flex;gap:16px;flex-wrap:wrap;padding:4px 0">
        <?php foreach ($featureOptions as $feat): ?>
          <label style="font-weight:400;display:flex;gap:6px;align-items:center">
            <input type="checkbox" name="features[]" value="<?= e($feat) ?>"
              <?= in_array($feat, (array)($old['features'] ?? []), true) ? 'checked' : '' ?>> <?= e($feat) ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Asking price (Rs)</label>
        <input type="number" name="price" required min="1" step="1000" value="<?= e($old['price'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>District</label>
        <select name="location_id" required>
          <option value="">Select district</option>
          <?php foreach ($locations as $loc): ?>
            <option value="<?= (int)$loc['location_id'] ?>" <?= (int)($old['location_id'] ?? 0) === (int)$loc['location_id'] ? 'selected' : '' ?>>
              <?= e($loc['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="display:flex;align-items:flex-end">
        <label style="font-weight:400;display:flex;gap:7px;align-items:center;padding-bottom:10px">
          <input type="checkbox" name="is_negotiable" <?= isset($old['is_negotiable']) ? 'checked' : '' ?>> Negotiable
        </label>
      </div>
    </div>
    <div class="form-group">
      <label>Description</label>
      <textarea name="description" rows="5" placeholder="Condition, service history, extras..."><?= e($old['description'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Photos (1 - <?= MAX_IMAGES_PER_AD ?>, JPG/PNG/WEBP, max 5 MB each)</label>
      <input type="file" name="images[]" id="images-input" accept="image/jpeg,image/png,image/webp" multiple required>
      <div class="img-preview" id="img-preview"></div>
    </div>

    <div id="price-check-result"></div>

    <div style="display:flex;gap:10px;flex-wrap:wrap">
      <button class="btn btn-outline" type="button" id="check-price-btn"><i class="fa-solid fa-robot"></i> Check fair price first</button>
      <button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i> Submit ad for AI check</button>
    </div>
  </form>
</div>
<?php require dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
