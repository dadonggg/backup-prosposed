-- ============================================================
-- Staff Application Flow Overhaul — Database Migration
-- Run this once in phpMyAdmin or via the MariaDB CLI.
-- ============================================================

-- 1. Create user_documents table (for Profile & Settings docs)
CREATE TABLE IF NOT EXISTS `user_documents` (
  `id`              int(11)       NOT NULL AUTO_INCREMENT,
  `user_id`         int(11)       NOT NULL,
  `doc_type`        varchar(50)   NOT NULL COMMENT 'resume, certification, medical_certificate',
  `doc_path`        varchar(500)  NOT NULL,
  `specialization`  varchar(255)  NULL DEFAULT NULL,
  `created_at`      datetime      NOT NULL DEFAULT current_timestamp(),
  `updated_at`      datetime      NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_documents_user_id` (`user_id`),
  KEY `idx_user_documents_type` (`doc_type`),
  CONSTRAINT `fk_user_documents_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Make medical_certificate and resume nullable in staff_applications
--    (new applications no longer require file upload at submission)
ALTER TABLE `staff_applications`
  MODIFY `medical_certificate` varchar(500) NULL DEFAULT NULL,
  MODIFY `resume`              varchar(500) NULL DEFAULT NULL;

-- Done.
