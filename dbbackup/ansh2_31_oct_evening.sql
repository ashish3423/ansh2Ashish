-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 31, 2026 at 01:21 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ansh2`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel_cache_5c785c036466adea360111aa28563bfd556b5fba', 'i:1;', 1793425125),
('laravel_cache_5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1793425125;', 1793425125);

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
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `contract_number` varchar(50) NOT NULL,
  `employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `organization` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contract_type` varchar(30) DEFAULT NULL,
  `paid_holidays` tinyint(1) NOT NULL DEFAULT 0,
  `leave_with_wages` tinyint(1) NOT NULL DEFAULT 0,
  `extension_date` date DEFAULT NULL,
  `paid_holidays_percentage` decimal(5,2) DEFAULT NULL,
  `leave_with_wages_percentage` decimal(5,2) DEFAULT NULL,
  `contract_category` varchar(30) NOT NULL DEFAULT 'regular_contract',
  `bonus_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `bonus_percentage` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contracts`
--

INSERT INTO `contracts` (`id`, `contract_number`, `employee_id`, `start_date`, `end_date`, `amount`, `status`, `notes`, `created_at`, `updated_at`, `department`, `organization`, `address`, `contract_type`, `paid_holidays`, `leave_with_wages`, `extension_date`, `paid_holidays_percentage`, `leave_with_wages_percentage`, `contract_category`, `bonus_enabled`, `bonus_percentage`) VALUES
(1, 'GMC-9875825500', NULL, '2026-08-01', '2026-11-30', 2555200.00, 'active', 'lorem ipsum dolor sit amet consectetur adipiscing elit culpa veniam dolorem aliquip est blanditiis soluta anim ex dolore elit repellendus et voluptate blanditiis occaecat qui et et aliquip quibusdam sint eiusmod quibusdam quidem proident in anim praesentium in qui ullamco cillum quibusdam at aut eum nulla sint illum distinctio nulla', '2026-10-31 06:11:25', '2026-10-31 06:13:22', 'Department of Health', 'Health Organization', 'Test Address , Test City 208001', 'Ambulance', 0, 0, NULL, NULL, NULL, 'regular_contract', 0, NULL),
(2, 'GMC-9875642010', NULL, '2026-09-01', '2026-11-20', 2586200.00, 'draft', 'lorem ipsum dolor sit amet consectetur adipiscing elit culpa veniam dolorem aliquip est blanditiis soluta anim ex dolore elit repellendus et voluptate blanditiis occaecat qui et et aliquip quibusdam sint eiusmod quibusdam quidem proident in anim praesentium in qui ullamco cillum quibusdam at aut eum nulla sint illum distinctio nulla', '2026-10-31 06:13:06', '2026-10-31 06:13:06', 'Test Demo Department', 'Test Organization', 'lorem ipsum dolor sit amet 202802', 'Manpower', 0, 0, NULL, NULL, NULL, 'regular_contract', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contract_items`
--

CREATE TABLE `contract_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `contract_id` bigint(20) UNSIGNED NOT NULL,
  `service_type` enum('ambulance','cab','manpower') NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT 1.00,
  `unit_rate` decimal(12,2) DEFAULT 0.00,
  `amount` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contract_service_assignments`
--

CREATE TABLE `contract_service_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `contract_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `service_vehicle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `driver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assistant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contract_service_assignments`
--

INSERT INTO `contract_service_assignments` (`id`, `contract_id`, `employee_id`, `service_vehicle_id`, `driver_id`, `assistant_id`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 1, 1, NULL, '2026-10-31 06:24:00', '2026-10-31 06:24:00');

-- --------------------------------------------------------

--
-- Table structure for table `contract_tables`
--

CREATE TABLE `contract_tables` (
  `id` int(11) NOT NULL,
  `contract_number` varchar(200) NOT NULL,
  `contract_description` text DEFAULT NULL,
  `contract_type` varchar(255) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `extention_date` date DEFAULT NULL,
  `dept` varchar(200) DEFAULT NULL,
  `organization` varchar(200) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contract_tables`
--

