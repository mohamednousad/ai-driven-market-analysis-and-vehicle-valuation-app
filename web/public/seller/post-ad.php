<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../lib/PredictionApi.php';
require_once __DIR__ . '/../../lib/PriceValidator.php';
require_once __DIR__ . '/../../lib/AdRepository.php';
require_once __DIR__ . '/../../lib/SettingsService.php';

require_role(ROLE_SELLER);
$assetBase = '../';

$settings = new SettingsService($pdo);
$band = $settings->fairnessBand();

$message = '';
$messageType = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $message = 'Your session expired. Please try again.';
    } else {
        $inputs = [
            'brand' => post('brand'),
            'model_year' => (int)post('model_year'),
            'mileage' => (int)post('mileage'),
            'engine_capacity' => (int)post('engine_capacity'),
            'fuel_type' => post('fuel_type'),
            'transmission' => post('transmission'),
            'condition' => post('condition_grade'),
        ];
        $asking = (float)post('asking_price');
        $title = trim(post('title'));

        if (!$title || !$inputs['brand'] || $asking <= 0) {
            $message = 'Please complete all required fields.';
        } else {
            $api = new PredictionApi(AI_API_BASE_URL);
            $validator = new PriceValidator($api, $band);
            $result = $validator->evaluate($inputs, $asking);

            if (($result['ok'] ?? false) !== true) {
                $message = $result['message'];
            } elseif (!$result['fair']) {
                $verdict = $result['verdict'] === 'overpriced' ? 'too high' : 'too low';
                $message = 'Your asking price is ' . $verdict . '. Fair range is '
                    . format_money($result['lower_bound']) . ' – ' . format_money($result['upper_bound'])
                    . '. Please adjust the price and resubmit.';
            } else {
                $imagePath = null;
                if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                        if (!is_dir(UPLOAD_DIR)) {
                            mkdir(UPLOAD_DIR, 0775, true);
                        }
                        $fileName = 'ad_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        if (move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . '/' . $fileName)) {
                            $imagePath = $fileName;
                        }
                    }
                }

                $adRepo = new AdRepository($pdo);
                $adRepo->create([
                    'seller_id' => current_user_id(),
                    'title' => $title,
                    'brand' => $inputs['brand'],
                    'vehicle_model' => trim(post('vehicle_model')),
                    'model_year' => $inputs['model_year'],
                    'mileage' => $inputs['mileage'],
                    'engine_capacity' => $inputs['engine_capacity'],
                    'fuel_type' => $inputs['fuel_type'],
                    'transmission' => $inputs['transmission'],
                    'condition_grade' => $inputs['condition'],
                    'asking_price' => $asking,
                    'predicted_price' => $result['predicted_price'],
                    'lower_bound' => $result['lower_bound'],
                    'upper_bound' => $result['upper_bound'],
                    'description' => trim(post('description')),
                    'location' => trim(post('location')),
                    'image_path' => $imagePath,
                    'status' => AD_STATUS_PENDING,
                ]);
                set_flash('success', 'Your ad passed the AI fair-price check and was submitted for approval.');
                redirect('my-ads.php');
            }
        }
    }
}

$brands = ['Toyota', 'Honda', 'Nissan', 'Suzuki', 'Mitsubishi', 'Mazda', 'BMW', 'Mercedes-Benz', 'Audi', 'Other'];
$fuels = ['Petrol', 'Diesel', 'Hybrid', 'Electric'];
$transmissions = ['Automatic', 'Manual'];
$conditions = ['Excellent', 'Good', 'Fair'];

$pageTitle = 'Post a vehicle';
$pageScripts = ['assets/js/post-ad.js'];
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="section">
    <span class="eyebrow">New listing</span>
    <h1 class="mb-0">Post a vehicle</h1>
    <p>Enter the details, run an instant AI valuation, then submit. Ads must pass the fair-price check (±<?php echo (int)$band; ?>%).</p>
</section>

