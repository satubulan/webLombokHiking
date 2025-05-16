
-- Create database
CREATE DATABASE IF NOT EXISTS lombok_hiking;
USE lombok_hiking;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'guide', 'admin') NOT NULL DEFAULT 'user',
    phone VARCHAR(20),
    image_url VARCHAR(255),
    active BOOLEAN NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create mountains table
CREATE TABLE IF NOT EXISTS mountains (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    height INT NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    difficulty ENUM('Easy', 'Moderate', 'Hard', 'Expert') NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    category VARCHAR(255) NOT NULL,
    estimated_time VARCHAR(50) NOT NULL,
    distance VARCHAR(50) NOT NULL,
    popularity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create guides table
CREATE TABLE IF NOT EXISTS guides (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    name VARCHAR(100) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    experience INT NOT NULL,
    rating DECIMAL(2,1) NOT NULL,
    specialization VARCHAR(255) NOT NULL,
    bio TEXT NOT NULL,
    languages VARCHAR(255) NOT NULL,
    active BOOLEAN NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create trips table
CREATE TABLE IF NOT EXISTS trips (
    id VARCHAR(36) PRIMARY KEY,
    mountain_id VARCHAR(36) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    duration INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    max_participants INT NOT NULL,
    current_participants INT NOT NULL DEFAULT 0,
    guide_id VARCHAR(36) NOT NULL,
    included TEXT NOT NULL,
    not_included TEXT NOT NULL,
    meeting_point VARCHAR(255) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    featured BOOLEAN NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mountain_id) REFERENCES mountains(id),
    FOREIGN KEY (guide_id) REFERENCES guides(id)
);

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    trip_id VARCHAR(36) NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    participants INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_proof VARCHAR(255),
    payment_date TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (trip_id) REFERENCES trips(id)
);

