<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireLogin();
Auth::requireCsrf();
$adId = (int)Helpers::post('ad_id');
$comment = trim(Helpers::post('comment'));
$ad = (new AdModel())->findFull($adId);
if (!$ad) {
    Helpers::json(['ok' => false, 'error' => 'Listing not found.'], 404);
}
if (mb_strlen($comment) < 5 || mb_strlen($comment) > 1000) {
    Helpers::json(['ok' => false, 'error' => 'Feedback must be between 5 and 1000 characters.'], 422);
}
(new FeedbackModel())->add((int)Auth::id(), $adId, $comment);
Helpers::json(['ok' => true]);