<?php if ($message): ?>
    <div class="alert <?php echo e($messageType); ?>"><i class="fa-solid fa-circle-exclamation"></i><span><?php echo e($message); ?></span></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="grid grid-sidebar" id="adForm">
    <?php echo csrf_field(); ?>
    <div class="stack">
        <div class="card">
            <h3>Vehicle details</h3>
            <div class="form-grid cols-2">
                <div class="field full-span">
                    <label>Ad title</label>
                    <input type="text" name="title" placeholder="e.g. Toyota Aqua 2018 Hybrid" value="<?php echo e(post('title')); ?>" required>
                </div>
                <div class="field">
                    <label>Brand</label>
                    <select name="brand" id="brand" required>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?php echo e($b); ?>" <?php echo post('brand') === $b ? 'selected' : ''; ?>><?php echo e($b); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Model</label>
                    <input type="text" name="vehicle_model" placeholder="e.g. Aqua" value="<?php echo e(post('vehicle_model')); ?>">
                </div>
                <div class="field">
                    <label>Model year</label>
                    <input type="number" name="model_year" id="model_year" min="1990" max="2026" value="<?php echo e(post('model_year', '2018')); ?>" required>
                </div>
                <div class="field">
                    <label>Mileage (km)</label>
                    <input type="number" name="mileage" id="mileage" min="0" step="1000" value="<?php echo e(post('mileage', '60000')); ?>" required>
                </div>
                <div class="field">
                    <label>Engine capacity (cc)</label>
                    <input type="number" name="engine_capacity" id="engine_capacity" min="600" step="50" value="<?php echo e(post('engine_capacity', '1500')); ?>" required>
                </div>
                <div class="field">
                    <label>Fuel type</label>
                    <select name="fuel_type" id="fuel_type" required>
                        <?php foreach ($fuels as $f): ?>
                            <option value="<?php echo e($f); ?>" <?php echo post('fuel_type') === $f ? 'selected' : ''; ?>><?php echo e($f); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Transmission</label>
                    <select name="transmission" id="transmission" required>
                        <?php foreach ($transmissions as $t): ?>
                            <option value="<?php echo e($t); ?>" <?php echo post('transmission') === $t ? 'selected' : ''; ?>><?php echo e($t); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Condition</label>
                    <select name="condition_grade" id="condition_grade" required>
                        <?php foreach ($conditions as $c): ?>
                            <option value="<?php echo e($c); ?>" <?php echo post('condition_grade') === $c ? 'selected' : ''; ?>><?php echo e($c); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Location</label>
                    <input type="text" name="location" placeholder="e.g. Colombo" value="<?php echo e(post('location')); ?>">
                </div>
            </div>
        </div>
        <div class="card">
            <h3>Pricing and description</h3>
            <div class="form-grid cols-2">
                <div class="field">
                    <label>Asking price (Rs)</label>
                    <input type="number" name="asking_price" id="asking_price" min="0" step="10000" value="<?php echo e(post('asking_price')); ?>" required>
                </div>
                <div class="field">
                    <label>Photo (optional)</label>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <div class="field full-span">
                    <label>Description</label>
                    <textarea name="description" placeholder="Service history, ownership, extras..."><?php echo e(post('description')); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="stack">
        <div class="card">
            <span class="eyebrow">AI valuation</span>
            <h3>Check fair price</h3>
            <p class="muted">Run the AI model to see the fair price range before submitting.</p>
            <button type="button" class="btn ghost full" id="valuateBtn"><i class="fa-solid fa-wand-magic-sparkles"></i> Run AI valuation</button>
            <div id="valuationResult" class="hidden" style="margin-top:16px"></div>
        </div>
        <div class="card">
            <button type="submit" class="btn primary full" id="submitBtn"><i class="fa-solid fa-paper-plane"></i> Submit for approval</button>
            <p class="muted" style="font-size:13px;margin-top:12px;margin-bottom:0">Ads are published after passing the AI fair-price check and admin approval.</p>
        </div>
    </div>
</form>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
