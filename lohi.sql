-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 25, 2025 at 04:23 AM
-- Server version: 10.4.14-MariaDB
-- PHP Version: 7.4.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lombok_hiking`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `trip_id` int(11) DEFAULT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `total_price` decimal(10,2) DEFAULT NULL,
  `addon_fee` decimal(10,2) DEFAULT 0.00,
  `mountain_ticket_id` int(11) NOT NULL,
  `selected_guide_id` int(11) DEFAULT NULL,
  `booking_type` enum('regular','regular_with_guide','package') NOT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `trip_id`, `booking_date`, `status`, `total_price`, `addon_fee`, `mountain_ticket_id`, `selected_guide_id`, `booking_type`, `bukti_pembayaran`, `start_date`, `end_date`) VALUES
(12, 3, NULL, '2025-06-24 16:00:00', 'confirmed', '1000000.00', '750000.00', 1, 1, 'regular', NULL, '2025-06-25', '2025-06-29'),
(13, 817, NULL, '2025-06-24 16:00:00', 'confirmed', '500000.00', '1300000.00', 2, 3, 'regular', NULL, '2025-06-18', '2025-06-27');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `guide_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `feedback_mountains`
--

CREATE TABLE `feedback_mountains` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `guide`
--

CREATE TABLE `guide` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `profile_picture` varchar(255) NOT NULL,
  `rating` decimal(10,0) NOT NULL,
  `specialization` text DEFAULT NULL,
  `experience` text DEFAULT NULL,
  `languages` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `guide`
--

INSERT INTO `guide` (`id`, `user_id`, `profile_picture`, `rating`, `specialization`, `experience`, `languages`, `bio`, `status`, `submitted_at`) VALUES
(1, 2, '', '0', 'Volcanic', '5', 'English', '222', 'approved', '2025-06-24 15:41:36'),
(2, 820, '', '0', 'gunung merapi', 'tawakal', 'indo, inggris, jepang, semua', 'ga ada', 'pending', '2025-06-25 02:01:56'),
(3, 821, '', '0', 'tracking', '10 tahun', 'semua ', 'no', 'pending', '2025-06-25 02:04:20');

-- --------------------------------------------------------

--
-- Table structure for table `guide_applications`
--

CREATE TABLE `guide_applications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `no_hp` VARCHAR(20) NOT NULL,
  `cv_file` VARCHAR(255) NOT NULL,
  `ktp_file` VARCHAR(255) NOT NULL,
  `foto_file` VARCHAR(255) NOT NULL,
  `status` ENUM('pending','accepted','rejected') DEFAULT 'pending',
  `tanggal_pengajuan` DATETIME NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- --------------------------------------------------------

--
-- Table structure for table `guide_services`
--

CREATE TABLE `guide_services` (
  `id` int(11) NOT NULL,
  `guide_id` int(11) DEFAULT NULL,
  `mountain_id` int(11) DEFAULT NULL,
  `service_fee` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `guide_services`
--

INSERT INTO `guide_services` (`id`, `guide_id`, `mountain_id`, `service_fee`, `description`, `active`, `created_at`) VALUES
(1, 1, 3, '150000.00', 'Pendakian Rinjani dengan guide berpengalaman', 1, '2025-06-24 20:44:17'),
(2, 2, 4, '120000.00', 'Pendakian pal jepang dengan guide berpengalaman', 1, '2025-06-24 20:44:17'),
(3, 3, 5, '130000.00', 'Pendakian pal jepang dengan guide berpengalaman', 1, '2025-06-24 20:44:17');

-- --------------------------------------------------------

--
-- Table structure for table `mountains`
--

CREATE TABLE `mountains` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mountains`
--

