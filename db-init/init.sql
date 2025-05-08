CREATE DATABASE IF NOT EXISTS file_share;
USE file_share;
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);
INSERT IGNORE INTO users (username, password) VALUES (
    'demo',
    '$2y$10$qr9X0YD6wuq8wJkwLH2HPuvcs7BWlPACmQl3.nwQfBBT3jiRlk3/C' -- demo/demo -- Replace with: php -r "echo password_hash('secure_password', PASSWORD_DEFAULT);"
);
