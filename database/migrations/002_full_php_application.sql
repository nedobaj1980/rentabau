USE `rentabau`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role` ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `company` VARCHAR(255) NOT NULL DEFAULT '',
  `phone` VARCHAR(80) NOT NULL DEFAULT '',
  `address` VARCHAR(255) NOT NULL DEFAULT '',
  `zip` VARCHAR(20) NOT NULL DEFAULT '',
  `city` VARCHAR(120) NOT NULL DEFAULT '',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_idx` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contact_requests` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(80) NOT NULL DEFAULT '',
  `topic` VARCHAR(120) NOT NULL DEFAULT '',
  `message` TEXT NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'offen',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `contact_requests_status_idx` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `reservations`
  ADD COLUMN IF NOT EXISTS `user_id` BIGINT UNSIGNED NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `company` VARCHAR(255) NOT NULL DEFAULT '' AFTER `customer_name`,
  ADD COLUMN IF NOT EXISTS `payment_preference` VARCHAR(50) NOT NULL DEFAULT '' AFTER `days`,
  ADD KEY IF NOT EXISTS `reservations_user_idx` (`user_id`);
