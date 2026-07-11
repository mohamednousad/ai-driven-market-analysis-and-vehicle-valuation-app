<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../lib/PredictionApi.php';
require_once __DIR__ . '/../../lib/PriceValidator.php';
require_once __DIR__ . '/../../lib/SettingsService.php';

require_role(ROLE_SELLER);

$payload = read_json_body();
$inputs = $payload['inputs'] ?? [];
$asking = (float)($payload['asking_price'] ?? 0);

if (!$inputs || $asking <= 0) {
    json_response(['success' => false, 'message' => 'Please complete the vehicle details and asking price.'], 422);
}

$settings = new SettingsService($pdo);
$api = new PredictionApi(AI_API_BASE_URL);
$validator = new PriceValidator($api, $settings->fairnessBand());
$result = $validator->evaluate($inputs, $asking);

if (($result['ok'] ?? false) !== true) {
    json_response(['success' => false, 'message' => $result['message'] ?? 'Valuation failed.'], 400);
}

$result['currency_symbol'] = $settings->currencySymbol();
$result['success'] = true;
json_response($result);