INSERT INTO `mountains` (`id`, `name`, `location`, `height`, `description`, `image`) VALUES
(3, 'Gunung Rinjani', NULL, 3726, 'Gunung Rinjani merupakan gunung berapi aktif yang terletak di Pulau Lombok, Nusa Tenggara Barat. Gunung ini menjadi salah satu destinasi favorit para pendaki karena menawarkan panorama alam yang memukau, seperti Danau Segara Anak, padang savana, dan puncak dengan ketinggian lebih dari 3700 meter.', '1750772257_MOUNT-RINJANI-TREKKING-SOUTH-SUMMIT-VIA-TIMBANUH-1024x512.jpg'),
(4, 'Pal Jepang', NULL, 1743, 'Gunung Pal Jepang merupakan salah satu destinasi pendakian yang berada di kawasan Lombok. Gunung ini dikenal dengan jalur yang relatif landai dan cocok untuk pendaki pemula. Pemandangan sepanjang jalur berupa hutan rindang dan area terbuka sangat cocok untuk aktivitas hiking santai atau rekreasi alam.', '1750772424_pal jepang.jpg'),
(5, 'Bukit Anak Dara', NULL, 1923, 'Bukit Anak Dara adalah salah satu bukit tertinggi di kawasan Sembalun, Lombok Timur. Destinasi ini terkenal dengan pemandangan matahari terbit yang menakjubkan serta panorama pegunungan yang mengelilingi Sembalun. Jalur pendakian relatif singkat dan cocok bagi pendaki pemula maupun wisatawan yang ingin menikmati keindahan alam tanpa harus mendaki gunung tinggi.', '1750781499_anakdara.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `mountain_tickets`
--

CREATE TABLE `mountain_tickets` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `type` enum('regular','package') NOT NULL,
  `mountain_id` int(11) DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `description_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mountain_tickets`
--

INSERT INTO `mountain_tickets` (`id`, `title`, `type`, `mountain_id`, `image_id`, `price`, `description_id`, `status`, `created_at`) VALUES
(1, 'Pendakian Rinjani', 'regular', 3, NULL, '1000000.00', NULL, 'active', '2025-06-24 18:40:56'),
(2, 'Pendakian Anak dara', 'regular', 5, NULL, '500000.00', NULL, 'active', '2025-06-24 18:42:30'),
(3, 'Pendakian Pal Jepang', 'regular', 4, NULL, '500000.00', NULL, 'active', '2025-06-24 18:42:58');

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi_contact_me`
--

CREATE TABLE `notifikasi_contact_me` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `pesan` text DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi_guide`
--

CREATE TABLE `notifikasi_guide` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `spesialis` text DEFAULT NULL,
  `pengalaman` text DEFAULT NULL,
  `waktu_pengajuan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `payment_code` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `status` enum('unpaid','paid','rejected') DEFAULT 'unpaid',
  `payment_proof` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pendapatan_guide`
--

CREATE TABLE `pendapatan_guide` (
  `id` int(11) NOT NULL,
  `guide_id` int(11) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `system_fee` decimal(10,2) DEFAULT NULL,
  `source` enum('package','regular_addon') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

CREATE TABLE `trips` (
  `id` int(11) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `type` enum('regular','package') NOT NULL,
  `mountain_id` int(11) DEFAULT NULL,
  `guide_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `package_price` decimal(10,2) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `facilities` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `image` varchar(255) DEFAULT NULL,
  `mountain_ticket_id` int(11) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `trips`
--

INSERT INTO `trips` (`id`, `title`, `type`, `mountain_id`, `guide_id`, `start_date`, `end_date`, `package_price`, `capacity`, `facilities`, `status`, `image`, `mountain_ticket_id`, `description`) VALUES
(1, 'ay', 'package', 3, 1, '2025-06-26', '2025-06-30', '1500000.00', 5, 'makan', 'active', '0', 1, 'aqkiahj'),
(4, 'naik gunung', 'package', 3, 1, '2025-06-30', '2025-07-05', '2000000.00', 7, 'pasangan', 'active', '0', 1, 'xyz'),
(5, 'a', 'package', 4, 1, '2025-07-02', '2025-07-05', '2000000.00', 10, 'diana', 'active', 'uploads/trips/trip_1750799875.png', 3, 'kan'),
(7, 'asderf', 'package', 3, 1, '2025-06-26', '2025-06-29', '10000000.00', 2, 'qqq', 'active', 'uploads/trips/trip_1750799857.png', 1, 'szs');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','guide','user') DEFAULT 'user',
  `profile_picture` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `role`, `profile_picture`, `active`, `created_at`) VALUES
(1, 'ayu', 'adiraa@gmail.com', '12345678', '0111111', 'user', NULL, 1, '2025-06-24 10:48:08'),
(2, 'andi', 'andip@gmail.com', '$2y$10$050VFnESR7bh.gRH61UiSuzhtN.NReF3CRO2wE.sbqkEKXl/Eg.mK', '1234567890908', 'guide', 'profile_2_1750780095.jpg', 1, '2025-06-24 11:04:42'),
(3, 'Lissaa', 'Lissa@gmail.com', '$2y$10$Tw0r6Lt/dJj0YS5bfrXZQuUFqx29VgJUYSWY9qWcKwLMAGIgaG.Te', '085761523987', 'user', 'default.jpg', 1, '2025-06-24 03:24:10'),
(4, 'zara', 'zahraaraa688@gmail.com', '$2y$10$/hgrfnjdqvEg4aUpMX0n3.PoNLMEfaUm.f81IgmZzwYxdNyQOx.uO', '087761523132', 'admin', 'profile_685ae71f2da84.jpeg', 1, '2025-06-24 03:25:02'),
(817, 'alii al', 'ali@gmail.com', '$2y$10$dg8weK6P1ZiAmwpE8RDumOnb9Iu4CcRQjD80.9DbSq9fxFWZ5G/Xa', '87761523132', 'user', NULL, 1, '2025-06-24 04:46:38'),
(820, 'nana', 'nana@gmail.com', '$2y$10$3VErz1Xq3Phx5r5gl3lTC.c94B9j2qiZ4YBnELr9o02y5gUf8Jz/6', '009989765', 'guide', NULL, 1, '2025-06-25 02:01:56'),
(821, 'ciki gawang', 'cikigawang@gmail.com', '$2y$10$IbkHALAhKMRY3db7yAZzF.l12k9MEpUdoCnQDSRCjLIzwkppNtKk.', '01010292928', 'guide', NULL, 1, '2025-06-25 02:04:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `trip_id` (`trip_id`),
  ADD KEY `mountain_ticket_id` (`mountain_ticket_id`),
  ADD KEY `selected_guide_id` (`selected_guide_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `guide_id` (`guide_id`);

--
-- Indexes for table `feedback_mountains`
--
ALTER TABLE `feedback_mountains`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guide`
--
ALTER TABLE `guide`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `guide_services`
--
ALTER TABLE `guide_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guide_id` (`guide_id`),
  ADD KEY `mountain_id` (`mountain_id`);

--
-- Indexes for table `mountains`
--
ALTER TABLE `mountains`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mountain_tickets`
--
ALTER TABLE `mountain_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mountain_id` (`mountain_id`),
  ADD KEY `image_id` (`image_id`),
  ADD KEY `description_id` (`description_id`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifikasi_contact_me`
--
ALTER TABLE `notifikasi_contact_me`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifikasi_guide`
--
ALTER TABLE `notifikasi_guide`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `pendapatan_guide`
--
ALTER TABLE `pendapatan_guide`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guide_id` (`guide_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mountain_id` (`mountain_id`),
  ADD KEY `guide_id` (`guide_id`),
  ADD KEY `mountain_ticket_id` (`mountain_ticket_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback_mountains`
--
ALTER TABLE `feedback_mountains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guide`
--
ALTER TABLE `guide`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `guide_services`
--
ALTER TABLE `guide_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mountains`
--
ALTER TABLE `mountains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mountain_tickets`
--
ALTER TABLE `mountain_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifikasi_contact_me`
--
ALTER TABLE `notifikasi_contact_me`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifikasi_guide`
--
ALTER TABLE `notifikasi_guide`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pendapatan_guide`
--
ALTER TABLE `pendapatan_guide`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trips`
--
ALTER TABLE `trips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=822;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`),
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`mountain_ticket_id`) REFERENCES `mountain_tickets` (`id`),
  ADD CONSTRAINT `bookings_ibfk_4` FOREIGN KEY (`selected_guide_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `feedback_ibfk_3` FOREIGN KEY (`guide_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `guide`
--
ALTER TABLE `guide`
  ADD CONSTRAINT `guide_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `guide_services`
--
ALTER TABLE `guide_services`
  ADD CONSTRAINT `guide_services_ibfk_1` FOREIGN KEY (`guide_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `guide_services_ibfk_2` FOREIGN KEY (`mountain_id`) REFERENCES `mountains` (`id`);

--
-- Constraints for table `mountain_tickets`
--
ALTER TABLE `mountain_tickets`
  ADD CONSTRAINT `mountain_tickets_ibfk_1` FOREIGN KEY (`mountain_id`) REFERENCES `mountains` (`id`),
  ADD CONSTRAINT `mountain_tickets_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `mountains` (`id`),
  ADD CONSTRAINT `mountain_tickets_ibfk_3` FOREIGN KEY (`description_id`) REFERENCES `mountains` (`id`);

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifikasi_contact_me`
--
ALTER TABLE `notifikasi_contact_me`
  ADD CONSTRAINT `notifikasi_contact_me_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifikasi_guide`
--
ALTER TABLE `notifikasi_guide`
  ADD CONSTRAINT `notifikasi_guide_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

--
-- Constraints for table `pendapatan_guide`
--
ALTER TABLE `pendapatan_guide`
  ADD CONSTRAINT `pendapatan_guide_ibfk_1` FOREIGN KEY (`guide_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pendapatan_guide_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

--
-- Constraints for table `trips`
--
ALTER TABLE `trips`
  ADD CONSTRAINT `trips_ibfk_1` FOREIGN KEY (`mountain_id`) REFERENCES `mountains` (`id`),
  ADD CONSTRAINT `trips_ibfk_2` FOREIGN KEY (`guide_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `trips_ibfk_3` FOREIGN KEY (`mountain_ticket_id`) REFERENCES `mountain_tickets` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
