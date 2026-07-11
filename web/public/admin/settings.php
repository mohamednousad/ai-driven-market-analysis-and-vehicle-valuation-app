<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../lib/SettingsService.php';
require_once __DIR__ . '/../../lib/PredictionApi.php';

require_role(ROLE_ADMIN);
$assetBase = '../';

$settings = new SettingsService($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $band = (float)post('fairness_band_percent');
    $band = max(1, min(90, $band));
    $settings->save([
        'fairness_band_percent' => $band,
        'currency' => trim(post('currency')),
        'currency_symbol' => trim(post('currency_symbol')),
        'gemini_api_key' => trim(post('gemini_api_key')),
        'gemini_model' => trim(post('gemini_model')),
        'site_contact_phone' => trim(post('site_contact_phone')),
        'site_contact_email' => trim(post('site_contact_email')),
        'ads_per_page' => max(3, (int)post('ads_per_page')),
    ]);
    set_flash('success', 'Settings saved.');
    redirect('settings.php');
}

$all = $settings->all();
$api = new PredictionApi(AI_API_BASE_URL);
$health = $api->health();
$aiOnline = ($health['status'] ?? '') === 'running';

$pageTitle = 'Settings';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="section">
    <span class="eyebrow">Configuration</span>
    <h1 class="mb-0">System settings</h1>
    <p>Control the AI fair-price band, assistant, currency and contact details.</p>
</section>

<div class="section">
    <div class="card tight stat-card" style="max-width:420px">
        <div class="stat-icon"><i class="fa-solid fa-<?php echo $aiOnline ? 'circle-check' : 'circle-xmark'; ?>"></i></div>
        <div class="stat-body">
            <strong style="font-size:18px"><?php echo $aiOnline ? 'AI service online' : 'AI service offline'; ?></strong>
            <span><?php echo $aiOnline ? 'Model ready: ' . (($health['model_ready'] ?? false) ? 'yes' : 'no') : 'Start the Flask service in ai_service'; ?></span>
        </div>
    </div>
</div>

<form method="POST" class="grid grid-2" style="align-items:start">
    <?php echo csrf_field(); ?>
    <div class="card">
        <h3>Pricing &amp; valuation</h3>
        <div class="form-grid">
            <div class="field">
                <label>Fair price band (%)</label>
                <input type="number" name="fairness_band_percent" min="1" max="90" step="1" value="<?php echo e($all['fairness_band_percent']); ?>" required>
                <span class="muted" style="font-size:12px">Ads within ± this percent of the AI predicted price are considered fair.</span>
            </div>
            <div class="field">
                <label>Listings per page</label>
                <input type="number" name="ads_per_page" min="3" max="48" value="<?php echo e($all['ads_per_page']); ?>">
            </div>
        </div>
    </div>

    <div class="card">
        <h3>Currency</h3>
        <div class="form-grid cols-2">
            <div class="field">
                <label>Currency code</label>
                <input type="text" name="currency" value="<?php echo e($all['currency']); ?>">
            </div>
            <div class="field">
                <label>Currency symbol</label>
                <input type="text" name="currency_symbol" value="<?php echo e($all['currency_symbol']); ?>">
            </div>
        </div>
    </div>

    <div class="card">
        <h3>AI assistant (Gemini)</h3>
        <div class="form-grid">
            <div class="field">
                <label>Gemini API key</label>
                <div class="input-icon"><i class="fa-solid fa-key"></i>
                    <input type="password" name="gemini_api_key" value="<?php echo e($all['gemini_api_key']); ?>" placeholder="Paste your Gemini API key">
                </div>
                <span class="muted" style="font-size:12px">Required to enable the buyer chat assistant.</span>
            </div>
            <div class="field">
                <label>Gemini model</label>
                <input type="text" name="gemini_model" value="<?php echo e($all['gemini_model']); ?>">
            </div>
        </div>
    </div>

    <div class="card">
        <h3>Contact details</h3>
        <div class="form-grid">
            <div class="field">
                <label>Support phone</label>
                <input type="text" name="site_contact_phone" value="<?php echo e($all['site_contact_phone']); ?>">
            </div>
            <div class="field">
                <label>Support email</label>
                <input type="email" name="site_contact_email" value="<?php echo e($all['site_contact_email']); ?>">
            </div>
        </div>
    </div>

    <div class="full-span">
        <button type="submit" class="btn primary"><i class="fa-solid fa-floppy-disk"></i> Save settings</button>
    </div>
</form>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
