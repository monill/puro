-- Travian Puro Database Schema
-- Generated for Travian Puro Installation

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `email` varchar(100) NOT NULL,
    `password` varchar(255) NOT NULL,
    `tribe` enum('romans','teutons','gauls') NOT NULL DEFAULT 'romans',
    `role` enum('user','admin','moderator') NOT NULL DEFAULT 'user',
    `population` int(11) NOT NULL DEFAULT 0,
    `gold` int(11) NOT NULL DEFAULT 0,
    `silver` int(11) NOT NULL DEFAULT 0,
    `last_login` datetime DEFAULT NULL,
    `last_activity` datetime DEFAULT NULL,
    `is_online` tinyint(1) NOT NULL DEFAULT 0,
    `is_banned` tinyint(1) NOT NULL DEFAULT 0,
    `ban_reason` text DEFAULT NULL,
    `ban_until` datetime DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`),
    KEY `tribe` (`tribe`),
    KEY `role` (`role`),
    KEY `is_online` (`is_online`),
    KEY `is_banned` (`is_banned`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Villages table
CREATE TABLE IF NOT EXISTS `villages` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `name` varchar(50) NOT NULL,
    `x` int(11) NOT NULL,
    `y` int(11) NOT NULL,
    `tribe` enum('romans','teutons','gauls') NOT NULL,
    `population` int(11) NOT NULL DEFAULT 2,
    `wood` int(11) NOT NULL DEFAULT 500,
    `clay` int(11) NOT NULL DEFAULT 500,
    `iron` int(11) NOT NULL DEFAULT 500,
    `crop` int(11) NOT NULL DEFAULT 500,
    `max_storage` int(11) NOT NULL DEFAULT 80000,
    `max_crops` int(11) NOT NULL DEFAULT 80000,
    `production_wood` decimal(5,2) NOT NULL DEFAULT 4.00,
    `production_clay` decimal(5,2) NOT NULL DEFAULT 4.00,
    `production_iron` decimal(5,2) NOT NULL DEFAULT 4.00,
    `production_crop` decimal(5,2) NOT NULL DEFAULT 4.00,
    `capital` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `coordinates` (`x`, `y`),
    KEY `tribe` (`tribe`),
    KEY `capital` (`capital`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Buildings table
CREATE TABLE IF NOT EXISTS `buildings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `village_id` int(11) NOT NULL,
    `building_type` enum('main','barracks','stable','workshop','market','embassy','academy','smithy','warehouse','granary','palace','residence','town_hall','trapper','hero_mansion','great_barracks','great_stable','great_workshop','wonder') NOT NULL,
    `level` int(11) NOT NULL DEFAULT 0,
    `max_level` int(11) NOT NULL DEFAULT 20,
    `under_construction` tinyint(1) NOT NULL DEFAULT 0,
    `construction_start` datetime DEFAULT NULL,
    `construction_end` datetime DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `village_building` (`village_id`, `building_type`),
    KEY `village_id` (`village_id`),
    KEY `building_type` (`building_type`),
    FOREIGN KEY (`village_id`) REFERENCES `villages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Troops table
CREATE TABLE IF NOT EXISTS `troops` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `village_id` int(11) NOT NULL,
    `unit_type` varchar(50) NOT NULL,
    `quantity` int(11) NOT NULL DEFAULT 0,
    `training` int(11) NOT NULL DEFAULT 0,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `village_unit` (`village_id`, `unit_type`),
    KEY `village_id` (`village_id`),
    FOREIGN KEY (`village_id`) REFERENCES `villages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Battles table
CREATE TABLE IF NOT EXISTS `battles` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `attacker_village_id` int(11) NOT NULL,
    `defender_village_id` int(11) NOT NULL,
    `attacker_user_id` int(11) NOT NULL,
    `defender_user_id` int(11) NOT NULL,
    `battle_time` datetime NOT NULL,
    `winner` enum('attacker','defender','draw') NOT NULL,
    `attacker_losses` text DEFAULT NULL,
    `defender_losses` text DEFAULT NULL,
    `resources_stolen` text DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `attacker_village` (`attacker_village_id`),
    KEY `defender_village` (`defender_village_id`),
    KEY `attacker_user` (`attacker_user_id`),
    KEY `defender_user` (`defender_user_id`),
    KEY `battle_time` (`battle_time`),
    FOREIGN KEY (`attacker_village_id`) REFERENCES `villages`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`defender_village_id`) REFERENCES `villages`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`attacker_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`defender_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages table