INSERT INTO `contract_tables` (`id`, `contract_number`, `contract_description`, `contract_type`, `from_date`, `to_date`, `extention_date`, `dept`, `organization`, `address`, `status`, `created_at`, `updated_at`) VALUES
(1, 'AC-987654GC', NULL, '', '2026-10-02', '2026-10-31', NULL, 'Mechanical', 'Ministry of Defence', 'Kanpur Nagar', 0, '2026-10-28 14:18:36', '2026-10-28 14:18:36');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_code` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `reference_number1` varchar(20) DEFAULT NULL,
  `reference_number2` varchar(20) DEFAULT NULL,
  `emp_type` enum('0','1','2','3') NOT NULL DEFAULT '0' COMMENT 'unskilled 0\r\nskilled 1\r\nsemi skilled 2\r\nhighly skilled 3',
  `department` varchar(100) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `work_location` varchar(255) DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `leaving_date` date DEFAULT NULL,
  `aadhar_number` varchar(255) DEFAULT NULL,
  `driving_license_number` varchar(255) DEFAULT NULL,
  `account_number` bigint(100) DEFAULT NULL,
  `ifsc_code` varchar(200) DEFAULT NULL,
  `uan_number` varchar(200) DEFAULT NULL,
  `esi_number` varchar(200) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `id_card_status` enum('1','0') NOT NULL DEFAULT '0',
  `dress_status` enum('1','0') NOT NULL DEFAULT '0',
  `police_verification_status` enum('1','0') NOT NULL DEFAULT '0',
  `police_certificate_number` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `wages_rate_per_day` decimal(10,2) DEFAULT NULL,
  `epf_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `esi_enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_code`, `first_name`, `last_name`, `email`, `phone`, `reference_number1`, `reference_number2`, `emp_type`, `department`, `designation`, `work_location`, `joining_date`, `leaving_date`, `aadhar_number`, `driving_license_number`, `account_number`, `ifsc_code`, `uan_number`, `esi_number`, `status`, `created_at`, `updated_at`, `id_card_status`, `dress_status`, `police_verification_status`, `police_certificate_number`, `photo`, `wages_rate_per_day`, `epf_enabled`, `esi_enabled`) VALUES
