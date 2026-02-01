-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    tribe TINYINT NOT NULL DEFAULT 1,
    population INT DEFAULT 0,
    role VARCHAR(20) DEFAULT 'player',
    last_login DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tribe (tribe),
    INDEX idx_population (population)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Aldeias
CREATE TABLE IF NOT EXISTS villages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_capital BOOLEAN DEFAULT FALSE,
    population INT DEFAULT 2,
    wood DECIMAL(14,6) DEFAULT 750,
    clay DECIMAL(14,6) DEFAULT 750,
    iron DECIMAL(14,6) DEFAULT 750,
    crop DECIMAL(14,6) DEFAULT 750,
    wood_production DECIMAL(14,6) DEFAULT 10,
    clay_production DECIMAL(14,6) DEFAULT 10,
    iron_production DECIMAL(14,6) DEFAULT 10,
    crop_production DECIMAL(14,6) DEFAULT 10,
    max_store INT DEFAULT 800,
    max_crop INT DEFAULT 800,
    loyalty INT DEFAULT 100,
    x INT DEFAULT 0,
    y INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_owner (owner_id),
    INDEX idx_population (population),
    INDEX idx_coordinates (x, y)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Configurações
CREATE TABLE IF NOT EXISTS config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir configurações padrão
INSERT INTO config (setting_key, setting_value) VALUES
('server_name', 'Travian Puro'),
('server_speed', '1'),
('game_speed', '1'),
('troop_speed', '1'),
('start_time', UNIX_TIMESTAMP()),
('debug_mode', '0');
