<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireLogin();
Auth::requireCsrf();
$adId = (int)Helpers::post('ad_id');
$reason = trim(Helpers::post('reason'));
$ad = (new AdModel())->findFull($adId);
if (!$ad) {
    Helpers::json(['ok' => false, 'error' => 'Listing not found.'], 404);
}
if (mb_strlen($reason) < 10) {
    Helpers::json(['ok' => false, 'error' => 'Please describe the issue in at least 10 characters.'], 422);
}
(new ReportModel())->add((int)Auth::id(), (int)$ad['seller_id'], $adId, 'ad', $reason);
Helpers::json(['ok' => true]);