(1, 'AE/1026/1', 'Test', 'User1', 'testuser1@gmail.com', NULL, NULL, NULL, '2', 'Test', 'Lorem Test', NULL, '2026-10-16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-10-16 01:03:08', '2026-10-31 05:30:44', '0', '0', '0', NULL, NULL, 2094.00, 0, 0),
(2, 'AE/1026/2', 'Test', 'User2', 'testuser2@gmail.com', NULL, NULL, NULL, '0', 'Test', 'Lorem Lipsum', NULL, '2026-10-16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-10-16 01:11:43', '2026-10-28 04:40:25', '0', '0', '0', NULL, NULL, 981.00, 1, 1),
(3, 'AE/1026/3', 'Test', 'User3', 'testuser3@gmail.com', NULL, NULL, NULL, '1', 'Dummy', 'Sample D', NULL, '2026-10-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-10-16 01:12:18', '2026-10-28 02:09:31', '0', '0', '0', NULL, NULL, 1094.00, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `employee_compliance_settings`
--

CREATE TABLE `employee_compliance_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `epf_employee_percentage` decimal(5,2) DEFAULT NULL,
  `epf_employer_percentage` decimal(5,2) DEFAULT NULL,
  `esi_employee_percentage` decimal(5,2) DEFAULT NULL,
  `esi_employer_percentage` decimal(5,2) DEFAULT NULL,
  `epf_eligibility_amount` decimal(12,2) DEFAULT NULL,
  `bonus_eligibility_amount` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `esi_eligibility_amount` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_compliance_settings`
--

INSERT INTO `employee_compliance_settings` (`id`, `epf_employee_percentage`, `epf_employer_percentage`, `esi_employee_percentage`, `esi_employer_percentage`, `epf_eligibility_amount`, `bonus_eligibility_amount`, `created_at`, `updated_at`, `esi_eligibility_amount`) VALUES
(1, 12.00, 13.00, 0.75, 3.25, 25000.00, 21000.00, '2026-10-27 01:39:33', '2026-10-31 06:06:57', 21000.00);

-- --------------------------------------------------------

--
-- Table structure for table `employee_salaries`
--

CREATE TABLE `employee_salaries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `basic_salary` decimal(12,2) DEFAULT 0.00,
  `hra` decimal(12,2) DEFAULT 0.00,
  `allowances` decimal(12,2) DEFAULT 0.00,
  `deductions` decimal(12,2) DEFAULT 0.00,
  `gross_salary` decimal(12,2) DEFAULT 0.00,
  `net_salary` decimal(12,2) DEFAULT 0.00,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `working_days` decimal(5,2) NOT NULL DEFAULT 0.00,
  `eligibility_salary` decimal(12,2) NOT NULL DEFAULT 0.00,
  `bonus_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `epf_employee_contribution` decimal(12,2) NOT NULL DEFAULT 0.00,
  `epf_employer_contribution` decimal(12,2) NOT NULL DEFAULT 0.00,
  `esi_employee_contribution` decimal(12,2) NOT NULL DEFAULT 0.00,
  `esi_employer_contribution` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_salary` decimal(12,2) NOT NULL DEFAULT 0.00,
  `contract_bonus_amount` decimal(12,2) NOT NULL DEFAULT 0.00
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
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_10_16_101400_add_photo_to_employees_table', 1),
(5, '2026_10_27_000000_add_role_to_users_table', 1),
(6, '2026_10_28_000000_add_esi_eligibility_amount_to_employee_compliance_settings_table', 2),
(7, '2026_10_28_000001_update_contract_details_and_make_employee_optional', 3),
(8, '2026_10_29_000000_create_service_vehicles_table', 4),
(9, '2026_10_29_000001_create_contract_service_assignments_table', 5),
(10, '2026_10_29_000002_remove_title_from_contracts_table', 6),
(11, '2026_10_29_000003_add_holiday_options_to_contracts_table', 7),
(12, '2026_10_31_000000_add_extension_date_and_holiday_percentages_to_contracts_table', 8),
(13, '2026_10_31_000001_add_epf_esi_eligibility_to_employees_table', 9),
(14, '2026_10_31_000002_add_category_and_bonus_to_contracts_table', 10),
(15, '2026_10_31_000003_add_contract_bonus_amount_to_employee_salaries_table', 11);

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
-- Table structure for table `service_vehicles`
--

CREATE TABLE `service_vehicles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_type` varchar(30) NOT NULL,
  `registration_number` varchar(50) NOT NULL,
  `make_model` varchar(150) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_vehicles`
--

INSERT INTO `service_vehicles` (`id`, `vehicle_type`, `registration_number`, `make_model`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Ambulance', 'UP78DL2589', '2026', 'active', '2026-10-29 00:29:12', '2026-10-29 04:01:30'),
(2, 'Ambulance', 'UP78GJ2570', '2026', 'active', '2026-10-29 00:29:12', '2026-10-29 04:01:11'),
(3, 'Cabs', 'UP78JJ1966', '2025', 'active', '2026-10-29 04:00:23', '2026-10-29 04:00:23'),
(4, 'Cabs', 'UP78LG9876', '2026', 'active', '2026-10-29 04:00:50', '2026-10-29 04:01:41');

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

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('svi4tFMNbI5iA5DoG2RwG4OqMZ7hUTjVXUX78JDw', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiaFhBdVRrazRMVmd5VVM4cWNhM2Nab2w3VHQ5OFB5Z2xsemxSbzNubyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9lbXBsb3llZS1zYWxhcmllcyI7czo1OiJyb3V0ZSI7czoyMzoiZW1wbG95ZWUtc2FsYXJpZXMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjY0OiI0YzJhMzhiODk0YjljOTZhMDZiNWZlOTdiZjk3ZjM5MjRkODhiNjUzNmViNWMxZTUxMTI0NTA3Y2QxZDc2OWJlIjt9', 1793448507);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'sub-admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`) VALUES
(1, 'Ashish', 'ashish@gmail.com', NULL, '$2y$12$E.08dmACXdDcYsPzxrwTf..4Am9z/AjvB7v04W2X2U8k0YrpNjbxi', NULL, '2026-10-28 01:29:00', '2026-10-28 01:29:00', 'super-admin'),
(2, 'Sumit', 'sumit@gmail.com', NULL, '$2y$12$2JuGSiF4ZcX7iFYgtnWkRe22fJelr3zJps.cst5LOracDer5ptuS2', NULL, '2026-10-28 03:59:10', '2026-10-28 03:59:10', 'sub-admin');

--
-- Indexes for dumped tables
--

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
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contracts_contract_number_unique` (`contract_number`),
  ADD KEY `contracts_employee_id_index` (`employee_id`);

--
-- Indexes for table `contract_items`
--
ALTER TABLE `contract_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_contract_item_contract` (`contract_id`);

--
-- Indexes for table `contract_service_assignments`
--
ALTER TABLE `contract_service_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contract_service_assignments_employee_id_foreign` (`employee_id`),
  ADD KEY `contract_service_assignments_service_vehicle_id_foreign` (`service_vehicle_id`),
  ADD KEY `contract_service_assignments_driver_id_foreign` (`driver_id`),
  ADD KEY `contract_service_assignments_assistant_id_foreign` (`assistant_id`),
  ADD KEY `contract_service_assignments_contract_id_employee_id_index` (`contract_id`,`employee_id`);

--
-- Indexes for table `contract_tables`
--
ALTER TABLE `contract_tables`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_code` (`employee_code`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `employee_compliance_settings`
--
ALTER TABLE `employee_compliance_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_salaries`
--
ALTER TABLE `employee_salaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_salary_employee` (`employee_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

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
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `service_vehicles`
--
ALTER TABLE `service_vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_vehicles_registration_number_unique` (`registration_number`),
  ADD KEY `service_vehicles_vehicle_type_status_index` (`vehicle_type`,`status`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_index` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contract_items`
--
ALTER TABLE `contract_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contract_service_assignments`
--
ALTER TABLE `contract_service_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contract_tables`
--
ALTER TABLE `contract_tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employee_compliance_settings`
--
ALTER TABLE `employee_compliance_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employee_salaries`
--
ALTER TABLE `employee_salaries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `service_vehicles`
--
ALTER TABLE `service_vehicles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contract_service_assignments`
--
ALTER TABLE `contract_service_assignments`
  ADD CONSTRAINT `contract_service_assignments_assistant_id_foreign` FOREIGN KEY (`assistant_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contract_service_assignments_contract_id_foreign` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contract_service_assignments_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contract_service_assignments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contract_service_assignments_service_vehicle_id_foreign` FOREIGN KEY (`service_vehicle_id`) REFERENCES `service_vehicles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employee_salaries`
--
ALTER TABLE `employee_salaries`
  ADD CONSTRAINT `fk_salary_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
