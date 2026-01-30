-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 10, 2026 at 10:55 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yakan_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'info',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Saputangan', 'saputangan', '2026-01-05 11:57:26', '2026-01-05 11:57:26'),
(2, 'Pinantupan', 'pinantupan', '2026-01-05 11:57:26', '2026-01-05 11:57:26'),
(3, 'Birey-Birey', 'birey-birey', '2026-01-05 11:57:26', '2026-01-05 11:57:26'),
(4, 'Sinaluan', 'sinaluan', '2026-01-05 11:57:26', '2026-01-05 11:57:26'),
(5, 'slipper', 'slipper', '2026-01-05 18:22:13', '2026-01-05 18:22:13');

-- --------------------------------------------------------

--
-- Table structure for table `chats`
--

CREATE TABLE `chats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `user_phone` varchar(255) DEFAULT NULL,
  `subject` text DEFAULT NULL,
  `status` enum('open','closed','pending') NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chats`
--

INSERT INTO `chats` (`id`, `user_id`, `user_name`, `user_email`, `user_phone`, `subject`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 'Yakan User', 'user@yakan.com', '', 'fggf', 'open', '2026-01-06 06:35:30', '2026-01-09 08:05:04');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `chat_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sender_type` enum('user','admin') NOT NULL DEFAULT 'user',
  `message` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `chat_id`, `user_id`, `sender_type`, `message`, `image_path`, `file_path`, `file_name`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 'user', 'gfgfgfgfgffgfgf', NULL, NULL, NULL, 1, '2026-01-06 06:35:30', '2026-01-06 06:35:56'),
(2, 1, NULL, 'admin', 'hello', NULL, NULL, NULL, 1, '2026-01-08 03:29:33', '2026-01-08 03:29:58'),
(3, 1, 3, 'user', 'i want this design', NULL, NULL, NULL, 1, '2026-01-08 04:34:27', '2026-01-08 04:35:11'),
(4, 1, 3, 'user', '.', 'chat-images/G2PZ4tiulnnqdCWnO8YOE7rFsK3A1nS0Ct9MntNY.png', NULL, NULL, 1, '2026-01-08 04:34:54', '2026-01-08 04:35:11'),
(5, 1, NULL, 'admin', '📋 PRICE QUOTE\r\n\r\nPrice: ₱1,500\r\n\r\nDescription:\r\njdhsjdhjsdhjshjd\r\n\r\nPlease review and let us know if you\'d like to proceed with this custom order.', NULL, NULL, NULL, 1, '2026-01-08 04:39:40', '2026-01-08 04:39:52'),
(6, 1, 3, 'user', '✅ Customer accepted the price quote.\n\nCustomer will proceed with the custom order.', NULL, NULL, NULL, 1, '2026-01-08 04:49:10', '2026-01-08 04:49:32'),
(7, 1, NULL, 'admin', '📋 PRICE QUOTE\r\n\r\nPrice: ₱1,000\r\n\r\nDescription:\r\nhahhahah\r\n\r\nPlease review and let us know if you\'d like to proceed with this custom order.', NULL, NULL, NULL, 1, '2026-01-08 04:56:47', '2026-01-08 04:56:47'),
(8, 1, 3, 'user', '✅ Customer accepted the price quote.\n\nCustomer will proceed with the custom order.', NULL, NULL, NULL, 1, '2026-01-08 05:04:06', '2026-01-08 11:19:21'),
(9, 1, 3, 'user', 'hey', NULL, NULL, NULL, 1, '2026-01-08 12:33:09', '2026-01-08 12:33:20'),
(10, 1, 3, 'user', 'this is the design that i want', 'chat-images/9BHmrKS3aPyP4YcbHCs2K0o6gKtAftcdUHBovE1B.jpg', NULL, NULL, 1, '2026-01-08 12:37:17', '2026-01-08 12:37:24'),
(11, 1, NULL, 'user', 'hahah', NULL, NULL, NULL, 1, '2026-01-09 03:37:07', '2026-01-09 03:38:00'),
(12, 1, NULL, 'user', 'this', NULL, NULL, NULL, 1, '2026-01-09 03:40:24', '2026-01-09 03:46:30'),
(13, 1, NULL, 'user', 'this one', NULL, NULL, NULL, 1, '2026-01-09 03:40:41', '2026-01-09 03:46:30'),
(14, 1, NULL, 'user', '...', 'chat_images/chat_1767930374_69607a06200c8.png', NULL, NULL, 1, '2026-01-09 03:46:14', '2026-01-09 03:46:30'),
(15, 1, NULL, 'user', 'hello', NULL, NULL, NULL, 1, '2026-01-09 08:05:04', '2026-01-09 08:05:16');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `type` enum('percent','fixed') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `min_spend` decimal(10,2) NOT NULL DEFAULT 0.00,
  `usage_limit` int(10) UNSIGNED DEFAULT NULL,
  `usage_limit_per_user` int(10) UNSIGNED DEFAULT NULL,
  `times_redeemed` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupon_redemptions`
--

CREATE TABLE `coupon_redemptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `coupon_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount_discounted` decimal(10,2) NOT NULL DEFAULT 0.00,
  `redeemed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cultural_heritage`
--

