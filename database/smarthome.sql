-- Membuat database
CREATE DATABASE IF NOT EXISTS smarthome;
USE smarthome;

-- Membuat tabel users
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
);

-- Membuat tabel rooms (ruangan)
CREATE TABLE IF NOT EXISTS rooms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    floor_number INT DEFAULT 1 CHECK (floor_number > 0), -- memastikan lantai lebih dari 0
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Membuat tabel device_categories (kategori perangkat)
CREATE TABLE IF NOT EXISTS device_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Membuat tabel devices (perangkat)
CREATE TABLE IF NOT EXISTS devices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    category_id INT NOT NULL,
    room_id INT NOT NULL,
    status BOOLEAN DEFAULT FALSE,
    power_consumption FLOAT DEFAULT 0 CHECK (power_consumption >= 0), -- memastikan konsumsi daya tidak negatif
    ip_address VARCHAR(15) DEFAULT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES device_categories(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Membuat tabel device_logs (log aktivitas perangkat)
CREATE TABLE IF NOT EXISTS device_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    device_id INT NOT NULL,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    status_before BOOLEAN NOT NULL,
    status_after BOOLEAN NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Insert data awal untuk rooms
INSERT INTO rooms (name, description, floor_number) VALUES
('Ruang Tamu', 'Ruangan untuk menerima tamu', 1),
('Kamar Utama', 'Kamar tidur utama', 2),
('Dapur', 'Ruangan untuk memasak', 1),
('Ruang Keluarga', 'Ruangan untuk berkumpul keluarga', 1),
('Kamar Mandi', 'Kamar mandi utama', 2);

-- Insert data awal untuk device_categories
INSERT INTO device_categories (name, icon, description) VALUES
('Lampu', 'lightbulb', 'Perangkat penerangan'),
('AC', 'snowflake', 'Pendingin ruangan'),
('TV', 'tv', 'Televisi'),
('Kipas Angin', 'fan', 'Kipas angin'),
('Kunci Pintu', 'lock', 'Kunci pintu elektronik');

-- Insert data awal untuk devices
INSERT INTO devices (name, category_id, room_id, status) VALUES
('Lampu Utama', 1, 1, FALSE),
('AC Ruang Tamu', 2, 1, FALSE),
('TV LED', 3, 1, FALSE),
('Lampu Kamar', 1, 2, FALSE),
('AC Kamar', 2, 2, FALSE),
('Kipas Angin', 4, 3, FALSE),
('Lampu Dapur', 1, 3, FALSE);

-- Insert user admin default (password hash untuk 'admin123')
INSERT INTO users (username, email, password, full_name) VALUES
('admin', 'admin@smarthome.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');
