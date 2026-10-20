-- ===================================================
-- Online Complaint Registration System
-- Database Schema: complaint_system
-- Supporting SDG 16: Peace, Justice & Strong Institutions
-- Compatible with MySQL / MariaDB (XAMPP / WAMP / LAMP)
-- ===================================================

CREATE DATABASE IF NOT EXISTS `complaint_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `complaint_system`;

-- ---------------------------------------------------
-- Table structure for table `users`
-- ---------------------------------------------------
DROP TABLE IF EXISTS `complaint_updates`;
DROP TABLE IF EXISTS `complaints`;
DROP TABLE IF EXISTS `officers`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------
-- Table structure for table `admins`
-- ---------------------------------------------------
CREATE TABLE `admins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL DEFAULT 'Super Administrator',
  `email` VARCHAR(100) DEFAULT 'admin@complaints.gov',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------
-- Table structure for table `officers`
-- ---------------------------------------------------
CREATE TABLE `officers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `department` VARCHAR(100) NOT NULL,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------
-- Table structure for table `complaints`
-- ---------------------------------------------------
CREATE TABLE `complaints` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `complaint_id` VARCHAR(30) NOT NULL UNIQUE,
  `user_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `location` VARCHAR(255) NOT NULL,
  `attachment` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Submitted', 'Under Review', 'Assigned', 'In Progress', 'Resolved', 'Rejected') NOT NULL DEFAULT 'Submitted',
  `assigned_to` INT(11) DEFAULT NULL,
  `admin_response` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `resolved_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_complaint_id` (`complaint_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_assigned_to` (`assigned_to`),
  CONSTRAINT `fk_complaint_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_complaint_officer` FOREIGN KEY (`assigned_to`) REFERENCES `officers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------
-- Table structure for table `complaint_updates`
-- ---------------------------------------------------
CREATE TABLE `complaint_updates` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `complaint_id` VARCHAR(30) NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `message` TEXT NOT NULL,
  `updated_by` VARCHAR(100) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_updates_complaint_id` (`complaint_id`),
  CONSTRAINT `fk_updates_complaint` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`complaint_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- SEED DATA FOR DEMO & TESTING
-- ===================================================

-- Default Admin Account (Password: admin123)
-- Password uses both standard bcrypt or upgrade fallback in auth
INSERT INTO `admins` (`id`, `username`, `password`, `name`, `email`, `created_at`) VALUES
(1, 'admin', 'admin123', 'System Administrator', 'admin@complaints.gov.in', NOW());

-- Sample Officers
INSERT INTO `officers` (`id`, `name`, `email`, `phone`, `department`, `status`, `created_at`) VALUES
(1, 'Rajesh Sharma', 'rajesh.sharma@pwd.gov.in', '9876543210', 'Roads & Infrastructure', 'Active', NOW()),
(2, 'Sunita Verma', 'sunita.verma@waterboard.gov.in', '9876543211', 'Water Supply & Sewage', 'Active', NOW()),
(3, 'Arun Kumar', 'arun.kumar@electricity.gov.in', '9876543212', 'Electricity & Power', 'Active', NOW()),
(4, 'Deepak Patel', 'deepak.patel@sanitation.gov.in', '9876543213', 'Sanitation & Waste Management', 'Active', NOW()),
(5, 'Meera Nair', 'meera.nair@transport.gov.in', '9876543214', 'Public Transport', 'Active', NOW());

-- Sample Registered Citizen (Password: user123)
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `address`, `created_at`) VALUES
(1, 'Rahul Deshmukh', 'rahul@example.com', '9123456780', 'user123', 'Flat 402, Green Avenue, Sector 14, Navi Mumbai', NOW() - INTERVAL 10 DAY),
(2, 'Priya Sharma', 'priya@example.com', '9123456781', 'user123', 'House 12B, MG Road, Ward 7, Bengaluru', NOW() - INTERVAL 8 DAY);

-- Sample Complaints
INSERT INTO `complaints` (`id`, `complaint_id`, `user_id`, `title`, `category`, `description`, `location`, `attachment`, `status`, `assigned_to`, `admin_response`, `created_at`, `updated_at`, `resolved_at`) VALUES
(1, 'CMP20260001', 1, 'Deep pothole causing accidents near main junction', 'Roads', 'There is a severe deep pothole right in the middle of the intersection near Central Market. Two two-wheelers skidded yesterday evening. Needs urgent road asphalt repair.', 'Market Road Junction, Sector 14', NULL, 'Resolved', 1, 'Road inspection completed on 18th Sept. Patch repair and asphalt resurfacing done by PWD team.', NOW() - INTERVAL 7 DAY, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY),

(2, 'CMP20260002', 1, 'Continuous low water pressure and muddy water supply', 'Water Supply', 'Residents of Block B have received muddy water for the past 3 days with negligible pressure. Requesting line cleaning and pipeline inspection immediately.', 'Block B, Green Avenue', NULL, 'In Progress', 2, 'Main pipeline valve serviced. Flushing operation currently ongoing.', NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 1 DAY, NULL),

(3, 'CMP20260003', 2, 'Street light not working for two weeks', 'Street Lights', 'Three consecutive street poles are dark, making the alleyway unsafe for pedestrians and women at night.', 'Crossroad 4, Ward 7', NULL, 'Assigned', 3, 'Assigned to Electrical maintenance team for LED bulb replacement.', NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 1 DAY, NULL),

(4, 'CMP20260004', 2, 'Uncollected garbage accumulating near community park', 'Garbage', 'Garbage bin has been overflowing for 4 days, causing strong foul smell and attracting stray animals next to the children play area.', 'Community Park Gate 2, MG Road', NULL, 'Submitted', NULL, NULL, NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 1 DAY, NULL);

-- Sample Complaint Updates / Timeline
INSERT INTO `complaint_updates` (`complaint_id`, `status`, `message`, `updated_by`, `created_at`) VALUES
('CMP20260001', 'Submitted', 'Complaint successfully registered by citizen Rahul Deshmukh.', 'Rahul Deshmukh', NOW() - INTERVAL 7 DAY),
('CMP20260001', 'Under Review', 'Grievance verified by Public Works Helpdesk.', 'Admin', NOW() - INTERVAL 6 DAY),
('CMP20260001', 'Assigned', 'Assigned to Officer Rajesh Sharma (Roads & Infrastructure).', 'Admin', NOW() - INTERVAL 5 DAY),
('CMP20260001', 'In Progress', 'PWD inspection team deployed on site for patch measurement.', 'Rajesh Sharma', NOW() - INTERVAL 4 DAY),
('CMP20260001', 'Resolved', 'Road inspection completed on 18th Sept. Patch repair and asphalt resurfacing done by PWD team.', 'Admin', NOW() - INTERVAL 2 DAY),

('CMP20260002', 'Submitted', 'Complaint registered by citizen Rahul Deshmukh.', 'Rahul Deshmukh', NOW() - INTERVAL 5 DAY),
('CMP20260002', 'Assigned', 'Assigned to Officer Sunita Verma (Water Supply & Sewage).', 'Admin', NOW() - INTERVAL 3 DAY),
('CMP20260002', 'In Progress', 'Main pipeline valve serviced. Flushing operation currently ongoing.', 'Sunita Verma', NOW() - INTERVAL 1 DAY),

('CMP20260003', 'Submitted', 'Complaint registered by citizen Priya Sharma.', 'Priya Sharma', NOW() - INTERVAL 3 DAY),
('CMP20260003', 'Assigned', 'Assigned to Officer Arun Kumar (Electricity & Power).', 'Admin', NOW() - INTERVAL 1 DAY),

('CMP20260004', 'Submitted', 'Complaint registered by citizen Priya Sharma.', 'Priya Sharma', NOW() - INTERVAL 1 DAY);
