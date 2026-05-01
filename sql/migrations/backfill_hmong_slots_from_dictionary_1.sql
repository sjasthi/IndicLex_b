-- Optional: Same English headwords as dictionary 1 → target_lang = Hmong dictionary.
SET @hm_dict_id := (SELECT id FROM dictionaries WHERE target_lang = 'Hmong' ORDER BY id ASC LIMIT 1);

INSERT INTO dictionary_entries (dictionary_id, word, telugu, hindi, part_of_speech, transliteration)
SELECT @hm_dict_id, de.word, '', '', COALESCE(de.part_of_speech, ''), ''
FROM dictionary_entries de
WHERE de.dictionary_id = 1
  AND @hm_dict_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM dictionary_entries h
    WHERE h.dictionary_id = @hm_dict_id AND h.word_norm = LOWER(de.word)
  );
