-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 13, 2023 at 08:20 AM
-- Server version: 10.5.19-MariaDB-cll-lve
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u806435594_xxxsdfg`
--

-- --------------------------------------------------------

--
-- Table structure for table `allow_pincode`
--

CREATE TABLE `allow_pincode` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pin_code` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `setting_id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `value` varchar(250) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `app_settings`
--

INSERT INTO `app_settings` (`id`, `setting_id`, `title`, `value`, `created_at`, `updated_at`) VALUES
(1, 1, 'Technical Issue Android', '0', NULL, '2023-05-09 00:48:50'),
(2, 2, 'Android Version', '1.0.0', NULL, '2023-09-20 05:32:59'),
(3, 3, 'Force Update Android', '0', NULL, '2023-05-06 18:53:47'),
(4, 4, 'Technical Issue IOS', '0', NULL, NULL),
(5, 5, 'IOS Version', '1.0.0', NULL, '2023-09-20 05:33:04'),
(6, 6, 'Force Update IOS', '0', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `assign_role`
--

CREATE TABLE `assign_role` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `available_delivery_location`
--

CREATE TABLE `available_delivery_location` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(250) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `available_delivery_location`
--

INSERT INTO `available_delivery_location` (`id`, `title`, `created_at`, `updated_at`) VALUES
(2, 'City A', '2023-06-20 08:43:23', '2023-06-20 08:43:23'),
(4, 'City B', '2023-06-20 08:43:36', '2023-06-20 08:43:36'),
(6, 'City C', '2023-06-30 10:37:23', '2023-06-30 10:37:23');

-- --------------------------------------------------------

--
-- Table structure for table `banner_image`
--

CREATE TABLE `banner_image` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `image` text NOT NULL,
  `image_type` tinyint(4) NOT NULL COMMENT '1=mobile',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banner_image`
--

INSERT INTO `banner_image` (`id`, `image`, `image_type`, `created_at`, `updated_at`) VALUES
(19, '932950633.png', 1, '2023-09-15 07:31:38', '2023-09-15 07:31:38'),
(20, '1910631836.png', 1, '2023-09-15 07:31:50', '2023-09-15 07:31:50'),
(21, '991370922.png', 1, '2023-09-15 07:31:55', '2023-09-15 07:31:55'),
(22, '2129770954.webp', 1, '2023-09-15 07:32:19', '2023-09-15 07:32:19'),
(23, '1281921476.jpg', 1, '2023-09-15 07:32:30', '2023-09-15 07:32:30');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `qty` int(11) NOT NULL,
  `price` double NOT NULL,
  `total_price` double NOT NULL,
  `mrp` double NOT NULL,
  `tax` double NOT NULL,
  `qty_text` varchar(250) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cat`
--

CREATE TABLE `cat` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(250) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `city`
--

CREATE TABLE `city` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(250) NOT NULL,
  `deleted` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` bigint(10) UNSIGNED NOT NULL,
  `name` varchar(250) NOT NULL,
  `file_url` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `file_for` int(11) NOT NULL COMMENT '1=admin.2=school table,3=user table, 4= emp tabel',
  `file_for_id` int(11) NOT NULL,
  `file_cat` tinyint(4) NOT NULL COMMENT '1=profile image'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `table_name` varchar(250) NOT NULL,
  `table_id` bigint(20) UNSIGNED NOT NULL,
  `image_type` tinyint(4) NOT NULL COMMENT '1= profile image, 2=slider image',
  `image` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_setting`
--

CREATE TABLE `invoice_setting` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(250) NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_setting`
--

