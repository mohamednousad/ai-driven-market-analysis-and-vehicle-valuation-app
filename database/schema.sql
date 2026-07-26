SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS buyer_seller_ratings;
DROP TABLE IF EXISTS buyer_chat_messages;
DROP TABLE IF EXISTS buyer_chats;
DROP TABLE IF EXISTS buyer_favourites;
DROP TABLE IF EXISTS ad_promotions;
DROP TABLE IF EXISTS analysis;
DROP TABLE IF EXISTS vehicle_images;
DROP TABLE IF EXISTS vehicle_specifications;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS ads;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS user_login_attempts;
DROP TABLE IF EXISTS user_verifications;
DROP TABLE IF EXISTS user_registration;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    user_id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name           VARCHAR(120)  NOT NULL,
    email               VARCHAR(150)  NOT NULL,
    password_hash       VARCHAR(255)  NOT NULL,
    role                ENUM('admin','seller','buyer') NOT NULL DEFAULT 'buyer',
    poster_type         ENUM('non_member','member','authorized_agent') NOT NULL DEFAULT 'non_member',
    status              ENUM('pending','active','suspended','banned') NOT NULL DEFAULT 'pending',
    created_by          BIGINT UNSIGNED NULL,
    created_at          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email),
    CONSTRAINT fk_users_created_by FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    KEY idx_users_role (role),
    KEY idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_registration (
    profile_id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             BIGINT UNSIGNED NOT NULL,
    phone               VARCHAR(20)    NULL,
    date_of_birth       DATE           NULL,
    gender              ENUM('male','female','other') NULL,
    profile_image       VARCHAR(255)   NULL,
    bio                 VARCHAR(500)   NULL,
    address_line        VARCHAR(255)   NULL,
    city                VARCHAR(80)    NULL,
    district            VARCHAR(80)    NULL,
    province            VARCHAR(80)    NULL,
    email_verified_at   DATETIME       NULL,
    created_at          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_registration_user (user_id),
    CONSTRAINT fk_user_registration_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_verifications (
    verification_id  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          BIGINT UNSIGNED NOT NULL,
    purpose          ENUM('email_verification','password_reset','login_otp') NOT NULL,
    code_hash        VARCHAR(255)    NOT NULL,
    expires_at       DATETIME        NOT NULL,
    consumed_at      DATETIME        NULL,
    created_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_verifications_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    KEY idx_user_verifications_user_purpose (user_id, purpose)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_login_attempts (
    attempt_id       BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          BIGINT UNSIGNED NULL,
    email_attempted  VARCHAR(150)    NOT NULL,
    was_successful   TINYINT(1)      NOT NULL DEFAULT 0,
    failure_reason   VARCHAR(100)    NULL,
    attempted_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_login_attempts_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    KEY idx_user_login_attempts_user (user_id),
    KEY idx_user_login_attempts_email (email_attempted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE locations (
    location_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(80)  NOT NULL,
    type         ENUM('city','district','province') NOT NULL DEFAULT 'city',
    UNIQUE KEY uq_locations_name_type (name, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ads (
    ad_id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seller_id      BIGINT UNSIGNED NOT NULL,
    location_id    INT UNSIGNED    NOT NULL,
    title          VARCHAR(150)    NOT NULL,
    description    TEXT            NULL,
    price          DECIMAL(12,2)   NOT NULL,
    is_negotiable  TINYINT(1)      NOT NULL DEFAULT 0,
    status         ENUM('pending_review','approved','rejected','sold') NOT NULL DEFAULT 'pending_review',
    views_count    INT UNSIGNED    NOT NULL DEFAULT 0,
    published_at   DATETIME        NULL,
    expires_at     DATETIME        NULL,
    created_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ads_seller   FOREIGN KEY (seller_id)   REFERENCES users(user_id)         ON DELETE CASCADE,
    CONSTRAINT fk_ads_location FOREIGN KEY (location_id) REFERENCES locations(location_id) ON DELETE RESTRICT,
    CONSTRAINT chk_ads_price   CHECK (price >= 0),
    KEY idx_ads_seller       (seller_id),
    KEY idx_ads_location     (location_id),
    KEY idx_ads_status       (status),
    KEY idx_ads_price        (price),
    KEY idx_ads_status_price (status, price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE vehicles (
    vehicle_id       BIGINT UNSIGNED PRIMARY KEY,
    manufacture_year SMALLINT UNSIGNED NOT NULL,
    register_year    SMALLINT UNSIGNED NULL,
    chassis_no       VARCHAR(40)  NULL,
    engine_cc        SMALLINT UNSIGNED NULL,
    fuel_type        ENUM('petrol','diesel','hybrid','electric','other') NOT NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_vehicles_chassis (chassis_no),
    CONSTRAINT fk_vehicles_ad FOREIGN KEY (vehicle_id) REFERENCES ads(ad_id) ON DELETE CASCADE,
    CONSTRAINT chk_vehicles_year CHECK (manufacture_year BETWEEN 1900 AND 2100),
    CONSTRAINT chk_vehicles_reg_year CHECK (register_year IS NULL OR register_year >= manufacture_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE vehicle_specifications (
    vehicle_id    BIGINT UNSIGNED PRIMARY KEY,
    make          VARCHAR(60)  NOT NULL,
    model         VARCHAR(80)  NOT NULL,
    body_type     VARCHAR(40)  NULL,
    transmission  ENUM('manual','automatic','tiptronic','other') NOT NULL,
    mileage_km    INT UNSIGNED NULL,
    features      JSON         NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vehicle_specifications_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id) ON DELETE CASCADE,
    KEY idx_vehicle_specifications_make_model (make, model)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE vehicle_images (
    image_id    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vehicle_id  BIGINT UNSIGNED NOT NULL,
    file_path   VARCHAR(255)    NOT NULL,
    is_primary  TINYINT(1)      NOT NULL DEFAULT 0,
    sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vehicle_images_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id) ON DELETE CASCADE,
    KEY idx_vehicle_images_vehicle (vehicle_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE analysis (
    analysis_id       BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ad_id             BIGINT UNSIGNED NOT NULL,
    submitted_price   DECIMAL(12,2)   NOT NULL,
    predicted_price   DECIMAL(12,2)   NOT NULL,
    lower_bound       DECIMAL(12,2)   NULL,
    upper_bound       DECIMAL(12,2)   NULL,
    confidence_score  DECIMAL(5,4)    NULL,
    result            ENUM('fair','overpriced','underpriced') NOT NULL,
    analyzed_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_analysis_ad FOREIGN KEY (ad_id) REFERENCES ads(ad_id) ON DELETE CASCADE,
    CONSTRAINT chk_analysis_confidence CHECK (confidence_score IS NULL OR (confidence_score BETWEEN 0 AND 1)),
    KEY idx_analysis_ad (ad_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ad_promotions (
    promotion_id    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ad_id           BIGINT UNSIGNED NOT NULL,
    promotion_type  ENUM('featured','urgent','top_ad') NOT NULL,
    amount          DECIMAL(10,2)   NOT NULL,
    payment_status  ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    starts_at       DATETIME        NULL,
    ends_at         DATETIME        NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ad_promotions_ad FOREIGN KEY (ad_id) REFERENCES ads(ad_id) ON DELETE CASCADE,
    KEY idx_ad_promotions_ad (ad_id),
    KEY idx_ad_promotions_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE buyer_favourites (
    favourite_id  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    buyer_id      BIGINT UNSIGNED NOT NULL,
    ad_id         BIGINT UNSIGNED NOT NULL,
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_buyer_favourites_buyer_ad (buyer_id, ad_id),
    CONSTRAINT fk_buyer_favourites_buyer FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_buyer_favourites_ad    FOREIGN KEY (ad_id)    REFERENCES ads(ad_id)      ON DELETE CASCADE,
    KEY idx_buyer_favourites_ad (ad_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE buyer_chats (
    chat_id     BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ad_id       BIGINT UNSIGNED NOT NULL,
    buyer_id    BIGINT UNSIGNED NOT NULL,
    status      ENUM('open','responded','closed') NOT NULL DEFAULT 'open',
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_buyer_chats_ad_buyer (ad_id, buyer_id),
    CONSTRAINT fk_buyer_chats_ad    FOREIGN KEY (ad_id)    REFERENCES ads(ad_id)     ON DELETE CASCADE,
    CONSTRAINT fk_buyer_chats_buyer FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    KEY idx_buyer_chats_buyer (buyer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE buyer_chat_messages (
    message_id    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chat_id       BIGINT UNSIGNED NOT NULL,
    sender_id     BIGINT UNSIGNED NOT NULL,
    message_text  TEXT            NOT NULL,
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_buyer_chat_messages_chat   FOREIGN KEY (chat_id)   REFERENCES buyer_chats(chat_id) ON DELETE CASCADE,
    CONSTRAINT fk_buyer_chat_messages_sender FOREIGN KEY (sender_id) REFERENCES users(user_id)       ON DELETE CASCADE,
    KEY idx_buyer_chat_messages_chat (chat_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE buyer_seller_ratings (
    rating_id   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ad_id       BIGINT UNSIGNED NOT NULL,
    seller_id   BIGINT UNSIGNED NOT NULL,
    buyer_id    BIGINT UNSIGNED NOT NULL,
    rating      TINYINT UNSIGNED NOT NULL,
    comment     VARCHAR(500)    NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_buyer_seller_ratings_ad_buyer (ad_id, buyer_id),
    CONSTRAINT fk_buyer_seller_ratings_ad     FOREIGN KEY (ad_id)     REFERENCES ads(ad_id)     ON DELETE CASCADE,
    CONSTRAINT fk_buyer_seller_ratings_seller FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_buyer_seller_ratings_buyer  FOREIGN KEY (buyer_id)  REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT chk_buyer_seller_ratings_range CHECK (rating BETWEEN 1 AND 5),
    KEY idx_buyer_seller_ratings_seller (seller_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reports (
    report_id    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reporter_id  BIGINT UNSIGNED NOT NULL,
    ad_id        BIGINT UNSIGNED NOT NULL,
    report_type  ENUM('spam','fraud','misleading_price','incorrect_info','offensive','other') NOT NULL,
    reason       VARCHAR(500)    NULL,
    status       ENUM('pending','reviewed','action_taken','dismissed') NOT NULL DEFAULT 'pending',
    reviewed_by  BIGINT UNSIGNED NULL,
    reviewed_at  DATETIME        NULL,
    created_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reports_reporter FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_reports_ad       FOREIGN KEY (ad_id)       REFERENCES ads(ad_id)     ON DELETE CASCADE,
    CONSTRAINT fk_reports_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL,
    KEY idx_reports_status (status),
    KEY idx_reports_ad     (ad_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
    notification_id  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          BIGINT UNSIGNED NOT NULL,
    ad_id            BIGINT UNSIGNED NULL,
    title            VARCHAR(150)    NOT NULL,
    message          VARCHAR(500)    NULL,
    type             VARCHAR(40)     NOT NULL,
    is_read          TINYINT(1)      NOT NULL DEFAULT 0,
    created_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_notifications_ad   FOREIGN KEY (ad_id)   REFERENCES ads(ad_id)      ON DELETE SET NULL,
    KEY idx_notifications_user_read (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO locations (name, type) VALUES
('Ampara','district'),
('Anuradhapura','district'),
('Badulla','district'),
('Batticaloa','district'),
('Colombo','district'),
('Galle','district'),
('Gampaha','district'),
('Hambantota','district'),
('Jaffna','district'),
('Kalutara','district'),
('Kandy','district'),
('Kegalle','district'),
('Kilinochchi','district'),
('Kurunegala','district'),
('Mannar','district'),
('Matale','district'),
('Matara','district'),
('Monaragala','district'),
('Mullaitivu','district'),
('Nuwara Eliya','district'),
('Polonnaruwa','district'),
('Puttalam','district'),
('Ratnapura','district'),
('Trincomalee','district'),
('Vavuniya','district');
