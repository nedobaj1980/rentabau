CREATE DATABASE IF NOT EXISTS `rentabau`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `rentabau`;

CREATE TABLE IF NOT EXISTS `reservations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `machine_id` VARCHAR(100) NOT NULL,
  `machine_name` VARCHAR(255) NOT NULL,
  `customer_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(80) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `handover` ENUM('abholung','lieferung') NOT NULL DEFAULT 'abholung',
  `notes` TEXT NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `days` INT UNSIGNED NOT NULL DEFAULT 1,
  `status` VARCHAR(50) NOT NULL DEFAULT 'angefragt',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reservations_email_idx` (`email`),
  KEY `reservations_machine_dates_idx` (`machine_id`,`start_date`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `reservations`
  ADD COLUMN IF NOT EXISTS `total_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `notes`,
  ADD COLUMN IF NOT EXISTS `days` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `total_price`;
