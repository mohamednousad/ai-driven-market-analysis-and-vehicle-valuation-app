-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 06, 2026 at 07:55 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `autovalue`
--

-- --------------------------------------------------------

--
-- Table structure for table `ads`
--

CREATE TABLE `ads` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `is_negotiable` tinyint(1) DEFAULT 0,
  `status` enum('pending','approved','rejected','sold') DEFAULT 'pending',
  `views` int(11) DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ads`
--

INSERT INTO `ads` (`id`, `seller_id`, `location_id`, `title`, `description`, `price`, `is_negotiable`, `status`, `views`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Toyota Aqua 2018 - Excellent Condition', 'Well maintained, single owner hybrid car. Garaged and serviced on schedule at an authorized center. Fresh interior detailing, alloy wheels, push-start ignition, and factory ABS with airbags. Genuine reason for sale.', 5850000.00, 1, 'approved', 855, '2026-07-15 10:00:00', '2026-07-27 12:55:29', '2026-07-29 17:59:23'),
(2, 3, 2, 'Honda Vezel 2017 Hybrid', 'Full option, low mileage, tip-top shape. Sunroof, leather interior and cruise control. Ongoing lease can be settled or transferred.', 8200000.00, 1, 'approved', 618, '2026-07-18 11:30:00', '2026-07-27 12:55:29', '2026-09-06 09:45:46'),
(3, 4, 3, 'Suzuki Wagon R 2019', 'Fuel efficient city car with power windows, AC and airbags. Ideal first car.', 3900000.00, 0, 'rejected', 203, NULL, '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(4, 2, 4, 'Nissan Leaf 2020 - Full Electric', 'Zero fuel cost daily driver. Battery health excellent, home charger included in the sale.', 7400000.00, 1, 'approved', 1206, '2026-07-10 09:00:00', '2026-07-27 12:55:29', '2026-08-30 07:08:48'),
(5, 3, 1, 'Kia Sportage 2016 - Family SUV', 'Diesel family SUV with strong service record. New tyres fitted this year.', 6750000.00, 1, 'approved', 366, '2026-07-20 14:00:00', '2026-07-27 12:55:29', '2026-08-29 13:50:16'),
(6, 2, 5, 'Mitsubishi Montero 2015 - 4WD', 'Legendary 4WD in excellent mechanical condition. Highway use mostly.', 9200000.00, 0, 'approved', 542, '2026-07-21 08:30:00', '2026-07-27 12:55:29', '2026-07-27 14:09:13'),
(7, 8, 6, 'asdasdasdasdsa', 'sadsadassadsad dasdasd', 3600000.00, 1, 'approved', 7, '2026-08-13 10:05:53', '2026-07-27 13:29:43', '2026-09-06 09:45:03'),
(8, 2, 1, 'Toyota Raize for Sale', 'good condition | first owner | no issues right now.\r\ncontact me for more details', 7850001.00, 1, 'rejected', 0, NULL, '2026-08-16 11:54:34', '2026-08-16 11:54:34'),
(9, 2, 1, 'Toyota Raize for Sale', 'good condition | first owner | no issues right now.\r\ncontact me for more details', 5849995.00, 1, 'approved', 6, '2026-08-16 11:54:46', '2026-08-16 11:54:46', '2026-08-31 16:56:46'),
(10, 2, 1, 'Toyota Raize for Sale', 'good condition | first owner | no issues right now.\r\ncontact me for more details', 7849990.00, 1, 'rejected', 0, NULL, '2026-08-16 11:55:48', '2026-08-16 11:55:48'),
(11, 9, 1, 'Toyota Sale now', 'Good nice paper are fine now', 760000.00, 0, 'rejected', 0, NULL, '2026-09-06 09:47:55', '2026-09-06 09:47:55'),
(12, 9, 1, 'Toyota Sale now', 'Good nice paper are fine now', 510000.00, 0, 'rejected', 0, NULL, '2026-09-06 09:48:06', '2026-09-06 09:48:06'),
(13, 9, 1, 'Toyota Sale now', 'Good nice paper are fine now', 640000.00, 0, 'rejected', 0, NULL, '2026-09-06 09:48:15', '2026-09-06 09:48:15'),
(14, 9, 1, 'Toyota Sale now', 'Good nice paper are fine now', 670000.00, 0, 'rejected', 0, NULL, '2026-09-06 09:48:22', '2026-09-06 09:48:22'),
(15, 9, 1, 'Toyota Sale now', 'Good nice paper are fine now', 6200000.00, 0, 'rejected', 0, NULL, '2026-09-06 09:48:38', '2026-09-06 09:48:38'),
(16, 9, 1, 'Toyota Sale now', 'Good nice paper are fine now', 6000000.00, 0, 'approved', 6, '2026-09-06 09:49:11', '2026-09-06 09:49:11', '2026-09-06 09:50:42');

-- --------------------------------------------------------

--
-- Table structure for table `ad_feedback`
--

CREATE TABLE `ad_feedback` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ad_feedback`
--

INSERT INTO `ad_feedback` (`id`, `buyer_id`, `ad_id`, `comment`, `created_at`) VALUES
(1, 5, 1, 'Photos matched the actual condition.', '2026-07-27 12:55:29'),
(2, 6, 2, 'Description was accurate and detailed.', '2026-07-27 12:55:29'),
(3, 9, 2, 'well satisfied', '2026-09-06 09:45:45');

-- --------------------------------------------------------

--
-- Table structure for table `ad_promotions`
--

