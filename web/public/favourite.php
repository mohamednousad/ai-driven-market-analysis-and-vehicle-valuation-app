<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireRole('buyer');
csrfVerify();

$adId = (int)($_POST['ad_id'] ?? 0);
$repo = new FavouriteRepository($pdo);
$added = $repo->toggle((int)Auth::id(), $adId);
flash('success', $added ? 'Ad saved to your favourites.' : 'Ad removed from your favourites.');
redirect('/ad.php?id=' . $adId);
