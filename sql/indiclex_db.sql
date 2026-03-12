-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 10, 2026 at 11:58 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `indiclex_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `dictionaries`
--

CREATE TABLE `dictionaries` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `source_lang` varchar(50) NOT NULL DEFAULT 'Hindi',
  `target_lang` varchar(50) NOT NULL DEFAULT 'English',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dictionaries`
--

INSERT INTO `dictionaries` (`id`, `name`, `description`, `source_lang`, `target_lang`, `created_by`, `created_at`) VALUES
(1, 'Hindi–English', 'Core Hindi to English word list', 'Hindi', 'English', NULL, '2026-03-10 22:57:24');

-- --------------------------------------------------------

--
-- Table structure for table `dictionary_entries`
--

CREATE TABLE `dictionary_entries` (
  `id` int(10) UNSIGNED NOT NULL,
  `dictionary_id` int(10) UNSIGNED NOT NULL,
  `word` varchar(255) NOT NULL,
  `translation` varchar(500) NOT NULL,
  `transliteration` varchar(255) DEFAULT '',
  `part_of_speech` varchar(100) DEFAULT '',
  `example_source` text DEFAULT '',
  `example_target` text DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dictionary_entries`
--

INSERT INTO `dictionary_entries` (`id`, `dictionary_id`, `word`, `translation`, `transliteration`, `part_of_speech`, `example_source`, `example_target`, `created_at`) VALUES
(1, 1, 'नमस्ते', 'Hello / Greetings', 'Namaste', 'interjection', 'नमस्ते, आप कैसे हैं?', 'Hello, how are you?', '2026-03-10 22:57:24'),
(2, 1, 'पानी', 'Water', 'Paani', 'noun', 'मुझे पानी चाहिए।', 'I need water.', '2026-03-10 22:57:24'),
(3, 1, 'खुश', 'Happy', 'Khush', 'adjective', 'मैं बहुत खुश हूँ।', 'I am very happy.', '2026-03-10 22:57:24'),
(4, 1, 'किताब', 'Book', 'Kitaab', 'noun', 'यह किताब बहुत अच्छी है।', 'This book is very good.', '2026-03-10 22:57:24'),
(5, 1, 'खाना', 'Food / To eat', 'Khaana', 'noun/verb', 'खाना तैयार है।', 'The food is ready.', '2026-03-10 22:57:24');

-- --------------------------------------------------------

--
-- Table structure for table `preferences`
--

CREATE TABLE `preferences` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `theme` enum('light','dark') NOT NULL DEFAULT 'light',
  `font_size` enum('small','medium','large') NOT NULL DEFAULT 'medium',
  `ui_language` varchar(10) NOT NULL DEFAULT 'en',
  `show_wod` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `preferences`
--

INSERT INTO `preferences` (`id`, `user_id`, `theme`, `font_size`, `ui_language`, `show_wod`, `updated_at`) VALUES
(1, 1, 'light', 'medium', 'en', 1, '2026-03-10 22:57:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@indiclex.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-03-10 22:57:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dictionaries`
--
ALTER TABLE `dictionaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `dictionary_entries`
--
ALTER TABLE `dictionary_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_word_per_dict` (`dictionary_id`,`word`);
ALTER TABLE `dictionary_entries` ADD FULLTEXT KEY `ft_search` (`word`,`translation`,`transliteration`);

--
-- Indexes for table `preferences`
--
ALTER TABLE `preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_prefs` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_username` (`username`),
  ADD UNIQUE KEY `uq_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dictionaries`
--
ALTER TABLE `dictionaries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `dictionary_entries`
--
ALTER TABLE `dictionary_entries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `preferences`
--
ALTER TABLE `preferences`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dictionaries`
--
ALTER TABLE `dictionaries`
  ADD CONSTRAINT `dictionaries_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `dictionary_entries`
--
ALTER TABLE `dictionary_entries`
  ADD CONSTRAINT `dictionary_entries_ibfk_1` FOREIGN KEY (`dictionary_id`) REFERENCES `dictionaries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `preferences`
--
ALTER TABLE `preferences`
  ADD CONSTRAINT `preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
