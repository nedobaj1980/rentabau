USE `rentabau`;

ALTER TABLE `reservations`
  ADD COLUMN IF NOT EXISTS `payment_status` VARCHAR(30) NOT NULL DEFAULT 'offen' AFTER `payment_preference`,
  ADD COLUMN IF NOT EXISTS `confirmed_at` DATETIME NULL AFTER `payment_status`,
  ADD COLUMN IF NOT EXISTS `paid_at` DATETIME NULL AFTER `confirmed_at`,
  ADD COLUMN IF NOT EXISTS `admin_note` TEXT NOT NULL AFTER `paid_at`;

CREATE INDEX IF NOT EXISTS `reservations_payment_status_idx` ON `reservations` (`payment_status`);
