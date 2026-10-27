CREATE TABLE IF NOT EXISTS fin_protocols (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, reservation_id BIGINT UNSIGNED NOT NULL,
 type ENUM('handover','return') NOT NULL, status ENUM('draft','final') NOT NULL DEFAULT 'draft',
 revision INT UNSIGNED NOT NULL DEFAULT 1, supersedes_id BIGINT UNSIGNED NULL,
 data_json LONGTEXT NOT NULL, snapshot_json LONGTEXT NULL, pdf_blob LONGBLOB NULL, pdf_sha256 CHAR(64) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, finalized_at DATETIME NULL,
 INDEX protocol_reservation(reservation_id,created_at), INDEX protocol_finalized(status,finalized_at),
 FOREIGN KEY (reservation_id) REFERENCES reservations(id), FOREIGN KEY (supersedes_id) REFERENCES fin_protocols(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS fin_protocol_files (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, protocol_id BIGINT UNSIGNED NOT NULL,
 kind ENUM('photo','customer_signature','operator_signature') NOT NULL, mime VARCHAR(32) NOT NULL,
 image_blob LONGBLOB NOT NULL, sha256 CHAR(64) NOT NULL, width INT UNSIGNED NOT NULL, height INT UNSIGNED NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX protocol_files(protocol_id,kind),
 FOREIGN KEY (protocol_id) REFERENCES fin_protocols(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