CREATE TABLE `cultural_heritage` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'history',
  `order` int(11) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `author` varchar(255) DEFAULT NULL,
  `published_date` date DEFAULT NULL,
  `gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gallery`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cultural_heritage`
--

INSERT INTO `cultural_heritage` (`id`, `title`, `slug`, `summary`, `content`, `image`, `category`, `order`, `is_published`, `author`, `published_date`, `gallery`, `metadata`, `created_at`, `updated_at`) VALUES
(5, 'Yakan History: From Ancient Times to Today', 'yakan-history-ancient-modern', 'An overview of Yakan history, traditions, and their journey through time.', 'The history of the Yakan people is marked by resilience, adaptation, and cultural pride. From their earliest settlements in the Philippines to their present-day communities, the Yakan have maintained distinctive cultural practices while navigating significant historical changes.\r\n\r\nArchaeological and oral historical evidence suggests that the Yakan people have inhabited their lands for centuries, developing sophisticated systems of agriculture, trade, and governance. Their maritime heritage connected them to broader regional trade networks, influencing cultural practices and economic structures.\r\n\r\nThroughout various periods of colonial rule and modernization, the Yakan maintained their cultural identity through conscious preservation of traditions and adaptation strategies. This balance between preservation and innovation continues to define Yakan society today. Understanding this history is crucial for appreciating the contemporary Yakan community and supporting their continued cultural vitality.', 'cultural-heritage/TkTsLGc4OTo6XdwHplo24nxz3jTzkC2wJxIB196G.jpg', 'history', 5, 1, 'Historical Research Bureau', '2025-12-23', NULL, NULL, '2026-01-02 09:46:02', '2026-01-06 06:23:39'),
(8, 'Evelyn Otong - Hamja', 'evelyn-otong-hamja', 'Nanay Evelynda is a fourth generation Yakan weaver currently residing at the Yakan Village in Zamboanga City. She was born and raised in Lamitan, Basilan where most of the Yakan community came from. In the 70\'s the military forced them out of Basilan and brought them to move to Zamoboanga where many settled in what is now called the Yakan Village.', 'Nanay Evelynda is a fourth generation Yakan weaver currently residing at the Yakan Village in Zamboanga City. She was born and raised in Lamitan, Basilan where most of the Yakan community came from. In the 70\'s the military forced them out of Basilan and brought them to move to Zamoboanga where many settled in what is now called the Yakan Village. \r\n\r\n​Nanay Evelynda describes their lifestyle as Yakan people very simple. Her father was a farmer cultivating vegetables and root crops while her mother stayed home to weave. She picked up the weaving skill at the age of 7 where she first made a coaster until such time that she was able to make table runners and larger scale fabrics.\r\n\r\n​Nanay Evelynda work with a community of weavers, mostly her cousins called the Tuwas Yakan Weavers of Basilan. They use backstrap loom which consists of different sticks and tangles of thread. Originally, they used to weave pineapple, abaca and banana threads harvested to produce threads from plant fibers. Over the centuries, the scarcity of these natural threads forced these weavers to use polyester cotton threads and it takes about four to seven days to produce one meter or a little over 3 feet of fabric.\r\n\r\n​Nanay Evelynda is being realistic about how she is not very interested in commercializing her products. She believes in the integrity of her work. However, it is part of her mission to keep the weaving tradition alive by influencing her community to keep weaving for other communities who are able to appreciate their artisan crafts.', 'cultural-heritage/UxDLjaTrPrVl8saLXIOkZg6wzVmIwzCaGJ012cSI.webp', 'tradition', 0, 1, NULL, '2026-01-06', NULL, NULL, '2026-01-06 06:21:22', '2026-01-06 06:22:30'),
(9, 'Birey-Birey Textile Pattern', 'birey-birey-textile-pattern', 'The birey-birey pattern, a traditional Yakan textile design characterized by multicolored vertical stripes. The pattern represents Yakan cultural identity and artistic expression, emphasizing the role of textile documentation in managing and preserving indigenous cultural knowledge.', 'The birey-birey pattern, a traditional Yakan textile design distinguished by its vibrant multicolored vertical stripes. Each color combination and arrangement reflects Yakan aesthetic values, social identity, and cultural symbolism. Traditionally woven using hand-operated looms, the birey-birey pattern demonstrates the advanced skill, creativity, and patience of Yakan weavers. Documenting and preserving this textile pattern is essential to Cultural Heritage Management, as it safeguards indigenous knowledge, weaving techniques, and artistic traditions for future generations.', 'cultural-heritage/KFsWQGSZrX3RK1N1iyDgBcuF0oBtT12D8Zn4LXMw.jpg', 'art', 0, 1, NULL, '2026-01-01', NULL, NULL, '2026-01-06 06:25:16', '2026-01-06 06:25:16');

-- --------------------------------------------------------

--
-- Table structure for table `custom_orders`
--

CREATE TABLE `custom_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fabric_type` varchar(255) DEFAULT NULL,
  `fabric_weight_gsm` int(11) DEFAULT NULL,
  `fabric_quantity_meters` decimal(8,2) DEFAULT NULL,
  `intended_use` varchar(255) DEFAULT NULL,
  `fabric_specifications` text DEFAULT NULL,
  `special_requirements` text DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `specifications` text NOT NULL,
  `design_method` varchar(255) NOT NULL DEFAULT 'text',
  `patterns` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`patterns`)),
  `design_upload` varchar(255) DEFAULT NULL,
  `design_metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`design_metadata`)),
  `customization_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`customization_settings`)),
  `design_version` varchar(255) NOT NULL DEFAULT '1.0',
  `canvas_width` decimal(8,2) DEFAULT NULL,
  `canvas_height` decimal(8,2) DEFAULT NULL,
  `pattern_positions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`pattern_positions`)),
  `color_palette` varchar(255) DEFAULT NULL,
  `artisan_notes` text DEFAULT NULL,
  `design_approved_at` timestamp NULL DEFAULT NULL,
  `design_approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `design_modifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`design_modifications`)),
  `last_design_update` timestamp NULL DEFAULT NULL,
  `design_completion_time` int(11) DEFAULT NULL,
  `pattern_count` int(11) NOT NULL DEFAULT 0,
  `complexity_score` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `delivery_type` varchar(255) DEFAULT NULL,
  `product_type` varchar(255) DEFAULT NULL,
  `dimensions` varchar(255) DEFAULT NULL,
  `preferred_colors` varchar(255) DEFAULT NULL,
  `primary_color` varchar(255) NOT NULL DEFAULT '#ef4444',
  `secondary_color` varchar(255) NOT NULL DEFAULT '#3b82f6',
  `accent_color` varchar(255) NOT NULL DEFAULT '#10b981',
  `budget_range` varchar(255) DEFAULT NULL,
  `expected_date` date DEFAULT NULL,
  `urgency` varchar(255) NOT NULL DEFAULT 'normal',
  `additional_notes` text DEFAULT NULL,
  `estimated_price` decimal(10,2) DEFAULT NULL,
  `final_price` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','price_quoted','approved','rejected','processing','in_production','production_complete','out_for_delivery','delivered','completed','cancelled') DEFAULT 'pending',
  `is_delayed` tinyint(1) NOT NULL DEFAULT 0,
  `delay_reason` text DEFAULT NULL,
  `delay_notified_at` timestamp NULL DEFAULT NULL,
  `payment_status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_notes` text DEFAULT NULL,
  `payment_receipt` varchar(255) DEFAULT NULL,
  `payment_confirmed_at` timestamp NULL DEFAULT NULL,
  `transfer_date` date DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `production_completed_at` timestamp NULL DEFAULT NULL,
  `out_for_delivery_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` varchar(255) DEFAULT NULL,
  `price_quoted_at` timestamp NULL DEFAULT NULL,
  `user_notified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `custom_orders`
--

INSERT INTO `custom_orders` (`id`, `user_id`, `product_id`, `fabric_type`, `fabric_weight_gsm`, `fabric_quantity_meters`, `intended_use`, `fabric_specifications`, `special_requirements`, `quantity`, `specifications`, `design_method`, `patterns`, `design_upload`, `design_metadata`, `customization_settings`, `design_version`, `canvas_width`, `canvas_height`, `pattern_positions`, `color_palette`, `artisan_notes`, `design_approved_at`, `design_approved_by`, `design_modifications`, `last_design_update`, `design_completion_time`, `pattern_count`, `complexity_score`, `phone`, `email`, `delivery_address`, `delivery_type`, `product_type`, `dimensions`, `preferred_colors`, `primary_color`, `secondary_color`, `accent_color`, `budget_range`, `expected_date`, `urgency`, `additional_notes`, `estimated_price`, `final_price`, `status`, `is_delayed`, `delay_reason`, `delay_notified_at`, `payment_status`, `payment_method`, `paid_at`, `transaction_id`, `payment_notes`, `payment_receipt`, `payment_confirmed_at`, `transfer_date`, `admin_notes`, `approved_at`, `production_completed_at`, `out_for_delivery_at`, `delivered_at`, `rejected_at`, `rejection_reason`, `price_quoted_at`, `user_notified_at`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, '1', NULL, 2.00, 'clothing', NULL, 'test', 1, 'Custom Fabric Order\nFabric Type: cotton\nQuantity: 2 meters\nIntended Use: clothing', 'pattern', '[4]', 'custom_orders/pattern_previews/pattern_preview_723a5837-a1e5-4762-9df3-7c92a287d4b4.png', '{\"pattern_id\":4,\"pattern_name\":\"Suhul\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1.7,\"rotation\":0,\"opacity\":0.9,\"hue\":210,\"saturation\":100,\"brightness\":100}}', NULL, '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '09656923753', 'heidilynnrubia09@gmail.com', 'RRM Perez Drive Sun street, Tumaga, Zamboanga City, Zamboanga del Sur 7000', 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 1800.00, 1800.00, 'approved', 0, NULL, NULL, 'paid', 'online_banking', NULL, '1111234545', NULL, 'payment_receipts/6RMWBoYD0JyIkttoxisDBBGflfAlGPw96xvZY9aM.jpg', NULL, '2026-01-05', NULL, '2026-01-05 15:16:40', NULL, NULL, NULL, NULL, NULL, '2026-01-05 15:02:56', '2026-01-05 15:02:56', '2026-01-05 13:52:02', '2026-01-09 22:12:55'),
(2, 3, NULL, '1', NULL, 2.00, 'clothing', NULL, 'ydshgdhs', 1, 'Custom Fabric Order\nFabric Type: cotton\nQuantity: 2 meters\nIntended Use: clothing', 'pattern', '[\"7\"]', NULL, '{\"pattern_id\":\"7\",\"pattern_name\":\"Bennig\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1.6,\"rotation\":75,\"opacity\":1,\"hue\":330,\"saturation\":100,\"brightness\":100}}', NULL, '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '09656923753', 'heidilynnrubia09@gmail.com', 'RRM Perez Drive Sun street, Tumaga, Zamboanga City, Zamboanga del Sur 7000', 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 3100.00, 3100.00, 'completed', 0, NULL, NULL, 'paid', 'bank_transfer', NULL, '1111234545', NULL, 'payment_receipts/7EmUgJM4p7YLIdUf5reV6McI0AQBiLg1Zodtznaa.jpg', NULL, '2026-01-06', NULL, '2026-01-05 17:13:05', '2026-01-05 18:03:18', '2026-01-05 18:03:52', '2026-01-05 18:04:11', NULL, NULL, '2026-01-05 17:12:29', '2026-01-05 17:12:29', '2026-01-05 15:22:33', '2026-01-09 22:12:55'),
(3, 3, NULL, '1', NULL, 2.00, 'clothing', NULL, 'test', 1, 'Custom Fabric Order\nFabric Type: cotton\nQuantity: 2 meters\nIntended Use: clothing', 'pattern', '[\"4\"]', NULL, '{\"pattern_id\":\"4\",\"pattern_name\":\"Suhul\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1.6,\"rotation\":35,\"opacity\":0.9,\"hue\":120,\"saturation\":100,\"brightness\":100}}', NULL, '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '09656923753', 'heidilynnrubia09@gmail.com', 'City Hall, Caridad, Cavite City, Cavite 4100', 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 1900.00, 1900.00, 'completed', 0, NULL, NULL, 'paid', 'online_banking', NULL, '1111111111', NULL, 'payment_receipts/bI3b6qNMmuZoYdW9vmVnfqdwOpHojpH0F1nccGaR.jpg', NULL, '2026-01-06', NULL, '2026-01-06 14:01:58', '2026-01-06 14:15:30', '2026-01-06 14:15:43', '2026-01-06 14:16:13', NULL, NULL, '2026-01-06 14:01:49', '2026-01-06 14:01:49', '2026-01-06 14:01:17', '2026-01-09 22:12:55'),
(4, 3, NULL, '1', NULL, 2.00, 'clothing', NULL, 'sds', 1, 'Custom Fabric Order\nFabric Type: cotton\nQuantity: 2 meters\nIntended Use: clothing', 'pattern', '[\"4\"]', NULL, '{\"pattern_id\":\"4\",\"pattern_name\":\"Suhul\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":87,\"saturation\":100,\"brightness\":100}}', NULL, '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '09656923753', 'heidilynnrubia09@gmail.com', 'City Hall, Caridad, Cavite City, Cavite 4100', 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 1900.00, 1900.00, 'completed', 0, NULL, NULL, 'paid', 'bank_transfer', NULL, '1111111111', NULL, 'payment_receipts/wcoaANzZFWByeXHouKW12htCOVbYpx6KjtugJSlS.jpg', '2026-01-06 14:54:21', '2026-01-06', NULL, '2026-01-06 14:18:52', '2026-01-06 14:57:30', '2026-01-06 14:57:40', '2026-01-06 14:57:59', NULL, NULL, '2026-01-06 14:18:41', '2026-01-06 14:18:41', '2026-01-06 14:17:57', '2026-01-09 22:12:55'),
(5, 3, NULL, '1', NULL, 2.00, 'clothing', NULL, 'test1', 1, 'Custom Fabric Order\nFabric Type: cotton\nQuantity: 2 meters\nIntended Use: clothing', 'pattern', '[\"12\"]', NULL, '{\"pattern_id\":\"12\",\"pattern_name\":\"Tali\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1.5,\"rotation\":0,\"opacity\":1,\"hue\":120,\"saturation\":100,\"brightness\":100}}', NULL, '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '09656923753', 'heidilynnrubia09@gmail.com', 'RRM Perez Drive Sun street, Tumaga, Zamboanga City, Zamboanga del Sur 7000', 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 3100.00, 3100.00, 'completed', 0, NULL, NULL, 'paid', 'online_banking', NULL, '1111111111', NULL, 'payment_receipts/5cm1JG1pnHBkvC7eD0aMS9D1pZh5pD2OJK4H9jQe.jpg', '2026-01-07 16:35:24', '2026-01-08', NULL, '2026-01-07 16:34:35', '2026-01-07 16:36:38', '2026-01-07 16:36:47', '2026-01-07 16:37:15', NULL, NULL, '2026-01-07 16:34:22', '2026-01-07 16:34:22', '2026-01-07 16:23:11', '2026-01-09 22:12:55'),
(6, 3, NULL, '1', NULL, 2.00, 'clothing', NULL, 'whatatat', 1, 'Custom Fabric Order\nFabric Type: cotton\nQuantity: 2 meters\nIntended Use: clothing', 'pattern', '[\"4\"]', NULL, '{\"pattern_id\":\"4\",\"pattern_name\":\"Suhul\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1.6,\"rotation\":55,\"opacity\":0.9,\"hue\":330,\"saturation\":100,\"brightness\":100}}', NULL, '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '09656923753', 'heidilynnrubia09@gmail.com', 'RRM Perez Drive Sun street, Tumaga, Zamboanga City, Zamboanga del Sur 7000', 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 1800.00, 1800.00, 'pending', 0, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-07 16:56:41', '2026-01-09 22:12:55'),
(7, 3, NULL, '1', NULL, 2.00, 'clothing', NULL, 'hahha', 1, 'Custom Fabric Order\nFabric Type: cotton\nQuantity: 2 meters\nIntended Use: clothing', 'pattern', '[\"4\"]', NULL, '{\"pattern_id\":\"4\",\"pattern_name\":\"Suhul\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1.7,\"rotation\":35,\"opacity\":0.9,\"hue\":280,\"saturation\":100,\"brightness\":100}}', '{\"scale\":1.7,\"rotation\":35,\"opacity\":0.9,\"hue\":280,\"saturation\":100,\"brightness\":100}', '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '09656923753', 'heidilynnrubia09@gmail.com', 'RRM Perez Drive Sun street, Tumaga, Zamboanga City, Zamboanga del Sur 7000', 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 1800.00, 1800.00, 'completed', 0, NULL, '2026-01-07 17:28:12', 'paid', 'online_banking', NULL, '1111111111', NULL, 'payment_receipts/Mi4jmYAtiB5jsBukVYc21tocHS0RDHzcVyWOHJ8q.jpg', '2026-01-07 17:26:52', '2026-01-08', NULL, '2026-01-07 17:25:56', '2026-01-07 17:45:07', '2026-01-07 17:55:42', '2026-01-07 17:55:53', NULL, NULL, '2026-01-07 17:25:39', '2026-01-07 17:25:39', '2026-01-07 17:06:51', '2026-01-09 22:12:55'),
(8, 3, NULL, '1', NULL, 2.00, 'clothing', NULL, 'dsdhsg', 1, 'Custom Fabric Order\nFabric Type: cotton\nQuantity: 2 meters\nIntended Use: clothing', 'pattern', '[\"4\"]', NULL, '{\"pattern_id\":\"4\",\"pattern_name\":\"Suhul\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1.7,\"rotation\":35,\"opacity\":0.9,\"hue\":55,\"saturation\":100,\"brightness\":100}}', '{\"scale\":1.7,\"rotation\":35,\"opacity\":0.9,\"hue\":55,\"saturation\":100,\"brightness\":100}', '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '09656923753', 'heidilynnrubia09@gmail.com', 'RRM Perez Drive Sun street, Tumaga, Zamboanga City, Zamboanga del Sur 7000', 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 1800.00, 1800.00, 'pending', 0, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 06:10:23', '2026-01-09 22:12:55'),
(9, 3, NULL, '1', NULL, 2.00, 'clothing', NULL, 'vecna', 1, 'Custom Fabric Order\nFabric Type: cotton\nQuantity: 2 meters\nIntended Use: clothing', 'pattern', '[\"4\"]', NULL, '{\"pattern_id\":\"4\",\"pattern_name\":\"Suhul\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":2,\"rotation\":0,\"opacity\":0.9,\"hue\":120,\"saturation\":100,\"brightness\":100}}', '{\"scale\":2,\"rotation\":0,\"opacity\":0.9,\"hue\":120,\"saturation\":100,\"brightness\":100}', '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '09656923753', 'heidilynnrubia09@gmail.com', 'P. Zamora Street, , Cavite, Cavite City 4100', 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 1900.00, 1900.00, 'completed', 0, NULL, NULL, 'paid', 'online_banking', NULL, '098676564545', NULL, 'payment_receipts/TKtKFz3p0qiWTcTctwWzCxz0yHyubRYN0bHePfLS.jpg', '2026-01-08 11:16:58', '2026-01-08', NULL, '2026-01-08 11:15:55', '2026-01-08 11:17:15', '2026-01-08 11:17:31', '2026-01-08 11:17:44', NULL, NULL, '2026-01-08 11:15:19', '2026-01-08 11:15:19', '2026-01-08 10:56:18', '2026-01-09 22:12:55'),
(10, 3, NULL, '1', NULL, 5.00, 'clothing', NULL, 'test', 1, 'Custom Fabric Order\nFabric Type: cotton\nQuantity: 5 meters\nIntended Use: clothing', 'pattern', '[\"2\"]', NULL, '{\"pattern_id\":\"2\",\"pattern_name\":\"Bunga Sama\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":280,\"saturation\":100,\"brightness\":100}}', '{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":280,\"saturation\":100,\"brightness\":100}', '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 4100.00, 4100.00, 'price_quoted', 0, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-09 09:07:20', '2026-01-09 09:07:20', '2026-01-09 09:04:56', '2026-01-09 22:12:55'),
(11, 3, NULL, '1', NULL, 2.00, 'clothing', NULL, NULL, 1, 'Custom Fabric Order\nFabric Type: cotton\nQuantity: 2 meters\nIntended Use: clothing', 'pattern', '[\"12\"]', NULL, '{\"pattern_id\":\"12\",\"pattern_name\":\"Tali\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}}', '{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}', '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'pickup', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 3200.00, 3200.00, 'approved', 0, NULL, NULL, 'pending', 'online_banking', NULL, 'BANK_6960C911870F3', NULL, NULL, NULL, NULL, 'gGGGg', '2026-01-09 09:23:07', NULL, NULL, NULL, NULL, NULL, '2026-01-09 09:22:38', '2026-01-09 09:22:38', '2026-01-09 09:20:46', '2026-01-09 22:12:55'),
(12, 3, NULL, '1', NULL, 2.00, '1', NULL, NULL, 1, 'Custom Fabric Order\nFabric Type: Cotton\nQuantity: 2.00 meters\nIntended Use: Clothing', 'pattern', '[\"4\"]', NULL, '{\"pattern_id\":\"4\",\"pattern_name\":\"Suhul\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}}', '{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}', '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 'RRM Perez Drive Sun street, Tumaga, Zamboanga City, Zamboanga del Sur, 7000', 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 1800.00, 1800.00, 'price_quoted', 0, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-09 22:26:53', '2026-01-09 22:26:53', '2026-01-09 21:24:30', '2026-01-09 22:26:53'),
(13, 3, NULL, '1', NULL, 2.00, '2', NULL, NULL, 1, 'Custom Fabric Order\nFabric Type: Cotton\nQuantity: 2 meters\nIntended Use: Home Decor', 'pattern', '[\"17\"]', NULL, '{\"pattern_id\":\"17\",\"pattern_name\":\"zigzagggg\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}}', '{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}', '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 1800.00, 2000.00, 'cancelled', 0, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'additional 200 in patterns fee', NULL, NULL, NULL, NULL, NULL, 'User rejected: price doesnt work for me', '2026-01-09 23:23:02', '2026-01-09 23:23:03', '2026-01-09 22:33:45', '2026-01-09 23:24:19'),
(14, 3, NULL, '1', NULL, 2.00, '1', NULL, NULL, 1, 'Custom Fabric Order\nFabric Type: Cotton\nQuantity: 2 meters\nIntended Use: Clothing', 'pattern', '[\"17\"]', NULL, '{\"pattern_id\":\"17\",\"pattern_name\":\"zigzagggg\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}}', '{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}', '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 1800.00, 2000.00, 'approved', 0, NULL, NULL, 'paid', 'online_banking', NULL, '1111111111', NULL, 'payment_receipts/8BFVdaIPmPCxa3iNiYm2oD5Xx1J8gdCQ5N41AXem.jpg', NULL, '2026-01-10', 'additional 200 in patterns fee', '2026-01-09 23:26:07', NULL, NULL, NULL, NULL, NULL, '2026-01-09 23:25:52', '2026-01-09 23:25:52', '2026-01-09 22:36:09', '2026-01-09 23:26:53'),
(15, 3, NULL, '1', NULL, 2.00, '1', NULL, NULL, 2, 'Custom Fabric Order\nFabric Type: Cotton\nQuantity: 2 meters\nIntended Use: Clothing', 'pattern', '[\"4\"]', NULL, '{\"pattern_id\":\"4\",\"pattern_name\":\"Suhul\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}}', '{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}', '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 2200.00, 2200.00, 'pending', 0, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-09 23:45:11', '2026-01-10 02:14:53'),
(16, 3, NULL, '1', NULL, 2.00, '1', NULL, NULL, 1, 'Test Order', 'pattern', '[17]', NULL, '[]', '[]', '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '09123456789', 'test@test.com', NULL, 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 3000.00, 3000.00, 'pending', 0, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 00:04:54', '2026-01-10 00:04:54'),
(17, 3, NULL, '1', NULL, 2.00, '1', NULL, NULL, 1, 'Test Order', 'pattern', '[17]', NULL, '[]', '[]', '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '09123456789', 'test@test.com', NULL, 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 3000.00, 3000.00, 'pending', 0, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 00:05:55', '2026-01-10 00:05:55'),
(18, 3, NULL, '1', NULL, 2.00, '1', NULL, NULL, 1, 'End-to-End Test Order', 'text', '[17]', NULL, NULL, NULL, '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '09000000000', 'user@yakan.com', NULL, 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 1800.00, 1800.00, 'pending', 0, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 00:17:15', '2026-01-10 00:17:15'),
(19, 3, NULL, '1', NULL, 2.00, '1', NULL, NULL, 1, 'Custom Fabric Order\nFabric Type: Cotton\nQuantity: 2 meters\nIntended Use: Clothing', 'pattern', '[\"17\"]', NULL, '{\"pattern_id\":\"17\",\"pattern_name\":\"zigzagggg\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}}', '{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}', '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 1800.00, 1800.00, 'pending', 0, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 00:27:30', '2026-01-10 00:27:30'),
(20, 3, NULL, '1', NULL, 2.00, '6', NULL, NULL, 2, 'Custom Fabric Order\nFabric Type: Cotton\nQuantity: 2 meters\nIntended Use: Bedding', 'pattern', '[\"6\"]', NULL, '{\"pattern_id\":\"6\",\"pattern_name\":\"Laggi\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}}', '{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}', '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '09656923753', 'user@yakan.com', 'RRM Perez Drive Sun street, Tumaga, Zamboanga City, Zamboanga del Sur, 7000', 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 7000.00, 7000.00, 'approved', 0, NULL, NULL, 'paid', 'online_banking', NULL, '1111111111', NULL, 'payment_receipts/ieLGvjIs4PrABNAYzS0OvcBo3rWYSkYWdFFBJ1gb.jpg', NULL, '2026-01-10', 'no manpower', '2026-01-10 02:19:34', NULL, NULL, NULL, NULL, NULL, '2026-01-10 02:19:20', '2026-01-10 02:19:20', '2026-01-10 02:17:47', '2026-01-10 02:31:19'),
(21, 3, NULL, '1', NULL, 2.00, '2', NULL, NULL, 2, 'Custom Fabric Order\nFabric Type: Cotton\nQuantity: 2 meters\nIntended Use: Home Decor', 'pattern', '[\"20\"]', NULL, '{\"pattern_id\":\"20\",\"pattern_name\":\"zigzagggg\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}}', '{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}', '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 4000.00, 4000.00, 'pending', 0, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 04:05:42', '2026-01-10 04:05:42'),
(22, 3, NULL, '1', NULL, 2.00, '1', NULL, NULL, 2, 'Custom Fabric Order\nFabric Type: Cotton\nQuantity: 2 meters\nIntended Use: Clothing', 'pattern', '[\"21\"]', NULL, '{\"pattern_id\":\"21\",\"pattern_name\":\"test\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}}', '{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}', '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 5000.00, 5000.00, 'approved', 0, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 04:26:14', NULL, NULL, NULL, NULL, NULL, '2026-01-10 04:25:44', '2026-01-10 04:25:44', '2026-01-10 04:20:08', '2026-01-10 04:26:14'),
(23, 3, NULL, '1', NULL, 2.00, '6', NULL, NULL, 2, 'Custom Fabric Order\nFabric Type: Cotton\nQuantity: 2 meters\nIntended Use: Bedding', 'pattern', '[\"21\"]', NULL, '{\"pattern_id\":\"21\",\"pattern_name\":\"test\",\"colors\":[],\"pattern_data\":[],\"customization_settings\":{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}}', '{\"scale\":1,\"rotation\":0,\"opacity\":0.9,\"hue\":0,\"saturation\":100,\"brightness\":100}', '1.0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'delivery', NULL, NULL, NULL, '#ef4444', '#3b82f6', '#10b981', NULL, NULL, 'normal', NULL, 5000.00, 5000.00, 'approved', 0, NULL, NULL, 'paid', 'online_banking', NULL, '1111111111', NULL, 'payment_receipts/G87tSINaGoaAAlw3X6kJagmVTwsmasnvIIC2ZEyo.jpg', '2026-01-10 04:33:52', '2026-01-10', 'hjhjehejjdfgdhfgdhfgd', '2026-01-10 04:28:52', NULL, NULL, NULL, NULL, NULL, '2026-01-10 04:28:14', '2026-01-10 04:28:14', '2026-01-10 04:27:45', '2026-01-10 04:33:52');

-- --------------------------------------------------------

--
-- Table structure for table `fabric_types`
--

CREATE TABLE `fabric_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `base_price_per_meter` decimal(10,2) NOT NULL,
  `material_composition` varchar(255) DEFAULT NULL,
  `weight_gsm` int(11) DEFAULT NULL,
  `texture` varchar(255) DEFAULT NULL,
  `typical_uses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`typical_uses`)),
  `care_instructions` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fabric_types`
