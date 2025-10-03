-- Create database for Darul Abrar Madrasa
CREATE DATABASE IF NOT EXISTS darul_abrar_madrasa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create database user
CREATE USER IF NOT EXISTS 'madrasa_user'@'localhost' IDENTIFIED BY 'Madrasa@2025#Secure';

-- Grant all privileges on the database to the user
GRANT ALL PRIVILEGES ON darul_abrar_madrasa.* TO 'madrasa_user'@'localhost';

-- Flush privileges to apply changes
FLUSH PRIVILEGES;

-- Show databases to confirm
SHOW DATABASES;

-- Show user to confirm
SELECT User, Host FROM mysql.user WHERE User = 'madrasa_user';
