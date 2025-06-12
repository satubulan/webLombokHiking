-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 11, 2025 at 03:18 PM
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
-- Database: `lombok_hiking`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `id_booking` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `id_gunung` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jumlah` int(11) NOT NULL,
  `metode_pembayaran` varchar(50) NOT NULL,
  `status` varchar(30) DEFAULT 'Menunggu Pembayaran',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `guide_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`id_booking`, `user_id`, `id_gunung`, `tanggal`, `jumlah`, `metode_pembayaran`, `status`, `created_at`, `guide_id`) VALUES
(2, 'u1001', 1, '2025-06-09', 4, 'Transfer Bank', 'Menunggu Pembayaran', '2025-06-08 14:26:31', NULL),
(3, 'u1001', 2, '2025-06-10', 2, 'E-Wallet', 'Menunggu Pembayaran', '2025-06-08 16:42:01', NULL),
(5, 'u1001', 3, '2025-06-12', 4, 'Transfer Bank', 'Menunggu Pembayaran', '2025-06-08 17:39:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `trip_id` varchar(36) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `participants` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `trip_id`, `booking_date`, `participants`, `total_price`, `status`, `payment_method`, `payment_proof`, `payment_date`) VALUES
('b1', 'u5', 't1', '2023-06-15 02:30:00', 2, 5000000.00, 'confirmed', 'bank_transfer', NULL, NULL),
('b2', 'u5', 't3', '2023-06-20 06:45:00', 1, 800000.00, 'pending', 'e-wallet', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `feedbacks`
--

CREATE TABLE `feedbacks` (
  `id` varchar(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `replied` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedbacks`
--

INSERT INTO `feedbacks` (`id`, `name`, `email`, `message`, `replied`, `created_at`) VALUES
('f1', 'Maria Johnson', 'maria@example.com', 'Saya ingin tahu lebih banyak tentang paket pendakian ke Gunung Rinjani untuk bulan Agustus.', 0, '2023-06-10 01:15:00'),
('f2', 'Robert Chen', 'robert@example.com', 'Pengalaman pendakian dengan guide Ahmad sangat memuaskan! Terima kasih LombokHiking.', 1, '2023-06-05 08:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `guides`
--

CREATE TABLE `guides` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `experience` int(11) NOT NULL,
  `rating` decimal(2,1) NOT NULL,
  `specialization` varchar(255) NOT NULL,
  `bio` text NOT NULL,
  `languages` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guides`
--

INSERT INTO `guides` (`id`, `user_id`, `name`, `image_url`, `experience`, `rating`, `specialization`, `bio`, `languages`, `active`, `created_at`) VALUES
('g1', 'u2', 'Ahmad Rinjani', 'assets/images/guides/guide1.jpg', 8, 4.9, 'High Peak,Volcanic', 'Ahmad adalah pemandu lokal berpengalaman khusus untuk pendakian Gunung Rinjani. Dengan pengalaman 8 tahun, Ahmad telah memandu ratusan pendaki dari berbagai negara.', 'Bahasa Indonesia,English,Japanese', 1, '2025-05-06 16:58:41'),
('g2', 'u3', 'Budi Sembalun', 'assets/images/guides/guide2.jpg', 5, 4.7, 'Savanna,Beginner Friendly', 'Budi menguasai jalur-jalur pendakian di kawasan Sembalun, termasuk Bukit Pergasingan dan Anak Dara. Ia sangat ramah dan sabar dengan pendaki pemula.', 'Bahasa Indonesia,English', 1, '2025-05-06 16:58:41'),
('g3', 'u4', 'Citra Senaru', 'assets/images/guides/guide3.jpg', 6, 4.8, 'Waterfall,Lake,Beginner Friendly', 'Citra adalah pemandu yang fokus pada area Senaru dan air terjun. Ia memiliki pengetahuan mendalam tentang flora dan fauna lokal serta sejarah budaya Lombok.', 'Bahasa Indonesia,English,German', 1, '2025-05-06 16:58:41');

-- --------------------------------------------------------

--
-- Table structure for table `gunung`
--

CREATE TABLE `gunung` (
  `id_gunung` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `ketinggian` int(11) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `tentang` text DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `populer` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gunung`
--

INSERT INTO `gunung` (`id_gunung`, `nama`, `alamat`, `ketinggian`, `gambar`, `tentang`, `harga`, `populer`) VALUES
(1, 'Gunung Rinjani', 'Lombok Timur', 3726, 'rinjani.jpg', 'Gunung tertinggi kedua di Indonesia bagian timur dengan pemandangan Danau Segara Anak.', 2500000, 0),
(2, 'Bukit Pergasingan', 'Sembalun', 1670, 'pergasingan.jpg', 'Destinasi favorit untuk pendakian ringan dengan view petak sawah yang indah.', 400000, 0),
(3, 'Gunung Anak Dara', 'Sembalun', 1923, 'anakdara.jpg', 'Pendakian pendek namun menantang dengan pemandangan sunrise spektakuler.', 450000, 0),
(4, 'Bukit Selong', 'Sembalun', 1300, 'bukitselong.jpg', 'Spot populer untuk menikmati sunrise dengan pemandangan desa dan sawah.', 350000, 0);

-- --------------------------------------------------------

--
-- Table structure for table `mountains`
--

CREATE TABLE `mountains` (
  `id` varchar(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `height` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `difficulty` enum('Easy','Moderate','Hard','Expert') NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `estimated_time` varchar(50) NOT NULL,
  `distance` varchar(50) NOT NULL,
  `popularity` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mountains`
--

INSERT INTO `mountains` (`id`, `name`, `height`, `location`, `description`, `difficulty`, `image_url`, `category`, `estimated_time`, `distance`, `popularity`, `created_at`) VALUES
('m1', 'Gunung Rinjani', 3726, 'Lombok Timur, NTB', 'Gunung Rinjani adalah gunung berapi tertinggi kedua di Indonesia dengan ketinggian 3.726 mdpl. Gunung ini merupakan bagian dari Taman Nasional Gunung Rinjani yang memiliki luas sekitar 41.330 ha. Gunung ini dikenal dengan keindahan Danau Segara Anak di ketinggian 2.008 mdpl.', 'Hard', 'rinjani.jpg', 'High Peak,Volcanic,Lake', '3-4 hari', '45 km', 100, '2025-05-06 16:58:41'),
('m2', 'Bukit Pergasingan', 1700, 'Sembalun, Lombok Timur', 'Bukit Pergasingan terletak di desa Sembalun, Lombok Timur. Dengan ketinggian sekitar 1.700 mdpl, bukit ini menawarkan pemandangan padang savana yang luas dan indah. Dari puncaknya, pengunjung dapat melihat hamparan sawah, desa tradisional, serta Gunung Rinjani.', 'Easy', 'pergasingan.jpg', 'Savanna,Beginner Friendly', '4-5 jam', '7 km', 85, '2025-05-06 16:58:41'),
('m3', 'Bukit Anak Dara', 1800, 'Sembalun, Lombok Timur', 'Bukit Anak Dara berada di kawasan Sembalun, Lombok Timur dengan ketinggian sekitar 1.800 mdpl. Disebut Anak Dara karena bentuknya yang menyerupai gadis yang sedang berbaring. Bukit ini terkenal dengan padang rumputnya yang luas dan pemandangan sunrise yang spektakuler.', 'Moderate', 'anakdara.jpg', 'Savanna,High Peak', '5-6 jam', '10 km', 75, '2025-05-06 16:58:41'),
('m5', 'Bukit Selong', 600, 'Sembalun, Lombok Timur', 'Bukit Selong menawarkan pemandangan panorama persawahan berbentuk geometris yang indah. Dengan ketinggian sekitar 600 mdpl, bukit ini cocok untuk pendakian singkat dan fotografi. Lokasi ini sangat populer untuk menikmati sunrise.', 'Easy', 'bukitselong.jpg', 'Beginner Friendly,Savanna', '30-45 menit', '1 km', 90, '2025-05-06 16:58:41');

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notifikasi` int(11) NOT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `pesan` text NOT NULL,
  `waktu` datetime DEFAULT current_timestamp(),
  `sudah_dibaca` tinyint(1) DEFAULT 0,
  `dibaca` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifikasi`
--

INSERT INTO `notifikasi` (`id_notifikasi`, `user_id`, `pesan`, `waktu`, `sudah_dibaca`, `dibaca`) VALUES
(1, 'u1001', 'Pembayaran Anda telah diverifikasi. Selamat mendaki!', '2025-06-09 09:15:09', 1, 1),
(2, 'u1001', 'Admin telah menetapkan guide untuk pendakian Anda.', '2025-06-09 09:15:09', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

CREATE TABLE `trips` (
  `id` varchar(36) NOT NULL,
  `mountain_id` varchar(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `duration` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `max_participants` int(11) NOT NULL,
  `current_participants` int(11) NOT NULL DEFAULT 0,
  `guide_id` varchar(36) NOT NULL,
  `included` text NOT NULL,
  `not_included` text NOT NULL,
  `meeting_point` varchar(255) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trips`
--

INSERT INTO `trips` (`id`, `mountain_id`, `title`, `description`, `start_date`, `end_date`, `duration`, `price`, `max_participants`, `current_participants`, `guide_id`, `included`, `not_included`, `meeting_point`, `image_url`, `featured`, `created_at`) VALUES
('t1', 'm1', 'Pendakian Rinjani via Sembalun', 'Paket pendakian Gunung Rinjani melalui jalur Sembalun selama 3 hari 2 malam. Nikmati keindahan Danau Segara Anak dan pemandangan sunrise dari puncak.', '2023-07-15', '2023-07-17', 3, 2500000.00, 15, 8, 'g1', 'Guide lokal berpengalaman,Transportasi dari bandara/hotel,Peralatan camping,Makanan selama pendakian,Dana konservasi,Sertifikat pendakian', 'Tiket pesawat,Asuransi perjalanan,Tips,Sewa peralatan pribadi', 'Hotel Sembalun Highland', 'assets/images/trips/trip_rinjani1.jpg', 1, '2025-05-06 16:58:41'),
('t2', 'm1', 'Rinjani Summit Attack', 'Paket khusus untuk pendaki berpengalaman yang ingin mencapai puncak Rinjani dalam waktu singkat. Jalur via Senaru dengan durasi 2 hari 1 malam.', '2023-07-20', '2023-07-21', 2, 1800000.00, 10, 3, 'g1', 'Guide ahli,Peralatan camping,Makanan selama pendakian,Dana konservasi,Sertifikat pendakian', 'Transportasi ke titik start,Tiket pesawat,Asuransi perjalanan,Tips,Sewa peralatan pribadi', 'Pos Pendakian Senaru', 'assets/images/trips/trip_rinjani2.jpg', 0, '2025-05-06 16:58:41'),
('t3', 'm2', 'Sunrise di Bukit Pergasingan', 'Nikmati keindahan matahari terbit dari Bukit Pergasingan. Paket 2 hari 1 malam dengan camping di puncak bukit.', '2023-07-10', '2023-07-11', 2, 800000.00, 20, 15, 'g2', 'Guide lokal,Transportasi dari Mataram,Peralatan camping,Makan 3x,Air mineral', 'Tiket pesawat,Asuransi perjalanan,Tips,Makanan tambahan', 'Terminal Bus Sembalun', 'assets/images/trips/trip_pergasingan.jpg', 1, '2025-05-06 16:58:41'),
('t4', 'm3', 'Trekking Anak Dara', 'Jalur pendakian yang tidak terlalu sulit dengan pemandangan savana yang menakjubkan. Cocok untuk pendaki pemula dan fotografi.', '2023-07-25', '2023-07-26', 2, 850000.00, 15, 5, 'g2', 'Guide lokal,Transportasi dari Mataram,Peralatan camping,Makan 3x,Air mineral', 'Tiket pesawat,Asuransi perjalanan,Tips,Makanan tambahan', 'Desa Sembalun Lawang', 'assets/images/trips/trip_anakdara.jpg', 0, '2025-05-06 16:58:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','guide','admin') NOT NULL DEFAULT 'user',
  `phone` varchar(20) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `image_url`, `active`, `created_at`, `avatar`) VALUES
('', 'Hanzzz', 'hanzzz123@gmail.com', '$2y$10$VbioZ..ZI43K4KIyOWVL4OpE7IrxHbFIZ/Q1c173dyJwErj1TvbiO', 'user', '081222333444', NULL, 1, '2025-06-08 02:46:45', NULL),
('a5a49021-0439-45e3-9d12-5b825cf7f957', 'Hanzzz', 'pendaki1@gmail.com', '$2y$10$eU9pT.hLAYprCVyDfIZg/.vy3lpmPRpcA4QR//N.Jk6hh1EkIoqK6', 'user', '081771177878', NULL, 1, '2025-06-07 11:15:08', 'pendaki1.jpg'),
('c99a6a00-5ade-4b00-b7b7-ab4b72cff286', 'Ridho', 'dhodho@gmail.com', '$2y$10$D7vgfJ1jb1BeDGIcGUwtneK35pYuL46j3D6j641p/QYzYgr1eKcQi', 'user', '081222333444', NULL, 1, '2025-06-07 11:17:15', NULL),
('u1001', 'Hanzzz', 'tester@example.com', '$2y$10$mD7.ZD4OPeJYB9ItuhFaEezf/PEwlvlx9OBNjW8X2YH6xiF/yrIom', 'user', '081234567890', NULL, 1, '2025-06-08 08:47:41', 'pendaki1.jpg'),
('u2', 'Hanzzz', 'ahmad@guide.com', '$2y$10$ZGDPlTQX8A89Ynxrb3GJbe/K1q3Xujz8aRice8pXr9CyDyX.vTsTC', 'guide', '081771177878', NULL, 1, '2025-05-06 16:58:41', 'pendaki1.jpg'),
('u3', 'Hanzzz', 'budi@guide.com', '$2y$10$ZGDPlTQX8A89Ynxrb3GJbe/K1q3Xujz8aRice8pXr9CyDyX.vTsTC', 'guide', '081771177878', NULL, 1, '2025-05-06 16:58:41', 'pendaki1.jpg'),
('u4', 'Hanzzz', 'citra@guide.com', '$2y$10$ZGDPlTQX8A89Ynxrb3GJbe/K1q3Xujz8aRice8pXr9CyDyX.vTsTC', 'guide', '081771177878', NULL, 1, '2025-05-06 16:58:41', 'pendaki1.jpg'),
('u5', 'Hanzzz', 'john@example.com', '$2y$10$ZGDPlTQX8A89Ynxrb3GJbe/K1q3Xujz8aRice8pXr9CyDyX.vTsTC', 'user', '081771177878', NULL, 1, '2025-05-06 16:58:41', 'pendaki1.jpg'),
('u684980d571f91', 'abcde', 'hahaha@gmail.com', '$2y$10$qpeA4yQxmTwNIodyt5GX8eQRR7MXc7BQDuPR9w9MqDu1zroyPWvH6', 'user', '085333444555', NULL, 1, '2025-06-11 13:12:53', 'default.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id_booking`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `id_gunung` (`id_gunung`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `trip_id` (`trip_id`);

--
-- Indexes for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guides`
--
ALTER TABLE `guides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `gunung`
--
ALTER TABLE `gunung`
  ADD PRIMARY KEY (`id_gunung`);

--
-- Indexes for table `mountains`
--
ALTER TABLE `mountains`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notifikasi`);

--
-- Indexes for table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mountain_id` (`mountain_id`),
  ADD KEY `guide_id` (`guide_id`);

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
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `id_booking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `gunung`
--
ALTER TABLE `gunung`
  MODIFY `id_gunung` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`id_gunung`) REFERENCES `gunung` (`id_gunung`);

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`);

--
-- Constraints for table `guides`
--
ALTER TABLE `guides`
  ADD CONSTRAINT `guides_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `trips`
--
ALTER TABLE `trips`
  ADD CONSTRAINT `trips_ibfk_1` FOREIGN KEY (`mountain_id`) REFERENCES `mountains` (`id`),
  ADD CONSTRAINT `trips_ibfk_2` FOREIGN KEY (`guide_id`) REFERENCES `guides` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
