<?php
define('APP_NAME', 'AutoValue');
define('APP_TAGLINE', 'AI-Driven Vehicle Marketplace');

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'Database operationsautovalue3');
define('DB_USER', 'root');
define('DB_PASS', '');

define('AI_API_BASE_URL', 'http://127.0.0.1:5000');
define('GEMINI_API_KEY', '');
define('GEMINI_MODEL', 'gemini-1.5-flash');

define('CURRENCY_SYMBOL', 'Rs');
define('ADS_PER_PAGE', 10);
define('AD_LIFETIME_DAYS', 30);
define('MAX_IMAGES_PER_AD', 6);
define('MAX_IMAGE_BYTES', 5 * 1024 * 1024);
define('MAX_FAILED_LOGINS', 5);
define('LOCK_MINUTES', 15);

define('PROMO_PRICES', serialize([
    'top_ad'   => 350.00,
    'featured' => 750.00,
    'urgent'   => 500.00,
]));

define('UPLOAD_DIR', dirname(__DIR__) . '/public/uploads');
define('UPLOAD_URL', 'uploads');