CREATE TABLE `ad_promotions` (
  `id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `type` enum('featured','top_listing','highlight') DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ad_promotions`
--

INSERT INTO `ad_promotions` (`id`, `ad_id`, `payment_id`, `type`, `amount`, `starts_at`, `ends_at`, `created_at`) VALUES
(1, 1, 4, 'featured', 2000.00, '2026-07-16 00:00:00', '2026-08-23 23:59:59', '2026-07-27 12:55:29'),
(2, 2, 5, 'top_listing', 3500.00, '2026-07-19 00:00:00', '2026-08-26 23:59:59', '2026-07-27 12:55:29'),
(3, 7, NULL, 'highlight', 0.00, '2026-08-13 10:06:07', '2026-08-20 10:06:07', '2026-08-13 10:06:07'),
(4, 9, 9, 'featured', 2000.00, '2026-08-29 16:44:16', '2026-09-05 16:44:16', '2026-08-29 16:44:16'),
(5, 7, NULL, 'highlight', 0.00, '2026-09-06 01:26:55', '2026-09-13 01:26:55', '2026-09-06 01:26:55');

-- --------------------------------------------------------

--
-- Table structure for table `ad_system_analysis`
--

CREATE TABLE `ad_system_analysis` (
  `id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL,
  `submitted_price` decimal(12,2) DEFAULT NULL,
  `fair_price_status` enum('fair','not_fair') DEFAULT NULL,
  `result` text DEFAULT NULL,
  `analyzed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ad_system_analysis`
--

INSERT INTO `ad_system_analysis` (`id`, `ad_id`, `submitted_price`, `fair_price_status`, `result`, `analyzed_at`, `created_at`) VALUES
(1, 1, 5850000.00, 'fair', 'Predicted range: LKR 5.7M - 6.0M. Submitted price is within the fair market range for this vehicle.', '2026-07-15 10:05:00', '2026-07-27 12:55:29'),
(2, 2, 8200000.00, 'fair', 'Predicted range: LKR 8.0M - 8.5M. Submitted price is within the fair market range for this vehicle.', '2026-07-18 11:35:00', '2026-07-27 12:55:29'),
(3, 3, 3900000.00, 'not_fair', 'Predicted range: LKR 3.2M - 3.6M. Submitted price is above the fair market range for this vehicle.', '2026-07-20 09:10:00', '2026-07-27 12:55:29'),
(4, 4, 7400000.00, 'fair', 'Predicted range: LKR 7.1M - 7.6M. Submitted price is within the fair market range for this vehicle.', '2026-07-10 09:04:00', '2026-07-27 12:55:29'),
(5, 5, 6750000.00, 'fair', 'Predicted range: LKR 6.5M - 7.0M. Submitted price is within the fair market range for this vehicle.', '2026-07-20 14:03:00', '2026-07-27 12:55:29'),
(6, 6, 9200000.00, 'fair', 'Predicted range: LKR 8.9M - 9.5M. Submitted price is within the fair market range for this vehicle.', '2026-07-21 08:33:00', '2026-07-27 12:55:29'),
(7, 7, 3434.00, 'not_fair', 'Predicted fair range: LKR 3.5M - LKR 5.1M. Submitted price LKR 0.0M is below the fair market range for this vehicle.', '2026-08-13 10:03:48', '2026-08-13 10:03:48'),
(8, 7, 3434.00, 'not_fair', 'Predicted fair range: LKR 3.5M - LKR 5.1M. Submitted price LKR 0.0M is below the fair market range for this vehicle.', '2026-08-13 10:04:11', '2026-08-13 10:04:11'),
(9, 7, 3400000.00, 'not_fair', 'Predicted fair range: LKR 3.5M - LKR 5.1M. Submitted price LKR 3.4M is below the fair market range for this vehicle.', '2026-08-13 10:05:20', '2026-08-13 10:05:20'),
(10, 7, 3500000.00, 'not_fair', 'Predicted fair range: LKR 3.5M - LKR 5.1M. Submitted price LKR 3.5M is below the fair market range for this vehicle.', '2026-08-13 10:05:36', '2026-08-13 10:05:36'),
(11, 7, 3600000.00, 'fair', 'Predicted fair range: LKR 3.5M - LKR 5.1M. Submitted price LKR 3.6M is within the market fair range.', '2026-08-13 10:05:53', '2026-08-13 10:05:53'),
(12, 8, 7850001.00, 'not_fair', 'Predicted fair range: LKR 4.3M - LKR 6.2M. Submitted price LKR 7.9M is above the fair market range for this vehicle.', '2026-08-16 11:54:34', '2026-08-16 11:54:34'),
(13, 9, 5849995.00, 'fair', 'Predicted fair range: LKR 4.3M - LKR 6.2M. Submitted price LKR 5.8M is within the market fair range.', '2026-08-16 11:54:46', '2026-08-16 11:54:46'),
(14, 10, 7849990.00, 'not_fair', 'Predicted fair range: LKR 4.3M - LKR 6.2M. Submitted price LKR 7.8M is above the fair market range for this vehicle.', '2026-08-16 11:55:48', '2026-08-16 11:55:48'),
(15, 11, 760000.00, 'not_fair', 'Predicted fair range: LKR 4.3M - LKR 6.2M. Submitted price LKR 0.8M is below the fair market range for this vehicle.', '2026-09-06 09:47:55', '2026-09-06 09:47:55'),
(16, 12, 510000.00, 'not_fair', 'Predicted fair range: LKR 4.3M - LKR 6.2M. Submitted price LKR 0.5M is below the fair market range for this vehicle.', '2026-09-06 09:48:06', '2026-09-06 09:48:06'),
(17, 13, 640000.00, 'not_fair', 'Predicted fair range: LKR 4.3M - LKR 6.2M. Submitted price LKR 0.6M is below the fair market range for this vehicle.', '2026-09-06 09:48:15', '2026-09-06 09:48:15'),
(18, 14, 670000.00, 'not_fair', 'Predicted fair range: LKR 4.3M - LKR 6.2M. Submitted price LKR 0.7M is below the fair market range for this vehicle.', '2026-09-06 09:48:22', '2026-09-06 09:48:22'),
(19, 15, 6200000.00, 'not_fair', 'Predicted fair range: LKR 4.3M - LKR 6.2M. Submitted price LKR 6.2M is above the fair market range for this vehicle.', '2026-09-06 09:48:38', '2026-09-06 09:48:38'),
(20, 16, 6000000.00, 'fair', 'Predicted fair range: LKR 4.3M - LKR 6.2M. Submitted price LKR 6.0M is within the market fair range.', '2026-09-06 09:49:11', '2026-09-06 09:49:11');

-- --------------------------------------------------------

--
-- Table structure for table `authorized_seller_requests`
--

CREATE TABLE `authorized_seller_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `business_name` varchar(150) DEFAULT NULL,
  `business_description` text DEFAULT NULL,
  `business_document` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `authorized_seller_requests`
--

INSERT INTO `authorized_seller_requests` (`id`, `user_id`, `business_name`, `business_description`, `business_document`, `status`, `rejection_reason`, `reviewed_by`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(1, 2, 'Nabeel Auto Traders', 'Registered dealer since 2020.', 'uploads/sellers/2/verification/1/business_document.pdf', 'approved', NULL, 1, '2026-05-20 12:00:00', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(2, 3, 'Kavindu Motors', 'Family-run vehicle sales.', 'uploads/sellers/3/verification/2/business_document.pdf', 'pending', NULL, NULL, NULL, '2026-07-27 12:55:29', '2026-07-27 12:55:29');

-- --------------------------------------------------------

--
-- Table structure for table `buyer_favourites`
--

CREATE TABLE `buyer_favourites` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `buyer_favourites`
--

INSERT INTO `buyer_favourites` (`id`, `buyer_id`, `ad_id`, `created_at`) VALUES
(1, 5, 1, '2026-07-27 12:55:29'),
(2, 5, 2, '2026-07-27 12:55:29'),
(3, 6, 1, '2026-07-27 12:55:29'),
(4, 7, 2, '2026-07-27 12:55:29'),
(6, 8, 6, '2026-07-27 14:07:26'),
(7, 8, 1, '2026-07-29 17:59:47');

-- --------------------------------------------------------

--
-- Table structure for table `chats`
--

CREATE TABLE `chats` (
  `id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `status` enum('active','closed') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chats`
--

INSERT INTO `chats` (`id`, `ad_id`, `buyer_id`, `seller_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 5, 2, 'active', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(2, 2, 6, 3, 'active', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(3, 1, 7, 2, 'closed', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(4, 2, 2, 3, 'active', '2026-07-27 13:02:11', '2026-07-27 13:02:11'),
(5, 1, 8, 2, 'active', '2026-07-27 13:43:42', '2026-07-27 13:43:42'),
(6, 7, 9, 8, 'active', '2026-09-06 09:45:05', '2026-09-06 09:45:05');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `district` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `district`, `city`, `created_at`) VALUES
(1, 'Colombo', 'Colombo 03', '2026-07-27 12:55:29'),
(2, 'Kandy', 'Peradeniya', '2026-07-27 12:55:29'),
(3, 'Galle', 'Galle Fort', '2026-07-27 12:55:29'),
(4, 'Gampaha', 'Negombo', '2026-07-27 12:55:29'),
(5, 'Matara', 'Matara', '2026-07-27 12:55:29'),
(6, 'Colombo', 'Dehiwala', '2026-07-27 12:55:29'),
(7, 'Gampaha', 'Ja-Ela', '2026-07-27 12:55:29'),
(8, 'Kurunegala', 'Kurunegala', '2026-07-27 12:55:29');

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` enum('success','failed') DEFAULT NULL,
  `attempted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_logs`
--

INSERT INTO `login_logs` (`id`, `user_id`, `ip_address`, `status`, `attempted_at`, `created_at`) VALUES
(1, 1, '192.168.1.10', 'success', '2026-07-20 08:00:00', '2026-07-27 12:55:29'),
(2, 2, '192.168.1.22', 'success', '2026-07-21 09:15:00', '2026-07-27 12:55:29'),
(3, 3, '192.168.1.35', 'failed', '2026-07-22 10:20:00', '2026-07-27 12:55:29'),
(4, 3, '192.168.1.35', 'success', '2026-07-22 10:22:00', '2026-07-27 12:55:29'),
(5, 5, '192.168.1.50', 'success', '2026-07-25 16:45:00', '2026-07-27 12:55:29'),
(6, 1, '::1', 'success', '2026-07-27 13:00:03', '2026-07-27 13:00:03'),
(7, 2, '::1', 'success', '2026-07-27 13:01:33', '2026-07-27 13:01:33'),
(8, 3, '::1', 'success', '2026-07-27 13:02:53', '2026-07-27 13:02:53'),
(9, 2, '::1', 'success', '2026-07-27 13:44:42', '2026-07-27 13:44:42'),
(10, 8, '::1', 'success', '2026-07-27 14:06:39', '2026-07-27 14:06:39'),
(11, 8, '::1', 'failed', '2026-07-29 17:58:30', '2026-07-29 17:58:30'),
(12, 8, '::1', 'success', '2026-07-29 17:58:36', '2026-07-29 17:58:36'),
(13, 1, '::1', 'success', '2026-08-09 02:12:28', '2026-08-09 02:12:28'),
(14, 8, '::1', 'success', '2026-08-13 09:55:34', '2026-08-13 09:55:34'),
(15, 8, '::1', 'success', '2026-08-13 09:55:35', '2026-08-13 09:55:35'),
(16, 7, '::1', 'failed', '2026-08-16 11:47:22', '2026-08-16 11:47:22'),
(17, 7, '::1', 'failed', '2026-08-16 11:47:25', '2026-08-16 11:47:25'),
(18, 7, '::1', 'success', '2026-08-16 11:47:28', '2026-08-16 11:47:28'),
(19, 3, '::1', 'success', '2026-08-16 11:48:05', '2026-08-16 11:48:05'),
(20, 2, '::1', 'success', '2026-08-16 11:48:57', '2026-08-16 11:48:57'),
(21, 5, '::1', 'success', '2026-08-16 11:49:10', '2026-08-16 11:49:10'),
(22, 6, '::1', 'failed', '2026-08-16 11:49:26', '2026-08-16 11:49:26'),
(23, 6, '::1', 'success', '2026-08-16 11:49:30', '2026-08-16 11:49:30'),
(24, 7, '::1', 'success', '2026-08-16 11:49:43', '2026-08-16 11:49:43'),
(25, 2, '::1', 'failed', '2026-08-16 11:49:55', '2026-08-16 11:49:55'),
(26, 2, '::1', 'success', '2026-08-16 11:49:58', '2026-08-16 11:49:58'),
(27, 1, '::1', 'success', '2026-08-16 11:59:23', '2026-08-16 11:59:23'),
(28, 2, '::1', 'success', '2026-08-29 13:44:09', '2026-08-29 13:44:09'),
(29, 2, '::1', 'success', '2026-08-29 16:44:02', '2026-08-29 16:44:02'),
(30, 2, '::1', 'success', '2026-08-30 07:06:17', '2026-08-30 07:06:17'),
(31, 1, '::1', 'success', '2026-08-30 07:12:50', '2026-08-30 07:12:50'),
(32, 7, '::1', 'success', '2026-08-31 16:49:54', '2026-08-31 16:49:54'),
(33, 8, '::1', 'failed', '2026-08-31 16:55:11', '2026-08-31 16:55:11'),
(34, 8, '::1', 'failed', '2026-08-31 16:55:14', '2026-08-31 16:55:14'),
(35, 8, '::1', 'failed', '2026-08-31 16:55:18', '2026-08-31 16:55:18'),
(36, 8, '::1', 'failed', '2026-08-31 16:55:21', '2026-08-31 16:55:21'),
(37, 8, '::1', 'success', '2026-08-31 16:55:26', '2026-08-31 16:55:26'),
(38, 8, '::1', 'failed', '2026-09-06 00:27:43', '2026-09-06 00:27:43'),
(39, 8, '::1', 'success', '2026-09-06 00:27:56', '2026-09-06 00:27:56'),
(40, 9, '::1', 'success', '2026-09-06 09:44:22', '2026-09-06 09:44:22'),
(41, 1, '::1', 'success', '2026-09-06 11:24:52', '2026-09-06 11:24:52');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `chat_id`, `sender_id`, `receiver_id`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 5, 2, 'Hi, is the Aqua still available?', 1, '2026-07-27 12:55:29'),
(2, 1, 2, 5, 'Yes, it is available. Would you like to view it?', 1, '2026-07-27 12:55:29'),
(3, 1, 5, 2, 'Yes please, this weekend?', 0, '2026-07-27 12:55:29'),
(4, 2, 6, 3, 'Is the Vezel price negotiable?', 1, '2026-07-27 12:55:29'),
(5, 2, 3, 6, 'Slight negotiation possible after inspection.', 0, '2026-07-27 12:55:29'),
(6, 4, 2, 3, 'hi', 1, '2026-07-27 13:02:13'),
(7, 4, 2, 3, 'i need this cae', 1, '2026-07-27 13:02:22'),
(8, 4, 3, 2, 'okay conatc me', 1, '2026-07-27 13:03:14'),
(9, 5, 8, 2, 'hey', 1, '2026-07-27 13:43:47'),
(10, 5, 2, 8, 'yes', 1, '2026-07-27 13:45:29'),
(11, 5, 2, 8, 'what you need', 1, '2026-07-27 13:45:33'),
(12, 5, 8, 2, 'hey bro tell me', 1, '2026-07-27 14:07:01'),
(13, 5, 8, 2, 'okay', 1, '2026-07-29 17:58:51'),
(14, 6, 9, 8, 'hi', 0, '2026-09-06 09:45:07'),
(15, 6, 9, 8, 'i need this car', 0, '2026-09-06 09:45:14');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ad_id` int(11) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` enum('system','ad','payment','message') DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `ad_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 2, 1, 'Ad Approved', 'Your Toyota Aqua ad has been approved and is now live.', 'ad', 1, '2026-07-27 12:55:29'),
(2, 3, 2, 'Promotion Active', 'Your Vezel Top Listing promotion is live.', 'payment', 1, '2026-07-27 12:55:29'),
(3, 4, 3, 'Ad Rejected', 'Your Wagon R ad was rejected. AI predicted range LKR 3.2M - 3.6M and your price is above it.', 'ad', 0, '2026-07-27 12:55:29'),
(4, 5, 1, 'New Message', 'You have a new reply from the seller.', 'message', 0, '2026-07-27 12:55:29'),
(5, 3, 2, 'New Message', 'You have a new message about \"Honda Vezel 2017 Hybrid\".', 'message', 1, '2026-07-27 13:02:13'),
(6, 3, 2, 'New Message', 'You have a new message about \"Honda Vezel 2017 Hybrid\".', 'message', 1, '2026-07-27 13:02:22'),
(7, 2, 2, 'New Message', 'You have a new message about \"Honda Vezel 2017 Hybrid\".', 'message', 1, '2026-07-27 13:03:14'),
(8, 2, 1, 'New Message', 'You have a new message about \"Toyota Aqua 2018 - Excellent Condition\".', 'message', 1, '2026-07-27 13:43:47'),
(9, 8, 1, 'New Message', 'You have a new message about \"Toyota Aqua 2018 - Excellent Condition\".', 'message', 1, '2026-07-27 13:45:29'),
(10, 8, 1, 'New Message', 'You have a new message about \"Toyota Aqua 2018 - Excellent Condition\".', 'message', 1, '2026-07-27 13:45:33'),
(11, 2, 1, 'New Message', 'You have a new message about \"Toyota Aqua 2018 - Excellent Condition\".', 'message', 1, '2026-07-27 14:07:01'),
(12, 8, NULL, 'Plan Activated', 'Your Basic Plan is now active. Enjoy your benefits!', 'payment', 1, '2026-07-27 14:09:46'),
(13, 8, NULL, 'Plan Activated', 'Your Member Plan is now active. Enjoy your benefits!', 'payment', 1, '2026-07-27 14:11:34'),
(14, 2, 1, 'New Message', 'You have a new message about \"Toyota Aqua 2018 - Excellent Condition\".', 'message', 1, '2026-07-29 17:58:51'),
(15, 2, 1, 'New Rating', 'A buyer rated you 3 stars on \"Toyota Aqua 2018 - Excellent Condition\".', 'system', 1, '2026-07-29 17:59:39'),
(16, 8, NULL, 'Plan Activated', 'Your Premium Plan is now active. Enjoy your benefits!', 'payment', 1, '2026-08-13 10:01:28'),
(17, 8, 7, 'Ad Rejected', 'Your ad \"asdasdasdasdsa\" was rejected because the price is outside the fair market range. Predicted fair range: LKR 3.5M - LKR 5.1M. Submitted price LKR 0.0M is below the fair market range for this vehicle.', 'ad', 1, '2026-08-13 10:03:48'),
(18, 8, 7, 'Ad Rejected', 'Your ad \"asdasdasdasdsa\" was rejected because the price is outside the fair market range. Predicted fair range: LKR 3.5M - LKR 5.1M. Submitted price LKR 0.0M is below the fair market range for this vehicle.', 'ad', 1, '2026-08-13 10:04:11'),
(19, 8, 7, 'Ad Rejected', 'Your ad \"asdasdasdasdsa\" was rejected because the price is outside the fair market range. Predicted fair range: LKR 3.5M - LKR 5.1M. Submitted price LKR 3.4M is below the fair market range for this vehicle.', 'ad', 1, '2026-08-13 10:05:20'),
(20, 8, 7, 'Ad Rejected', 'Your ad \"asdasdasdasdsa\" was rejected because the price is outside the fair market range. Predicted fair range: LKR 3.5M - LKR 5.1M. Submitted price LKR 3.5M is below the fair market range for this vehicle.', 'ad', 1, '2026-08-13 10:05:36'),
(21, 8, 7, 'Ad Approved', 'Your ad \"asdasdasdasdsa\" passed the AI fair price check and is now live.', 'ad', 1, '2026-08-13 10:05:54'),
(22, 8, 7, 'Promotion Active', 'Your free highlight promotion is now live.', 'payment', 1, '2026-08-13 10:06:07'),
(23, 2, 8, 'Ad Rejected', 'Your ad \"Toyota Raize for Sale\" was rejected because the price is outside the fair market range. Predicted fair range: LKR 4.3M - LKR 6.2M. Submitted price LKR 7.9M is above the fair market range for this vehicle.', 'ad', 1, '2026-08-16 11:54:34'),
(24, 2, 9, 'Ad Approved', 'Your ad \"Toyota Raize for Sale\" passed the AI fair price check and is now live.', 'ad', 1, '2026-08-16 11:54:46'),
(25, 2, 10, 'Ad Rejected', 'Your ad \"Toyota Raize for Sale\" was rejected because the price is outside the fair market range. Predicted fair range: LKR 4.3M - LKR 6.2M. Submitted price LKR 7.8M is above the fair market range for this vehicle.', 'ad', 1, '2026-08-16 11:55:48'),
(26, 2, 9, 'Promotion Active', 'Your featured promotion is now live.', 'payment', 0, '2026-08-29 16:44:16'),
(27, 8, 7, 'Promotion Active', 'Your free highlight promotion is now live.', 'payment', 0, '2026-09-06 01:26:55'),
(28, 8, 7, 'New Message', 'You have a new message about \"asdasdasdasdsa\".', 'message', 0, '2026-09-06 09:45:07'),
(29, 8, 7, 'New Message', 'You have a new message about \"asdasdasdasdsa\".', 'message', 0, '2026-09-06 09:45:14'),
(30, 3, 2, 'New Rating', 'A buyer rated you 2 stars on \"Honda Vezel 2017 Hybrid\".', 'system', 0, '2026-09-06 09:45:37'),
(31, 9, 11, 'Ad Rejected', 'Your ad \"Toyota Sale now\" was rejected because the price is outside the fair market range. Predicted fair range: LKR 4.3M - LKR 6.2M. Submitted price LKR 0.8M is below the fair market range for this vehicle.', 'ad', 1, '2026-09-06 09:47:55'),
(32, 9, 12, 'Ad Rejected', 'Your ad \"Toyota Sale now\" was rejected because the price is outside the fair market range. Predicted fair range: LKR 4.3M - LKR 6.2M. Submitted price LKR 0.5M is below the fair market range for this vehicle.', 'ad', 1, '2026-09-06 09:48:06'),
(33, 9, 13, 'Ad Rejected', 'Your ad \"Toyota Sale now\" was rejected because the price is outside the fair market range. Predicted fair range: LKR 4.3M - LKR 6.2M. Submitted price LKR 0.6M is below the fair market range for this vehicle.', 'ad', 1, '2026-09-06 09:48:15'),
(34, 9, 14, 'Ad Rejected', 'Your ad \"Toyota Sale now\" was rejected because the price is outside the fair market range. Predicted fair range: LKR 4.3M - LKR 6.2M. Submitted price LKR 0.7M is below the fair market range for this vehicle.', 'ad', 1, '2026-09-06 09:48:22'),
(35, 9, 15, 'Ad Rejected', 'Your ad \"Toyota Sale now\" was rejected because the price is outside the fair market range. Predicted fair range: LKR 4.3M - LKR 6.2M. Submitted price LKR 6.2M is above the fair market range for this vehicle.', 'ad', 1, '2026-09-06 09:48:38'),
(36, 9, 16, 'Ad Approved', 'Your ad \"Toyota Sale now\" passed the AI fair price check and is now live.', 'ad', 1, '2026-09-06 09:49:11'),
(37, 9, NULL, 'Plan Activated', 'Your Basic Plan is now active. Enjoy your benefits!', 'payment', 1, '2026-09-06 09:50:48');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_logs`
--

CREATE TABLE `password_reset_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `request_sent_at` datetime DEFAULT NULL,
  `status` enum('requested','completed','expired') DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_logs`
--

INSERT INTO `password_reset_logs` (`id`, `user_id`, `reset_token`, `request_sent_at`, `status`, `created_at`) VALUES
(1, 3, 'tok_9f8e7d6c5b4a3', '2026-07-15 11:00:00', 'completed', '2026-07-27 12:55:29'),
(2, 5, 'tok_1a2b3c4d5e6f7', '2026-07-24 13:30:00', 'requested', '2026-07-27 12:55:29');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subscription_id` int(11) DEFAULT NULL,
  `category` enum('subscription','promotion') NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `meta` text DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `subscription_id`, `category`, `amount`, `method`, `transaction_id`, `meta`, `status`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'subscription', 6000.00, 'card', 'TXN_SUB_00001', NULL, 'completed', '2026-07-01 09:00:00', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(2, 3, 2, 'subscription', 3500.00, 'card', 'TXN_SUB_00002', NULL, 'completed', '2026-07-05 10:30:00', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(3, 4, 3, 'subscription', 1500.00, 'bank', 'TXN_SUB_00003', NULL, 'completed', '2026-06-01 08:15:00', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(4, 2, NULL, 'promotion', 2000.00, 'card', 'TXN_PRO_00001', NULL, 'completed', '2026-07-16 09:05:00', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(5, 3, NULL, 'promotion', 3500.00, 'card', 'TXN_PRO_00002', NULL, 'completed', '2026-07-19 10:15:00', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(6, 8, 4, 'subscription', 1500.00, 'demo', 'DEMO_5259968E38FB', NULL, 'completed', '2026-07-27 14:09:46', '2026-07-27 14:09:46', '2026-07-27 14:09:46'),
(7, 8, 5, 'subscription', 3500.00, 'demo', 'DEMO_085913AED2C5', NULL, 'completed', '2026-07-27 14:11:34', '2026-07-27 14:11:34', '2026-07-27 14:11:34'),
(8, 8, 6, 'subscription', 6000.00, 'demo', 'DEMO_985D6C655E40', '{\"plan_id\":3}', 'completed', '2026-08-13 10:01:27', '2026-08-13 10:01:27', '2026-08-13 10:01:27'),
(9, 2, NULL, 'promotion', 2000.00, 'demo', 'DEMO_20089823ADF1', '{\"ad_id\":9,\"promo_type\":\"featured\"}', 'completed', '2026-08-29 16:44:16', '2026-08-29 16:44:16', '2026-08-29 16:44:16'),
(10, 8, NULL, 'subscription', 1500.00, 'payhere', NULL, '{\"plan_id\":1}', 'pending', NULL, '2026-09-06 01:27:13', '2026-09-06 01:27:13'),
(11, 9, NULL, 'subscription', 1500.00, 'payhere', NULL, '{\"plan_id\":1}', 'pending', NULL, '2026-09-06 01:28:26', '2026-09-06 01:28:26'),
(12, 9, NULL, 'subscription', 1500.00, 'payhere', NULL, '{\"plan_id\":1}', 'pending', NULL, '2026-09-06 01:30:10', '2026-09-06 01:30:10'),
(13, 9, NULL, 'subscription', 1500.00, 'payhere', NULL, '{\"plan_id\":1}', 'pending', NULL, '2026-09-06 01:32:25', '2026-09-06 01:32:25'),
(14, 9, NULL, 'subscription', 1500.00, 'payhere', NULL, '{\"plan_id\":1}', 'pending', NULL, '2026-09-06 09:44:28', '2026-09-06 09:44:28'),
(15, 9, NULL, 'subscription', 3500.00, 'payhere', NULL, '{\"plan_id\":2}', 'pending', NULL, '2026-09-06 09:44:32', '2026-09-06 09:44:32'),
(16, 9, NULL, 'subscription', 3500.00, 'payhere', NULL, '{\"plan_id\":2}', 'pending', NULL, '2026-09-06 09:44:41', '2026-09-06 09:44:41'),
(17, 9, NULL, 'subscription', 3500.00, 'payhere', NULL, '{\"plan_id\":2}', 'pending', NULL, '2026-09-06 09:44:50', '2026-09-06 09:44:50'),
(18, 9, 7, 'subscription', 1500.00, 'demo', 'DEMO_5C47BA5B69C7', '{\"plan_id\":1}', 'completed', '2026-09-06 09:50:48', '2026-09-06 09:50:48', '2026-09-06 09:50:48');

-- --------------------------------------------------------

--
-- Table structure for table `promotion_usage`
--

CREATE TABLE `promotion_usage` (
  `id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `total_limit` int(11) DEFAULT 0,
  `used_count` int(11) DEFAULT 0,
  `remaining_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promotion_usage`
--

INSERT INTO `promotion_usage` (`id`, `subscription_id`, `total_limit`, `used_count`, `remaining_count`, `created_at`, `updated_at`) VALUES
(1, 1, 8, 1, 7, '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(2, 2, 3, 1, 2, '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(3, 3, 0, 0, 0, '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(4, 4, 0, 0, 0, '2026-07-27 14:09:46', '2026-07-27 14:09:46'),
(5, 5, 3, 0, 3, '2026-07-27 14:11:34', '2026-07-27 14:11:34'),
(6, 6, 8, 2, 6, '2026-08-13 10:01:27', '2026-09-06 01:26:55'),
(7, 7, 0, 0, 0, '2026-09-06 09:50:48', '2026-09-06 09:50:48');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `reported_user_id` int(11) NOT NULL,
  `ad_id` int(11) DEFAULT NULL,
  `type` enum('user','ad','other') DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','reviewed','resolved') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `reporter_id`, `reported_user_id`, `ad_id`, `type`, `reason`, `status`, `reviewed_by`, `reviewed_at`, `created_at`) VALUES
(1, 6, 4, 3, 'ad', 'Vehicle priced far above market range.', 'pending', NULL, NULL, '2026-07-27 12:55:29'),
(2, 7, 4, 3, 'ad', 'Same vehicle posted elsewhere at lower price.', 'reviewed', 1, '2026-07-25 12:00:00', '2026-07-27 12:55:29');

-- --------------------------------------------------------

--
-- Table structure for table `seller_profile`
--

CREATE TABLE `seller_profile` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `seller_type` enum('non_member','member','authorized') DEFAULT 'non_member',
  `phone_number` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `avg_rating` decimal(2,1) DEFAULT 0.0,
  `response_rate` decimal(5,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seller_profile`
--

INSERT INTO `seller_profile` (`id`, `user_id`, `seller_type`, `phone_number`, `date_of_birth`, `gender`, `profile_image`, `bio`, `address`, `city`, `district`, `province`, `postal_code`, `avg_rating`, `response_rate`, `created_at`, `updated_at`) VALUES
(1, 2, 'authorized', '+94771234567', '1995-04-12', 'male', 'https://images.unsplash.com/photo-1633332755192-727a05c4013d?auto=format&fit=crop&w=200&q=80', 'We specialize in carefully inspected Japanese hybrid vehicles with transparent service and ownership history.', '12 Galle Rd', 'Colombo', 'Colombo', 'Western', '00300', 4.0, 98.00, '2026-07-27 12:55:29', '2026-07-29 17:59:39'),
(2, 3, 'member', '+94772345678', '1990-08-22', 'male', 'https://images.unsplash.com/photo-1531891437562-4301cf35b7e4?auto=format&fit=crop&w=200&q=80', 'Selling well-maintained vehicles from Kandy.', '45 Kandy Rd', 'Kandy', 'Kandy', 'Central', '20000', 3.5, 92.00, '2026-07-27 12:55:29', '2026-09-06 09:45:37'),
(3, 4, 'non_member', '+94773456789', '1988-11-05', 'male', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=200&q=80', 'First time seller.', '78 Matara Rd', 'Galle', 'Galle', 'Southern', '80000', 4.2, 75.00, '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(4, 8, 'member', '+76 55 24 196', NULL, NULL, NULL, '', '157/ 2 C , mathavan road, Kalmunai - 03', 'Kalmunai', '', '', '', 0.0, 0.00, '2026-07-27 13:20:15', '2026-07-27 14:11:34'),
(5, 9, 'non_member', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0, 0.00, '2026-09-06 01:28:26', '2026-09-06 01:28:26');

-- --------------------------------------------------------

--
-- Table structure for table `seller_ratings`
--

CREATE TABLE `seller_ratings` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seller_ratings`
--

INSERT INTO `seller_ratings` (`id`, `seller_id`, `buyer_id`, `ad_id`, `rating`, `comment`, `created_at`) VALUES
(1, 2, 5, 1, 5, 'Very responsive and honest seller.', '2026-07-27 12:55:29'),
(2, 2, 7, 1, 4, 'Good communication overall.', '2026-07-27 12:55:29'),
(3, 3, 6, 2, 5, 'Smooth transaction, highly recommended.', '2026-07-27 12:55:29'),
(4, 2, 8, 1, 3, 'good seller', '2026-07-29 17:59:39'),
(5, 3, 9, 2, 2, 'poor', '2026-09-06 09:45:37');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key` varchar(100) DEFAULT NULL,
  `value` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'AutoValue', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(2, 'support_email', 'support@autovalue.lk', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(3, 'max_images_per_ad', '10', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(4, 'flask_api_endpoint', 'http://127.0.0.1:5000/predict', '2026-07-27 12:55:29', '2026-08-13 10:01:08'),
(5, 'default_currency', 'LKR', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(6, 'promo_price_featured', '2000', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(7, 'promo_price_top_listing', '3500', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(8, 'promo_price_highlight', '1500', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(9, 'promo_duration_days', '7', '2026-07-27 12:55:29', '2026-07-27 12:55:29');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `plan_id`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 3, '2026-07-01', '2026-07-31', 'active', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(2, 3, 2, '2026-07-05', '2026-08-04', 'active', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(3, 4, 1, '2026-06-01', '2026-06-30', 'expired', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(4, 8, 1, '2026-07-27', '2026-08-26', 'cancelled', '2026-07-27 14:09:46', '2026-08-13 10:01:27'),
(5, 8, 2, '2026-07-27', '2026-08-26', 'cancelled', '2026-07-27 14:11:34', '2026-08-13 10:01:27'),
(6, 8, 3, '2026-08-13', '2026-09-12', 'active', '2026-08-13 10:01:27', '2026-08-13 10:01:27'),
(7, 9, 1, '2026-09-06', '2026-10-06', 'active', '2026-09-06 09:50:48', '2026-09-06 09:50:48');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `features` text DEFAULT NULL,
  `free_promotions` int(11) DEFAULT 0,
  `grants_member_badge` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_plans`
--

INSERT INTO `subscription_plans` (`id`, `name`, `description`, `price`, `duration`, `features`, `free_promotions`, `grants_member_badge`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Basic Plan', 'Analytics dashboard with limited features. No member badge.', 1500.00, 30, 'Post unlimited ads|AI fair-price analysis|Views and enquiry dashboard|Standard support', 0, 0, 'active', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(2, 'Member Plan', 'Grants Member badge. Advanced analytics plus limited free ad promotions.', 3500.00, 30, 'Everything in Basic|Gold Member badge|Advanced buyer-interest analytics|3 free ad promotions per month', 3, 1, 'active', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(3, 'Premium Plan', 'Top tier. Member badge. Deep advanced analytics plus higher free ad promotions.', 6000.00, 30, 'Everything in Member|Deep valuation insights|8 free ad promotions per month|Priority listing placement', 8, 1, 'active', '2026-07-27 12:55:29', '2026-07-27 12:55:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `profile_image`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin_user', 'admin@autovalue.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=200&q=80', 'admin', 'active', '2026-07-27 12:55:29', '2026-09-06 11:23:13'),
(2, 'nisar_s', 'nisar@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'https://images.unsplash.com/photo-1633332755192-727a05c4013d?auto=format&fit=crop&w=200&q=80', 'user', 'active', '2026-07-27 12:55:29', '2026-07-27 13:44:23'),
(3, 'kavindu_p', 'kavindu@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'https://images.unsplash.com/photo-1531891437562-4301cf35b7e4?auto=format&fit=crop&w=200&q=80', 'user', 'active', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(4, 'rihan_ahmed', 'rihan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=200&q=80', 'user', 'active', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(5, 'sarah_j', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=200&q=80', 'user', 'active', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(6, 'michael_t', 'michael@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80', 'user', 'active', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(7, 'jessica_l', 'jessica@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=200&q=80', 'user', 'active', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(8, 'nousad', 'nousadnousad021@gmail.com', '$2y$10$ZdSQIwApE2tRD845cKCbZ.oSW2Jua5gY3OkndgCpMusGZ9iWGMgu.', 'uploads/users/8/profile/profile_image_1785140006.png', 'user', 'active', '2026-07-27 13:05:45', '2026-07-27 13:43:26'),
(9, 'khan', 'mrnoukhan7377@gmail.com', '$2y$10$7mlBy90XwIwhuNVmbxv3suSOTTItRlBWudd.0KM8aCwKpAd/8uZba', NULL, 'user', 'active', '2026-09-06 01:28:19', '2026-09-06 01:28:19');

-- --------------------------------------------------------

--
-- Table structure for table `user_verifications`
--

CREATE TABLE `user_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `method` enum('email','phone') DEFAULT NULL,
  `type` enum('registration_verification','password_reset') DEFAULT NULL,
  `otp_code` varchar(10) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_verifications`
--

INSERT INTO `user_verifications` (`id`, `user_id`, `method`, `type`, `otp_code`, `is_verified`, `verified_at`, `created_at`) VALUES
(1, 2, 'email', 'registration_verification', '482913', 1, '2026-01-10 10:15:00', '2026-07-27 12:55:29'),
(2, 3, 'phone', 'registration_verification', '739201', 1, '2026-02-05 09:30:00', '2026-07-27 12:55:29'),
(3, 5, 'email', 'registration_verification', '105832', 1, '2026-03-01 14:00:00', '2026-07-27 12:55:29');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_images`
--

CREATE TABLE `vehicle_images` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `file_size` int(11) DEFAULT NULL,
  `file_format` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicle_images`
--

INSERT INTO `vehicle_images` (`id`, `vehicle_id`, `image_path`, `is_primary`, `file_size`, `file_format`, `created_at`) VALUES
(1, 1, 'https://images.unsplash.com/photo-1502877338535-766e1452684a?auto=format&fit=crop&w=1200&q=80', 1, 245000, 'jpg', '2026-07-27 12:55:29'),
(2, 1, 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=1200&q=80', 0, 231000, 'jpg', '2026-07-27 12:55:29'),
(3, 1, 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=1200&q=80', 0, 224000, 'jpg', '2026-07-27 12:55:29'),
(4, 2, 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?auto=format&fit=crop&w=1200&q=80', 1, 305000, 'jpg', '2026-07-27 12:55:29'),
(5, 2, 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=80', 0, 288000, 'jpg', '2026-07-27 12:55:29'),
(6, 3, 'https://images.unsplash.com/photo-1471479917193-f00955256257?auto=format&fit=crop&w=1200&q=80', 1, 198000, 'jpg', '2026-07-27 12:55:29'),
(7, 4, 'https://images.unsplash.com/photo-1593941707882-a5bba14938c7?auto=format&fit=crop&w=1200&q=80', 1, 265000, 'jpg', '2026-07-27 12:55:29'),
(8, 5, 'https://images.unsplash.com/photo-1580273916550-e323be2ae537?auto=format&fit=crop&w=1200&q=80', 1, 244000, 'jpg', '2026-07-27 12:55:29'),
(9, 6, 'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=1200&q=80', 1, 287000, 'jpg', '2026-07-27 12:55:29'),
(10, 8, 'uploads/ads/8/vehicle_images/10.jpg', 1, 125824, 'jpg', '2026-08-16 11:54:34'),
(11, 9, 'uploads/ads/9/vehicle_images/11.jpg', 1, 125824, 'jpg', '2026-08-16 11:54:46');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_info`
--

CREATE TABLE `vehicle_info` (
  `id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL,
  `make` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `manufacture_year` year(4) DEFAULT NULL,
  `registration_year` year(4) DEFAULT NULL,
  `body_type` varchar(50) DEFAULT NULL,
  `transmission` enum('manual','automatic') DEFAULT NULL,
  `fuel_type` enum('petrol','diesel','hybrid','electric') DEFAULT NULL,
  `engine_cc` int(11) DEFAULT NULL,
  `mileage` int(11) DEFAULT NULL,
  `colour` varchar(50) DEFAULT NULL,
  `condition` varchar(50) DEFAULT NULL,
  `number_of_owners` int(11) DEFAULT NULL,
  `finance_status` varchar(50) DEFAULT NULL,
  `finance_company` varchar(100) DEFAULT NULL,
  `accident_history` tinyint(1) DEFAULT 0,
  `service_history` tinyint(1) DEFAULT 0,
  `features` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicle_info`
--

INSERT INTO `vehicle_info` (`id`, `ad_id`, `make`, `model`, `manufacture_year`, `registration_year`, `body_type`, `transmission`, `fuel_type`, `engine_cc`, `mileage`, `colour`, `condition`, `number_of_owners`, `finance_status`, `finance_company`, `accident_history`, `service_history`, `features`, `created_at`, `updated_at`) VALUES
(1, 1, 'Toyota', 'Aqua', '2018', '2019', 'Hatchback', 'automatic', 'hybrid', 1500, 65000, 'White', 'Used', 1, 'Clear', NULL, 0, 1, 'ABS|Airbags|Alloy Wheels|Push Start|Reverse Camera|Bluetooth Audio', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(2, 2, 'Honda', 'Vezel', '2017', '2018', 'SUV', 'automatic', 'hybrid', 1500, 78000, 'Black', 'Used', 1, 'Under Finance', 'LB Finance', 0, 1, 'Sunroof|Leather Seats|Cruise Control|Reverse Camera', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(3, 3, 'Suzuki', 'Wagon R', '2019', '2019', 'Hatchback', 'automatic', 'petrol', 660, 42000, 'Silver', 'Used', 1, 'Clear', NULL, 0, 0, 'Power Windows|AC|Airbags', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(4, 4, 'Nissan', 'Leaf', '2020', '2020', 'Hatchback', 'automatic', 'electric', 1500, 30000, 'Blue', 'Used', 1, 'Clear', NULL, 0, 1, 'Home Charger|Reverse Camera|Cruise Control', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(5, 5, 'Kia', 'Sportage', '2016', '2016', 'SUV', 'automatic', 'diesel', 2000, 98000, 'Grey', 'Used', 2, 'Clear', NULL, 0, 1, 'Roof Rails|Parking Sensors|Dual Zone AC', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(6, 6, 'Mitsubishi', 'Montero', '2015', '2015', 'SUV', 'automatic', 'diesel', 3200, 120000, 'Black', 'Used', 2, 'Clear', NULL, 0, 1, '4WD|Leather Seats|Sunroof|Tow Bar', '2026-07-27 12:55:29', '2026-07-27 12:55:29'),
(7, 7, 'sdfs', 'fsdfds', '2021', '2020', 'sdfsd', 'automatic', 'petrol', 3434, 43, 'sdf', 'Used', 1, 'Clear', 'sdfsd', 0, 1, 'dfsds', '2026-07-27 13:29:43', '2026-07-27 13:29:43'),
(8, 8, 'TOYOTA', 'Rauze', '2018', '2019', 'Hatchbakac', 'automatic', 'diesel', 1500, 6500, 'Black', 'Used', 1, 'Under Finance', 'no', 1, 1, 'ABS', '2026-08-16 11:54:34', '2026-08-16 11:54:34'),
(9, 9, 'TOYOTA', 'Rauze', '2018', '2019', 'Hatchbakac', 'automatic', 'diesel', 1500, 6500, 'Black', 'Used', 1, 'Under Finance', 'no', 1, 1, 'ABS', '2026-08-16 11:54:46', '2026-08-16 11:54:46'),
(10, 10, 'TOYOTA', 'Rauze', '2018', '2019', 'Hatchbakac', 'automatic', 'diesel', 1500, 6500, 'Black', 'Used', 1, 'Under Finance', 'no', 1, 1, 'ABS', '2026-08-16 11:55:48', '2026-08-16 11:55:48'),
(11, 11, 'Toyota', 'Raize', '2019', '2020', 'Hatckback', 'manual', 'diesel', 1400, 60000, 'Black', 'Used', 1, 'Clear', '', 1, 1, 'ABS', '2026-09-06 09:47:55', '2026-09-06 09:47:55'),
(12, 12, 'Toyota', 'Raize', '2019', '2020', 'Hatckback', 'manual', 'diesel', 1400, 60000, 'Black', 'Used', 1, 'Clear', '', 1, 1, 'ABS', '2026-09-06 09:48:06', '2026-09-06 09:48:06'),
(13, 13, 'Toyota', 'Raize', '2019', '2020', 'Hatckback', 'manual', 'diesel', 1400, 60000, 'Black', 'Used', 1, 'Clear', '', 1, 1, 'ABS', '2026-09-06 09:48:15', '2026-09-06 09:48:15'),
(14, 14, 'Toyota', 'Raize', '2019', '2020', 'Hatckback', 'manual', 'diesel', 1400, 60000, 'Black', 'Used', 1, 'Clear', '', 1, 1, 'ABS', '2026-09-06 09:48:22', '2026-09-06 09:48:22'),
(15, 15, 'Toyota', 'Raize', '2019', '2020', 'Hatckback', 'manual', 'diesel', 1400, 60000, 'Black', 'Used', 1, 'Clear', '', 1, 1, 'ABS', '2026-09-06 09:48:38', '2026-09-06 09:48:38'),
(16, 16, 'Toyota', 'Raize', '2019', '2020', 'Hatckback', 'manual', 'diesel', 1400, 60000, 'Black', 'Used', 1, 'Clear', '', 1, 1, 'ABS', '2026-09-06 09:49:11', '2026-09-06 09:49:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ads`
--
ALTER TABLE `ads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `ad_feedback`
--
ALTER TABLE `ad_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `ad_id` (`ad_id`);

--
-- Indexes for table `ad_promotions`
--
ALTER TABLE `ad_promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ad_id` (`ad_id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `ad_system_analysis`
--
ALTER TABLE `ad_system_analysis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ad_id` (`ad_id`);

--
-- Indexes for table `authorized_seller_requests`
--
ALTER TABLE `authorized_seller_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `buyer_favourites`
--
ALTER TABLE `buyer_favourites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_fav` (`buyer_id`,`ad_id`),
  ADD KEY `ad_id` (`ad_id`);

--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ad_id` (`ad_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_id` (`chat_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ad_id` (`ad_id`);

--
-- Indexes for table `password_reset_logs`
--
ALTER TABLE `password_reset_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `subscription_id` (`subscription_id`);

--
-- Indexes for table `promotion_usage`
--
ALTER TABLE `promotion_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_id` (`subscription_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reporter_id` (`reporter_id`),
  ADD KEY `reported_user_id` (`reported_user_id`),
  ADD KEY `ad_id` (`ad_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `seller_profile`
--
ALTER TABLE `seller_profile`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `seller_ratings`
--
ALTER TABLE `seller_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_rating` (`seller_id`,`buyer_id`,`ad_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `ad_id` (`ad_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_verifications`
--
ALTER TABLE `user_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `vehicle_images`
--
ALTER TABLE `vehicle_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `vehicle_info`
--
ALTER TABLE `vehicle_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ad_id` (`ad_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ads`
--
ALTER TABLE `ads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `ad_feedback`
--
ALTER TABLE `ad_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ad_promotions`
--
ALTER TABLE `ad_promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ad_system_analysis`
--
ALTER TABLE `ad_system_analysis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `authorized_seller_requests`
--
ALTER TABLE `authorized_seller_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `buyer_favourites`
--
ALTER TABLE `buyer_favourites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `password_reset_logs`
--
ALTER TABLE `password_reset_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `promotion_usage`
--
ALTER TABLE `promotion_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `seller_profile`
--
ALTER TABLE `seller_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `seller_ratings`
--
ALTER TABLE `seller_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_verifications`
--
ALTER TABLE `user_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vehicle_images`
--
ALTER TABLE `vehicle_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `vehicle_info`
--
ALTER TABLE `vehicle_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ads`
--
ALTER TABLE `ads`
  ADD CONSTRAINT `ads_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ads_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`);

--
-- Constraints for table `ad_feedback`
--
ALTER TABLE `ad_feedback`
  ADD CONSTRAINT `ad_feedback_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ad_feedback_ibfk_2` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ad_promotions`
--
ALTER TABLE `ad_promotions`
  ADD CONSTRAINT `ad_promotions_ibfk_1` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ad_promotions_ibfk_2` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ad_system_analysis`
--
ALTER TABLE `ad_system_analysis`
  ADD CONSTRAINT `ad_system_analysis_ibfk_1` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `authorized_seller_requests`
--
ALTER TABLE `authorized_seller_requests`
  ADD CONSTRAINT `authorized_seller_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `authorized_seller_requests_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `buyer_favourites`
--
ALTER TABLE `buyer_favourites`
  ADD CONSTRAINT `buyer_favourites_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `buyer_favourites_ibfk_2` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `chats_ibfk_1` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chats_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chats_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD CONSTRAINT `login_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `password_reset_logs`
--
ALTER TABLE `password_reset_logs`
  ADD CONSTRAINT `password_reset_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `promotion_usage`
--
ALTER TABLE `promotion_usage`
  ADD CONSTRAINT `promotion_usage_ibfk_1` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`reported_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_3` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reports_ibfk_4` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `seller_profile`
--
ALTER TABLE `seller_profile`
  ADD CONSTRAINT `seller_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `seller_ratings`
--
ALTER TABLE `seller_ratings`
  ADD CONSTRAINT `seller_ratings_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seller_ratings_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seller_ratings_ibfk_3` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`);

--
-- Constraints for table `user_verifications`
--
ALTER TABLE `user_verifications`
  ADD CONSTRAINT `user_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_images`
--
ALTER TABLE `vehicle_images`
  ADD CONSTRAINT `vehicle_images_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicle_info` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_info`
--
ALTER TABLE `vehicle_info`
  ADD CONSTRAINT `vehicle_info_ibfk_1` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
