<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireLogin();
Auth::requireCsrf();
$adId = (int)Helpers::post('ad_id');
if (!$adId) {
    Helpers::json(['ok' => false, 'error' => 'Missing ad reference.'], 422);
}
$favourited = (new FavouriteModel())->toggle((int)Auth::id(), $adId);
Helpers::json(['ok' => true, 'favourited' => $favourited]);