--

INSERT INTO `fabric_types` (`id`, `name`, `icon`, `description`, `base_price_per_meter`, `material_composition`, `weight_gsm`, `texture`, `typical_uses`, `care_instructions`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Cotton', '🌾', 'Soft, breathable, and comfortable for everyday wear', 250.00, NULL, NULL, NULL, NULL, NULL, 1, 0, '2026-01-09 15:39:18', '2026-01-09 18:55:31'),
(2, 'Silk', '✨', 'Luxurious, smooth, and perfect for special occasions', 500.00, NULL, NULL, NULL, NULL, NULL, 1, 0, '2026-01-09 15:39:18', '2026-01-09 15:39:18'),
(3, 'Linen', '📋', 'Lightweight, durable, and great for warm weather', 300.00, NULL, NULL, NULL, NULL, NULL, 1, 0, '2026-01-09 15:39:18', '2026-01-09 15:39:18'),
(4, 'Canvas', '🎒', 'Heavy-duty fabric ideal for bags and durable items', 350.00, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-01-09 15:39:18', '2026-01-10 02:47:40');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `intended_uses`
--

CREATE TABLE `intended_uses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `intended_uses`
--

INSERT INTO `intended_uses` (`id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Clothing', 'For making garments and apparel', 1, '2026-01-09 15:40:57', '2026-01-09 15:40:57'),
(2, 'Home Decor', 'For home furnishings and decorative items', 1, '2026-01-09 15:40:57', '2026-01-09 15:40:57'),
(6, 'Bedding', 'For bed linens and bedding materials', 1, '2026-01-09 15:40:57', '2026-01-09 17:13:14');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `total_sold` int(11) NOT NULL DEFAULT 0,
  `total_revenue` decimal(12,2) NOT NULL DEFAULT 0.00,
  `min_stock_level` int(11) NOT NULL DEFAULT 10,
  `max_stock_level` int(11) NOT NULL DEFAULT 100,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `low_stock_alert` tinyint(1) NOT NULL DEFAULT 0,
  `last_restocked_at` timestamp NULL DEFAULT NULL,
  `last_sale_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `product_id`, `quantity`, `total_sold`, `total_revenue`, `min_stock_level`, `max_stock_level`, `cost_price`, `selling_price`, `supplier`, `location`, `notes`, `low_stock_alert`, `last_restocked_at`, `last_sale_at`, `created_at`, `updated_at`) VALUES
(1, 1, 48, 2, 100.00, 5, 100, 30.00, 50.00, NULL, NULL, NULL, 0, NULL, '2026-01-10 02:45:53', '2026-01-05 15:41:36', '2026-01-10 02:45:53'),
(2, 2, 36, 9, 450.00, 5, 100, 30.00, 50.00, NULL, NULL, NULL, 0, NULL, '2026-01-10 02:45:53', '2026-01-06 06:49:39', '2026-01-10 02:45:53'),
(3, 3, 39, 1, 50.00, 5, 100, 30.00, 50.00, NULL, NULL, NULL, 0, NULL, '2026-01-07 17:57:39', '2026-01-07 17:57:13', '2026-01-07 17:57:40'),
(4, 9, 19, 1, 75.00, 5, 100, 45.00, 75.00, NULL, NULL, NULL, 0, NULL, '2026-01-10 02:45:53', '2026-01-10 02:40:17', '2026-01-10 02:45:53');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(10, '0001_01_01_000000_create_users_table', 1),
(11, '0001_01_01_000001_create_cache_table', 1),
(12, '0001_01_01_000002_create_jobs_table', 1),
(13, '2024_12_11_create_orders_table', 1),
(14, '2025_11_10_100000_create_categories_table', 2),
(15, '2025_11_10_110000_create_products_table', 2),
(16, '2025_11_12_145521_create_order_items_table', 1),
(17, '2025_12_02_143500_create_cultural_heritage_table', 3),
(18, '2025_11_15_134929_create_custom_orders_table', 4),
(19, '2025_11_18_060149_add_column_to_custom_orders_table', 4),
(20, '2025_11_21_125718_update_category_id_in_products_table', 4),
(21, '2025_11_21_133237_create_carts_table', 4),
(22, '2025_11_21_170230_add_total_amount_to_orders_table', 4),
(23, '2025_11_21_170356_rename_total_to_total_amount_in_orders_table', 4),
(24, '2025_11_21_171145_remove_total_from_orders_table', 4),
(25, '2025_11_22_023415_add_role_to_users_table', 4),
(26, '2025_11_23_155546_add_tracking_to_orders_table', 4),
(27, '2025_11_24_025718_create_contact_messages_table', 4),
(28, '2025_11_25_124348_create_admins_table', 4),
(29, '2025_11_26_154500_add_price_and_admin_notes_to_custom_orders', 4),
(30, '2025_11_26_154800_add_rejection_reason_to_custom_orders', 4),
(31, '2025_11_26_155000_add_payment_fields_to_custom_orders', 4),
(32, '2025_11_26_155200_add_payment_verification_fields_to_custom_orders', 4),
(33, '2025_11_27_000000_add_transfer_date_to_custom_orders', 4),
(34, '2025_11_27_010000_add_last_login_to_users', 4),
(35, '2025_11_27_065040_add_name_fields_to_users_table', 4),
(36, '2025_11_28_031451_add_patterns_to_custom_orders_table', 4),
(37, '2025_11_28_143503_add_slug_to_categories_table', 4),
(38, '2025_11_28_145023_add_social_auth_fields_to_users_table', 4),
(39, '2025_11_28_154253_add_oauth_fields_to_users_table', 4),
(40, '2025_11_28_180123_add_visual_design_fields_to_custom_orders_table', 4),
(41, '2025_11_29_002634_create_reviews_table', 4),
(42, '2025_11_29_003155_create_notifications_table', 4),
(43, '2025_11_29_004302_create_yakan_patterns_table', 4),
(44, '2025_11_29_004302_make_pattern_data_nullable_in_yakan_patterns', 4),
(45, '2025_11_29_030000_add_custom_order_workflow_fields', 4),
(46, '2025_11_29_143539_create_personal_access_tokens_table', 4),
(47, '2025_11_29_154122_create_inventory_table', 4),
(48, '2025_11_29_155516_add_order_fulfillment_fields_to_inventory_table', 4),
(49, '2025_12_01_000000_create_fabric_types_table', 4),
(50, '2025_12_01_010000_add_fabric_fields_to_custom_orders_table', 4),
(51, '2025_12_01_020000_create_pattern_fabric_compatibility_table', 4),
(52, '2025_12_02_020044_create_sessions_table', 4),
(53, '2025_12_02_032829_create_coupons_table', 4),
(54, '2025_12_02_033022_create_coupon_redemptions_table', 4),
(55, '2025_12_02_033126_add_promotion_fields_to_orders_table', 4),
(56, '2025_12_02_040857_create_pattern_media_table', 4),
(57, '2025_12_02_041017_create_pattern_tags_table', 4),
(58, '2025_12_02_042154_create_pattern_tag_pivot_table', 4),
(59, '2025_12_02_042439_add_deleted_at_to_users_table', 4),
(60, '2025_12_02_043510_add_slug_to_pattern_tags_table', 4),
(61, '2025_12_02_043904_create_wishlists_table', 4),
(62, '2025_12_02_043928_create_wishlist_items_table', 4),
(63, '2025_12_02_043942_create_recent_views_table', 4),
(64, '2025_12_02_210948_add_delivery_coordinates_to_orders_table', 4),
(65, '2025_12_03_000001_add_two_factor_auth_to_users_table', 4),
(66, '2025_12_03_092125_add_urgency_to_custom_orders_table', 4),
(67, '2025_12_03_092433_make_user_id_nullable_in_custom_orders', 4),
(68, '2025_12_04_000000_create_admin_notifications_table', 4),
(69, '2025_12_05_120000_add_in_production_status_to_custom_orders', 4),
(70, '2025_12_09_010000_add_delivery_type_to_custom_orders_table', 4),
(71, '2025_12_09_172700_add_source_to_orders_table', 4),
(72, '2025_12_09_203233_add_svg_path_to_yakan_patterns_table', 4),
(73, '2025_12_10_022134_add_bank_receipt_to_orders_table', 4),
(74, '2025_12_10_023907_add_gcash_receipt_to_orders_table', 4),
(75, '2025_12_10_043659_add_delivery_tracking_to_custom_orders_table', 4),
(76, '2025_12_10_115803_update_custom_orders_status_enum', 4),
(77, '2025_12_10_200834_create_product_images_table', 4),
(78, '2025_12_10_205908_add_all_images_to_products_table', 4),
(79, '2025_12_10_215052_add_price_quoted_status_to_custom_orders_table', 4),
(80, '2025_12_12_000001_add_payment_proof_to_orders', 4),
(81, '2025_12_12_000100_add_mobile_fields_to_orders_table', 4),
(82, '2025_12_12_010000_add_customer_fields_to_orders_table', 4),
(83, '2025_12_12_020000_add_payment_reference_to_orders_table', 4),
(84, '2025_12_12_030000_add_pricing_fields_to_orders_table', 4),
(85, '2025_12_12_040000_add_notes_to_orders_table', 4),
(86, '2025_12_16_093437_add_otp_fields_to_users_table', 4),
(87, '2025_12_17_add_user_address_to_orders_table', 5),
(88, '2025_12_17_create_user_addresses_table', 5),
(89, '2025_12_24_add_courier_tracking_to_orders_table', 5),
(90, '2025_12_24_add_missing_columns_to_reviews_table', 6),
(91, '2025_12_24_add_order_id_to_reviews_table', 6),
(92, '2025_12_24_add_review_columns_to_reviews_table', 6),
(93, '2025_12_24_create_reviews_table', 7),
(94, '2026_01_02_000000_add_tracking_number_to_orders_table', 8),
(95, '2025_12_26_create_chats_table', 9),
(96, '2025_12_26_create_chat_messages_table', 8),
(97, '2026_01_06_000001_update_custom_orders_status_enum', 10),
(98, '2026_01_06_224957_add_payment_confirmed_at_to_custom_orders_table', 11),
(99, '2026_01_07_230706_create_addresses_table', 12),
(100, '2026_01_08_004158_add_delay_fields_to_custom_orders_table', 12),
(101, '2026_01_08_010252_add_customization_settings_to_custom_orders_table', 13),
(102, '2026_01_09_fix_base_price_multiplier_column', 14),
(106, '2026_01_09_add_price_per_meter_to_yakan_patterns', 15),
(107, '2026_01_09_create_system_settings_table', 15),
(108, '2026_01_09_normalize_difficulty_levels', 15),
(109, '2026_01_09_000000_add_icon_to_fabric_types', 16),
(110, '2026_01_09_000001_create_intended_uses_table', 17),
(112, '2026_01_10_033813_add_production_days_to_yakan_patterns', 18),
(113, '2026_01_10_add_pattern_price_to_yakan_patterns', 19),
(114, '2026_01_10_add_price_per_meter_to_patterns', 20);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `data`, `url`, `is_read`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #1 has been submitted successfully and is now pending admin review.', '{\"order_id\":1,\"order_name\":\"Custom Order #1\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/1', 0, NULL, '2026-01-05 13:52:02', '2026-01-05 13:52:02'),
(2, 4, 'custom_order', 'New Custom Order', 'A new custom order #1 has been submitted by Yakan User.', '{\"order_id\":1,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #1\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-05 13:52:02', '2026-01-05 13:52:02'),
(3, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #2 has been submitted successfully and is now pending admin review.', '{\"order_id\":2,\"order_name\":\"Custom Order #2\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/2', 0, NULL, '2026-01-05 15:22:33', '2026-01-05 15:22:33'),
(4, 4, 'custom_order', 'New Custom Order', 'A new custom order #2 has been submitted by Yakan User.', '{\"order_id\":2,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #2\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-05 15:22:33', '2026-01-05 15:22:33'),
(5, 3, 'custom_order', 'Custom Order #2 - Production complete', 'Your custom order status has been updated to production_complete.', '{\"order_id\":2,\"old_status\":\"in_production\",\"new_status\":\"production_complete\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/2', 0, NULL, '2026-01-05 18:03:19', '2026-01-05 18:03:19'),
(6, 3, 'custom_order', 'Custom Order #2 - Out for delivery', 'Your custom order status has been updated to out_for_delivery.', '{\"order_id\":2,\"old_status\":\"production_complete\",\"new_status\":\"out_for_delivery\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/2', 0, NULL, '2026-01-05 18:03:52', '2026-01-05 18:03:52'),
(7, 3, 'custom_order', 'Custom Order #2 - Delivered', 'Your custom order status has been updated to delivered.', '{\"order_id\":2,\"old_status\":\"out_for_delivery\",\"new_status\":\"delivered\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/2', 0, NULL, '2026-01-05 18:04:11', '2026-01-05 18:04:11'),
(8, 3, 'order', 'Order Placed Successfully', 'Your order #4 has been placed successfully! Total amount: ₱530.00', '{\"order_id\":4,\"tracking_number\":\"YAK-DBKXVGDWVX\",\"total_amount\":530,\"payment_method\":\"gcash\"}', 'http://127.0.0.1:8000/orders/4', 0, NULL, '2026-01-06 10:55:07', '2026-01-06 10:55:07'),
(9, 4, 'order', 'New Order Received', 'A new order #4 has been placed by Yakan User. Amount: ₱530.00', '{\"order_id\":4,\"customer_name\":\"Yakan User\",\"tracking_number\":\"YAK-DBKXVGDWVX\",\"total_amount\":530,\"payment_method\":\"gcash\"}', 'http://127.0.0.1:8000/admin/orders', 0, NULL, '2026-01-06 10:55:07', '2026-01-06 10:55:07'),
(10, 3, 'order', 'Order Placed Successfully', 'Your order #5 has been placed successfully! Total amount: ₱430.00', '{\"order_id\":5,\"tracking_number\":\"YAK-GD2SPSB1UY\",\"total_amount\":430,\"payment_method\":\"bank_transfer\"}', 'http://127.0.0.1:8000/orders/5', 0, NULL, '2026-01-06 11:07:18', '2026-01-06 11:07:18'),
(11, 4, 'order', 'New Order Received', 'A new order #5 has been placed by Yakan User. Amount: ₱430.00', '{\"order_id\":5,\"customer_name\":\"Yakan User\",\"tracking_number\":\"YAK-GD2SPSB1UY\",\"total_amount\":430,\"payment_method\":\"bank_transfer\"}', 'http://127.0.0.1:8000/admin/orders', 0, NULL, '2026-01-06 11:07:18', '2026-01-06 11:07:18'),
(12, 3, 'order', 'Order Placed Successfully', 'Your order #6 has been placed successfully! Total amount: ₱330.00', '{\"order_id\":6,\"tracking_number\":\"YAK-SO6FQWSAEJ\",\"total_amount\":330,\"payment_method\":\"gcash\"}', 'http://127.0.0.1:8000/orders/6', 0, NULL, '2026-01-06 12:45:14', '2026-01-06 12:45:14'),
(13, 4, 'order', 'New Order Received', 'A new order #6 has been placed by Yakan User. Amount: ₱330.00', '{\"order_id\":6,\"customer_name\":\"Yakan User\",\"tracking_number\":\"YAK-SO6FQWSAEJ\",\"total_amount\":330,\"payment_method\":\"gcash\"}', 'http://127.0.0.1:8000/admin/orders', 0, NULL, '2026-01-06 12:45:14', '2026-01-06 12:45:14'),
(14, 3, 'payment', 'GCash payment verified', 'Your GCash payment for order #6 has been verified. Your order is now being processed!', '{\"order_id\":6,\"payment_method\":\"gcash\",\"payment_status\":\"paid\"}', 'http://127.0.0.1:8000/orders/6', 0, NULL, '2026-01-06 12:45:28', '2026-01-06 12:45:28'),
(15, 4, 'payment', 'Payment Received', 'Payment received for order #6 via GCash. Amount: ₱330.00', '{\"order_id\":6,\"payment_method\":\"gcash\",\"payment_status\":\"paid\"}', 'http://127.0.0.1:8000/admin/orders/6', 0, NULL, '2026-01-06 12:45:28', '2026-01-06 12:45:28'),
(16, 3, 'payment', 'Bank payment verified', 'Your bank payment for order #5 has been verified. Your order is now being processed!', '{\"order_id\":5,\"payment_method\":\"bank_transfer\",\"payment_status\":\"verified\"}', 'http://127.0.0.1:8000/orders/5', 0, NULL, '2026-01-06 13:00:26', '2026-01-06 13:00:26'),
(17, 4, 'payment', 'Payment Received', 'Payment received for order #5 via Bank Transfer. Amount: ₱430.00', '{\"order_id\":5,\"payment_method\":\"bank_transfer\",\"payment_status\":\"verified\"}', 'http://127.0.0.1:8000/admin/orders/5', 0, NULL, '2026-01-06 13:00:26', '2026-01-06 13:00:26'),
(18, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #3 has been submitted successfully and is now pending admin review.', '{\"order_id\":3,\"order_name\":\"Custom Order #3\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/3', 0, NULL, '2026-01-06 14:01:17', '2026-01-06 14:01:17'),
(19, 4, 'custom_order', 'New Custom Order', 'A new custom order #3 has been submitted by Yakan User.', '{\"order_id\":3,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #3\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-06 14:01:17', '2026-01-06 14:01:17'),
(20, 3, 'custom_order', 'Custom Order #3 - In production', 'Your custom order status has been updated to in_production.', '{\"order_id\":3,\"old_status\":\"approved\",\"new_status\":\"in_production\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/3', 0, NULL, '2026-01-06 14:15:16', '2026-01-06 14:15:16'),
(21, 3, 'custom_order', 'Custom Order #3 - Production complete', 'Your custom order status has been updated to production_complete.', '{\"order_id\":3,\"old_status\":\"in_production\",\"new_status\":\"production_complete\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/3', 0, NULL, '2026-01-06 14:15:30', '2026-01-06 14:15:30'),
(22, 3, 'custom_order', 'Custom Order #3 - Out for delivery', 'Your custom order status has been updated to out_for_delivery.', '{\"order_id\":3,\"old_status\":\"production_complete\",\"new_status\":\"out_for_delivery\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/3', 0, NULL, '2026-01-06 14:15:43', '2026-01-06 14:15:43'),
(23, 3, 'custom_order', 'Custom Order #3 - Delivered', 'Your custom order status has been updated to delivered.', '{\"order_id\":3,\"old_status\":\"out_for_delivery\",\"new_status\":\"delivered\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/3', 0, NULL, '2026-01-06 14:16:13', '2026-01-06 14:16:13'),
(24, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #4 has been submitted successfully and is now pending admin review.', '{\"order_id\":4,\"order_name\":\"Custom Order #4\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/4', 0, NULL, '2026-01-06 14:17:57', '2026-01-06 14:17:57'),
(25, 4, 'custom_order', 'New Custom Order', 'A new custom order #4 has been submitted by Yakan User.', '{\"order_id\":4,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #4\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-06 14:17:57', '2026-01-06 14:17:57'),
(26, 3, 'custom_order', 'Custom Order #4 - In production', 'Your custom order status has been updated to in_production.', '{\"order_id\":4,\"old_status\":\"approved\",\"new_status\":\"in_production\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/4', 0, NULL, '2026-01-06 14:57:19', '2026-01-06 14:57:19'),
(27, 3, 'custom_order', 'Custom Order #4 - Production complete', 'Your custom order status has been updated to production_complete.', '{\"order_id\":4,\"old_status\":\"in_production\",\"new_status\":\"production_complete\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/4', 0, NULL, '2026-01-06 14:57:30', '2026-01-06 14:57:30'),
(28, 3, 'custom_order', 'Custom Order #4 - Out for delivery', 'Your custom order status has been updated to out_for_delivery.', '{\"order_id\":4,\"old_status\":\"production_complete\",\"new_status\":\"out_for_delivery\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/4', 0, NULL, '2026-01-06 14:57:40', '2026-01-06 14:57:40'),
(29, 3, 'custom_order', 'Custom Order #4 - Delivered', 'Your custom order status has been updated to delivered.', '{\"order_id\":4,\"old_status\":\"out_for_delivery\",\"new_status\":\"delivered\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/4', 0, NULL, '2026-01-06 14:57:59', '2026-01-06 14:57:59'),
(30, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #5 has been submitted successfully and is now pending admin review.', '{\"order_id\":5,\"order_name\":\"Custom Order #5\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/5', 0, NULL, '2026-01-07 16:23:11', '2026-01-07 16:23:11'),
(31, 4, 'custom_order', 'New Custom Order', 'A new custom order #5 has been submitted by Yakan User.', '{\"order_id\":5,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #5\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-07 16:23:11', '2026-01-07 16:23:11'),
(32, 3, 'custom_order', 'Custom Order #5 - In production', 'Your custom order status has been updated to in_production.', '{\"order_id\":5,\"old_status\":\"approved\",\"new_status\":\"in_production\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/5', 0, NULL, '2026-01-07 16:36:26', '2026-01-07 16:36:26'),
(33, 3, 'custom_order', 'Custom Order #5 - Production complete', 'Your custom order status has been updated to production_complete.', '{\"order_id\":5,\"old_status\":\"in_production\",\"new_status\":\"production_complete\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/5', 0, NULL, '2026-01-07 16:36:38', '2026-01-07 16:36:38'),
(34, 3, 'custom_order', 'Custom Order #5 - Out for delivery', 'Your custom order status has been updated to out_for_delivery.', '{\"order_id\":5,\"old_status\":\"production_complete\",\"new_status\":\"out_for_delivery\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/5', 0, NULL, '2026-01-07 16:36:47', '2026-01-07 16:36:47'),
(35, 3, 'custom_order', 'Custom Order #5 - Delivered', 'Your custom order status has been updated to delivered.', '{\"order_id\":5,\"old_status\":\"out_for_delivery\",\"new_status\":\"delivered\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/5', 0, NULL, '2026-01-07 16:37:15', '2026-01-07 16:37:15'),
(36, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #6 has been submitted successfully and is now pending admin review.', '{\"order_id\":6,\"order_name\":\"Custom Order #6\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/6', 0, NULL, '2026-01-07 16:56:41', '2026-01-07 16:56:41'),
(37, 4, 'custom_order', 'New Custom Order', 'A new custom order #6 has been submitted by Yakan User.', '{\"order_id\":6,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #6\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-07 16:56:41', '2026-01-07 16:56:41'),
(38, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #7 has been submitted successfully and is now pending admin review.', '{\"order_id\":7,\"order_name\":\"Custom Order #7\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/7', 0, NULL, '2026-01-07 17:06:51', '2026-01-07 17:06:51'),
(39, 4, 'custom_order', 'New Custom Order', 'A new custom order #7 has been submitted by Yakan User.', '{\"order_id\":7,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #7\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-07 17:06:51', '2026-01-07 17:06:51'),
(40, 3, 'custom_order', 'Custom Order #7 - In production', 'Your custom order status has been updated to in_production.', '{\"order_id\":7,\"old_status\":\"approved\",\"new_status\":\"in_production\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/7', 0, NULL, '2026-01-07 17:27:01', '2026-01-07 17:27:01'),
(41, 3, 'custom_order', 'Custom Order #7 - Production complete', 'Your custom order status has been updated to production_complete.', '{\"order_id\":7,\"old_status\":\"in_production\",\"new_status\":\"production_complete\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/7', 0, NULL, '2026-01-07 17:45:07', '2026-01-07 17:45:07'),
(42, 3, 'custom_order', 'Custom Order #7 - Out for delivery', 'Your custom order status has been updated to out_for_delivery.', '{\"order_id\":7,\"old_status\":\"production_complete\",\"new_status\":\"out_for_delivery\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/7', 0, NULL, '2026-01-07 17:55:42', '2026-01-07 17:55:42'),
(43, 3, 'custom_order', 'Custom Order #7 - Delivered', 'Your custom order status has been updated to delivered.', '{\"order_id\":7,\"old_status\":\"out_for_delivery\",\"new_status\":\"delivered\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/7', 0, NULL, '2026-01-07 17:55:53', '2026-01-07 17:55:53'),
(44, 3, 'order', 'Order Placed Successfully', 'Your order #7 has been placed successfully! Total amount: ₱50.00', '{\"order_id\":7,\"tracking_number\":\"YAK-S9NGEINH9W\",\"total_amount\":50,\"payment_method\":\"gcash\"}', 'http://127.0.0.1:8000/orders/7', 0, NULL, '2026-01-07 17:57:40', '2026-01-07 17:57:40'),
(45, 4, 'order', 'New Order Received', 'A new order #7 has been placed by Yakan User. Amount: ₱50.00', '{\"order_id\":7,\"customer_name\":\"Yakan User\",\"tracking_number\":\"YAK-S9NGEINH9W\",\"total_amount\":50,\"payment_method\":\"gcash\"}', 'http://127.0.0.1:8000/admin/orders', 0, NULL, '2026-01-07 17:57:40', '2026-01-07 17:57:40'),
(46, 3, 'payment', 'GCash payment verified', 'Your GCash payment for order #7 has been verified. Your order is now being processed!', '{\"order_id\":7,\"payment_method\":\"gcash\",\"payment_status\":\"paid\"}', 'http://127.0.0.1:8000/orders/7', 0, NULL, '2026-01-07 17:57:55', '2026-01-07 17:57:55'),
(47, 4, 'payment', 'Payment Received', 'Payment received for order #7 via GCash. Amount: ₱50.00', '{\"order_id\":7,\"payment_method\":\"gcash\",\"payment_status\":\"paid\"}', 'http://127.0.0.1:8000/admin/orders/7', 0, NULL, '2026-01-07 17:57:55', '2026-01-07 17:57:55'),
(48, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #8 has been submitted successfully and is now pending admin review.', '{\"order_id\":8,\"order_name\":\"Custom Order #8\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/8', 0, NULL, '2026-01-08 06:10:23', '2026-01-08 06:10:23'),
(49, 4, 'custom_order', 'New Custom Order', 'A new custom order #8 has been submitted by Yakan User.', '{\"order_id\":8,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #8\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-08 06:10:23', '2026-01-08 06:10:23'),
(50, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #9 has been submitted successfully and is now pending admin review.', '{\"order_id\":9,\"order_name\":\"Custom Order #9\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/9', 0, NULL, '2026-01-08 10:56:18', '2026-01-08 10:56:18'),
(51, 4, 'custom_order', 'New Custom Order', 'A new custom order #9 has been submitted by Yakan User.', '{\"order_id\":9,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #9\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-08 10:56:18', '2026-01-08 10:56:18'),
(52, 3, 'custom_order', 'Custom Order #9 - In production', 'Your custom order status has been updated to in_production.', '{\"order_id\":9,\"old_status\":\"approved\",\"new_status\":\"in_production\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/9', 0, NULL, '2026-01-08 11:17:05', '2026-01-08 11:17:05'),
(53, 3, 'custom_order', 'Custom Order #9 - Production complete', 'Your custom order status has been updated to production_complete.', '{\"order_id\":9,\"old_status\":\"in_production\",\"new_status\":\"production_complete\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/9', 0, NULL, '2026-01-08 11:17:15', '2026-01-08 11:17:15'),
(54, 3, 'custom_order', 'Custom Order #9 - Out for delivery', 'Your custom order status has been updated to out_for_delivery.', '{\"order_id\":9,\"old_status\":\"production_complete\",\"new_status\":\"out_for_delivery\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/9', 0, NULL, '2026-01-08 11:17:31', '2026-01-08 11:17:31'),
(55, 3, 'custom_order', 'Custom Order #9 - Delivered', 'Your custom order status has been updated to delivered.', '{\"order_id\":9,\"old_status\":\"out_for_delivery\",\"new_status\":\"delivered\",\"final_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/9', 0, NULL, '2026-01-08 11:17:44', '2026-01-08 11:17:44'),
(56, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #10 has been submitted successfully and is now pending admin review.', '{\"order_id\":10,\"order_name\":\"Custom Order #10\",\"estimated_price\":\"3800.00\"}', 'http://127.0.0.1:8000/custom-orders/10', 0, NULL, '2026-01-09 09:04:56', '2026-01-09 09:04:56'),
(57, 4, 'custom_order', 'New Custom Order', 'A new custom order #10 has been submitted by Yakan User.', '{\"order_id\":10,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #10\",\"estimated_price\":\"3800.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-09 09:04:56', '2026-01-09 09:04:56'),
(58, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #11 has been submitted successfully and is now pending admin review.', '{\"order_id\":11,\"order_name\":\"Custom Order #11\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/11', 0, NULL, '2026-01-09 09:20:46', '2026-01-09 09:20:46'),
(59, 4, 'custom_order', 'New Custom Order', 'A new custom order #11 has been submitted by Yakan User.', '{\"order_id\":11,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #11\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-09 09:20:46', '2026-01-09 09:20:46'),
(60, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #12 has been submitted successfully and is now pending admin review.', '{\"order_id\":12,\"order_name\":\"Custom Order #12\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/12', 0, NULL, '2026-01-09 21:24:30', '2026-01-09 21:24:30'),
(61, 4, 'custom_order', 'New Custom Order', 'A new custom order #12 has been submitted by Yakan User.', '{\"order_id\":12,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #12\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-09 21:24:30', '2026-01-09 21:24:30'),
(62, 5, 'custom_order', 'New Custom Order', 'A new custom order #12 has been submitted by Yakan User.', '{\"order_id\":12,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #12\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-09 21:24:30', '2026-01-09 21:24:30'),
(63, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #13 has been submitted successfully and is now pending admin review.', '{\"order_id\":13,\"order_name\":\"Custom Order #13\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/13', 0, NULL, '2026-01-09 22:33:46', '2026-01-09 22:33:46'),
(64, 4, 'custom_order', 'New Custom Order', 'A new custom order #13 has been submitted by Yakan User.', '{\"order_id\":13,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #13\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-09 22:33:46', '2026-01-09 22:33:46'),
(65, 5, 'custom_order', 'New Custom Order', 'A new custom order #13 has been submitted by Yakan User.', '{\"order_id\":13,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #13\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-09 22:33:46', '2026-01-09 22:33:46'),
(66, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #14 has been submitted successfully and is now pending admin review.', '{\"order_id\":14,\"order_name\":\"Custom Order #14\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/custom-orders/14', 0, NULL, '2026-01-09 22:36:09', '2026-01-09 22:36:09'),
(67, 4, 'custom_order', 'New Custom Order', 'A new custom order #14 has been submitted by Yakan User.', '{\"order_id\":14,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #14\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-09 22:36:09', '2026-01-09 22:36:09'),
(68, 5, 'custom_order', 'New Custom Order', 'A new custom order #14 has been submitted by Yakan User.', '{\"order_id\":14,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #14\",\"estimated_price\":\"2300.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-09 22:36:09', '2026-01-09 22:36:09'),
(69, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #15 has been submitted successfully and is now pending admin review.', '{\"order_id\":15,\"order_name\":\"Custom Order #15\",\"estimated_price\":\"1800.00\"}', 'http://127.0.0.1:8000/custom-orders/15', 0, NULL, '2026-01-09 23:45:11', '2026-01-09 23:45:11'),
(70, 4, 'custom_order', 'New Custom Order', 'A new custom order #15 has been submitted by Yakan User.', '{\"order_id\":15,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #15\",\"estimated_price\":\"1800.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-09 23:45:11', '2026-01-09 23:45:11'),
(71, 5, 'custom_order', 'New Custom Order', 'A new custom order #15 has been submitted by Yakan User.', '{\"order_id\":15,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #15\",\"estimated_price\":\"1800.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-09 23:45:11', '2026-01-09 23:45:11'),
(72, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #19 has been submitted successfully and is now pending admin review.', '{\"order_id\":19,\"order_name\":\"Custom Order #19\",\"estimated_price\":\"1800.00\"}', 'http://127.0.0.1:8000/custom-orders/19', 0, NULL, '2026-01-10 00:27:30', '2026-01-10 00:27:30'),
(73, 4, 'custom_order', 'New Custom Order', 'A new custom order #19 has been submitted by Yakan User.', '{\"order_id\":19,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #19\",\"estimated_price\":\"1800.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-10 00:27:30', '2026-01-10 00:27:30'),
(74, 5, 'custom_order', 'New Custom Order', 'A new custom order #19 has been submitted by Yakan User.', '{\"order_id\":19,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #19\",\"estimated_price\":\"1800.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-10 00:27:30', '2026-01-10 00:27:30'),
(75, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #20 has been submitted successfully and is now pending admin review.', '{\"order_id\":20,\"order_name\":\"Custom Order #20\",\"estimated_price\":\"7000.00\"}', 'http://127.0.0.1:8000/custom-orders/20', 0, NULL, '2026-01-10 02:17:47', '2026-01-10 02:17:47'),
(76, 4, 'custom_order', 'New Custom Order', 'A new custom order #20 has been submitted by Yakan User.', '{\"order_id\":20,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #20\",\"estimated_price\":\"7000.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-10 02:17:47', '2026-01-10 02:17:47'),
(77, 5, 'custom_order', 'New Custom Order', 'A new custom order #20 has been submitted by Yakan User.', '{\"order_id\":20,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #20\",\"estimated_price\":\"7000.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-10 02:17:47', '2026-01-10 02:17:47'),
(78, 3, 'order', 'Order Placed Successfully', 'Your order #11 has been placed successfully! Total amount: ₱455.00', '{\"order_id\":11,\"tracking_number\":\"YAK-DBWXVNQLQ9\",\"total_amount\":455,\"payment_method\":\"gcash\"}', 'http://127.0.0.1:8000/orders/11', 0, NULL, '2026-01-10 02:45:53', '2026-01-10 02:45:53'),
(79, 4, 'order', 'New Order Received', 'A new order #11 has been placed by Yakan User. Amount: ₱455.00', '{\"order_id\":11,\"customer_name\":\"Yakan User\",\"tracking_number\":\"YAK-DBWXVNQLQ9\",\"total_amount\":455,\"payment_method\":\"gcash\"}', 'http://127.0.0.1:8000/admin/orders', 0, NULL, '2026-01-10 02:45:53', '2026-01-10 02:45:53'),
(80, 5, 'order', 'New Order Received', 'A new order #11 has been placed by Yakan User. Amount: ₱455.00', '{\"order_id\":11,\"customer_name\":\"Yakan User\",\"tracking_number\":\"YAK-DBWXVNQLQ9\",\"total_amount\":455,\"payment_method\":\"gcash\"}', 'http://127.0.0.1:8000/admin/orders', 0, NULL, '2026-01-10 02:45:53', '2026-01-10 02:45:53'),
(81, 3, 'payment', 'GCash payment verified', 'Your GCash payment for order #11 has been verified. Your order is now being processed!', '{\"order_id\":11,\"payment_method\":\"gcash\",\"payment_status\":\"paid\"}', 'http://127.0.0.1:8000/orders/11', 0, NULL, '2026-01-10 02:46:17', '2026-01-10 02:46:17'),
(82, 4, 'payment', 'Payment Received', 'Payment received for order #11 via GCash. Amount: ₱455.00', '{\"order_id\":11,\"payment_method\":\"gcash\",\"payment_status\":\"paid\"}', 'http://127.0.0.1:8000/admin/orders/11', 0, NULL, '2026-01-10 02:46:17', '2026-01-10 02:46:17'),
(83, 5, 'payment', 'Payment Received', 'Payment received for order #11 via GCash. Amount: ₱455.00', '{\"order_id\":11,\"payment_method\":\"gcash\",\"payment_status\":\"paid\"}', 'http://127.0.0.1:8000/admin/orders/11', 0, NULL, '2026-01-10 02:46:17', '2026-01-10 02:46:17'),
(84, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #21 has been submitted successfully and is now pending admin review.', '{\"order_id\":21,\"order_name\":\"Custom Order #21\",\"estimated_price\":\"4000.00\"}', 'http://127.0.0.1:8000/custom-orders/21', 0, NULL, '2026-01-10 04:05:42', '2026-01-10 04:05:42'),
(85, 4, 'custom_order', 'New Custom Order', 'A new custom order #21 has been submitted by Yakan User.', '{\"order_id\":21,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #21\",\"estimated_price\":\"4000.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-10 04:05:43', '2026-01-10 04:05:43'),
(86, 5, 'custom_order', 'New Custom Order', 'A new custom order #21 has been submitted by Yakan User.', '{\"order_id\":21,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #21\",\"estimated_price\":\"4000.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-10 04:05:43', '2026-01-10 04:05:43'),
(87, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #22 has been submitted successfully and is now pending admin review.', '{\"order_id\":22,\"order_name\":\"Custom Order #22\",\"estimated_price\":\"5000.00\"}', 'http://127.0.0.1:8000/custom-orders/22', 0, NULL, '2026-01-10 04:20:09', '2026-01-10 04:20:09'),
(88, 4, 'custom_order', 'New Custom Order', 'A new custom order #22 has been submitted by Yakan User.', '{\"order_id\":22,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #22\",\"estimated_price\":\"5000.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-10 04:20:09', '2026-01-10 04:20:09'),
(89, 5, 'custom_order', 'New Custom Order', 'A new custom order #22 has been submitted by Yakan User.', '{\"order_id\":22,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #22\",\"estimated_price\":\"5000.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-10 04:20:09', '2026-01-10 04:20:09'),
(90, 3, 'custom_order', 'Custom Order Submitted', 'Your custom order #23 has been submitted successfully and is now pending admin review.', '{\"order_id\":23,\"order_name\":\"Custom Order #23\",\"estimated_price\":\"5000.00\"}', 'http://127.0.0.1:8000/custom-orders/23', 0, NULL, '2026-01-10 04:27:45', '2026-01-10 04:27:45'),
(91, 4, 'custom_order', 'New Custom Order', 'A new custom order #23 has been submitted by Yakan User.', '{\"order_id\":23,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #23\",\"estimated_price\":\"5000.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-10 04:27:45', '2026-01-10 04:27:45'),
(92, 5, 'custom_order', 'New Custom Order', 'A new custom order #23 has been submitted by Yakan User.', '{\"order_id\":23,\"customer_name\":\"Yakan User\",\"order_name\":\"Custom Order #23\",\"estimated_price\":\"5000.00\"}', 'http://127.0.0.1:8000/admin/custom-orders', 0, NULL, '2026-01-10 04:27:45', '2026-01-10 04:27:45');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_ref` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_address_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(255) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_type` enum('pickup','deliver') NOT NULL DEFAULT 'deliver',
  `shipping_address` text NOT NULL,
  `shipping_city` varchar(255) DEFAULT NULL,
  `shipping_province` varchar(255) DEFAULT NULL,
  `payment_method` enum('gcash','bank_transfer','cash') NOT NULL DEFAULT 'gcash',
  `payment_proof_path` varchar(255) DEFAULT NULL,
  `bank_receipt` varchar(255) DEFAULT NULL,
  `gcash_receipt` varchar(255) DEFAULT NULL,
  `payment_status` enum('pending','paid','verified','failed') NOT NULL DEFAULT 'pending',
  `payment_reference` varchar(255) DEFAULT NULL,
  `payment_verified_at` datetime DEFAULT NULL,
  `status` enum('pending_confirmation','confirmed','processing','shipped','delivered','completed','cancelled','refunded') NOT NULL DEFAULT 'pending_confirmation',
  `notes` text DEFAULT NULL,
  `customer_notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `source` varchar(255) NOT NULL DEFAULT 'mobile',
  `confirmed_at` datetime DEFAULT NULL,
  `shipped_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tracking_number` varchar(255) DEFAULT NULL,
  `tracking_status` varchar(255) NOT NULL DEFAULT 'Order Placed',
  `courier_name` varchar(255) DEFAULT NULL,
  `courier_contact` varchar(255) DEFAULT NULL,
  `courier_tracking_url` varchar(255) DEFAULT NULL,
  `estimated_delivery_date` date DEFAULT NULL,
  `tracking_notes` text DEFAULT NULL,
  `tracking_history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tracking_history`)),
  `coupon_id` bigint(20) UNSIGNED DEFAULT NULL,
  `coupon_code` varchar(255) DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_address` varchar(255) DEFAULT NULL,
  `delivery_latitude` decimal(10,8) DEFAULT NULL,
  `delivery_longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_ref`, `user_id`, `user_address_id`, `customer_name`, `customer_email`, `customer_phone`, `subtotal`, `shipping_fee`, `discount`, `total`, `total_amount`, `delivery_type`, `shipping_address`, `shipping_city`, `shipping_province`, `payment_method`, `payment_proof_path`, `bank_receipt`, `gcash_receipt`, `payment_status`, `payment_reference`, `payment_verified_at`, `status`, `notes`, `customer_notes`, `admin_notes`, `source`, `confirmed_at`, `shipped_at`, `delivered_at`, `cancelled_at`, `created_at`, `updated_at`, `tracking_number`, `tracking_status`, `courier_name`, `courier_contact`, `courier_tracking_url`, `estimated_delivery_date`, `tracking_notes`, `tracking_history`, `coupon_id`, `coupon_code`, `discount_amount`, `delivery_address`, `delivery_latitude`, `delivery_longitude`) VALUES
(4, 'ORD-20260106-001', 3, NULL, 'Yakan User', 'user@yakan.com', '', 250.00, 280.00, 0.00, 530.00, 530.00, 'deliver', 'City Hall, Brgy. Caridad, Cavite City, Cavite, 4100', 'Cavite City', 'Cavite', 'gcash', NULL, NULL, NULL, 'paid', NULL, NULL, 'processing', NULL, NULL, NULL, 'mobile', NULL, NULL, NULL, NULL, '2026-01-06 10:55:07', '2026-01-06 12:07:40', 'YAK-DBKXVGDWVX', 'Processing', NULL, NULL, NULL, NULL, NULL, '[{\"status\":\"Processing\",\"date\":\"2026-01-06 08:07 PM\"}]', NULL, NULL, 0.00, 'City Hall, Brgy. Caridad, Cavite City, Cavite, 4100', NULL, NULL),
(5, 'ORD-20260106-002', 3, NULL, 'Yakan User', 'user@yakan.com', '', 150.00, 280.00, 0.00, 430.00, 430.00, 'deliver', 'City Hall, Brgy. Caridad, Cavite City, Cavite, 4100', 'Cavite City', 'Cavite', 'bank_transfer', NULL, 'bank_receipts/dnAMU7bVUA7C61GQCVocpw8KZ8KntovaYHZYiMeN.jpg', NULL, 'verified', NULL, NULL, 'processing', NULL, NULL, NULL, 'mobile', NULL, NULL, NULL, NULL, '2026-01-06 11:07:18', '2026-01-06 13:00:26', 'YAK-GD2SPSB1UY', 'Order Placed', NULL, NULL, NULL, NULL, NULL, '[{\"status\":\"Bank receipt uploaded - Pending verification\",\"date\":\"2026-01-06 09:00 PM\"}]', NULL, NULL, 0.00, 'City Hall, Brgy. Caridad, Cavite City, Cavite, 4100', NULL, NULL),
(6, 'ORD-20260106-003', 3, 2, 'Yakan User', 'user@yakan.com', '', 50.00, 280.00, 0.00, 330.00, 330.00, 'deliver', 'City Hall, Brgy. Caridad, Cavite City, Cavite, 4100', 'Cavite City', 'Cavite', 'gcash', NULL, NULL, 'payment_proofs/1767703528_695d03e867de1.jpg', 'paid', NULL, '2026-01-06 20:45:28', 'delivered', NULL, NULL, NULL, 'mobile', NULL, NULL, NULL, NULL, '2026-01-06 12:45:14', '2026-01-06 17:21:42', 'YAK-SO6FQWSAEJ', 'Delivered', NULL, NULL, NULL, NULL, NULL, '[{\"status\":\"Order Placed\",\"date\":\"2026-01-06 08:45 PM\"},{\"status\":\"Payment verified via GCash (Ref: 11111111112345)\",\"date\":\"2026-01-06 08:45 PM\"},{\"status\":\"Shipped\",\"date\":\"2026-01-06 09:53 PM\"},{\"status\":\"Delivered\",\"date\":\"2026-01-07 01:21 AM\"}]', NULL, NULL, 0.00, 'City Hall, Brgy. Caridad, Cavite City, Cavite, 4100', NULL, NULL),
(7, 'ORD-20260108-001', 3, 1, 'Yakan User', 'user@yakan.com', '', 50.00, 0.00, 0.00, 50.00, 50.00, 'deliver', 'RRM Perez Drive Sun street, Brgy. Tumaga, Zamboanga City, Zamboanga del Sur, 7000', 'Zamboanga City', 'Zamboanga del Sur', 'gcash', NULL, NULL, 'payment_proofs/1767808675_695e9ea320053.jpg', 'paid', NULL, '2026-01-08 01:57:55', 'completed', NULL, NULL, NULL, 'mobile', NULL, NULL, '2026-01-08 02:13:08', NULL, '2026-01-07 17:57:39', '2026-01-07 22:36:40', 'YAK-S9NGEINH9W', 'Delivered', NULL, NULL, NULL, NULL, NULL, '[{\"status\":\"Order Placed\",\"date\":\"2026-01-08 01:57 AM\"},{\"status\":\"Payment verified via GCash (Ref: 11111111112345)\",\"date\":\"2026-01-08 01:57 AM\"},{\"status\":\"Shipped\",\"date\":\"2026-01-08 02:06 AM\"},{\"status\":\"Delivered\",\"date\":\"2026-01-08 02:06 AM\"}]', NULL, NULL, 0.00, 'RRM Perez Drive Sun street, Brgy. Tumaga, Zamboanga City, Zamboanga del Sur, 7000', NULL, NULL),
(9, 'ORD-695EC1C6C8652', 3, NULL, 'Yakan User', 'user@yakan.com', '09656923753', 50.00, 220.00, 0.00, NULL, 270.00, 'deliver', 'P. Zamora Street, Cavite, Cavite City 4100', NULL, NULL, 'bank_transfer', 'payment_proofs/7P59MtjyP6LUKAx0jxpTaZXnNWdu3cS7fDUs9ewq.png', NULL, NULL, 'paid', NULL, NULL, 'completed', 'Order from mobile app', NULL, NULL, 'mobile', NULL, NULL, NULL, NULL, '2026-01-07 20:27:50', '2026-01-07 21:55:13', 'ORD-695EC1C6C8652', 'Delivered', NULL, NULL, NULL, NULL, NULL, '[{\"status\":\"Shipped\",\"date\":\"2026-01-08 05:46 AM\"},{\"status\":\"Delivered\",\"date\":\"2026-01-08 05:46 AM\"}]', NULL, NULL, 0.00, 'P. Zamora Street, Cavite, Cavite City 4100', NULL, NULL),
(10, 'ORD-6960B648660C6', 3, NULL, 'Yakan User', 'mobile@user.com', '09656923753', 50.00, 220.00, 0.00, NULL, 270.00, 'deliver', 'P. Zamora Street, Cavite, Cavite City 4100', NULL, NULL, 'gcash', 'payment_proofs/REC72Uz4V2TVwL98sv09GzWgv7UHJySzFHeEDWhW.png', NULL, NULL, 'paid', '1223456666', NULL, 'completed', 'Order from mobile app', NULL, NULL, 'mobile', NULL, NULL, NULL, NULL, '2026-01-09 08:03:20', '2026-01-09 08:04:40', 'ORD-6960B648660C6', 'Delivered', NULL, NULL, NULL, NULL, NULL, '[{\"status\":\"Shipped\",\"date\":\"2026-01-09 04:04 PM\"},{\"status\":\"Delivered\",\"date\":\"2026-01-09 04:04 PM\"}]', NULL, NULL, 0.00, 'P. Zamora Street, Cavite, Cavite City 4100', NULL, NULL),
(11, 'ORD-20260110-001', 3, 4, 'Yakan User', 'user@yakan.com', '', 175.00, 280.00, 0.00, 455.00, 455.00, 'deliver', 'Padayhag 1, Brgy. San Carlos, Pagadian City, Tukuran, 7019', 'Pagadian City', 'Tukuran', 'gcash', NULL, NULL, 'payment_proofs/1768013176_6961bd78ee3ca.jpg', 'paid', NULL, '2026-01-10 10:46:17', 'processing', NULL, NULL, NULL, 'mobile', NULL, NULL, NULL, NULL, '2026-01-10 02:45:53', '2026-01-10 02:46:17', 'YAK-DBWXVNQLQ9', 'Order Placed', NULL, NULL, NULL, NULL, NULL, '[{\"status\":\"Order Placed\",\"date\":\"2026-01-10 10:45 AM\"},{\"status\":\"Payment verified via GCash (Ref: 11111111112345)\",\"date\":\"2026-01-10 10:46 AM\"}]', NULL, NULL, 0.00, 'Padayhag 1, Brgy. San Carlos, Pagadian City, Tukuran, 7019', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
(1, 4, 2, 5, 50.00, NULL, NULL),
(2, 5, 2, 3, 50.00, NULL, NULL),
(3, 6, 1, 1, 50.00, NULL, NULL),
(4, 7, 3, 1, 50.00, NULL, NULL),
(6, 9, 2, 1, 50.00, NULL, NULL),
(7, 10, 1, 1, 50.00, NULL, NULL),
(8, 11, 1, 1, 50.00, NULL, NULL),
(9, 11, 2, 1, 50.00, NULL, NULL),
(10, 11, 9, 1, 75.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pattern_fabric_compatibility`
--

