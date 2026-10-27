USE `rentabau`;

ALTER TABLE `users`
  MODIFY COLUMN `role` ENUM('customer', 'admin', 'guest') NOT NULL DEFAULT 'customer';