INSERT INTO `invoice_setting` (`id`, `title`, `value`, `created_at`, `updated_at`) VALUES
(1, 'Logo', '2138841096.png', '2023-09-16 12:28:19', '2023-09-17 08:12:33'),
(2, 'Address Line 1', 'BASKET APP', '2023-09-16 12:28:19', '2023-09-16 12:28:19'),
(3, 'Address Line 2', 'REG. ADDRESS: H.NO.890/S.NO. 11', '2023-09-16 12:28:19', '2023-09-16 12:28:19'),
(4, 'Address Line 3', 'STATION ROAD RAIPUR VILLA', '2023-09-16 12:28:19', '2023-09-16 12:28:19'),
(5, 'Address Line 4', 'MAHARASHTRA, INDIA - 402001', '2023-09-16 12:28:19', '2023-09-16 12:28:19'),
(6, 'Below Description', 'GSTIN : xxxxxxxx // PAN : xxxxx // CIN : xxxxx // MOB : + 1234567890 // +91 1234567890 //+91 1234567890 // EMAIL : basketapp@gmail.com // FSSAI NUMBER : xxxxxxx// xxx HSN : xxx //Paneer HSN : xxxx', '2023-09-16 12:28:19', '2023-09-16 12:28:19');

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
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `trasation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_type` int(11) DEFAULT NULL COMMENT '1= prepaid,2=pos, 3=pay now,4=cod\r\n',
  `order_amount` double NOT NULL,
  `price` double NOT NULL,
  `mrp` double NOT NULL,
  `tax` double NOT NULL,
  `qty` int(11) DEFAULT NULL,
  `selected_days_for_weekly` text DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `address_id` bigint(20) UNSIGNED NOT NULL,
  `start_date` date DEFAULT NULL,
  `subscription_type` int(11) DEFAULT NULL COMMENT '1=daliy,2=weekly,3=monthly,4=alternative days\r\n',
  `status` int(11) NOT NULL COMMENT '1=confirmed 1=confirmed, 0=pending,2=canceled',
  `delivery_status` int(11) DEFAULT NULL COMMENT '1=delivered ',
  `order_status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=active,1=stop',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_user_assign`
--

CREATE TABLE `order_user_assign` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_gateway`
--

CREATE TABLE `payment_gateway` (
  `id` bigint(20) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `title` varchar(250) NOT NULL,
  `key_id` text NOT NULL,
  `secret_id` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_gateway`
--

INSERT INTO `payment_gateway` (`id`, `active`, `title`, `key_id`, `secret_id`, `created_at`, `updated_at`) VALUES
(1, 1, 'Razorpay ', 'xxx', 'xxx', '2023-06-20 10:37:41', '2023-10-13 06:41:28'),
(2, 0, 'Paystack', 'xxx', 'xxx', '2023-06-20 10:37:41', '2023-10-13 06:41:28'),
(3, 0, 'Stripe', 'xxx', 'xxx', '2023-06-20 10:37:41', '2023-10-13 06:41:28'),
(4, 0, 'Paypal', 'xxx', 'xxx', '2023-06-20 10:37:41', '2023-10-13 06:41:28'),
(5, 0, 'Flutterwave', 'xxx', 'xxx', '2023-06-20 10:37:41', '2023-10-13 06:41:28');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(250) NOT NULL,
  `qty_text` varchar(250) NOT NULL,
  `stock_qty` bigint(11) DEFAULT NULL,
  `sub_cat_id` bigint(20) UNSIGNED NOT NULL,
  `price` double NOT NULL,
  `tax` double NOT NULL,
  `mrp` double NOT NULL,
  `offer_text` varchar(250) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `disclaimer` text DEFAULT NULL,
  `subscription` tinyint(4) NOT NULL COMMENT '1= true ,0=false',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(250) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `title`, `created_at`, `updated_at`, `deleted`) VALUES
(1, 'SUPER ADMIN', '2023-01-15 16:42:27', '2023-01-15 16:42:28', 0),
(2, 'ADMIN', '2023-01-15 16:42:27', '2023-01-15 16:42:28', 0),
(3, 'USER', '2023-01-15 16:42:27', '2023-01-15 16:42:28', 0),
(4, 'DRIVER', '2023-01-15 16:42:27', '2023-01-15 16:42:28', 0);

-- --------------------------------------------------------

--
-- Table structure for table `social_media`
--

CREATE TABLE `social_media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(250) NOT NULL,
  `image` text NOT NULL,
  `url` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `social_media`
--

INSERT INTO `social_media` (`id`, `title`, `image`, `url`, `created_at`, `updated_at`) VALUES
(12, 'Facebook', '2023931362.png', 'https://m.facebook.com/', '2023-09-19 07:16:20', '2023-09-19 07:16:20'),
(13, 'Twitter', '1504481982.png', 'https://twitter.com/', '2023-09-19 07:17:01', '2023-09-19 07:17:01'),
(14, 'Whatsapp', '1246266110.png', 'https://www.whatsapp.com/', '2023-09-19 07:17:23', '2023-09-19 07:17:23');

-- --------------------------------------------------------

--
-- Table structure for table `specific_notification`
--

CREATE TABLE `specific_notification` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(250) NOT NULL,
  `body` varchar(250) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscribed_order_delivery`
