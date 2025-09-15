-- Add user_id column to invoice_payments table for better tracking
-- Run this script if you want to track who processed each payment

ALTER TABLE `invoice_payments` 
ADD COLUMN `user_id` int(11) NULL AFTER `reference`,
ADD INDEX `user_id` (`user_id`);

-- Update existing records to set user_id to 1 (default admin user)
-- You can modify this value based on your needs
UPDATE `invoice_payments` SET `user_id` = 1 WHERE `user_id` IS NULL;

