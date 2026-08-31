<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireLogin();
Auth::requireCsrf();
$userId = (int)Auth::id();
$adId = (int)Helpers::post('ad_id');
$rating = (int)Helpers::post('rating');
$comment = trim(Helpers::post('comment'));
$ad = (new AdModel())->findFull($adId);
if (!$ad) {
    Helpers::json(['ok' => false, 'error' => 'Listing not found.'], 404);
}
if ((int)$ad['seller_id'] === $userId) {
    Helpers::json(['ok' => false, 'error' => 'You cannot rate yourself.'], 422);
}
if ($rating < 1 || $rating > 5) {
    Helpers::json(['ok' => false, 'error' => 'Rating must be between 1 and 5 stars.'], 422);
}
$ratingModel = new RatingModel();
if (!$ratingModel->add((int)$ad['seller_id'], $userId, $adId, $rating, $comment)) {
    Helpers::json(['ok' => false, 'error' => 'You have already rated this seller for this ad.'], 422);
}
(new SellerProfileModel())->refreshAvgRating((int)$ad['seller_id']);
(new NotificationModel())->push((int)$ad['seller_id'], 'New Rating', 'A buyer rated you ' . $rating . ' stars on "' . $ad['title'] . '".', 'system', $adId);
Helpers::json(['ok' => true]);
