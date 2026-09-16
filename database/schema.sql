CREATE DATABASE IF NOT EXISTS lab_inventory;
USE lab_inventory;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'student') NOT NULL DEFAULT 'student'
);

CREATE TABLE equipment (
    equipment_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    quantity INT NOT NULL DEFAULT 0
);

INSERT INTO users (username, password, full_name, email, role) VALUES
('admin1', '$2b$10$mpl9AWjNZepYFeYiYsd3mudzRhL5M.jQqNVB418IRWKeRH.M09lDO', 'Admin User', 'admin@lab.local', 'admin'),
('student1', '$2b$10$mpl9AWjNZepYFeYiYsd3mudzRhL5M.jQqNVB418IRWKeRH.M09lDO', 'Student User', 'student@lab.local', 'student');

INSERT INTO equipment (name, description, quantity) VALUES
('Laptop', 'Basic lab laptop', 5),
('Arduino Uno', 'Arduino development board', 10),
('Mouse', 'USB computer mouse', 8);
