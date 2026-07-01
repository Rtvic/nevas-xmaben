-- QR Tracking System Database Schema

CREATE DATABASE IF NOT EXISTS qr_tracking_db;
USE qr_tracking_db;

-- Table for shortened links
CREATE TABLE IF NOT EXISTS links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE NOT NULL,
    destination_url TEXT NOT NULL,
    campaign VARCHAR(100),
    active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for QR code customization
CREATE TABLE IF NOT EXISTS qr_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    link_id INT NOT NULL,
    dots_style VARCHAR(50) DEFAULT 'square',
    corners_square_style VARCHAR(50) DEFAULT 'square',
    corners_dot_style VARCHAR(50) DEFAULT 'square',
    fg_color VARCHAR(20) DEFAULT '#000000',
    bg_color VARCHAR(20) DEFAULT '#ffffff',
    logo_path VARCHAR(255),
    label_text VARCHAR(100),
    label_color VARCHAR(20) DEFAULT '#000000',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for tracking events
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    link_id INT NOT NULL,
    ip VARCHAR(45) NOT NULL,
    country VARCHAR(100),
    city VARCHAR(100),
    os VARCHAR(50),
    browser VARCHAR(50),
    device VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE CASCADE,
    INDEX (link_id),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
