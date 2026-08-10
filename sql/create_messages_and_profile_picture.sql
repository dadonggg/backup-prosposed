-- SQL migration file for Messaging System and Profile Picture Upload
-- Execute in phpMyAdmin or MySQL client

-- 1. Add profile_picture_url column to users table if not existing
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `profile_picture_url` VARCHAR(255) DEFAULT NULL AFTER `email`;

-- 2. Create messages table for Trainer-Client messaging
CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sender_id` INT NOT NULL,
  `receiver_id` INT NOT NULL,
  `request_id` INT DEFAULT NULL,
  `message_text` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `read_at` DATETIME DEFAULT NULL,
  INDEX `idx_sender_receiver` (`sender_id`, `receiver_id`),
  INDEX `idx_receiver_read` (`receiver_id`, `read_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