CREATE TABLE `pattern_fabric_compatibility` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `yakan_pattern_id` bigint(20) UNSIGNED NOT NULL,
  `fabric_type_id` bigint(20) UNSIGNED NOT NULL,
  `difficulty_level` enum('simple','medium','complex') NOT NULL DEFAULT 'medium',
  `price_multiplier` decimal(3,2) NOT NULL DEFAULT 1.00,
  `estimated_production_days` int(11) NOT NULL DEFAULT 14,
  `notes` text DEFAULT NULL,
  `is_recommended` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pattern_media`
--

CREATE TABLE `pattern_media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `yakan_pattern_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'image',
  `path` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pattern_media`
--

INSERT INTO `pattern_media` (`id`, `yakan_pattern_id`, `type`, `path`, `alt_text`, `sort_order`, `created_at`, `updated_at`) VALUES
(5, 19, 'svg', 'patterns/svg/test-1768016145.svg', 'test pattern', 0, '2026-01-10 03:35:45', '2026-01-10 03:35:45'),
(6, 20, 'svg', 'patterns/svg/zigzagggg-1768017594.svg', 'zigzagggg pattern', 0, '2026-01-10 03:59:54', '2026-01-10 03:59:54'),
(7, 21, 'svg', 'patterns/svg/test-1768018711.svg', 'test pattern', 0, '2026-01-10 04:18:31', '2026-01-10 04:18:31');

