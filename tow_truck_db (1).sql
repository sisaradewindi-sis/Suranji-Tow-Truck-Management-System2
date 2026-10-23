-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Oct 23, 2026 at 09:47 AM
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
-- Database: `tow_truck_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `driver_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `operators`
--

CREATE TABLE `operators` (
  `operator_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `operators`
--

INSERT INTO `operators` (`operator_id`, `user_id`, `created_at`) VALUES
(1, 7, '2026-10-22 07:26:37');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_status` varchar(30) NOT NULL DEFAULT 'Pending',
  `transaction_reference` varchar(100) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tow_requests`
--

CREATE TABLE `tow_requests` (
  `request_id` int(11) NOT NULL,
  `customer_user_id` int(11) DEFAULT NULL,
  `created_by_operator_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `vehicle_brand` varchar(100) DEFAULT NULL,
  `vehicle_model` varchar(100) DEFAULT NULL,
  `vehicle_registration` varchar(30) DEFAULT NULL,
  `vehicle_colour` varchar(50) DEFAULT NULL,
  `problem_description` text DEFAULT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `additional_details` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `distance_km` decimal(10,2) DEFAULT NULL,
  `total_fee` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tow_requests`
--

INSERT INTO `tow_requests` (`request_id`, `customer_user_id`, `created_by_operator_id`, `customer_name`, `customer_phone`, `vehicle_type`, `vehicle_brand`, `vehicle_model`, `vehicle_registration`, `vehicle_colour`, `problem_description`, `pickup_location`, `destination`, `additional_details`, `status`, `created_at`, `distance_km`, `total_fee`) VALUES
(1, 6, NULL, 'P.V.KAMAL', '0709876543', 'heavy', 'toyota', 'h56', 'vf4354', '545465', 'Engine Problem', 'deniyaya', 'mathara', '', 'Pending', '2026-10-22 12:44:08', NULL, NULL),
(2, 6, NULL, 'P.V.KAMAL', '07854433433', 'heavy', 'toyota', 'haylacs', 'nh54646', 'red', 'Engine Problem', 'deniyaya', 'akuressa', '', 'Pending', '2026-10-23 05:07:24', 44.80, 89600.00),
(3, 7, NULL, 'H.K.SISARA', '077896543', 'heavy', 'honda', 'vicel', 'cf4353', 'blak', 'Accident', 'deniyaya', 'kotapola', '', 'Pending', '2026-10-23 06:27:37', 8.91, 17820.00);

-- --------------------------------------------------------

--
-- Table structure for table `truck_assignments`
--

CREATE TABLE `truck_assignments` (
  `assignment_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(30) NOT NULL DEFAULT 'Assigned'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `created_at`, `phone`) VALUES
(1, 'Test User', 'test@suranji.com', '$2y$10$pVN50.gUcJhClbFhiTS70uZvLH7UYM6ztsSs1.2dNugkvBWaj73Ny', 'user', '2026-10-20 13:06:49', NULL),
(2, 'H.L.PERERA', 'PERERA@mali.com', '$2y$10$IH/8cBc7Imhcnxq2f/V0Bu3PETaHKW2yEPxWKdMI.MAsojuQLBrYW', 'user', '2026-10-21 04:36:11', '0712277564'),
(3, 'H.L.PERER', 'PERERdaA@mali.com', '$2y$10$BtnvJvf60LqnCkbLwKkSLeGNu5/cTNaQNR9c2y.Db39krt.GXZ.Km', 'user', '2026-10-21 04:38:02', '0712277555'),
(4, 'H.L SUNIL', 'SUNIL@S.COM', '$2y$10$pRlYFvFh8XXTGitPva0FYOKqz7ucd95f2Tfy6ZWO061kxSdFqZHa2', 'user', '2026-10-21 06:37:44', '0712277564'),
(5, 'D.S.SURANJI', 'SURANJI@S.COM', '$2y$10$Ep0Z6X.ZeGe1dNRtdWT2GeTN7NyE454XlmjakL3XCwumYGWVxMiaO', 'customer', '2026-10-21 06:42:03', '070436578'),
(6, 'P.V.KAMAL', 'KAMAL@K.COM', '$2y$10$hDWRHeOkqceQC2PTXP2gSOH4Qr05YUrftmHAPzeB7Xy0q/2BR0i0q', 'customer', '2026-10-22 04:13:36', '0701111111'),
(7, 'H.K.SISARA', 'SISARA@S.COM', '$2y$10$zR6xOxHsDRc.8Vhp1j.V1ubY3uIfaahoCYZZxUN6ayIHDkaoGhP86', 'operator', '0000-00-00 00:00:00', '0701111112');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`driver_id`),
  ADD KEY `fk_drivers_user_link` (`user_id`);

--
-- Indexes for table `operators`
--
ALTER TABLE `operators`
  ADD PRIMARY KEY (`operator_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `fk_payment_request` (`request_id`);

--
-- Indexes for table `tow_requests`
--
ALTER TABLE `tow_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `fk_request_customer` (`customer_user_id`),
  ADD KEY `fk_request_operator` (`created_by_operator_id`);

--
-- Indexes for table `truck_assignments`
--
ALTER TABLE `truck_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `fk_assignment_request` (`request_id`),
  ADD KEY `fk_assignment_driver` (`driver_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `operators`
--
ALTER TABLE `operators`
  MODIFY `operator_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tow_requests`
--
ALTER TABLE `tow_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `truck_assignments`
--
ALTER TABLE `truck_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `drivers`
--
ALTER TABLE `drivers`
  ADD CONSTRAINT `fk_drivers_user_link` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `operators`
--
ALTER TABLE `operators`
  ADD CONSTRAINT `fk_operators_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_operators_user_link` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_request` FOREIGN KEY (`request_id`) REFERENCES `tow_requests` (`request_id`);

--
-- Constraints for table `tow_requests`
--
ALTER TABLE `tow_requests`
  ADD CONSTRAINT `fk_request_customer` FOREIGN KEY (`customer_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_request_operator` FOREIGN KEY (`created_by_operator_id`) REFERENCES `operators` (`operator_id`);

--
-- Constraints for table `truck_assignments`
--
ALTER TABLE `truck_assignments`
  ADD CONSTRAINT `fk_assignment_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`),
  ADD CONSTRAINT `fk_assignment_request` FOREIGN KEY (`request_id`) REFERENCES `tow_requests` (`request_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
