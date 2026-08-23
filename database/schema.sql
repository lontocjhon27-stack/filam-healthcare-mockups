-- Run this once in phpMyAdmin (SQL tab) against the u536536872_admin database.

CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(30),
  current_location VARCHAR(150),
  profession VARCHAR(100),
  specialty VARCHAR(150),
  experience VARCHAR(50),
  nclex_status VARCHAR(50),
  education VARCHAR(255),
  license_credentials VARCHAR(255),
  resume_path VARCHAR(255),
  passport_path VARCHAR(255),
  diploma_path VARCHAR(255),
  transcript_path VARCHAR(255),
  employment_cert_path VARCHAR(255),
  status VARCHAR(30) DEFAULT 'new',
  submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE employer_inquiries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  company_name VARCHAR(150) NOT NULL,
  hiring_position VARCHAR(150),
  staff_needed VARCHAR(20),
  location VARCHAR(150),
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(30),
  status VARCHAR(30) DEFAULT 'new',
  submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  subject VARCHAR(100),
  message TEXT,
  status VARCHAR(30) DEFAULT 'new',
  submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
