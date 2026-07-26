<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireRole('buyer');
csrfVerify();

$adId = (int)($_POST['ad_id'] ?? 0);
$adRepo = new AdRepository($pdo);
$sellerId = $adRepo->ownerOf($adId);
if ($sellerId === null) {
    flash('error', 'Ad not found.');
    redirect('/index.php');
}
$ok = (new RatingRepository($pdo))->rate($adId, $sellerId, (int)Auth::id(), (int)($_POST['rating'] ?? 0), trim($_POST['comment'] ?? ''));
flash($ok ? 'success' : 'error', $ok ? 'Your rating has been saved.' : 'Invalid rating.');
redirect('/ad.php?id=' . $adId);