-- --------------------------------------------------------

--
-- Table structure for table `pattern_tags`
--

CREATE TABLE `pattern_tags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `color` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pattern_tag_pivot`
--

CREATE TABLE `pattern_tag_pivot` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `yakan_pattern_id` bigint(20) UNSIGNED NOT NULL,
  `pattern_tag_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(13, 'App\\Models\\User', 3, 'api-token', '229286c85853173c4bb6e45bbebb2b481b458b165007f2b68aa386f58e2c9e8e', '[\"*\"]', '2026-01-10 04:16:04', NULL, '2026-01-10 02:38:59', '2026-01-10 04:16:04');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `all_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`all_images`)),
  `sku` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `available_sizes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`available_sizes`)),
  `available_colors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`available_colors`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `category_id`, `image`, `all_images`, `sku`, `status`, `available_sizes`, `available_colors`, `created_at`, `updated_at`) VALUES
(1, 'Saputangan', 'The Saputangan is a square piece of woven cloth usually measuring no less than standard size with traditional Yakan patterns.', 50.00, 48, 1, '1767678543_0_saputangan.jpg', '[{\"path\":\"1767678543_0_saputangan.jpg\",\"color\":null,\"sort_order\":0}]', 'YKN-7261D155', 'active', NULL, NULL, '2026-01-05 11:57:26', '2026-01-10 02:45:53'),
(2, 'Pinantupan', 'Pinantupan uses simple patterns like flowers and diamonds and are also used for special occasions and traditional celebrations.', 50.00, 36, 2, '1767678492_0_y_pinantupan.jpg', '[{\"path\":\"1767678492_0_y_pinantupan.jpg\",\"color\":null,\"sort_order\":0}]', 'YKN-7261E03F', 'active', NULL, NULL, '2026-01-05 11:57:26', '2026-01-10 02:45:53'),
(3, 'Birey-Birey', 'Birey-birey is a traditional handwoven textile pattern that resembles the sections of rice fields.', 50.00, 39, 3, '1767678517_0_birey-birey.jpg', '[{\"path\":\"1767678517_0_birey-birey.jpg\",\"color\":null,\"sort_order\":0}]', 'YKN-7261EABA', 'active', NULL, NULL, '2026-01-05 11:57:26', '2026-01-07 17:57:40'),
(9, 'Yakan Authentic Slipper', 'fgjhgjhgfhsgfhghdgf', 75.00, 19, 5, '1767678598_0_slipper.jpg', '[{\"path\":\"1767678598_0_slipper.jpg\",\"color\":null,\"sort_order\":0}]', 'YKN-198185BD', 'active', NULL, NULL, '2026-01-05 18:23:20', '2026-01-10 02:45:53');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `color` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recent_views`
--

CREATE TABLE `recent_views` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `viewable_type` varchar(255) NOT NULL,
  `viewable_id` bigint(20) UNSIGNED NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `comment` text NOT NULL,
  `verified_purchase` tinyint(1) NOT NULL DEFAULT 1,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `is_approved` tinyint(1) NOT NULL DEFAULT 1,
  `rejection_reason` text DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `admin_response` text DEFAULT NULL,
  `admin_response_at` timestamp NULL DEFAULT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `helpful_count` int(11) NOT NULL DEFAULT 0,
  `unhelpful_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `custom_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_item_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'price_per_meter', '500', NULL, '2026-01-09 13:22:53', '2026-01-10 01:48:02'),
(2, 'pattern_fee_simple', '1200', NULL, '2026-01-09 13:22:53', '2026-01-09 14:47:40'),
(3, 'pattern_fee_medium', '1900', NULL, '2026-01-09 13:22:53', '2026-01-09 14:47:40'),
(4, 'pattern_fee_complex', '2500', NULL, '2026-01-09 13:22:53', '2026-01-09 14:47:40'),
(5, 'quality_check_days', '1', NULL, '2026-01-09 20:37:27', '2026-01-09 20:37:54'),
(6, 'shipping_days', '1', NULL, '2026-01-09 20:37:27', '2026-01-09 20:37:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `middle_initial` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `provider_id` varchar(255) DEFAULT NULL,
  `provider_token` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `otp_attempts` int(11) NOT NULL DEFAULT 0,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `first_name`, `last_name`, `middle_initial`, `email`, `provider`, `provider_id`, `provider_token`, `avatar`, `role`, `email_verified_at`, `otp_code`, `otp_expires_at`, `otp_attempts`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `last_login_at`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Demo User', NULL, NULL, NULL, 'user@example.com', NULL, NULL, NULL, NULL, 'user', '2026-01-05 11:45:51', NULL, NULL, 0, NULL, NULL, NULL, NULL, '$2y$12$K48jMtGHJT2Fv3JsdCsPfOl7pjLmbWahiEXjpR5FFheW7z8T8Z646', NULL, '2026-01-05 11:45:51', '2026-01-05 11:45:51', NULL),
(2, 'Test User', 'Test', 'User', NULL, 'test@example.com', NULL, NULL, NULL, NULL, 'user', '2026-01-05 12:10:13', NULL, NULL, 0, NULL, NULL, NULL, NULL, '$2y$12$YGhoTtEu.7qfLfY0Twysg.r8pXmULH.heftiNUZCUPy8eSTnOwJSS', NULL, '2026-01-05 11:45:57', '2026-01-05 11:45:57', NULL),
(3, 'Yakan User', 'Yakan', 'User', NULL, 'user@yakan.com', NULL, NULL, NULL, NULL, 'user', '2026-01-05 12:02:03', NULL, NULL, 0, NULL, NULL, NULL, NULL, '$2y$12$Q80iu2vkOubga4kl7LdaeeHb3XU02I8zkkmlRkesA47c5HFXYmZpO', NULL, '2026-01-05 12:02:03', '2026-01-05 12:02:03', NULL),
(4, 'Admin User', 'Admin', 'User', NULL, 'admin@yakan.com', NULL, NULL, NULL, NULL, 'admin', '2026-01-05 12:19:03', NULL, NULL, 0, NULL, NULL, NULL, NULL, '$2y$12$evfLwpNpq1bd1JCRltfQ4.M9rj7QuZUXzoWyunHZBh3ZLtLriUBym', NULL, '2026-01-05 12:19:03', '2026-01-05 12:19:03', NULL),
(5, 'Karil Jaslim', 'Karil', 'Jaslim', NULL, 'kariljaslem@gmail.com', NULL, NULL, NULL, NULL, 'admin', '2026-01-09 15:24:27', NULL, NULL, 0, NULL, NULL, NULL, NULL, '$2y$12$5G7muzoaQxLTjgmHAyepbO.tdo/WE0.IKOdn7Qp6D0ru.8LaknFti', NULL, '2026-01-09 15:24:28', '2026-01-09 15:24:28', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL DEFAULT 'Home',
  `full_name` varchar(255) NOT NULL,
  `phone_number` varchar(255) NOT NULL,
  `street` varchar(255) NOT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `province` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `label`, `full_name`, `phone_number`, `street`, `barangay`, `city`, `province`, `postal_code`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 3, 'Home', 'Yakan User', '09656923753', 'RRM Perez Drive Sun street', 'Tumaga', 'Zamboanga City', 'Zamboanga del Sur', '7000', 1, '2026-01-05 13:32:56', '2026-01-09 21:07:23'),
(4, 3, 'Home', 'Yakan User', '09544571603', 'Padayhag 1', 'San Carlos', 'Pagadian City', 'Tukuran', '7019', 0, '2026-01-09 08:33:14', '2026-01-09 21:07:23');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT 'My Wishlist',
  `is_default` tinyint(1) NOT NULL DEFAULT 1,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `name`, `is_default`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 3, 'My Wishlist', 1, 0, '2026-01-05 15:40:34', '2026-01-05 15:40:34'),
(2, 1, 'My Wishlist', 1, 0, '2026-01-06 18:07:39', '2026-01-06 18:07:39');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist_items`
--

