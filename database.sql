CREATE DATABASE IF NOT EXISTS lombok_hiking;
USE lombok_hiking;

-- Table for users
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin', 'guide') DEFAULT 'user',
    profile_picture VARCHAR(255) DEFAULT 'assets/images/users/default.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for mountains
DROP TABLE IF EXISTS mountains;
CREATE TABLE mountains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    height INT NOT NULL,
    image_url VARCHAR(255) DEFAULT 'assets/images/mountains/default.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for guides
DROP TABLE IF EXISTS guides;
CREATE TABLE guides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    rating DECIMAL(2,1) DEFAULT 0.0,
    languages VARCHAR(255),
    active TINYINT(1) DEFAULT 1,
    image_url VARCHAR(255) DEFAULT 'assets/images/guides/default.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table for trips
DROP TABLE IF EXISTS trips;
CREATE TABLE trips (
    id VARCHAR(20) PRIMARY KEY,
    mountain_id INT NOT NULL,
    guide_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    duration INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    max_participants INT NOT NULL,
    included TEXT,
    not_included TEXT,
    meeting_point VARCHAR(255),
    image_url VARCHAR(255) DEFAULT 'assets/images/trips/trip_default.jpg',
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mountain_id) REFERENCES mountains(id),
    FOREIGN KEY (guide_id) REFERENCES guides(id)
);

-- Table for bookings
DROP TABLE IF EXISTS bookings;
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    trip_id VARCHAR(20) NOT NULL,
    booking_date DATE NOT NULL,
    num_participants INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (trip_id) REFERENCES trips(id)
);

-- Table for feedback
DROP TABLE IF EXISTS feedback;
CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Optional: Add some initial data
-- INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@example.com', '$2y$10$Q.Q7Y5R.Q.Q7Y5R.Q.Q7Y5R.Q.Q7Y5R.Q.Q7Y5R.Q.Q7Y5R.Q', 'admin'); -- password is 'password'
-- INSERT INTO users (name, email, password, role) VALUES ('User Contoh', 'user@example.com', '$2y$10$Q.Q7Y5R.Q.Q7Y5R.Q.Q7Y5R.Q.Q7Y5R.Q.Q7Y5R.Q.Q7Y5R.Q', 'user'); 