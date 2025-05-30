-- Create the database
CREATE DATABASE IF NOT EXISTS plantitos_cinema;
USE plantitos_cinema;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Movies table
CREATE TABLE IF NOT EXISTS movies (
    movie_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Schedules table
CREATE TABLE IF NOT EXISTS schedules (
    schedule_id INT PRIMARY KEY AUTO_INCREMENT,
    movie_id INT NOT NULL,
    showtime TIME NOT NULL,
    showdate DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id)
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    schedule_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (schedule_id) REFERENCES schedules(schedule_id)
);

-- Booked seats table
CREATE TABLE IF NOT EXISTS booked_seats (
    seat_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
);

-- Insert sample movies
INSERT INTO movies (title, image_path, price) VALUES
('Lilo And Stitch', '../assets/lilo.jpg', 350.00),
('Thunderbolts', '../assets/thunderbolts.jpeg', 350.00),
('Karate Kid', '../assets/karate.jpg', 350.00);

-- Insert sample schedules
INSERT INTO schedules (movie_id, showtime, showdate) VALUES
(1, '10:00:00', CURDATE()),
(1, '13:00:00', CURDATE()),
(1, '16:00:00', CURDATE()),
(2, '11:00:00', CURDATE()),
(2, '14:00:00', CURDATE()),
(2, '17:00:00', CURDATE()),
(3, '12:00:00', CURDATE()),
(3, '15:00:00', CURDATE()),
(3, '18:00:00', CURDATE()); 