-- Add password reset token column to users table
ALTER TABLE users ADD COLUMN reset_token VARCHAR(100) NULL AFTER password;
ALTER TABLE users ADD COLUMN reset_token_expires DATETIME NULL AFTER reset_token;
