-- 1. Buat Database
CREATE DATABASE IF NOT EXISTS db_task_manager;
USE db_task_manager;

-- 2. Tabel Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    foto_profil VARCHAR(255) DEFAULT 'default.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabel Categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL,
    warna_label VARCHAR(20) DEFAULT 'primary', 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Tabel Tasks
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    judul VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    prioritas ENUM('Rendah', 'Sedang', 'Tinggi') DEFAULT 'Sedang',
    status ENUM('Belum', 'Selesai') DEFAULT 'Belum',
    deadline DATETIME NULL,               
    gcal_link TEXT NULL,                  
    reminder_sent TINYINT(1) DEFAULT 0,   
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- 5. Tabel Notifications (Fitur Inbox/Lonceng Ala Sosmed)
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(50) NOT NULL,
    pesan TEXT NOT NULL,
    tipe ENUM('info', 'warning', 'danger', 'success') DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,         
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 6. Tabel Activity Logs (Monitoring Admin)
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- A. Insert Kategori
INSERT INTO categories (nama_kategori, warna_label) VALUES 
('Kuliah', 'primary'), 
('Organisasi', 'warning'),
('Pribadi', 'success'),
('Urgent', 'danger');

-- B. Insert User (Password default: '123456')
INSERT INTO users (nama, email, password, role) VALUES 
('Admin Ganteng', 'admin@gmail.com', '123456', 'admin'),
('Yudha Putra', 'yudhaptradw12@gmail.com', '123456', 'user');