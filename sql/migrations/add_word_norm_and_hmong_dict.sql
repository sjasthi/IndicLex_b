-- Run once on MariaDB/MySQL against your IndicLex database.
-- 1) Adds word_norm + index so dictionary comparison uses indexed lookups (much faster).
-- 2) Inserts English–Hmong dictionary and sample glosses.

-- ── Indexes for comparisons (LOWER(word) prevented index use before) ──────────
ALTER TABLE `dictionary_entries`
  ADD COLUMN `word_norm` varchar(255) COLLATE utf8mb4_unicode_ci
    GENERATED ALWAYS AS (LOWER(`word`)) STORED,

  ADD KEY `idx_dict_word_norm` (`dictionary_id`, `word_norm`(191));

-- ── Dictionary: English (headwords) → Hmong (primary gloss column `telugu`) ────
INSERT INTO `dictionaries` (`name`, `description`, `source_lang`, `target_lang`, `created_by`)
VALUES ('English–Hmong', 'English headwords with Hmong (Roman Popular Hmong) gloss samples.', 'English', 'Hmong', NULL);

SET @hm_dict_id = LAST_INSERT_ID();

INSERT INTO `dictionary_entries`
  (`dictionary_id`, `word`, `telugu`, `hindi`, `transliteration`, `part_of_speech`)
VALUES
  (@hm_dict_id, 'Water', 'dej', '', '', 'noun'),
  (@hm_dict_id, 'Rice', 'mov', '', '', 'noun'),
  (@hm_dict_id, 'Eat', 'noj', '', '', 'verb'),
  (@hm_dict_id, 'House', 'tsev', '', '', 'noun'),
  (@hm_dict_id, 'Person', 'tus neeg', '', '', 'noun'),
  (@hm_dict_id, 'Good', 'zoo', '', '', 'adj'),
  (@hm_dict_id, 'Bad', 'phem', '', '', 'adj'),
  (@hm_dict_id, 'Hello', 'Nyob zoo', '', '', 'interj'),
  (@hm_dict_id, 'Goodbye', 'Mus zoo', '', '', 'interj'),
  (@hm_dict_id, 'Thank you', 'Ua tsaug', '', '', 'interj'),
  (@hm_dict_id, 'Language', 'lus', '', '', 'noun'),
  (@hm_dict_id, 'Book', 'phau ntawv', '', '', 'noun'),
  (@hm_dict_id, 'Child', 'me nyuam', '', '', 'noun'),
  (@hm_dict_id, 'Love', 'hlub', '', '', 'verb'),
  (@hm_dict_id, 'Friend', 'phooj ywg', '', '', 'noun');