--

CREATE TABLE `subscribed_order_delivery` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `entry_user_id` bigint(11) UNSIGNED NOT NULL,
  `date` date DEFAULT NULL,
  `payment_mode` int(11) DEFAULT NULL COMMENT '1= online, 2=offline',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sub_cat`
--

CREATE TABLE `sub_cat` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cat_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(250) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` bigint(20) NOT NULL,
  `title` varchar(250) NOT NULL,
  `sub_title` varchar(250) NOT NULL,
  `rating` int(11) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `payment_id` text DEFAULT NULL,
  `amount` double NOT NULL,
  `description` text DEFAULT NULL,
  `type` int(11) DEFAULT NULL COMMENT '1=credit 2=debited',
  `payment_mode` int(11) NOT NULL DEFAULT 1 COMMENT '1=online,2=cash',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `wallet_amount` double DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(250) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(250) NOT NULL,
  `fcm` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_address`
--

CREATE TABLE `user_address` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(250) NOT NULL,
  `s_phone` varchar(250) NOT NULL,
  `flat_no` varchar(250) DEFAULT NULL,
  `apartment_name` varchar(250) DEFAULT NULL,
  `area` varchar(250) NOT NULL,
  `landmark` varchar(250) NOT NULL,
  `city` varchar(250) NOT NULL,
  `pincode` int(11) NOT NULL,
  `lat` double DEFAULT NULL,
  `lng` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_holiday`
--

CREATE TABLE `user_holiday` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_notification`
--

CREATE TABLE `user_notification` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(250) NOT NULL,
  `body` varchar(250) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `web_app_settings`
--

CREATE TABLE `web_app_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(250) NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `web_app_settings`
--

INSERT INTO `web_app_settings` (`id`, `title`, `value`, `created_at`, `updated_at`) VALUES
(1, 'App Name', 'Bbbbb', '2023-06-20 10:18:41', '2023-09-18 07:29:09'),
(2, 'Contact Number', '+918889990643', '2023-06-20 10:18:41', '2023-09-20 05:23:38'),
(3, 'Contact Email', 'appwebdevash@gmail.com', '2023-06-20 10:18:41', NULL),
(4, 'Play Store Link', 'https://play.google.com/', '2023-06-20 10:18:41', '2023-09-20 05:10:12'),
(5, 'App Store Link', 'https://www.apple.com/in/app-store/', '2023-06-20 10:18:41', '2023-09-20 05:09:40'),
(6, 'About Us', 'At Basket App, we are more than just an app; we are your dedicated partner in the world of dairy. We understand the significance of convenience, quality, and freshness in meeting your daily milk and dairy requirements. Our fundamental mission is to enhance the quality of your life by ensuring the seamless delivery of farm-fresh, pure, and wholesome dairy products right to your doorstep.', '2023-06-20 10:18:41', '2023-09-17 18:02:58'),
(7, 'Logo Image', '2115840143.png', '2023-06-20 10:18:41', '2023-09-17 15:40:07'),
(8, 'Background Image', '1844662302.png', '2023-06-20 10:18:41', '2023-09-17 17:42:05'),
(9, 'Address', 'Near Railway Station Raipur India', '2023-06-20 10:18:41', '2023-07-19 08:14:34'),
(10, 'FCM Server key', 'Firebase Messaging Server Key', '2023-06-20 10:18:41', '2023-09-15 13:57:23');

-- --------------------------------------------------------

--
-- Table structure for table `web_pages`
--

