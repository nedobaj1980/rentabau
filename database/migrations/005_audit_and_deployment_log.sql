-- Vor dem Import in phpMyAdmin die gewünschte Datenbank auswählen
-- (lokal: rentabau, Hoststar: ch369984_rentabau).

CREATE TABLE IF NOT EXISTS `admin_audit_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NULL,
  `user_name` VARCHAR(255) NOT NULL DEFAULT '',
  `user_email` VARCHAR(255) NOT NULL DEFAULT '',
  `action` VARCHAR(80) NOT NULL,
  `area` VARCHAR(80) NOT NULL,
  `entity_type` VARCHAR(80) NOT NULL DEFAULT '',
  `entity_id` VARCHAR(80) NOT NULL DEFAULT '',
  `summary` VARCHAR(255) NOT NULL DEFAULT '',
  `before_json` JSON NULL,
  `after_json` JSON NULL,
  `ip_address` VARCHAR(45) NOT NULL DEFAULT '',
  `user_agent` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_audit_log_created_idx` (`created_at`),
  KEY `admin_audit_log_user_idx` (`user_id`),
  KEY `admin_audit_log_area_idx` (`area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `deployment_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NULL,
  `user_name` VARCHAR(255) NOT NULL DEFAULT '',
  `user_email` VARCHAR(255) NOT NULL DEFAULT '',
  `release_name` VARCHAR(120) NOT NULL,
  `description` VARCHAR(255) NOT NULL DEFAULT '',
  `changed_files_json` JSON NULL,
  `reference` VARCHAR(160) NOT NULL DEFAULT '',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `deployment_log_created_idx` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
