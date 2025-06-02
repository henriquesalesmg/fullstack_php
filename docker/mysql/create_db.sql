CREATE DATABASE IF NOT EXISTS fluentpdo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE fluentpdo;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE task_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    due_date DATE,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES task_categories(id) ON DELETE SET NULL
);

CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

/*
-- Utilizei a hash em php echo password_hash('user123', PASSWORD_DEFAULT); 
-- para gerar a senha
*/

-- Usuário admin
INSERT INTO users (name, email, password, role) VALUES (
    'Admin',
    'admin@admin.com',
    -- Senha 'admin123'
    '$2y$10$uAaIjeE24mJ6H9U03pG5BODqV1TqIzXg3vVo05cCnGz/g1XZZbfyW',
    'admin'
);

-- Usuário padrão (para testes)
INSERT INTO users (name, email, password, role) VALUES (
    'User Teste',
    'user@teste.com',
    -- Senha 'user123'
    '$2y$10$hwjDztX.Tk8TnZdA6pMdOe31D4Kb5YoDPC8QICQ69TfIXVqMQVLcq',
    'user'
);
