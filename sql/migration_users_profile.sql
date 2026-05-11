-- Run once on an existing `webdev` database (phpMyAdmin: Import, or mysql CLI).
-- If columns already exist, skip this file.

USE webdev;

ALTER TABLE users
    ADD COLUMN firstname VARCHAR(50) NOT NULL DEFAULT '' AFTER id,
    ADD COLUMN lastname VARCHAR(50) NOT NULL DEFAULT '' AFTER firstname,
    ADD COLUMN middle_initial VARCHAR(5) NULL DEFAULT NULL AFTER lastname,
    ADD COLUMN age TINYINT UNSIGNED NULL DEFAULT NULL AFTER middle_initial,
    ADD COLUMN height_cm DECIMAL(5, 2) UNSIGNED NULL DEFAULT NULL AFTER age,
    ADD COLUMN weight_kg DECIMAL(5, 2) UNSIGNED NULL DEFAULT NULL AFTER height_cm;

-- Copy legacy display name into first name when the new fields are empty.
UPDATE users SET firstname = fullname, lastname = '' WHERE firstname = '';
