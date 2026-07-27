DROP DATABASE IF EXISTS autovalue;
CREATE DATABASE autovalue;
USE autovalue;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  profile_image VARCHAR(255),
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  status ENUM('active','inactive','suspended') DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE seller_profile (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  seller_type ENUM('non_member','member','authorized') DEFAULT 'non_member',
  phone_number VARCHAR(20),
  date_of_birth DATE,
  avg_rating DECIMAL(2,1) DEFAULT 0.0,
  response_rate DECIMAL(5,2) DEFAULT 0.00
  gender ENUM('male','female','other'),
  profile_image VARCHAR(255),
  bio TEXT,
  address VARCHAR(255),
  city VARCHAR(100),
  district VARCHAR(100),
  province VARCHAR(100),
  postal_code VARCHAR(20),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE authorized_seller_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  business_name VARCHAR(150),
  business_description TEXT,
  business_document VARCHAR(255),
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  rejection_reason TEXT,
  reviewed_by INT,
  reviewed_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE user_verifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  method ENUM('email','phone'),
  type ENUM('registration_verification','password_reset'),
  otp_code VARCHAR(10),
  is_verified BOOLEAN DEFAULT FALSE,
  verified_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE login_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  ip_address VARCHAR(45),
  status ENUM('success','failed'),
  attempted_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE password_reset_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  reset_token VARCHAR(255),
  request_sent_at DATETIME,
  status ENUM('requested','completed','expired'),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE locations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  district VARCHAR(100),
  city VARCHAR(100),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE subscription_plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  description TEXT,
  price DECIMAL(10,2),
  duration INT,
  features TEXT,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  plan_id INT NOT NULL,
  start_date DATE,
  end_date DATE,
  status ENUM('active','expired','cancelled') DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(plan_id) REFERENCES subscription_plans(id) ON DELETE RESTRICT
);

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  subscription_id INT NULL,
  category ENUM('subscription','promotion') NOT NULL,
  amount DECIMAL(10,2),
  method VARCHAR(50),
  transaction_id VARCHAR(255),
  status ENUM('pending','completed','failed') DEFAULT 'pending',
  paid_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL
);

CREATE TABLE promotion_usage (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subscription_id INT NOT NULL,
  total_limit INT DEFAULT 0,
  used_count INT DEFAULT 0,
  remaining_count INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY(subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE
);

CREATE TABLE ads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  seller_id INT NOT NULL,
  location_id INT NOT NULL,
  title VARCHAR(150),
  description TEXT,
  price DECIMAL(12,2),
  is_negotiable BOOLEAN DEFAULT FALSE,
  status ENUM('pending','approved','rejected','sold') DEFAULT 'pending',
  published_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY(seller_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(location_id) REFERENCES locations(id) ON DELETE RESTRICT
);

CREATE TABLE vehicle_info (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ad_id INT NOT NULL,
  make VARCHAR(100),
  model VARCHAR(100),
  manufacture_year YEAR,
  registration_year YEAR,
  body_type VARCHAR(50),
  transmission ENUM('manual','automatic'),
  fuel_type ENUM('petrol','diesel','hybrid','electric'),
  engine_cc INT,
  mileage INT,
  colour VARCHAR(50),
  `condition` VARCHAR(50),
  number_of_owners INT,
  finance_status VARCHAR(50),
  finance_company VARCHAR(100),
  accident_history BOOLEAN DEFAULT FALSE,
  service_history BOOLEAN DEFAULT FALSE,
  features TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY(ad_id) REFERENCES ads(id) ON DELETE CASCADE
);

CREATE TABLE vehicle_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  image_path VARCHAR(255),
  is_primary BOOLEAN DEFAULT FALSE,
  file_size INT,
  file_format VARCHAR(20),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(vehicle_id) REFERENCES vehicle_info(id) ON DELETE CASCADE
);

CREATE TABLE ad_system_analysis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ad_id INT NOT NULL,
  submitted_price DECIMAL(12,2),
  fair_price_status ENUM('fair','not_fair'),
  result TEXT,
  analyzed_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(ad_id) REFERENCES ads(id) ON DELETE CASCADE
);

