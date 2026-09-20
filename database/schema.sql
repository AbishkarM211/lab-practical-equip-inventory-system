CREATE DATABASE IF NOT EXISTS lab_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lab_inventory;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE equipment_models (
    model_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE physical_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    model_id INT NOT NULL,
    serial_number VARCHAR(50) NOT NULL UNIQUE,
    status ENUM('available', 'reserved', 'borrowed') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (model_id) REFERENCES equipment_models(model_id) ON DELETE RESTRICT
);

CREATE TABLE borrow_records (
    borrow_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_id INT NOT NULL,
    status ENUM('reserved', 'borrowed', 'returned', 'expired', 'cancelled') NOT NULL DEFAULT 'reserved',
    reservation_time DATETIME NOT NULL,
    expiry_time DATETIME NOT NULL,
    borrow_date DATETIME NULL,
    due_date DATETIME NULL,
    return_date DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES physical_items(item_id) ON DELETE RESTRICT,
    INDEX idx_borrow_status (status),
    INDEX idx_borrow_expiry (expiry_time)
);

INSERT INTO users (username, password, full_name, email, role) VALUES
('admin1', '$2b$10$mpl9AWjNZepYFeYiYsd3mudzRhL5M.jQqNVB418IRWKeRH.M09lDO', 'Admin User', 'admin@lab.local', 'admin'),
('student1', '$2b$10$mpl9AWjNZepYFeYiYsd3mudzRhL5M.jQqNVB418IRWKeRH.M09lDO', 'Student User', 'student@lab.local', 'student');

INSERT INTO equipment_models (name, description, category) VALUES
('Laptop', 'Basic lab laptop', 'Computer'),
('Arduino Uno', 'Arduino development board', 'Electronics'),
('Mouse', 'USB computer mouse', 'Accessory');

INSERT INTO physical_items (model_id, serial_number, status) VALUES
(1, 'LAP-001', 'available'),
(1, 'LAP-002', 'available'),
(2, 'ARD-001', 'available'),
(3, 'MOU-001', 'available');