CREATE TABLE IF NOT EXISTS `messages` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `sender_id` int(11) DEFAULT NULL,
    `receiver_id` int(11) NOT NULL,
    `subject` varchar(255) NOT NULL,
    `message` text NOT NULL,
    `type` enum('normal','report','trade','alliance') NOT NULL DEFAULT 'normal',
    `read_at` datetime DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `sender_id` (`sender_id`),
    KEY `receiver_id` (`receiver_id`),
    KEY `type` (`type`),
    KEY `read_at` (`read_at`),
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alliances table
CREATE TABLE IF NOT EXISTS `alliances` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `tag` varchar(10) NOT NULL,
    `description` text DEFAULT NULL,
    `founder_id` int(11) NOT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`),
    UNIQUE KEY `tag` (`tag`),
    KEY `founder_id` (`founder_id`),
    FOREIGN KEY (`founder_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alliance Members table
CREATE TABLE IF NOT EXISTS `alliance_members` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `alliance_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `role` enum('leader','vice_leader','member','diplomat') NOT NULL DEFAULT 'member',
    `joined_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `alliance_user` (`alliance_id`, `user_id`),
    KEY `alliance_id` (`alliance_id`),
    KEY `user_id` (`user_id`),
    FOREIGN KEY (`alliance_id`) REFERENCES `alliances`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reports table
CREATE TABLE IF NOT EXISTS `reports` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `type` enum('battle','trade','construction','research') NOT NULL,
    `title` varchar(255) NOT NULL,
    `content` text NOT NULL,
    `read_at` datetime DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `type` (`type`),
    KEY `read_at` (`read_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Market Offers table
CREATE TABLE IF NOT EXISTS `market_offers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `village_id` int(11) NOT NULL,
    `offer_resource` enum('wood','clay','iron','crop') NOT NULL,
    `offer_amount` int(11) NOT NULL,
    `seek_resource` enum('wood','clay','iron','crop') NOT NULL,
    `seek_amount` int(11) NOT NULL,
    `active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `village_id` (`village_id`),
    KEY `offer_resource` (`offer_resource`),
    KEY `seek_resource` (`seek_resource`),
    KEY `active` (`active`),
    FOREIGN KEY (`village_id`) REFERENCES `villages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Game Settings table
CREATE TABLE IF NOT EXISTS `game_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `key` varchar(100) NOT NULL,
    `value` text NOT NULL,
    `description` text DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Server Statistics table
CREATE TABLE IF NOT EXISTS `server_stats` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `date` date NOT NULL,
    `total_users` int(11) NOT NULL DEFAULT 0,
    `active_users` int(11) NOT NULL DEFAULT 0,
    `total_villages` int(11) NOT NULL DEFAULT 0,
    `total_population` int(11) NOT NULL DEFAULT 0,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default game settings
INSERT IGNORE INTO `game_settings` (`key`, `value`, `description`) VALUES
('game_speed', '1', 'Game speed multiplier'),
('max_players', '1000', 'Maximum number of players'),
('map_size', '400', 'Map size in each direction'),
('registration_enabled', '1', 'Enable new user registration'),
('trade_ratio', '1', 'Trade ratio between resources'),
('protection_time', '3', 'New player protection time in days'),
('catapult_damage', '1', 'Catapult damage multiplier'),
('troop_speed', '1', 'Troop movement speed multiplier'),
('building_speed', '1', 'Building construction speed multiplier'),
('research_speed', '1', 'Research speed multiplier'),
('resource_production', '1', 'Resource production multiplier'),
('storage_multiplier', '1', 'Storage capacity multiplier'),
('crop_consumption', '1', 'Crop consumption multiplier'),
('village_limit', '10', 'Maximum villages per player'),
('alliance_limit', '50', 'Maximum members per alliance'),
('wonder_villages', '13', 'Number of Wonder of the World villages');