CREATE TABLE ad_promotions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ad_id INT NOT NULL,
  payment_id INT NULL,
  type ENUM('featured','top_listing','highlight'),
  amount DECIMAL(10,2),
  starts_at DATETIME,
  ends_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(ad_id) REFERENCES ads(id) ON DELETE CASCADE,
  FOREIGN KEY(payment_id) REFERENCES payments(id) ON DELETE SET NULL
);

CREATE TABLE buyer_favourites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  buyer_id INT NOT NULL,
  ad_id INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(buyer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(ad_id) REFERENCES ads(id) ON DELETE CASCADE
);

CREATE TABLE chats (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ad_id INT NOT NULL,
  buyer_id INT NOT NULL,
  seller_id INT NOT NULL,
  status ENUM('active','closed') DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY(ad_id) REFERENCES ads(id) ON DELETE CASCADE,
  FOREIGN KEY(buyer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(seller_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  chat_id INT NOT NULL,
  sender_id INT,
  receiver_id INT,
  message TEXT,
  is_read BOOLEAN DEFAULT FALSE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(chat_id) REFERENCES chats(id) ON DELETE CASCADE,
  FOREIGN KEY(sender_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY(receiver_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE seller_ratings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  seller_id INT NOT NULL,
  buyer_id INT NOT NULL,
  ad_id INT NOT NULL,
  rating INT,
  comment TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(seller_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(buyer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(ad_id) REFERENCES ads(id) ON DELETE CASCADE
);

CREATE TABLE ad_feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  buyer_id INT NOT NULL,
  ad_id INT NOT NULL,
  comment TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(buyer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(ad_id) REFERENCES ads(id) ON DELETE CASCADE
);

CREATE TABLE reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reporter_id INT NOT NULL,
  reported_user_id INT NOT NULL,
  ad_id INT NULL,
  type ENUM('user','ad','other'),
  reason TEXT,
  status ENUM('pending','reviewed','resolved') DEFAULT 'pending',
  reviewed_by INT,
  reviewed_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(reporter_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(reported_user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(ad_id) REFERENCES ads(id) ON DELETE SET NULL,
  FOREIGN KEY(reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  ad_id INT NULL,
  title VARCHAR(150),
  message TEXT,
  type ENUM('system','ad','payment','message'),
  is_read BOOLEAN DEFAULT FALSE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(ad_id) REFERENCES ads(id) ON DELETE SET NULL
);

CREATE TABLE settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) UNIQUE,
  `value` TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


-- ============================================================
-- ============================================================
--                     INSERT SAMPLE DATA
-- ============================================================
-- ============================================================

-- ---------- 1. USERS ----------
INSERT INTO users (username, email, password, profile_image, role, status) VALUES
('admin_user',  'admin@carmarket.com',  'hashed_pw_admin', 'uploads/users/1/profile/profile_image.jpg', 'admin', 'active'),
('nabeel_s',    'nabeel@example.com',   'hashed_pw_001',   'uploads/users/2/profile/profile_image.jpg', 'user',  'active'),
('kavindu_p',   'kavindu@example.com',  'hashed_pw_002',   'uploads/users/3/profile/profile_image.jpg', 'user',  'active'),
('rihan_ahmed', 'rihan@example.com',    'hashed_pw_003',   'uploads/users/4/profile/profile_image.jpg', 'user',  'active'),
('sarah_j',     'sarah@example.com',    'hashed_pw_004',   'uploads/users/5/profile/profile_image.jpg', 'user',  'active'),
('michael_t',   'michael@example.com',  'hashed_pw_005',   'uploads/users/6/profile/profile_image.jpg', 'user',  'active'),
('jessica_l',   'jessica@example.com',  'hashed_pw_006',   'uploads/users/7/profile/profile_image.jpg', 'user',  'active');

-- ---------- SELLER REGISTRATION (insert) ----------
INSERT INTO seller_profile
(user_id, seller_type, phone_number, date_of_birth, gender, profile_image, bio, address, city, district, province, postal_code) VALUES
(2, 'authorized', '+94771234567', '1995-04-12', 'male', 'uploads/sellers/2/profile/profile_image.jpg', 'Trusted car seller in Colombo.',    '12 Galle Rd',  'Colombo', 'Colombo', 'Western',  '00300'),
(3, 'member',     '+94772345678', '1990-08-22', 'male', 'uploads/sellers/3/profile/profile_image.jpg', 'Selling well-maintained vehicles.', '45 Kandy Rd',  'Kandy',   'Kandy',   'Central',  '20000'),
(4, 'non_member', '+94773456789', '1988-11-05', 'male', 'uploads/sellers/4/profile/profile_image.jpg', 'First time seller.',                '78 Matara Rd', 'Galle',   'Galle',   'Southern', '80000');

-- ---------- 4. AUTHORIZED SELLER REQUESTS ----------
INSERT INTO authorized_seller_requests
(user_id, business_name, business_description, business_document, status, rejection_reason, reviewed_by, reviewed_at) VALUES
(2, 'Nabeel Auto Traders', 'Registered dealer since 2020.', 'uploads/sellers/2/verification/1/business_document.pdf', 'approved', NULL, 1, '2026-05-20 12:00:00'),
(3, 'Kavindu Motors',      'Family-run vehicle sales.',    'uploads/sellers/3/verification/2/business_document.pdf', 'pending',  NULL, NULL, NULL);

-- ---------- 5. USER VERIFICATIONS ----------
INSERT INTO user_verifications (user_id, method, type, otp_code, is_verified, verified_at) VALUES
(2, 'email', 'registration_verification', '482913', TRUE, '2026-01-10 10:15:00'),
(3, 'phone', 'registration_verification', '739201', TRUE, '2026-02-05 09:30:00'),
(5, 'email', 'registration_verification', '105832', TRUE, '2026-03-01 14:00:00');

-- ---------- 6. LOGIN ACTIVITIES ----------
INSERT INTO login_logs (user_id, ip_address, status, attempted_at) VALUES
(1, '192.168.1.10', 'success', '2026-07-20 08:00:00'),
(2, '192.168.1.22', 'success', '2026-07-21 09:15:00'),
(3, '192.168.1.35', 'failed',  '2026-07-22 10:20:00'),
(3, '192.168.1.35', 'success', '2026-07-22 10:22:00'),
(5, '192.168.1.50', 'success', '2026-07-25 16:45:00');

-- ---------- 7. PASSWORD RESET LOGS ----------
INSERT INTO password_reset_logs (user_id, reset_token, request_sent_at, status) VALUES
(3, 'tok_9f8e7d6c5b4a3', '2026-07-15 11:00:00', 'completed'),
(5, 'tok_1a2b3c4d5e6f7', '2026-07-24 13:30:00', 'requested');

-- ---------- 8. LOCATIONS ----------
INSERT INTO locations (district, city) VALUES
('Colombo', 'Colombo 03'),
('Kandy',   'Peradeniya'),
('Galle',   'Galle Fort'),
('Gampaha', 'Negombo'),
('Matara',  'Matara');

-- ---------- 9. SUBSCRIPTION PLANS ----------
INSERT INTO subscription_plans (name, description, price, duration, features, status) VALUES
('Basic Plan',   'Analytics dashboard with limited features. No member badge.',                     1500.00, 30, 'Basic analytics, Views/Counts dashboard', 'active'),
('Member Plan',  'Grants Member badge. Advanced analytics + limited free ad promotions.',           3500.00, 30, 'Member badge, Advanced analytics, 3 free promotions', 'active'),
('Premium Plan', 'Top tier. Member badge. Deep advanced analytics + higher free ad promotions.',    6000.00, 30, 'Member badge, Deep analytics, 8 free promotions', 'active');

-- ---------- 10. SUBSCRIPTIONS ----------
INSERT INTO subscriptions (user_id, plan_id, start_date, end_date, status) VALUES
(2, 3, '2026-07-01', '2026-07-31', 'active'),
(3, 2, '2026-07-05', '2026-08-04', 'active'),
(4, 1, '2026-06-01', '2026-06-30', 'expired');

-- ---------- 11. PAYMENTS ----------
INSERT INTO payments (user_id, subscription_id, category, amount, method, transaction_id, status, paid_at) VALUES
(2, 1,    'subscription', 6000.00, 'Card', 'TXN_SUB_00001', 'completed', '2026-07-01 09:00:00'),
(3, 2,    'subscription', 3500.00, 'Card', 'TXN_SUB_00002', 'completed', '2026-07-05 10:30:00'),
(4, 3,    'subscription', 1500.00, 'Bank', 'TXN_SUB_00003', 'completed', '2026-06-01 08:15:00'),
(2, NULL, 'promotion',    2000.00, 'Card', 'TXN_PRO_00001', 'completed', '2026-07-16 09:05:00'),
(3, NULL, 'promotion',    3500.00, 'Card', 'TXN_PRO_00002', 'completed', '2026-07-19 10:15:00');

-- ---------- 12. PROMOTION USAGE ----------
INSERT INTO promotion_usage (subscription_id, total_limit, used_count, remaining_count) VALUES
(1, 8, 1, 7),
(2, 3, 1, 2),
(3, 0, 0, 0);

-- ---------- 13. ADS ----------
INSERT INTO ads (seller_id, location_id, title, description, price, is_negotiable, status, published_at) VALUES
(2, 1, 'Toyota Aqua 2018 - Excellent Condition', 'Well maintained, single owner hybrid car.', 5850000.00, TRUE,  'approved', '2026-07-15 10:00:00'),
(3, 2, 'Honda Vezel 2017 Hybrid',                'Full option, low mileage, tip-top shape.',  8200000.00, TRUE,  'approved', '2026-07-18 11:30:00'),
(4, 3, 'Suzuki Wagon R 2019',                    'Fuel efficient city car.',                  3900000.00, FALSE, 'pending',  NULL);

-- ---------- 14. VEHICLE INFO ----------
INSERT INTO vehicle_info
(ad_id, make, model, manufacture_year, registration_year, body_type, transmission, fuel_type, engine_cc, mileage, colour, `condition`, number_of_owners, finance_status, finance_company, accident_history, service_history, features) VALUES
(1, 'Toyota', 'Aqua',    2018, 2019, 'Hatchback', 'automatic', 'hybrid', 1500, 65000, 'White', 'Used', 1, 'Clear',         NULL,          FALSE, TRUE,  'ABS, Airbags, Alloy Wheels, Push Start'),
(2, 'Honda',  'Vezel',   2017, 2018, 'SUV',       'automatic', 'hybrid', 1500, 78000, 'Black', 'Used', 1, 'Under Finance', 'LB Finance',  FALSE, TRUE,  'Sunroof, Leather Seats, Cruise Control'),
(3, 'Suzuki', 'Wagon R', 2019, 2019, 'Hatchback', 'automatic', 'petrol', 660,  42000, 'Silver','Used', 1, 'Clear',         NULL,          FALSE, FALSE, 'Power Windows, AC, Airbags');

-- ---------- 15. VEHICLE IMAGES ----------
INSERT INTO vehicle_images (vehicle_id, image_path, is_primary, file_size, file_format) VALUES
(1, 'uploads/ads/1/vehicle_images/1.jpg', TRUE,  245000, 'jpg'),
(1, 'uploads/ads/1/vehicle_images/2.jpg', FALSE, 231000, 'jpg'),
(2, 'uploads/ads/2/vehicle_images/3.jpg', TRUE,  305000, 'jpg'),
(2, 'uploads/ads/2/vehicle_images/4.jpg', FALSE, 288000, 'jpg'),
(3, 'uploads/ads/3/vehicle_images/5.jpg', TRUE,  198000, 'jpg');;

-- ---------- 16. AD SYSTEM ANALYSIS ----------
INSERT INTO ad_system_analysis (ad_id, submitted_price, fair_price_status, result, analyzed_at) VALUES
(1, 5850000.00, 'fair',     'Predicted range: 5.7M - 6.0M. Within fair range.', '2026-07-15 10:05:00'),
(2, 8200000.00, 'fair',     'Predicted range: 8.0M - 8.5M. Within fair range.', '2026-07-18 11:35:00'),
(3, 3900000.00, 'not_fair', 'Predicted range: 3.2M - 3.6M. Price above range.', '2026-07-20 09:10:00');

-- ---------- 17. AD PROMOTIONS ----------
INSERT INTO ad_promotions (ad_id, payment_id, type, amount, starts_at, ends_at) VALUES
(1, 4, 'featured',    2000.00, '2026-07-16 00:00:00', '2026-07-23 23:59:59'),
(2, 5, 'top_listing', 3500.00, '2026-07-19 00:00:00', '2026-07-26 23:59:59');

-- ---------- 18. BUYER FAVOURITES ----------
INSERT INTO buyer_favourites (buyer_id, ad_id) VALUES
(5, 1),
(5, 2),
(6, 1),
(7, 2);

-- ---------- 19. CHATS ----------
INSERT INTO chats (ad_id, buyer_id, seller_id, status) VALUES
(1, 5, 2, 'active'),
(2, 6, 3, 'active'),
(1, 7, 2, 'closed');

-- ---------- 20. MESSAGES ----------
INSERT INTO messages (chat_id, sender_id, receiver_id, message, is_read) VALUES
(1, 5, 2, 'Hi, is the Aqua still available?',                 TRUE),
(1, 2, 5, 'Yes, it is available. Would you like to view it?', TRUE),
(1, 5, 2, 'Yes please, this weekend?',                        FALSE),
(2, 6, 3, 'Is the Vezel price negotiable?',                   TRUE),
(2, 3, 6, 'Slight negotiation possible after inspection.',    FALSE);

-- ---------- 21. SELLER RATINGS ----------
INSERT INTO seller_ratings (seller_id, buyer_id, ad_id, rating, comment) VALUES
(2, 5, 1, 5, 'Very responsive and honest seller.'),
(2, 7, 1, 4, 'Good communication overall.'),
(3, 6, 2, 5, 'Smooth transaction, highly recommended.');

-- ---------- 22. AD FEEDBACK ----------
INSERT INTO ad_feedback (buyer_id, ad_id, comment) VALUES
(5, 1, 'Photos matched the actual condition.'),
(6, 2, 'Description was accurate and detailed.');

-- ---------- 23. REPORTS ----------
INSERT INTO reports (reporter_id, reported_user_id, ad_id, type, reason, status, reviewed_by, reviewed_at) VALUES
(6, 4, 3, 'ad', 'Vehicle priced far above market range.',        'pending',  NULL, NULL),
(7, 4, 3, 'ad', 'Same vehicle posted elsewhere at lower price.', 'reviewed', 1,    '2026-07-25 12:00:00');

-- ---------- 24. NOTIFICATIONS ----------
INSERT INTO notifications (user_id, ad_id, title, message, type, is_read) VALUES
(2, 1, 'Ad Approved',      'Your Toyota Aqua ad has been approved.',       'ad',      TRUE),
(3, 2, 'Promotion Active', 'Your Vezel Top Listing promotion is live.',    'payment', FALSE),
(4, 3, 'Ad Under Review',  'Your Wagon R ad is pending review.',           'ad',      FALSE),
(5, 1, 'New Message',      'You have a new reply from the seller.',        'message', FALSE);

-- ---------- 25. SETTINGS ----------
INSERT INTO settings (`key`, `value`) VALUES
('site_name',           'Car Marketplace'),
('support_email',       'support@carmarket.com'),
('max_images_per_ad',   '10'),
('ai_service_endpoint', 'http://localhost:5000/predict'),
('default_currency',    'LKR');
