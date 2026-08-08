<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireLogin();
Auth::requireCsrf();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helpers::json(['ok' => false, 'error' => 'Invalid request method.'], 405);
}
$fields = [
    'title' => Helpers::post('title'),
    'description' => Helpers::post('description'),
    'price' => Helpers::post('price'),
    'location_id' => Helpers::post('location_id'),
    'make' => Helpers::post('make'),
    'model' => Helpers::post('model'),
    'manufacture_year' => Helpers::post('manufacture_year'),
    'registration_year' => Helpers::post('registration_year'),
    'engine_cc' => Helpers::post('engine_cc'),
    'mileage' => Helpers::post('mileage'),
];
$v = new Validator();
$v->required('title', $fields['title'], 'Title')->min('title', $fields['title'], 8, 'Title')
  ->required('description', $fields['description'], 'Description')->min('description', $fields['description'], 20, 'Description')
  ->required('price', $fields['price'], 'Price')->numeric('price', $fields['price'], 'Price')
  ->required('location_id', $fields['location_id'], 'Location')
  ->required('make', $fields['make'], 'Make')
  ->required('model', $fields['model'], 'Model')
  ->required('manufacture_year', $fields['manufacture_year'], 'Manufacture year')->numeric('manufacture_year', $fields['manufacture_year'], 'Manufacture year')
  ->required('engine_cc', $fields['engine_cc'], 'Engine capacity')->numeric('engine_cc', $fields['engine_cc'], 'Engine capacity')
  ->required('mileage', $fields['mileage'], 'Mileage')->numeric('mileage', $fields['mileage'], 'Mileage');
if ((float)$fields['price'] <= 0 && is_numeric($fields['price'])) {
    $v->inList('price', 'invalid', ['valid'], 'Price');
}
$year = (int)$fields['manufacture_year'];
if ($year && ($year < 1980 || $year > (int)date('Y') + 1)) {
    $errors = $v->errors();
    $errors['manufacture_year'] = 'Manufacture year looks invalid.';
}
if (!$v->passes() || isset($errors)) {
    Helpers::json(['ok' => false, 'errors' => array_merge($v->errors(), $errors ?? [])], 422);
}
$adModel = new AdModel();
$adId = $adModel->createWithVehicle((int)Auth::id(), [
    'location_id' => $fields['location_id'],
    'title' => $fields['title'],
    'description' => $fields['description'],
    'price' => $fields['price'],
    'is_negotiable' => Helpers::post('is_negotiable') === '1' ? 1 : 0,
], [
    'make' => $fields['make'],
    'model' => $fields['model'],
    'manufacture_year' => $year,
    'registration_year' => (int)($fields['registration_year'] ?: $year),
    'body_type' => Helpers::post('body_type'),
    'transmission' => in_array(Helpers::post('transmission'), ['manual', 'automatic'], true) ? Helpers::post('transmission') : 'automatic',
    'fuel_type' => in_array(Helpers::post('fuel_type'), ['petrol', 'diesel', 'hybrid', 'electric'], true) ? Helpers::post('fuel_type') : 'petrol',
    'engine_cc' => $fields['engine_cc'],
    'mileage' => $fields['mileage'],
    'colour' => Helpers::post('colour'),
    'condition' => 'Used',
    'number_of_owners' => (int)(Helpers::post('number_of_owners') ?: 1),
    'finance_status' => Helpers::post('finance_status') ?: 'Clear',
    'finance_company' => Helpers::post('finance_company'),
    'accident_history' => Helpers::post('accident_history') === '1' ? 1 : 0,
    'service_history' => Helpers::post('service_history') === '1' ? 1 : 0,
    'features' => Helpers::post('features'),
]);
$vehicle = $adModel->vehicleByAd($adId);
$primaryIndex = (int)Helpers::post('primary_index', '0');
if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
    $count = count($_FILES['images']['name']);
    $max = (int)Config::get('MAX_IMAGES_PER_AD');
    for ($i = 0; $i < min($count, $max); $i++) {
        $file = [
            'name' => $_FILES['images']['name'][$i],
            'type' => $_FILES['images']['type'][$i],
            'tmp_name' => $_FILES['images']['tmp_name'][$i],
            'error' => $_FILES['images']['error'][$i],
            'size' => $_FILES['images']['size'][$i],
        ];
        $imageId = $adModel->addImage((int)$vehicle['id'], '', $i === $primaryIndex, (int)$file['size'], '');
        $upload = FileUploader::saveImage($file, 'uploads/ads/' . $adId . '/vehicle_images', (string)$imageId);
        if ($upload['ok']) {
            $adModel->updateImagePath($imageId, $upload['path']);
        }
    }
}
Helpers::json(['ok' => true, 'ad_id' => $adId]);
