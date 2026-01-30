-- Create Database
CREATE DATABASE IF NOT EXISTS `optispace_airport_db`;
USE `optispace_airport_db`;

-- Parking Slots Table
CREATE TABLE IF NOT EXISTS `parking_slots` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slot_id` VARCHAR(10) NOT NULL,
    `slot_name` VARCHAR(50) NOT NULL,
    `status` ENUM('free', 'occupied', 'inefficient') DEFAULT 'free',
    `zone_type` ENUM('premium', 'general', 'logistics') NOT NULL,
    `lat` DECIMAL(10, 8) NOT NULL,
    `lng` DECIMAL(11, 8) NOT NULL,
    `current_vehicle` VARCHAR(20) DEFAULT NULL
);

-- Seed TRV Terminal 2 Coordinates
INSERT INTO `parking_slots` (`slot_id`, `slot_name`, `zone_type`, `lat`, `lng`) VALUES
('A-01', 'Premium SUV Bay 1', 'premium', 8.488100, 76.923100),
('A-02', 'Premium SUV Bay 2', 'premium', 8.488080, 76.923080),
('B-01', 'General Parking 1', 'general', 8.488060, 76.923060),
('B-02', 'General Parking 2', 'general', 8.488040, 76.923040),
('B-03', 'General Parking 3', 'general', 8.488020, 76.923020),
('L-01', 'Logistics Bay', 'logistics', 8.487900, 76.922900);

-- Global Stats for SOC
CREATE TABLE IF NOT EXISTS `soc_stats` (
    `id` INT PRIMARY KEY DEFAULT 1,
    `total_entries` INT DEFAULT 0,
    `alerts_triggered` INT DEFAULT 0,
    `revenue` DECIMAL(15, 2) DEFAULT 0.00,
    `co2_saved` DECIMAL(15, 2) DEFAULT 0.00
);

INSERT INTO `soc_stats` (`id`, `total_entries`, `alerts_triggered`, `revenue`, `co2_saved`) VALUES (1, 0, 0, 0.00, 0.00) 
ON DUPLICATE KEY UPDATE `id`=`id`;
