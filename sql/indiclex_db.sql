-- ============================================================
-- schema.sql — IndicLex / DictionaryHub
-- Run in phpMyAdmin: your database → SQL tab → paste & run
-- ============================================================

-- ------------------------------------------------------------
-- 1. USERS
--    Stores registered accounts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `username`     VARCHAR(50)    NOT NULL,
  `email`        VARCHAR(255)   NOT NULL,
  `password`     VARCHAR(255)   NOT NULL,        -- store bcrypt hash, never plain text
  `role`         ENUM('admin','user')  NOT NULL DEFAULT 'user',
  `created_at`   TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`),
  UNIQUE KEY `uq_email`    (`email`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- 2. DICTIONARIES
--    A dictionary is a named collection of word entries
--    (e.g. "Telugu–English Basic", "Telugu–English Advanced")
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dictionaries` (
  `id`           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(255)   NOT NULL,
  `description`  TEXT,
  `source_lang`  VARCHAR(50)    NOT NULL DEFAULT 'Telugu',
  `target_lang`  VARCHAR(50)    NOT NULL DEFAULT 'English',
  `created_by`   INT UNSIGNED,                   -- FK to users.id (nullable = system created)
  `created_at`   TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- 3. DICTIONARY ENTRIES
--    Each row is one word/phrase and its translation,
--    belonging to a dictionary
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dictionary_entries` (
  `id`               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `dictionary_id`    INT UNSIGNED  NOT NULL,
  `word`             VARCHAR(255)  NOT NULL,       -- Telugu word
  `translation`      VARCHAR(500)  NOT NULL,       -- English translation
  `transliteration`  VARCHAR(255)  DEFAULT '',     -- e.g. "Namaste"
  `part_of_speech`   VARCHAR(100)  DEFAULT '',     -- e.g. "noun", "verb"
  `example_source`   TEXT          DEFAULT '',     -- example sentence in Telugu
  `example_target`   TEXT          DEFAULT '',     -- example sentence in English
  `created_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),

  -- One dictionary cannot have the same word twice
  UNIQUE KEY `uq_word_per_dict` (`dictionary_id`, `word`),

  -- Full-text search across word, translation, transliteration
  FULLTEXT KEY `ft_search` (`word`, `translation`, `transliteration`),

  FOREIGN KEY (`dictionary_id`) REFERENCES `dictionaries`(`id`) ON DELETE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- 4. PREFERENCES
--    Per-user UI settings (theme, font size, language, etc.)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `preferences` (
  `id`            INT UNSIGNED          NOT NULL AUTO_INCREMENT,
  `user_id`       INT UNSIGNED          NOT NULL,
  `theme`         ENUM('light','dark')  NOT NULL DEFAULT 'light',
  `font_size`     ENUM('small','medium','large') NOT NULL DEFAULT 'medium',
  `ui_language`   VARCHAR(10)           NOT NULL DEFAULT 'en',
  `show_wod`      TINYINT(1)            NOT NULL DEFAULT 1,   -- show word of the day?
  `updated_at`    TIMESTAMP             NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),

  -- One preferences row per user
  UNIQUE KEY `uq_user_prefs` (`user_id`),

  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SEED DATA — starter records so the app has something to show
-- ============================================================

-- Default admin user (password = "admin123" — change immediately)
INSERT IGNORE INTO `users` (`username`, `email`, `password`, `role`)
VALUES ('admin', 'admin@indiclex.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Default dictionary
INSERT IGNORE INTO `dictionaries` (`id`, `name`, `description`, `source_lang`, `target_lang`)
VALUES (1, 'Telugu–English', 'Core Telugu to English word list', 'Telugu', 'English');

-- Sample entries
INSERT IGNORE INTO `dictionary_entries` (`dictionary_id`, `word`, `translation`, `transliteration`, `part_of_speech`, `example_source`, `example_target`)
VALUES
  (1, 'నమస్తే',   'Hello / Greetings', 'Namaste',   'interjection', 'నమస్తే, మీరు ఎలా ఉన్నారు?',      'Hello, how are you?'),
  (1, 'నీళ్ళు',   'Water',             'Neellu',    'noun',         'నాకు నీళ్ళు కావాలి.',             'I need water.'),
  (1, 'సంతోషం',   'Happiness / Happy', 'Santosham', 'noun',         'నాకు చాలా సంతోషంగా ఉంది.',       'I am very happy.'),
  (1, 'పుస్తకం',  'Book',              'Pustakam',  'noun',         'ఈ పుస్తకం చాలా బాగుంది.',         'This book is very good.'),
  (1, 'అన్నం',    'Food / Rice',       'Annam',     'noun',         'అన్నం తయారైంది.',                  'The food is ready.');

-- Default preferences for admin
INSERT IGNORE INTO `preferences` (`user_id`, `theme`, `font_size`, `ui_language`, `show_wod`)
VALUES (1, 'light', 'medium', 'en', 1);