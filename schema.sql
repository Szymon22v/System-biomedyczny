-- Uruchom ten plik w phpMyAdmin (zakładka SQL)
-- Usuwa istniejące tabele i tworzy od nowa

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS measurements;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS parameters;
DROP TABLE IF EXISTS units;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    success TINYINT(1) NOT NULL DEFAULT 0,
    attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    symbol VARCHAR(20) NOT NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE parameters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    unit_id INT NOT NULL,
    norm_min DECIMAL(10,2) DEFAULT NULL,
    norm_max DECIMAL(10,2) DEFAULT NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE measurements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    parameter_id INT NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    measured_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (parameter_id) REFERENCES parameters(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
