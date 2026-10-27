-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2026 at 12:30 PM
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_compliance_settings`
--

INSERT INTO `employee_compliance_settings` (`id`, `epf_employee_percentage`, `epf_employer_percentage`, `esi_employee_percentage`, `esi_employer_percentage`, `epf_eligibility_amount`, `bonus_eligibility_amount`, `created_at`, `updated_at`) VALUES
(1, 12.00, 13.00, 0.75, 3.25, 25000.00, 21000.00, '2026-10-27 01:39:33', '2026-10-27 01:47:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `employee_compliance_settings`
--
ALTER TABLE `employee_compliance_settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `employee_compliance_settings`
--
ALTER TABLE `employee_compliance_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
