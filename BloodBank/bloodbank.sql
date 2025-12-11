-- MySQL dump for BloodBank
CREATE DATABASE IF NOT EXISTS bloodbank CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bloodbank;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('hospital','receiver') NOT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  blood_group VARCHAR(5) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS hospitals (
  user_id INT PRIMARY KEY,
  registration_number VARCHAR(100),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS blood_samples (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hospital_id INT NOT NULL,
  blood_group VARCHAR(5) NOT NULL,
  units INT NOT NULL DEFAULT 1,
  notes VARCHAR(255) DEFAULT NULL,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (hospital_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sample_id INT NOT NULL,
  hospital_id INT NOT NULL,
  receiver_id INT NOT NULL,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  message VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (sample_id) REFERENCES blood_samples(id) ON DELETE CASCADE,
  FOREIGN KEY (hospital_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Optional seed users (password: password123)
INSERT INTO users (name,email,password,role,phone,address,blood_group)
VALUES
('City Hospital','hospital@example.com', '', 'hospital','9999999999','123 Main St', NULL),
('Ravi Kumar','receiver@example.com', '', 'receiver','8888888888', NULL, 'A+');

-- Note: the above password hash placeholder will be replaced by the included config's setup instructions.
