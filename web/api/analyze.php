<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireLogin();
Auth::requireCsrf();
$adId = (int)Helpers::post('ad_id');
$adModel = new AdModel();
$ad = $adModel->findFull($adId);
if (!$ad || ((int)$ad['seller_id'] !== (int)Auth::id() && !Auth::isAdmin())) {
    Helpers::json(['ok' => false, 'error' => 'Ad not found in your account.'], 404);
}
$vehicle = $adModel->vehicleByAd($adId);
if (!$vehicle) {
    Helpers::json(['ok' => false, 'error' => 'Vehicle details are missing for this ad.'], 422);
}
$result = AiClient::analyze($vehicle, (float)$ad['price']);
if (!$result['ok']) {
    Helpers::json(['ok' => false, 'error' => $result['error']], 502);
}
$data = $result['data'];
$verdict = $data['fair_price_status'] === 'fair' ? 'fair' : 'not_fair';
$summary = (string)($data['result'] ?? '');
$adModel->saveAnalysis($adId, (float)$ad['price'], $verdict, $summary);
$notif = new NotificationModel();
if ($verdict === 'fair') {
    $adModel->setStatus($adId, 'approved');
    $notif->push((int)$ad['seller_id'], 'Ad Approved', 'Your ad "' . $ad['title'] . '" passed the AI fair price check and is now live.', 'ad', $adId);
    $message = 'Your price sits inside the AI predicted fair range. ' . $summary;
} else {
    $adModel->setStatus($adId, 'rejected');
    $notif->push((int)$ad['seller_id'], 'Ad Rejected', 'Your ad "' . $ad['title'] . '" was rejected because the price is outside the fair market range. ' . $summary, 'ad', $adId);
    $message = 'Your asking price is outside the fair market range, so this ad was not published. ' . $summary . ' Adjust your price and resubmit for another instant review.';
}
Helpers::json(['ok' => true, 'verdict' => $verdict, 'message' => $message, 'detail' => $data]);