CREATE TABLE `web_pages` (
  `id` bigint(20) NOT NULL,
  `page_id` int(11) NOT NULL COMMENT '1=about us,2=privacy,3=terms',
  `title` varchar(250) NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `web_pages`
--

INSERT INTO `web_pages` (`id`, `page_id`, `title`, `body`, `created_at`, `updated_at`) VALUES
(1, 1, 'About Us', '<h1>About Us</h1>\n<p>Welcome to [Your Shop Name], your trusted source for premium dairy products. At [Your Shop Name], we are passionate about delivering the purest and freshest dairy goodness to your doorstep. Our commitment to quality and tradition sets us apart in the world of dairy.</p>\n<p></p>\n<h3><strong>Our Story</strong></h3>\n<p></p>\n<p>Founded with a vision to provide wholesome dairy products to our community, [Your Shop Name] has been a cornerstone of nourishment and satisfaction for [X] years. Our journey began with a small dairy farm and a dream to share the goodness of dairy with our customers.</p>\n<p></p>\n<h3><strong>Our Products</strong></h3>\n<p></p>\n<p>We take immense pride in offering a wide range of dairy delights, carefully crafted to meet the highest standards of quality:</p>\n<p></p>\n<p><strong>Pure Milk</strong>: Our farm-fresh milk is a testament to the care and love we invest in our cattle. It\'s not just milk; it\'s the essence of our commitment to health and nutrition.</p>\n<p></p>\n<p><strong>Creamy Dahi</strong>: Enjoy the rich and creamy texture of our dahi (yogurt), a timeless favorite in Indian households. It\'s the result of a traditional recipe perfected over generations.</p>\n<p></p>\n<p><strong>Golden Ghee</strong>: Experience the golden goodness of our ghee, prepared from the finest butter, with a taste that lingers on your taste buds and adds flavor to your meals.</p>\n<p></p>\n<p><strong>Delicious Paneer</strong>: Our paneer is soft, fresh, and versatile, making it a staple for both traditional and modern Indian cuisines. It\'s the ideal choice for your culinary adventures.</p>\n<p></p>\n<h3><strong>Our Promise</strong></h3>\n<p></p>\n<p>Quality and purity are at the heart of everything we do. We maintain the highest hygiene standards and ensure that every product that leaves our shop is a reflection of our dedication to your well-being. We source our ingredients locally, supporting our local farmers and communities.</p>\n<p></p>\n<h3>Why Choose Us?</h3>\n<p></p>\n<p>Quality Assurance: We are committed to delivering dairy products that meet stringent quality standards.</p>\n<p></p>\n<p>Freshness Guaranteed: Our products are always fresh, ensuring you get the best taste and nutrition.</p>\n<p></p>\n<p>Community Connection: We are proud to be an integral part of our community, supporting local farmers and businesses.</p>\n<p></p>\n<p>Customer Satisfaction: Your satisfaction is our top priority. We are here to serve you with a smile.</p>\n<p></p>\n<p>Thank you for choosing [Your Shop Name] as your preferred destination for dairy excellence. We look forward to being a part of your daily life, bringing health, taste, and tradition to your table.</p>\n<p></p>\n<p>[Contact Information]</p>', NULL, '2023-09-15 11:58:23'),
(2, 2, 'Privacy Plociy', '<h1>PRIVACY POLICY</h1>\n<p>Last Updated: [Date]</p>\n<p></p>\n<p>[Your Company Name] (\"we,\" \"us,\" or \"our\") is committed to protecting your privacy. This Privacy Policy outlines how we collect, use, disclose, and safeguard your personal information when you visit our website or use our services.</p>\n<p></p>\n<h3>Information We Collect</h3>\n<p></p>\n<p>We may collect personal information that you provide to us directly, including but not limited to your name, email address, postal address, phone number, and other contact information when you:</p>\n<p></p>\n<p>Register for an account.</p>\n<p>Make a purchase or place an order.</p>\n<p>Subscribe to our newsletter or updates.</p>\n<p>Contact us through our website or customer support.</p>\n<p>We may also collect non-personal information automatically, such as your IP address, browser type, operating system, and browsing behavior when you visit our website.</p>\n<p></p>\n<h3>How We Use Your Information</h3>\n<p></p>\n<p>We may use your personal information for various purposes, including but not limited to:</p>\n<p></p>\n<p>Processing and fulfilling your orders.</p>\n<p>Providing customer support and responding to your inquiries.</p>\n<p>Sending you updates, newsletters, and promotional materials.</p>\n<p>Analyzing website usage to improve our products and services.</p>\n<p>Complying with legal and regulatory requirements.</p>\n<p>Information Sharing</p>\n<p></p>\n<p>We may share your personal information with trusted third-party service providers to help us perform services, such as payment processing, shipping, and marketing. These service providers are contractually obligated to protect your information and use it solely for the purposes specified by us.</p>\n<p></p>\n<p>We will not sell, trade, or rent your personal information to third parties for their marketing purposes.</p>\n<p></p>\n<h3>Cookies and Similar Technologies</h3>\n<p></p>\n<p>We may use cookies and similar technologies to enhance your browsing experience. You can manage your preferences for these technologies through your browser settings.</p>\n<p></p>\n<h3>Security</h3>\n<p></p>\n<p>We implement reasonable security measures to protect your personal information from unauthorized access, disclosure, alteration, or destruction. However, no method of transmission over the internet or electronic storage is entirely secure, and we cannot guarantee absolute security.</p>\n<p></p>\n<h3>Your Choices</h3>\n<p></p>\n<p>You may choose to opt-out of receiving marketing communications from us by following the unsubscribe instructions in our emails or contacting us directly.</p>\n<p></p>\n<p>Changes to this Privacy Policy</p>\n<p></p>\n<p>We may update this Privacy Policy from time to time to reflect changes in our practices or for legal and regulatory reasons. The revised policy will be posted on our website, and the date of the latest update will be indicated.</p>\n<p></p>\n<h3>Contact Us</h3>\n<p></p>\n<p>If you have any questions or concerns about this Privacy Policy or your personal information, please contact us at [Your Contact Information].</p>\n<p></p>\n<p>By using our website or services, you consent to the terms of this Privacy Policy.</p>\n<p></p>\n<p>Remember that this is a generic privacy policy template and should be customized to match the specific practices and requirements of your website or business. Additionally, it\'s crucial to stay informed about privacy laws and regulations that may apply to your jurisdiction and industry. Consultation with a legal professional is advisable to ensure compliance.</p>\n<p></p>', NULL, '2023-09-15 12:01:11'),
(3, 3, 'Terms and Condition ', '<h1>TERMS &amp; CONDITION</h1>\n<p>Last Updated: [Date]</p>\n<p></p>\n<h3>1. Acceptance of Terms</h3>\n<p>By accessing or using [Your Company Name] (\"we,\" \"us,\" or \"our\") services, you agree to comply with and be bound by these Terms and Conditions. If you do not agree to these terms, please do not use our services.</p>\n<h3>2. Use of Services</h3>\n<p>You must be of legal age in your jurisdiction to use our services.</p>\n<p>You agree to use our services only for lawful purposes and in accordance with these terms.</p>\n<p>You are responsible for maintaining the confidentiality of your account credentials.</p>\n<p>We reserve the right to modify or discontinue our services at any time without notice.</p>\n<h3>3. Intellectual Property</h3>\n<p>All content, logos, trademarks, and intellectual property displayed on our website or provided through our services are the property of [Your Company Name].</p>\n<p>You may not reproduce, distribute, or use our intellectual property without our express written consent.</p>\n<h3>4. Privacy Policy</h3>\n<p>Our Privacy Policy outlines how we collect, use, and protect your personal information. By using our services, you agree to our Privacy Policy.</p>\n<h3>5. Payments and Fees</h3>\n<p>Payment terms for our services are specified at the time of purchase or registration.</p>\n<p>We may change our pricing and fees at any time and will notify you of such changes.</p>\n<h3>6. Termination</h3>\n<p>We reserve the right to terminate or suspend your account and access to our services for any reason, including a violation of these terms.</p>\n<h3>7. Disclaimers</h3>\n<p>Our services are provided \"as is\" and without warranties of any kind, either expressed or implied.</p>\n<p>We do not guarantee the accuracy, completeness, or reliability of any content provided through our services.</p>\n<h3>8. Limitation of Liability</h3>\n<p>We are not liable for any indirect, incidental, consequential, or punitive damages arising out of your use of our services.</p>\n<h3>9. Governing Law</h3>\n<p>These terms and conditions are governed by the laws of [Your Jurisdiction], without regard to its conflict of laws principles.</p>\n<h3>10. Changes to Terms and Conditions</h3>\n<p>We may update these Terms and Conditions from time to time. The revised terms will be posted on our website, and the date of the latest update will be indicated.</p>\n<h3>Contact Us</h3>\n<p>If you have any questions or concerns about these Terms and Conditions, please contact us at [Your Contact Information].</p>\n<p>By using our services, you agree to these Terms and Conditions.</p>', NULL, '2023-09-15 12:03:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allow_pincode`
--
ALTER TABLE `allow_pincode`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assign_role`
--
ALTER TABLE `assign_role`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ar_role_id` (`role_id`),
  ADD KEY `ar_sync_id` (`user_id`);

--
-- Indexes for table `available_delivery_location`
--
ALTER TABLE `available_delivery_location`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banner_image`
--
ALTER TABLE `banner_image`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_p_id` (`product_id`),
  ADD KEY `cat_uid` (`user_id`);

--
-- Indexes for table `cat`
--
ALTER TABLE `cat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `city`
--
ALTER TABLE `city`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice_setting`
--
ALTER TABLE `invoice_setting`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_uid` (`user_id`),
  ADD KEY `order_txid` (`trasation_id`),
  ADD KEY `order_pid` (`product_id`),
  ADD KEY `order_addid` (`address_id`);

--
-- Indexes for table `order_user_assign`
--
ALTER TABLE `order_user_assign`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oau_user_id` (`user_id`),
  ADD KEY `oau_order_id` (`order_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `payment_gateway`
--
ALTER TABLE `payment_gateway`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `p_sub_cat` (`sub_cat_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `social_media`
--
ALTER TABLE `social_media`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `specific_notification`
--
ALTER TABLE `specific_notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sp_noti_user_id` (`user_id`);

--
-- Indexes for table `subscribed_order_delivery`
--
ALTER TABLE `subscribed_order_delivery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sdo_orde_id` (`order_id`),
  ADD KEY `sdo_user_id` (`entry_user_id`);

--
-- Indexes for table `sub_cat`
--
ALTER TABLE `sub_cat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `s_cat_id` (`cat_id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `txn_uid` (`user_id`),
  ADD KEY `txn_order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `user_address`
--
ALTER TABLE `user_address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `address_uid` (`user_id`);

--
-- Indexes for table `user_holiday`
--
ALTER TABLE `user_holiday`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_notification`
--
ALTER TABLE `user_notification`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_app_settings`
--
ALTER TABLE `web_app_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_pages`
--
ALTER TABLE `web_pages`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allow_pincode`
--
ALTER TABLE `allow_pincode`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_settings`
--
ALTER TABLE `app_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `assign_role`
--
ALTER TABLE `assign_role`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `available_delivery_location`
--
ALTER TABLE `available_delivery_location`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `banner_image`
--
ALTER TABLE `banner_image`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cat`
--
ALTER TABLE `cat`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `city`
--
ALTER TABLE `city`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` bigint(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_setting`
--
ALTER TABLE `invoice_setting`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_user_assign`
--
ALTER TABLE `order_user_assign`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_gateway`
--
ALTER TABLE `payment_gateway`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `social_media`
--
ALTER TABLE `social_media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `specific_notification`
--
ALTER TABLE `specific_notification`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscribed_order_delivery`
--
ALTER TABLE `subscribed_order_delivery`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sub_cat`
--
ALTER TABLE `sub_cat`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=543;

--
-- AUTO_INCREMENT for table `user_address`
--
ALTER TABLE `user_address`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_holiday`
--
ALTER TABLE `user_holiday`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_notification`
--
ALTER TABLE `user_notification`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `web_app_settings`
--
ALTER TABLE `web_app_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `web_pages`
--
ALTER TABLE `web_pages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assign_role`
--
ALTER TABLE `assign_role`
  ADD CONSTRAINT `ar_role_id` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`),
  ADD CONSTRAINT `ar_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_p_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  ADD CONSTRAINT `cat_uid` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `order_addid` FOREIGN KEY (`address_id`) REFERENCES `user_address` (`id`),
  ADD CONSTRAINT `order_pid` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  ADD CONSTRAINT `order_txid` FOREIGN KEY (`trasation_id`) REFERENCES `transactions` (`id`),
  ADD CONSTRAINT `order_uid` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_user_assign`
--
ALTER TABLE `order_user_assign`
  ADD CONSTRAINT `oau_order_id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `oau_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `p_sub_cat` FOREIGN KEY (`sub_cat_id`) REFERENCES `sub_cat` (`id`);

--
-- Constraints for table `specific_notification`
--
ALTER TABLE `specific_notification`
  ADD CONSTRAINT `sp_noti_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `subscribed_order_delivery`
--
ALTER TABLE `subscribed_order_delivery`
  ADD CONSTRAINT `sdo_orde_id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `sdo_user_id` FOREIGN KEY (`entry_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `sub_cat`
--
ALTER TABLE `sub_cat`
  ADD CONSTRAINT `s_cat_id` FOREIGN KEY (`cat_id`) REFERENCES `cat` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `txn_order_id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `txn_uid` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
