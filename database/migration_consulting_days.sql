-- ============================================
-- Migration: Add consulting_days to users table
-- Run this migration on your database
-- ============================================

ALTER TABLE `users` ADD COLUMN `consulting_days` VARCHAR(255) DEFAULT NULL AFTER `consulting_hours`;

-- Update existing doctors with default consulting days (Mon-Fri)
UPDATE `users` SET `consulting_days` = 'Monday,Tuesday,Wednesday,Thursday,Friday' WHERE role = 'doctor';
