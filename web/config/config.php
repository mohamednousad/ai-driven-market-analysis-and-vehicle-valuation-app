<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'AutoValue AI');
define('APP_TAGLINE', 'AI-Driven Vehicle Marketplace');

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'autovalue_ai');
define('DB_USER', 'root');
define('DB_PASS', '');

define('AI_API_BASE_URL', 'http://127.0.0.1:5000');

define('ROLE_ADMIN', 'admin');
define('ROLE_SELLER', 'seller');
define('ROLE_BUYER', 'buyer');

define('AD_STATUS_PENDING', 'pending');
define('AD_STATUS_APPROVED', 'approved');
define('AD_STATUS_REJECTED', 'rejected');

define('UPLOAD_DIR', __DIR__ . '/../storage/uploads');
define('UPLOAD_URL', 'storage/uploads');

define('DEFAULT_SETTINGS', serialize([
    'fairness_band_percent' => 15,
    'currency' => 'LKR',
    'currency_symbol' => 'Rs',
    'gemini_api_key' => '',
    'gemini_model' => 'gemini-1.5-flash',
    'site_contact_phone' => '+94 11 234 5678',
    'site_contact_email' => 'support@autovalue.lk',
    'allow_registration' => 1,
    'ads_per_page' => 9,
]));
