CREATE TABLE `reservations` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`machine_id` text NOT NULL,
	`machine_name` text NOT NULL,
	`customer_name` text NOT NULL,
	`email` text NOT NULL,
	`phone` text NOT NULL,
	`start_date` text NOT NULL,
	`end_date` text NOT NULL,
	`handover` text DEFAULT 'abholung' NOT NULL,
	`notes` text DEFAULT '' NOT NULL,
	`status` text DEFAULT 'angefragt' NOT NULL,
	`created_at` integer NOT NULL
);