CREATE TABLE `wishlist_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `wishlist_id` bigint(20) UNSIGNED NOT NULL,
  `item_type` varchar(255) NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlist_items`
--

INSERT INTO `wishlist_items` (`id`, `wishlist_id`, `item_type`, `item_id`, `created_at`, `updated_at`) VALUES
(2, 2, 'App\\Models\\Product', 1, '2026-01-06 18:07:39', '2026-01-06 18:07:39');

-- --------------------------------------------------------

--
-- Table structure for table `yakan_patterns`
--

CREATE TABLE `yakan_patterns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `difficulty_level` varchar(255) NOT NULL,
  `production_days` int(10) UNSIGNED NOT NULL DEFAULT 14,
  `pattern_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`pattern_data`)),
  `svg_path` varchar(255) DEFAULT NULL,
  `base_color` varchar(255) NOT NULL,
  `color_variations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`color_variations`)),
  `base_price_multiplier` decimal(10,2) NOT NULL DEFAULT 1.00,
  `pattern_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Individual price for this pattern',
  `price_per_meter` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `popularity_score` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `yakan_patterns`
--

INSERT INTO `yakan_patterns` (`id`, `name`, `description`, `category`, `difficulty_level`, `production_days`, `pattern_data`, `svg_path`, `base_color`, `color_variations`, `base_price_multiplier`, `pattern_price`, `price_per_meter`, `is_active`, `popularity_score`, `created_at`, `updated_at`) VALUES
(1, 'Sinaluan', 'The most sacred Yakan wedding pattern featuring intricate eight-point star (bita) motifs with sacred checkerboard backgrounds. Each star contains 16 smaller stars representing the extended family members. The pattern includes traditional diamond borders and zigzag lightning motifs representing protection from evil spirits. Woven by master weavers using natural dyes - turmeric yellow, indigo blue, mangosteen red, and charcoal black.', 'traditional', 'complex', 14, '\"<svg width=\\\"200\\\" height=\\\"200\\\" xmlns=\\\"http:\\/\\/www.w3.org\\/2000\\/svg\\\">\\r\\n        <defs>\\r\\n            <pattern id=\\\"sinaluan\\\" x=\\\"0\\\" y=\\\"0\\\" width=\\\"50\\\" height=\\\"50\\\" patternUnits=\\\"userSpaceOnUse\\\">\\r\\n                <rect width=\\\"50\\\" height=\\\"50\\\" fill=\\\"#7D1935\\\"\\/>\\r\\n                <polygon points=\\\"25,10 40,25 25,40 10,25\\\" fill=\\\"#E8C547\\\" stroke=\\\"#FFFFFF\\\" stroke-width=\\\"1\\\"\\/>\\r\\n                <polygon points=\\\"25,17 33,25 25,33 17,25\\\" fill=\\\"#2E1A47\\\"\\/>\\r\\n                <rect x=\\\"23\\\" y=\\\"23\\\" width=\\\"4\\\" height=\\\"4\\\" fill=\\\"#FFFFFF\\\"\\/>\\r\\n            <\\/pattern>\\r\\n        <\\/defs>\\r\\n        <rect width=\\\"200\\\" height=\\\"200\\\" fill=\\\"url(#sinaluan)\\\"\\/>\\r\\n    <\\/svg>\"', NULL, '#8B0000', '[\"#8B0000,#FFD700,#4B0082,#FFFFFF\",\"#4B0082,#FFD700,#8B0000,#FFFFFF\",\"#228B22,#FFD700,#8B0000,#FFFFFF\"]', 2600.00, 0.00, NULL, 1, 25, '2026-01-05 12:25:37', '2026-01-09 19:48:31'),
(2, 'Bunga Sama', 'Authentic Yakan floral pattern featuring sacred ylang-ylang and sampaguita flowers with eight petals each, representing the eight directions of Yakan cosmology. Each flower contains a central star motif and is surrounded by smaller flower buds. The pattern includes traditional leaf vines and diamond connectors that symbolize the interconnectedness of all living things in Basilan.', 'traditional', 'complex', 14, '\"<svg width=\\\"200\\\" height=\\\"200\\\" xmlns=\\\"http:\\/\\/www.w3.org\\/2000\\/svg\\\">\\r\\n        <defs>\\r\\n            <pattern id=\\\"bunga\\\" x=\\\"0\\\" y=\\\"0\\\" width=\\\"40\\\" height=\\\"40\\\" patternUnits=\\\"userSpaceOnUse\\\">\\r\\n                <rect width=\\\"40\\\" height=\\\"40\\\" fill=\\\"#C93756\\\"\\/>\\r\\n                <circle cx=\\\"20\\\" cy=\\\"20\\\" r=\\\"10\\\" fill=\\\"#E8C547\\\"\\/>\\r\\n                <circle cx=\\\"20\\\" cy=\\\"20\\\" r=\\\"5\\\" fill=\\\"#FFFFFF\\\"\\/>\\r\\n                <circle cx=\\\"10\\\" cy=\\\"10\\\" r=\\\"3\\\" fill=\\\"#E8C547\\\" opacity=\\\"0.7\\\"\\/>\\r\\n                <circle cx=\\\"30\\\" cy=\\\"10\\\" r=\\\"3\\\" fill=\\\"#E8C547\\\" opacity=\\\"0.7\\\"\\/>\\r\\n                <circle cx=\\\"10\\\" cy=\\\"30\\\" r=\\\"3\\\" fill=\\\"#E8C547\\\" opacity=\\\"0.7\\\"\\/>\\r\\n                <circle cx=\\\"30\\\" cy=\\\"30\\\" r=\\\"3\\\" fill=\\\"#E8C547\\\" opacity=\\\"0.7\\\"\\/>\\r\\n            <\\/pattern>\\r\\n        <\\/defs>\\r\\n        <rect width=\\\"200\\\" height=\\\"200\\\" fill=\\\"url(#bunga)\\\"\\/>\\r\\n    <\\/svg>\"', NULL, '#FF6347', '[\"#FF6347,#8B0000,#FFD700,#FF8C00,#FFA500,#FFB6C1\",\"#FF69B4,#4B0082,#FFD700,#FFFFFF\",\"#FF8C00,#228B22,#FFD700,#8B0000\"]', 2600.00, 0.00, NULL, 1, 22, '2026-01-05 12:25:37', '2026-01-09 19:48:40'),
(3, 'Pinalantikan', 'Sacred nested diamond pattern representing the bamboo fish traps (bubo) and protective amulets (anting-anting) of Yakan warriors. Features five concentric diamonds with intricate geometric borders, each containing smaller diamond motifs representing generations of protection. The pattern includes traditional lightning bolts and wave symbols representing the Sulu Sea and Basilan mountains.', 'traditional', 'medium', 14, '\"<svg width=\\\"200\\\" height=\\\"200\\\" xmlns=\\\"http:\\/\\/www.w3.org\\/2000\\/svg\\\">\\r\\n        <defs>\\r\\n            <pattern id=\\\"pinalantikan\\\" x=\\\"0\\\" y=\\\"0\\\" width=\\\"50\\\" height=\\\"50\\\" patternUnits=\\\"userSpaceOnUse\\\">\\r\\n                <rect width=\\\"50\\\" height=\\\"50\\\" fill=\\\"#2E1A47\\\"\\/>\\r\\n                <polygon points=\\\"25,8 38,25 25,42 12,25\\\" fill=\\\"#E8C547\\\"\\/>\\r\\n                <polygon points=\\\"25,14 32,25 25,36 18,25\\\" fill=\\\"#7D1935\\\"\\/>\\r\\n                <polygon points=\\\"25,20 26,25 25,30 24,25\\\" fill=\\\"#FFFFFF\\\"\\/>\\r\\n            <\\/pattern>\\r\\n        <\\/defs>\\r\\n        <rect width=\\\"200\\\" height=\\\"200\\\" fill=\\\"url(#pinalantikan)\\\"\\/>\\r\\n    <\\/svg>\"', NULL, '#4B0082', '[\"#4B0082,#FFD700,#8B0000,#FFD700,#FFFFFF\",\"#8B0000,#FFD700,#4B0082,#FFFFFF\",\"#228B22,#FFD700,#4B0082,#FFFFFF\"]', 1950.00, 0.00, NULL, 1, 18, '2026-01-05 12:25:37', '2026-01-09 19:48:52'),
(4, 'Suhul', 'Sacred ocean wave pattern representing the Sulu Sea\'s eternal rhythms and the monsoon winds (habagat) that guide Yakan sailors. Features multiple layered waves with different intensities, representing the changing tides and seasons. The waves contain starfish motifs and navigation constellations used by Yakan fishermen for safe passage. Each wave crest includes traditional diamond patterns representing sea foam and marine life.', 'traditional', 'simple', 7, '\"<svg width=\\\"200\\\" height=\\\"200\\\" xmlns=\\\"http:\\/\\/www.w3.org\\/2000\\/svg\\\">\\r\\n        <defs>\\r\\n            <pattern id=\\\"suhul\\\" x=\\\"0\\\" y=\\\"0\\\" width=\\\"60\\\" height=\\\"25\\\" patternUnits=\\\"userSpaceOnUse\\\">\\r\\n                <rect width=\\\"60\\\" height=\\\"25\\\" fill=\\\"#2F5F7F\\\"\\/>\\r\\n                <path d=\\\"M0,12 Q15,5 30,12 T60,12\\\" stroke=\\\"#67C4E8\\\" stroke-width=\\\"2.5\\\" fill=\\\"none\\\"\\/>\\r\\n                <path d=\\\"M0,18 Q15,11 30,18 T60,18\\\" stroke=\\\"#94D5EC\\\" stroke-width=\\\"1.5\\\" fill=\\\"none\\\" opacity=\\\"0.8\\\"\\/>\\r\\n                <path d=\\\"M0,6 Q15,-1 30,6 T60,6\\\" stroke=\\\"#1E4D6B\\\" stroke-width=\\\"2\\\" fill=\\\"none\\\"\\/>\\r\\n            <\\/pattern>\\r\\n        <\\/defs>\\r\\n        <rect width=\\\"200\\\" height=\\\"200\\\" fill=\\\"url(#suhul)\\\"\\/>\\r\\n    <\\/svg>\"', NULL, '#4682B4', '[\"#4682B4,#00CED1,#87CEEB,#1E90FF,#FFD700,#FFA500,#FFFFFF\",\"#4682B4,#FFFFFF,#8B0000,#FFD700\",\"#4B0082,#FFD700,#87CEEB,#FFFFFF\"]', 1300.00, 1300.00, 500.00, 1, 15, '2026-01-05 12:25:38', '2026-01-10 03:41:04'),
(5, 'Kabkaban', 'Traditional interlocking squares pattern inspired by the ancient Yakan communal houses (baul) and the sacred rice terraces of Basilan. Features complex geometric arrangements with multiple square sizes representing different family units. Each square contains inner patterns symbolizing the hearth and home. The interlocking design represents how Yakan families connect to form the larger community while maintaining their individual identities.', 'traditional', 'medium', 14, '\"<svg width=\\\"200\\\" height=\\\"200\\\" xmlns=\\\"http:\\/\\/www.w3.org\\/2000\\/svg\\\">\\r\\n        <defs>\\r\\n            <pattern id=\\\"kabkaban\\\" x=\\\"0\\\" y=\\\"0\\\" width=\\\"25\\\" height=\\\"25\\\" patternUnits=\\\"userSpaceOnUse\\\">\\r\\n                <rect width=\\\"12.5\\\" height=\\\"12.5\\\" fill=\\\"#6B4423\\\"\\/>\\r\\n                <rect x=\\\"12.5\\\" y=\\\"12.5\\\" width=\\\"12.5\\\" height=\\\"12.5\\\" fill=\\\"#6B4423\\\"\\/>\\r\\n                <rect x=\\\"12.5\\\" y=\\\"0\\\" width=\\\"12.5\\\" height=\\\"12.5\\\" fill=\\\"#A67C52\\\"\\/>\\r\\n                <rect x=\\\"0\\\" y=\\\"12.5\\\" width=\\\"12.5\\\" height=\\\"12.5\\\" fill=\\\"#A67C52\\\"\\/>\\r\\n                <rect x=\\\"4\\\" y=\\\"4\\\" width=\\\"5\\\" height=\\\"5\\\" fill=\\\"#D4A76A\\\"\\/>\\r\\n                <rect x=\\\"16.5\\\" y=\\\"16.5\\\" width=\\\"5\\\" height=\\\"5\\\" fill=\\\"#D4A76A\\\"\\/>\\r\\n            <\\/pattern>\\r\\n        <\\/defs>\\r\\n        <rect width=\\\"200\\\" height=\\\"200\\\" fill=\\\"url(#kabkaban)\\\"\\/>\\r\\n    <\\/svg>\"', NULL, '#8B4513', '[\"#8B4513,#D2691E,#DEB887,#FFD700\",\"#8B0000,#FFD700,#4B0082,#FFFFFF\",\"#654321,#8B4513,#D2691E,#F4A460\"]', 1.20, 1900.00, 300.00, 1, 12, '2026-01-05 12:25:38', '2026-01-10 03:58:44'),
(6, 'Laggi', 'Sacred eight-pointed star pattern representing the constellations that guide Yakan fishermen and farmers through the Sulu Sea and Basilan mountains. Each star contains intricate geometric patterns with smaller stars representing ancestral spirits. The eight points symbolize the sacred directions in Yakan cosmology. Used in ceremonial clothing for weddings, harvest festivals, and important community rituals.', 'traditional', 'complex', 21, '\"<svg width=\\\"200\\\" height=\\\"200\\\" xmlns=\\\"http:\\/\\/www.w3.org\\/2000\\/svg\\\">\\r\\n        <defs>\\r\\n            <pattern id=\\\"laggi\\\" x=\\\"0\\\" y=\\\"0\\\" width=\\\"60\\\" height=\\\"60\\\" patternUnits=\\\"userSpaceOnUse\\\">\\r\\n                <rect width=\\\"60\\\" height=\\\"60\\\" fill=\\\"#7D1935\\\"\\/>\\r\\n                <polygon points=\\\"30,10 40,20 45,30 40,40 30,50 20,40 15,30 20,20\\\" fill=\\\"#E8C547\\\" stroke=\\\"#FFFFFF\\\" stroke-width=\\\"1.5\\\"\\/>\\r\\n                <polygon points=\\\"30,18 36,25 38,30 36,35 30,42 24,35 22,30 24,25\\\" fill=\\\"#D4A034\\\"\\/>\\r\\n                <circle cx=\\\"30\\\" cy=\\\"30\\\" r=\\\"4\\\" fill=\\\"#FFFFFF\\\"\\/>\\r\\n            <\\/pattern>\\r\\n        <\\/defs>\\r\\n        <rect width=\\\"200\\\" height=\\\"200\\\" fill=\\\"url(#laggi)\\\"\\/>\\r\\n    <\\/svg>\"', NULL, '#FFD700', '[\"#FFD700,#8B0000,#FF8C00,#FFA500,#FFFFFF,#4B0082\",\"#FFD700,#4B0082,#FFFFFF,#228B22\",\"#FFD700,#228B22,#8B0000,#FF8C00\"]', 2600.00, 2500.00, 500.00, 1, 20, '2026-01-05 12:25:38', '2026-01-10 03:59:04'),
(7, 'Bennig', 'Master-level sacred spiral pattern representing the eternal flow of life, seasons, and agricultural cycles in Yakan culture. Inspired by the nautilus shells found in Sulu Sea waters and the circular pangalay dances performed during festivals. Features triple interconnected spirals with intricate geometric patterns representing past, present, and future. Each spiral contains smaller motifs representing the continuity of Yakan traditions through generations.', 'traditional', 'complex', 14, '\"<svg width=\\\"200\\\" height=\\\"200\\\" xmlns=\\\"http:\\/\\/www.w3.org\\/2000\\/svg\\\">\\r\\n        <defs>\\r\\n            <pattern id=\\\"bennig\\\" x=\\\"0\\\" y=\\\"0\\\" width=\\\"35\\\" height=\\\"35\\\" patternUnits=\\\"userSpaceOnUse\\\">\\r\\n                <rect width=\\\"35\\\" height=\\\"35\\\" fill=\\\"#C93756\\\"\\/>\\r\\n                <line x1=\\\"0\\\" y1=\\\"0\\\" x2=\\\"35\\\" y2=\\\"35\\\" stroke=\\\"#2E1A47\\\" stroke-width=\\\"2.5\\\"\\/>\\r\\n                <line x1=\\\"35\\\" y1=\\\"0\\\" x2=\\\"0\\\" y2=\\\"35\\\" stroke=\\\"#2E1A47\\\" stroke-width=\\\"2.5\\\"\\/>\\r\\n                <line x1=\\\"17.5\\\" y1=\\\"0\\\" x2=\\\"17.5\\\" y2=\\\"35\\\" stroke=\\\"#E8C547\\\" stroke-width=\\\"2\\\"\\/>\\r\\n                <line x1=\\\"0\\\" y1=\\\"17.5\\\" x2=\\\"35\\\" y2=\\\"17.5\\\" stroke=\\\"#E8C547\\\" stroke-width=\\\"2\\\"\\/>\\r\\n                <circle cx=\\\"17.5\\\" cy=\\\"17.5\\\" r=\\\"5\\\" fill=\\\"#FFFFFF\\\"\\/>\\r\\n            <\\/pattern>\\r\\n        <\\/defs>\\r\\n        <rect width=\\\"200\\\" height=\\\"200\\\" fill=\\\"url(#bennig)\\\"\\/>\\r\\n    <\\/svg>\"', NULL, '#228B22', '[\"#228B22,#8B0000,#FFD700,#4B0082,#FFA500,#FFFFFF\",\"#228B22,#4B0082,#FFD700,#8B0000\",\"#228B22,#FFFFFF,#8B0000,#FFD700\"]', 2600.00, 0.00, NULL, 1, 16, '2026-01-05 12:25:38', '2026-01-09 19:47:41'),
(8, 'Pangapun', 'Sacred triangle pattern symbolizing the three sacred mountains of Basilan and the three pillars of Yakan society: family (kaluluwa), community (bayan), and tradition (adat). The triangles also represent the traditional Yakan spear tips (bangkaw) and the protective mountains that surround their homeland. Each triangle contains the sacred mountain spirit.', 'traditional', 'complex', 14, '\"<svg width=\\\"200\\\" height=\\\"200\\\" xmlns=\\\"http:\\/\\/www.w3.org\\/2000\\/svg\\\">\\r\\n        <defs>\\r\\n            <pattern id=\\\"pangapun\\\" x=\\\"0\\\" y=\\\"0\\\" width=\\\"30\\\" height=\\\"60\\\" patternUnits=\\\"userSpaceOnUse\\\">\\r\\n                <rect width=\\\"30\\\" height=\\\"60\\\" fill=\\\"#1B4D3E\\\"\\/>\\r\\n                <rect x=\\\"7\\\" y=\\\"8\\\" width=\\\"16\\\" height=\\\"12\\\" fill=\\\"#6B4423\\\"\\/>\\r\\n                <rect x=\\\"7\\\" y=\\\"24\\\" width=\\\"16\\\" height=\\\"12\\\" fill=\\\"#A67C52\\\"\\/>\\r\\n                <rect x=\\\"7\\\" y=\\\"40\\\" width=\\\"16\\\" height=\\\"12\\\" fill=\\\"#6B4423\\\"\\/>\\r\\n                <line x1=\\\"15\\\" y1=\\\"0\\\" x2=\\\"15\\\" y2=\\\"60\\\" stroke=\\\"#E8C547\\\" stroke-width=\\\"1.5\\\"\\/>\\r\\n            <\\/pattern>\\r\\n        <\\/defs>\\r\\n        <rect width=\\\"200\\\" height=\\\"200\\\" fill=\\\"url(#pangapun)\\\"\\/>\\r\\n    <\\/svg>\"', NULL, '#FF8C00', '[\"#FF8C00,#8B0000,#FFD700,#FFFFFF,#4B0082\",\"#FF8C00,#4B0082,#FFFFFF,#228B22\",\"#FF8C00,#228B22,#8B0000,#FFD700\"]', 2600.00, 0.00, NULL, 1, 14, '2026-01-05 12:25:38', '2026-01-09 19:47:52'),
(9, 'Sarang Kayu', 'Sacred honeycomb pattern celebrating the traditional honey gathering and the sacred bees in Yakan ecology. Each hexagon represents a family unit working together for community prosperity. The pattern is woven during harvest festivals and represents the sweet rewards of cooperation and sustainable living in harmony with nature.', 'traditional', 'complex', 14, '\"<svg width=\\\"200\\\" height=\\\"200\\\" xmlns=\\\"http:\\/\\/www.w3.org\\/2000\\/svg\\\">\\r\\n        <defs>\\r\\n            <pattern id=\\\"sarang\\\" x=\\\"0\\\" y=\\\"0\\\" width=\\\"35\\\" height=\\\"35\\\" patternUnits=\\\"userSpaceOnUse\\\">\\r\\n                <rect width=\\\"35\\\" height=\\\"35\\\" fill=\\\"#4A3728\\\"\\/>\\r\\n                <polygon points=\\\"0,12 12,0 23,12 12,23\\\" fill=\\\"#A67C52\\\"\\/>\\r\\n                <polygon points=\\\"12,23 23,12 35,23 23,35\\\" fill=\\\"#6B4423\\\"\\/>\\r\\n                <polygon points=\\\"23,0 35,12 23,23 12,12\\\" fill=\\\"#8B6F47\\\"\\/>\\r\\n            <\\/pattern>\\r\\n        <\\/defs>\\r\\n        <rect width=\\\"200\\\" height=\\\"200\\\" fill=\\\"url(#sarang)\\\"\\/>\\r\\n    <\\/svg>\"', NULL, '#FFB347', '[\"#FFB347,#8B0000,#FFA500,#FF8C00,#FFD700,#FFFFFF\",\"#FFB347,#4B0082,#FFD700,#FFFFFF\",\"#FFB347,#228B22,#FF8C00,#8B0000\"]', 2600.00, 0.00, NULL, 1, 17, '2026-01-05 12:25:38', '2026-01-09 19:48:01'),
(10, 'Ikan Mas', 'Sacred fish pattern celebrating the abundant marine life of Basilan waters and the fishing heritage. The fish represents prosperity, good fortune, and the bounty of the Sulu Sea. Multiple fish swimming in formation symbolize community cooperation and the interconnected nature of Yakan fishing families who work together for the common good.', 'traditional', 'complex', 14, '\"<svg width=\\\"200\\\" height=\\\"200\\\" xmlns=\\\"http:\\/\\/www.w3.org\\/2000\\/svg\\\">\\r\\n        <defs>\\r\\n            <pattern id=\\\"ikan\\\" x=\\\"0\\\" y=\\\"0\\\" width=\\\"50\\\" height=\\\"35\\\" patternUnits=\\\"userSpaceOnUse\\\">\\r\\n                <rect width=\\\"50\\\" height=\\\"35\\\" fill=\\\"#2F5F7F\\\"\\/>\\r\\n                <ellipse cx=\\\"25\\\" cy=\\\"17.5\\\" rx=\\\"14\\\" ry=\\\"8\\\" fill=\\\"#E89547\\\"\\/>\\r\\n                <polygon points=\\\"11,17.5 7,14 7,21\\\" fill=\\\"#E8C547\\\"\\/>\\r\\n                <circle cx=\\\"32\\\" cy=\\\"15\\\" r=\\\"2\\\" fill=\\\"#1A1A1A\\\"\\/>\\r\\n                <path d=\\\"M25,10 Q30,10 33,13 Q36,16 33,19 Q30,22 25,22\\\" stroke=\\\"#D4A034\\\" stroke-width=\\\"1.2\\\" fill=\\\"none\\\"\\/>\\r\\n            <\\/pattern>\\r\\n        <\\/defs>\\r\\n        <rect width=\\\"200\\\" height=\\\"200\\\" fill=\\\"url(#ikan)\\\"\\/>\\r\\n    <\\/svg>\"', NULL, '#4682B4', '[\"#4682B4,#00CED1,#87CEEB,#1E90FF,#FFD700,#FFFFFF\",\"#4682B4,#FFD700,#8B0000,#FF8C00\",\"#4682B4,#4B0082,#FFFFFF,#87CEEB\"]', 2600.00, 0.00, NULL, 1, 19, '2026-01-05 12:25:38', '2026-01-09 19:48:12'),
(12, 'Tali', 'Sacred interwoven rope pattern representing the strong bonds that tie Yakan families and communities together. The interlocking design symbolizes unity, cooperation, and the interconnected nature of Yakan social structure. Each knot represents a promise or commitment within the community, and the continuous rope represents the eternal flow of Yakan culture.', 'traditional', 'complex', 14, '\"<svg width=\\\"200\\\" height=\\\"200\\\" xmlns=\\\"http:\\/\\/www.w3.org\\/2000\\/svg\\\">\\r\\n        <defs>\\r\\n            <pattern id=\\\"tali\\\" x=\\\"0\\\" y=\\\"0\\\" width=\\\"25\\\" height=\\\"50\\\" patternUnits=\\\"userSpaceOnUse\\\">\\r\\n                <rect width=\\\"25\\\" height=\\\"50\\\" fill=\\\"#6B4423\\\"\\/>\\r\\n                <path d=\\\"M8,0 Q12,12 8,25 Q4,37 8,50\\\" stroke=\\\"#A67C52\\\" stroke-width=\\\"3.5\\\" fill=\\\"none\\\"\\/>\\r\\n                <path d=\\\"M17,0 Q13,12 17,25 Q21,37 17,50\\\" stroke=\\\"#A67C52\\\" stroke-width=\\\"3.5\\\" fill=\\\"none\\\"\\/>\\r\\n                <line x1=\\\"8\\\" y1=\\\"12\\\" x2=\\\"17\\\" y2=\\\"12\\\" stroke=\\\"#E8C547\\\" stroke-width=\\\"1.5\\\"\\/>\\r\\n                <line x1=\\\"8\\\" y1=\\\"37\\\" x2=\\\"17\\\" y2=\\\"37\\\" stroke=\\\"#E8C547\\\" stroke-width=\\\"1.5\\\"\\/>\\r\\n            <\\/pattern>\\r\\n        <\\/defs>\\r\\n        <rect width=\\\"200\\\" height=\\\"200\\\" fill=\\\"url(#tali)\\\"\\/>\\r\\n    <\\/svg>\"', NULL, '#8B4513', '[\"#8B4513,#D2691E,#DEB887,#FFD700,#FFFFFF\",\"#8B0000,#FFD700,#4B0082,#FFFFFF\",\"#654321,#8B4513,#D2691E,#F4A460\"]', 2600.00, 0.00, NULL, 1, 13, '2026-01-05 12:25:38', '2026-01-09 19:48:22'),
(19, 'test', 'hghghg', 'modern', 'simple', 7, NULL, 'test-1768016145.svg', 'pink', NULL, 1.00, 1300.00, 500.00, 1, 0, '2026-01-10 03:35:45', '2026-01-10 03:35:45'),
(20, 'zigzagggg', 'vfdfdfdf', 'modern', 'simple', 7, NULL, 'zigzagggg-1768017594.svg', 'pink', NULL, 1.00, 1200.00, 400.00, 1, 0, '2026-01-10 03:59:54', '2026-01-10 04:05:10'),
(21, 'test', 'dghfgh', 'traditional', 'simple', 14, NULL, 'test-1768018711.svg', 'pink', NULL, 1.00, 1500.00, 500.00, 1, 0, '2026-01-10 04:18:31', '2026-01-10 04:18:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admins_email_unique` (`email`);

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_notifications_admin_id_is_read_index` (`admin_id`,`is_read`),
  ADD KEY `admin_notifications_created_at_index` (`created_at`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `carts_user_id_foreign` (`user_id`),
  ADD KEY `carts_product_id_foreign` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chats_status_index` (`status`),
  ADD KEY `chats_user_id_index` (`user_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contact_messages_is_read_index` (`is_read`),
  ADD KEY `contact_messages_created_at_index` (`created_at`),
  ADD KEY `contact_messages_email_index` (`email`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`),
  ADD KEY `coupons_created_by_foreign` (`created_by`);

--
-- Indexes for table `coupon_redemptions`
--
ALTER TABLE `coupon_redemptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coupon_redemptions_user_id_foreign` (`user_id`),
  ADD KEY `coupon_redemptions_order_id_foreign` (`order_id`),
  ADD KEY `coupon_redemptions_coupon_id_user_id_index` (`coupon_id`,`user_id`);

--
-- Indexes for table `cultural_heritage`
--
ALTER TABLE `cultural_heritage`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cultural_heritage_slug_unique` (`slug`);

--
-- Indexes for table `custom_orders`
--
ALTER TABLE `custom_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `custom_orders_user_id_foreign` (`user_id`),
  ADD KEY `custom_orders_product_id_foreign` (`product_id`),
  ADD KEY `custom_orders_design_approved_by_foreign` (`design_approved_by`),
  ADD KEY `custom_orders_design_method_status_index` (`design_method`,`status`),
  ADD KEY `custom_orders_design_approved_at_index` (`design_approved_at`),
  ADD KEY `custom_orders_pattern_count_index` (`pattern_count`);

--
-- Indexes for table `fabric_types`
--
ALTER TABLE `fabric_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `intended_uses`
--
ALTER TABLE `intended_uses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `intended_uses_name_unique` (`name`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_product_id_index` (`product_id`),
  ADD KEY `inventory_low_stock_alert_index` (`low_stock_alert`),
  ADD KEY `inventory_quantity_min_stock_level_index` (`quantity`,`min_stock_level`),
  ADD KEY `inventory_total_sold_index` (`total_sold`),
  ADD KEY `inventory_last_sale_at_index` (`last_sale_at`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_is_read_index` (`user_id`,`is_read`),
  ADD KEY `notifications_type_index` (`type`),
  ADD KEY `notifications_created_at_index` (`created_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_ref_unique` (`order_ref`),
  ADD KEY `orders_status_index` (`status`),
  ADD KEY `orders_payment_status_index` (`payment_status`),
  ADD KEY `orders_created_at_status_index` (`created_at`,`status`),
  ADD KEY `orders_user_id_index` (`user_id`),
  ADD KEY `orders_coupon_id_foreign` (`coupon_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pattern_fabric_compatibility`
--
ALTER TABLE `pattern_fabric_compatibility`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pattern_fabric_unique` (`yakan_pattern_id`,`fabric_type_id`),
  ADD KEY `pattern_fabric_compatibility_fabric_type_id_foreign` (`fabric_type_id`);

--
-- Indexes for table `pattern_media`
--
ALTER TABLE `pattern_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pattern_media_yakan_pattern_id_sort_order_index` (`yakan_pattern_id`,`sort_order`);

--
-- Indexes for table `pattern_tags`
--
ALTER TABLE `pattern_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pattern_tags_slug_unique` (`slug`),
  ADD KEY `pattern_tags_slug_index` (`slug`);

--
-- Indexes for table `pattern_tag_pivot`
--
ALTER TABLE `pattern_tag_pivot`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pattern_tag_unique` (`yakan_pattern_id`,`pattern_tag_id`),
  ADD KEY `pattern_tag_pivot_pattern_tag_id_index` (`pattern_tag_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD KEY `products_category_id_foreign` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_foreign` (`product_id`);

--
-- Indexes for table `recent_views`
--
ALTER TABLE `recent_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recent_views_viewable_type_viewable_id_index` (`viewable_type`,`viewable_id`),
  ADD KEY `recent_views_user_id_viewable_type_viewable_id_index` (`user_id`,`viewable_type`,`viewable_id`),
  ADD KEY `recent_views_viewed_at_index` (`viewed_at`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reviews_user_id_product_id_unique` (`user_id`,`product_id`),
  ADD KEY `reviews_admin_id_foreign` (`admin_id`),
  ADD KEY `reviews_product_id_rating_index` (`product_id`,`rating`),
  ADD KEY `reviews_user_id_product_id_index` (`user_id`,`product_id`),
  ADD KEY `reviews_is_approved_index` (`is_approved`),
  ADD KEY `reviews_custom_order_id_index` (`custom_order_id`),
  ADD KEY `reviews_order_item_id_index` (`order_item_id`),
  ADD KEY `reviews_order_id_index` (`order_id`),
  ADD KEY `reviews_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `system_settings_key_unique` (`key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_addresses_user_id_index` (`user_id`),
  ADD KEY `user_addresses_is_default_index` (`is_default`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wishlists_user_id_name_unique` (`user_id`,`name`),
  ADD KEY `wishlists_user_id_index` (`user_id`);

--
-- Indexes for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wishlist_item_unique` (`wishlist_id`,`item_type`,`item_id`),
  ADD KEY `wishlist_items_item_type_item_id_index` (`item_type`,`item_id`);

--
-- Indexes for table `yakan_patterns`
--
ALTER TABLE `yakan_patterns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `yakan_patterns_category_is_active_index` (`category`,`is_active`),
  ADD KEY `yakan_patterns_difficulty_level_index` (`difficulty_level`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupon_redemptions`
--
ALTER TABLE `coupon_redemptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cultural_heritage`
--
ALTER TABLE `cultural_heritage`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `custom_orders`
--
ALTER TABLE `custom_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `fabric_types`
--
ALTER TABLE `fabric_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `intended_uses`
--
ALTER TABLE `intended_uses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pattern_fabric_compatibility`
--
ALTER TABLE `pattern_fabric_compatibility`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pattern_media`
--
ALTER TABLE `pattern_media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pattern_tags`
--
ALTER TABLE `pattern_tags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pattern_tag_pivot`
--
ALTER TABLE `pattern_tag_pivot`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recent_views`
--
ALTER TABLE `recent_views`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `yakan_patterns`
--
ALTER TABLE `yakan_patterns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD CONSTRAINT `admin_notifications_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `chats_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `coupons_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `coupon_redemptions`
--
ALTER TABLE `coupon_redemptions`
  ADD CONSTRAINT `coupon_redemptions_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_redemptions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `coupon_redemptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `custom_orders`
--
ALTER TABLE `custom_orders`
  ADD CONSTRAINT `custom_orders_design_approved_by_foreign` FOREIGN KEY (`design_approved_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `custom_orders_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `custom_orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pattern_fabric_compatibility`
--
ALTER TABLE `pattern_fabric_compatibility`
  ADD CONSTRAINT `pattern_fabric_compatibility_fabric_type_id_foreign` FOREIGN KEY (`fabric_type_id`) REFERENCES `fabric_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pattern_fabric_compatibility_yakan_pattern_id_foreign` FOREIGN KEY (`yakan_pattern_id`) REFERENCES `yakan_patterns` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pattern_media`
--
ALTER TABLE `pattern_media`
  ADD CONSTRAINT `pattern_media_yakan_pattern_id_foreign` FOREIGN KEY (`yakan_pattern_id`) REFERENCES `yakan_patterns` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pattern_tag_pivot`
--
ALTER TABLE `pattern_tag_pivot`
  ADD CONSTRAINT `pattern_tag_pivot_pattern_tag_id_foreign` FOREIGN KEY (`pattern_tag_id`) REFERENCES `pattern_tags` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pattern_tag_pivot_yakan_pattern_id_foreign` FOREIGN KEY (`yakan_pattern_id`) REFERENCES `yakan_patterns` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recent_views`
--
ALTER TABLE `recent_views`
  ADD CONSTRAINT `recent_views_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reviews_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reviews_custom_order_id_foreign` FOREIGN KEY (`custom_order_id`) REFERENCES `custom_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD CONSTRAINT `wishlist_items_wishlist_id_foreign` FOREIGN KEY (`wishlist_id`) REFERENCES `wishlists` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
