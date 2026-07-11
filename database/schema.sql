CREATE DATABASE IF NOT EXISTS autovalue_ai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE autovalue_ai;

DROP TABLE IF EXISTS seller_ratings;
DROP TABLE IF EXISTS chat_messages;
DROP TABLE IF EXISTS vehicle_ads;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'seller', 'buyer') NOT NULL DEFAULT 'buyer',
    phone VARCHAR(40) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vehicle_ads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    title VARCHAR(160) NOT NULL,
    brand VARCHAR(60) NOT NULL,
    vehicle_model VARCHAR(80) NOT NULL,
    model_year INT NOT NULL,
    mileage INT NOT NULL,
    engine_capacity INT NOT NULL,
    fuel_type VARCHAR(30) NOT NULL,
    transmission VARCHAR(30) NOT NULL,
    condition_grade VARCHAR(30) NOT NULL,
    asking_price DECIMAL(14,2) NOT NULL,
    predicted_price DECIMAL(14,2) DEFAULT NULL,
    lower_bound DECIMAL(14,2) DEFAULT NULL,
    upper_bound DECIMAL(14,2) DEFAULT NULL,
    description TEXT,
    location VARCHAR(120) DEFAULT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_brand (brand),
    INDEX idx_price (asking_price),
    INDEX idx_year (model_year)
);

CREATE TABLE seller_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    buyer_id INT NOT NULL,
    ad_id INT DEFAULT NULL,
    rating TINYINT NOT NULL,
    comment VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_buyer_seller (buyer_id, seller_id),
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role ENUM('user', 'assistant') NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE settings (
    setting_key VARCHAR(80) PRIMARY KEY,
    setting_value TEXT
);

-- Seed accounts. All seeded passwords are: password123
-- (bcrypt hash accepted by PHP password_verify)
INSERT INTO users (full_name, email, password_hash, role, phone) VALUES
('System Admin', 'admin@autovalue.lk', '$2b$10$6dU8YmBkrp0n0KjMpeJ5ZO5K93IWl6NZ7EdpHPA5Ddkw8NFRm6j2G', 'admin', '+94 11 234 5678'),
('Kasun Perera', 'seller@autovalue.lk', '$2b$10$6dU8YmBkrp0n0KjMpeJ5ZO5K93IWl6NZ7EdpHPA5Ddkw8NFRm6j2G', 'seller', '+94 77 111 2233'),
('Nimal Silva', 'seller2@autovalue.lk', '$2b$10$6dU8YmBkrp0n0KjMpeJ5ZO5K93IWl6NZ7EdpHPA5Ddkw8NFRm6j2G', 'seller', '+94 76 445 6677'),
('Amaya Fernando', 'buyer@autovalue.lk', '$2b$10$6dU8YmBkrp0n0KjMpeJ5ZO5K93IWl6NZ7EdpHPA5Ddkw8NFRm6j2G', 'buyer', '+94 71 998 8776');

INSERT INTO vehicle_ads
    (seller_id, title, brand, vehicle_model, model_year, mileage, engine_capacity, fuel_type, transmission, condition_grade, asking_price, predicted_price, lower_bound, upper_bound, description, location, status)
VALUES
    (2, 'Toyota Aqua 2018 Hybrid', 'Toyota', 'Aqua', 2018, 62000, 1500, 'Hybrid', 'Automatic', 'Excellent', 9500000, 9748000, 8285800, 11210200, 'Well maintained hybrid, single owner, full service history.', 'Colombo', 'approved'),
    (3, 'Nissan Leaf 2016 Electric', 'Nissan', 'Leaf', 2016, 78000, 1000, 'Electric', 'Automatic', 'Good', 7200000, 7500000, 6375000, 8625000, 'City-friendly EV, new battery pack, accident free.', 'Kandy', 'approved'),
    (2, 'Suzuki Wagon R 2015', 'Suzuki', 'Wagon R', 2015, 95000, 1000, 'Petrol', 'Automatic', 'Good', 4200000, 4300000, 3655000, 4945000, 'Economical daily runner, low fuel cost.', 'Gampaha', 'approved'),
    (3, 'Honda Vezel 2017', 'Honda', 'Vezel', 2017, 71000, 1500, 'Hybrid', 'Automatic', 'Excellent', 12800000, 11200000, 9520000, 12880000, 'Top spec, sunroof, leather interior.', 'Colombo', 'pending');
