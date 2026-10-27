-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2026 at 12:29 PM
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
-- Database: `ansh2`
--

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
  `wages_rate_per_day` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_code`, `first_name`, `last_name`, `email`, `phone`, `reference_number1`, `reference_number2`, `emp_type`, `department`, `designation`, `work_location`, `joining_date`, `leaving_date`, `aadhar_number`, `driving_license_number`, `account_number`, `ifsc_code`, `uan_number`, `esi_number`, `status`, `created_at`, `updated_at`, `id_card_status`, `dress_status`, `police_verification_status`, `police_certificate_number`, `photo`, `wages_rate_per_day`) VALUES
(1, 'AE/1026/1', 'Test', 'User1', 'testuser1@gmail.com', NULL, NULL, NULL, '2', 'Test', 'Lorem Test', NULL, '2026-10-16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-10-16 01:03:08', '2026-10-16 01:17:14', '0', '0', '0', NULL, NULL, NULL),
(2, 'AE/1026/2', 'Test', 'User2', 'testuser2@gmail.com', NULL, NULL, NULL, '3', 'Test', 'Lorem Lipsum', NULL, '2026-10-16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-10-16 01:11:43', '2026-10-16 01:16:54', '0', '0', '0', NULL, NULL, NULL),
(3, 'AE/1026/3', 'Test', 'User3', 'testuser3@gmail.com', NULL, NULL, NULL, '1', 'Dummy', 'Sample D', NULL, '2026-10-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-10-16 01:12:18', '2026-10-27 01:44:53', '0', '0', '0', NULL, NULL, 3500.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_code` (`employee_code`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
