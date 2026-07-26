<?php
require_once dirname(dirname(__DIR__)) . '/includes/bootstrap.php';
header('Content-Type: application/json');

if (!Auth::check()) {
    echo json_encode(['success' => false, 'message' => 'Please log in first.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request body.']);
    exit;
}

$price = (float)($input['price'] ?? 0);
if ($price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Enter your asking price first.']);
    exit;
}

$api = new PredictionApi();
$response = $api->predict([
    'brand'            => trim((string)($input['brand'] ?? '')),
    'model'            => trim((string)($input['model'] ?? '')),
    'manufacture_year' => (int)($input['manufacture_year'] ?? 0),
    'transmission'     => ucfirst(trim((string)($input['transmission'] ?? ''))),
    'fuel_type'        => ucfirst(trim((string)($input['fuel_type'] ?? ''))),
    'engine_cc'        => (int)($input['engine_cc'] ?? 0),
    'mileage_km'       => (int)($input['mileage_km'] ?? 0),
    'feature_count'    => (int)($input['feature_count'] ?? 0),
]);

if (empty($response['success'])) {
    echo json_encode(['success' => false, 'message' => $response['message'] ?? 'Valuation failed.']);
    exit;
}

$prediction = $response['prediction'];
$validator = new PriceValidator();
$result = $validator->classify($price, $prediction);

$headlines = [
    'fair'        => 'Fair price. You are good to publish.',
    'overpriced'  => 'Overpriced. This ad would be blocked.',
    'underpriced' => 'Underpriced. This ad would be blocked.',
];

echo json_encode([
    'success'    => true,
    'result'     => $result,
    'headline'   => $headlines[$result],
    'message'    => $validator->message($result, $prediction),
    'prediction' => [
        'predicted_price'   => $prediction['predicted_price'],
        'lower_bound'       => $prediction['lower_bound'],
        'upper_bound'       => $prediction['upper_bound'],
        'confidence_score'  => $prediction['confidence_score'],
        'predicted_display' => money((float)$prediction['predicted_price']),
        'range_display'     => money((float)$prediction['lower_bound']) . ' - ' . money((float)$prediction['upper_bound']),
    ],
]);
