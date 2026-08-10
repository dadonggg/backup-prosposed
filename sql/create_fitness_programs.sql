-- ============================================================
-- Nutrify — Fitness Program Table
-- Run this once in phpMyAdmin or MySQL CLI before testing.
-- ============================================================

CREATE TABLE IF NOT EXISTS `fitness_programs` (
  `id`                  INT          NOT NULL AUTO_INCREMENT,
  `member_id`           INT          NOT NULL COMMENT 'gym_members.id',
  `user_id`             INT          NOT NULL COMMENT 'users.id',
  `goal`                VARCHAR(100) NOT NULL,
  `experience_level`    VARCHAR(50)  NOT NULL,
  `available_days`      TINYINT      NOT NULL DEFAULT 3,
  `list_of_weekdays`    VARCHAR(200) NOT NULL,
  `session_length`      SMALLINT     NOT NULL DEFAULT 60,
  `equipment`           VARCHAR(1000) NOT NULL,
  `injuries_limitations` TEXT         DEFAULT NULL,
  `gym_name`            VARCHAR(200) NOT NULL,
  `split_name`          VARCHAR(100) DEFAULT NULL,
  `program_json`        LONGTEXT     NOT NULL COMMENT 'Full Gemini JSON response',
  `generated_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_member` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
