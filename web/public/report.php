<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireLogin();
csrfVerify();

$adId = (int)($_POST['ad_id'] ?? 0);
$repo = new ReportRepository($pdo);
$repo->create((int)Auth::id(), $adId, $_POST['report_type'] ?? 'other', trim($_POST['reason'] ?? ''));
flash('success', 'Thanks. Our team will review this ad.');
redirect('/ad.php?id=' . $adId);