-- Create feedback table
CREATE TABLE IF NOT EXISTS feedbacks (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    replied BOOLEAN NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample data for users
INSERT INTO users (id, name, email, password, role, phone, active) VALUES
('u1', 'Administrator', 'admin@lombokhiking.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '081234567890', 1),
('u2', 'Ahmad Guide', 'ahmad@guide.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guide', '081234567891', 1),
('u3', 'Budi Guide', 'budi@guide.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guide', '081234567892', 1),
('u4', 'Citra Guide', 'citra@guide.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guide', '081234567893', 1),
('u5', 'John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '081234567894', 1);

-- Sample data for mountains
INSERT INTO mountains (id, name, height, location, description, difficulty, image_url, category, estimated_time, distance, popularity) VALUES
('m1', 'Gunung Rinjani', 3726, 'Lombok Timur, NTB', 'Gunung Rinjani adalah gunung berapi tertinggi kedua di Indonesia dengan ketinggian 3.726 mdpl. Gunung ini merupakan bagian dari Taman Nasional Gunung Rinjani yang memiliki luas sekitar 41.330 ha. Gunung ini dikenal dengan keindahan Danau Segara Anak di ketinggian 2.008 mdpl.', 'Hard', 'assets/images/mountains/rinjani.jpg', 'High Peak,Volcanic,Lake', '3-4 hari', '45 km', 100),
('m2', 'Bukit Pergasingan', 1700, 'Sembalun, Lombok Timur', 'Bukit Pergasingan terletak di desa Sembalun, Lombok Timur. Dengan ketinggian sekitar 1.700 mdpl, bukit ini menawarkan pemandangan padang savana yang luas dan indah. Dari puncaknya, pengunjung dapat melihat hamparan sawah, desa tradisional, serta Gunung Rinjani.', 'Easy', 'assets/images/mountains/pergasingan.jpg', 'Savanna,Beginner Friendly', '4-5 jam', '7 km', 85),
('m3', 'Bukit Anak Dara', 1800, 'Sembalun, Lombok Timur', 'Bukit Anak Dara berada di kawasan Sembalun, Lombok Timur dengan ketinggian sekitar 1.800 mdpl. Disebut Anak Dara karena bentuknya yang menyerupai gadis yang sedang berbaring. Bukit ini terkenal dengan padang rumputnya yang luas dan pemandangan sunrise yang spektakuler.', 'Moderate', 'assets/images/mountains/anak_dara.jpg', 'Savanna,High Peak', '5-6 jam', '10 km', 75),
('m4', 'Air Terjun Sendang Gile', 850, 'Senaru, Lombok Utara', 'Air Terjun Sendang Gile terletak di desa Senaru, kaki Gunung Rinjani. Meski bukan gunung, tempat ini populer untuk trekking pendek. Air terjun setinggi sekitar 30 meter ini memiliki air yang jernih dan sejuk, ideal untuk berenang.', 'Easy', 'assets/images/mountains/sendang_gile.jpg', 'Waterfall,Beginner Friendly', '1-2 jam', '3 km', 80),
('m5', 'Bukit Selong', 600, 'Sembalun, Lombok Timur', 'Bukit Selong menawarkan pemandangan panorama persawahan berbentuk geometris yang indah. Dengan ketinggian sekitar 600 mdpl, bukit ini cocok untuk pendakian singkat dan fotografi. Lokasi ini sangat populer untuk menikmati sunrise.', 'Easy', 'assets/images/mountains/selong.jpg', 'Beginner Friendly,Savanna', '30-45 menit', '1 km', 90);

-- Sample data for guides
INSERT INTO guides (id, user_id, name, image_url, experience, rating, specialization, bio, languages, active) VALUES
('g1', 'u2', 'Ahmad Rinjani', 'assets/images/guides/guide1.jpg', 8, 4.9, 'High Peak,Volcanic', 'Ahmad adalah pemandu lokal berpengalaman khusus untuk pendakian Gunung Rinjani. Dengan pengalaman 8 tahun, Ahmad telah memandu ratusan pendaki dari berbagai negara.', 'Bahasa Indonesia,English,Japanese', 1),
('g2', 'u3', 'Budi Sembalun', 'assets/images/guides/guide2.jpg', 5, 4.7, 'Savanna,Beginner Friendly', 'Budi menguasai jalur-jalur pendakian di kawasan Sembalun, termasuk Bukit Pergasingan dan Anak Dara. Ia sangat ramah dan sabar dengan pendaki pemula.', 'Bahasa Indonesia,English', 1),
('g3', 'u4', 'Citra Senaru', 'assets/images/guides/guide3.jpg', 6, 4.8, 'Waterfall,Lake,Beginner Friendly', 'Citra adalah pemandu yang fokus pada area Senaru dan air terjun. Ia memiliki pengetahuan mendalam tentang flora dan fauna lokal serta sejarah budaya Lombok.', 'Bahasa Indonesia,English,German', 1);

-- Sample data for trips
INSERT INTO trips (id, mountain_id, title, description, start_date, end_date, duration, price, max_participants, current_participants, guide_id, included, not_included, meeting_point, image_url, featured) VALUES
('t1', 'm1', 'Pendakian Rinjani via Sembalun', 'Paket pendakian Gunung Rinjani melalui jalur Sembalun selama 3 hari 2 malam. Nikmati keindahan Danau Segara Anak dan pemandangan sunrise dari puncak.', '2023-07-15', '2023-07-17', 3, 2500000.00, 15, 8, 'g1', 'Guide lokal berpengalaman,Transportasi dari bandara/hotel,Peralatan camping,Makanan selama pendakian,Dana konservasi,Sertifikat pendakian', 'Tiket pesawat,Asuransi perjalanan,Tips,Sewa peralatan pribadi', 'Hotel Sembalun Highland', 'assets/images/trips/trip_rinjani1.jpg', 1),
('t2', 'm1', 'Rinjani Summit Attack', 'Paket khusus untuk pendaki berpengalaman yang ingin mencapai puncak Rinjani dalam waktu singkat. Jalur via Senaru dengan durasi 2 hari 1 malam.', '2023-07-20', '2023-07-21', 2, 1800000.00, 10, 3, 'g1', 'Guide ahli,Peralatan camping,Makanan selama pendakian,Dana konservasi,Sertifikat pendakian', 'Transportasi ke titik start,Tiket pesawat,Asuransi perjalanan,Tips,Sewa peralatan pribadi', 'Pos Pendakian Senaru', 'assets/images/trips/trip_rinjani2.jpg', 0),
('t3', 'm2', 'Sunrise di Bukit Pergasingan', 'Nikmati keindahan matahari terbit dari Bukit Pergasingan. Paket 2 hari 1 malam dengan camping di puncak bukit.', '2023-07-10', '2023-07-11', 2, 800000.00, 20, 15, 'g2', 'Guide lokal,Transportasi dari Mataram,Peralatan camping,Makan 3x,Air mineral', 'Tiket pesawat,Asuransi perjalanan,Tips,Makanan tambahan', 'Terminal Bus Sembalun', 'assets/images/trips/trip_pergasingan.jpg', 1),
('t4', 'm3', 'Trekking Anak Dara', 'Jalur pendakian yang tidak terlalu sulit dengan pemandangan savana yang menakjubkan. Cocok untuk pendaki pemula dan fotografi.', '2023-07-25', '2023-07-26', 2, 850000.00, 15, 5, 'g2', 'Guide lokal,Transportasi dari Mataram,Peralatan camping,Makan 3x,Air mineral', 'Tiket pesawat,Asuransi perjalanan,Tips,Makanan tambahan', 'Desa Sembalun Lawang', 'assets/images/trips/trip_anakdara.jpg', 0),
('t5', 'm4', 'Wisata Air Terjun Sendang Gile & Tiu Kelep', 'Paket trekking ringan mengunjungi Air Terjun Sendang Gile dan Air Terjun Tiu Kelep dalam satu hari.', '2023-07-05', '2023-07-05', 1, 350000.00, 25, 10, 'g3', 'Guide lokal,Transportasi dari Senggigi,Makan siang,Air mineral,Tiket masuk', 'Tiket pesawat,Asuransi perjalanan,Tips,Peralatan renang', 'Hotel Senaru', 'assets/images/trips/trip_sendanggile.jpg', 1);

-- Sample data for bookings
INSERT INTO bookings (id, user_id, trip_id, booking_date, participants, total_price, status, payment_method) VALUES
('b1', 'u5', 't1', '2023-06-15 10:30:00', 2, 5000000.00, 'confirmed', 'bank_transfer'),
('b2', 'u5', 't3', '2023-06-20 14:45:00', 1, 800000.00, 'pending', 'e-wallet');

-- Sample data for feedback
INSERT INTO feedbacks (id, name, email, message, replied, created_at) VALUES
('f1', 'Maria Johnson', 'maria@example.com', 'Saya ingin tahu lebih banyak tentang paket pendakian ke Gunung Rinjani untuk bulan Agustus.', 0, '2023-06-10 09:15:00'),
('f2', 'Robert Chen', 'robert@example.com', 'Pengalaman pendakian dengan guide Ahmad sangat memuaskan! Terima kasih LombokHiking.', 1, '2023-06-05 16:20:00');
