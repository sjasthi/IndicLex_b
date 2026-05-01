<?php
/**
 * Stream Kaikki/Wiktextract English JSONL and populate English–Hmong glossary.
 *
 * Source: https://kaikki.org/dictionary/English/ (CC BY-SA 4.0 — credit Wiktionary contributors)
 * Data is English lemmas with translation lines; we keep Hmong (and major Hmong dialect codes).
 *
 * Usage (from project root):
 *   php tools/import_hmong_from_kaikki_jsonl.php
 *   php tools/import_hmong_from_kaikki_jsonl.php --file=C:\path\to\kaikki.org-dictionary-English.jsonl
 *   php tools/import_hmong_from_kaikki_jsonl.php --url=https://kaikki.org/dictionary/English/kaikki.org-dictionary-English.jsonl
 *   php tools/import_hmong_from_kaikki_jsonl.php --dict-name=English–Hmong --dry-run
 *
 * Full network import is ~3 GB download; expect a long run. Prefer downloading once and using --file=.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only.\n");
    exit(1);
}

set_time_limit(0);
ini_set('memory_limit', '512M');

require_once __DIR__ . '/../includes/db.php';

$args = array_slice($argv, 1);
$opts = [
    'file'       => null,
    'url'        => 'https://kaikki.org/dictionary/English/kaikki.org-dictionary-English.jsonl',
    'dict-name'  => 'English–Hmong',
    'dict-id'    => null,
    'dry-run'    => false,
    'max-lines'  => 0, // 0 = unlimited
];

foreach ($args as $a) {
    if ($a === '--dry-run') {
        $opts['dry-run'] = true;
        continue;
    }
    if (preg_match('/^--([^=]+)=(.*)$/', $a, $m)) {
        $k = $m[1];
        if (array_key_exists($k, $opts)) {
            $opts[$k] = $m[2];
        }
    }
}

if ($opts['dict-id'] !== null) {
    $opts['dict-id'] = max(0, (int) $opts['dict-id']);
}
if ($opts['max-lines'] !== null) {
    $opts['max-lines'] = (int) ($opts['max-lines'] ?? 0);
}

/** ISO / Wiktionary codes that map to Hmong/Mong languages */
$HMONG_CODES = [
    'hmn', 'mww', 'hma', 'hmj', 'hmv', 'hmz', 'mwq', // Wiktionary language codes → Hmongic
];

$dictId = $opts['dict-id'];
if (!$dictId) {
    // Prefer target_lang match (handles oddities in legacy `name` encodings).
    $stmt = $db->prepare('SELECT id FROM dictionaries WHERE target_lang = \'Hmong\' ORDER BY id ASC LIMIT 1');
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$res) {
        $stmt = $db->prepare('SELECT id FROM dictionaries WHERE name = ? LIMIT 1');
        $stmt->bind_param('s', $opts['dict-name']);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
    if (!$res) {
        fwrite(STDERR, "No English–Hmong dictionary found. Create one (target_lang = Hmong) or pass --dict-id=N.\n");
        exit(1);
    }
    $dictId = (int) $res['id'];
}

$source = $opts['file'] ?: $opts['url'];
$input = $opts['file']
    ? fopen($opts['file'], 'rb')
    : fopen($opts['url'], 'rb');

if (!$input) {
    fwrite(STDERR, "Cannot open: {$source}\n");
    exit(1);
}

$friendly = $opts['file'] ? $opts['file'] : $opts['url'];
fwrite(STDERR, "Streaming: {$friendly}\nDictionary id: {$dictId}\n");

/** @param mixed $t */
function is_hmong_translation(array $t, array $HMONG_CODES): bool
{
    $code = strtolower((string) ($t['code'] ?? ''));
    if ($code !== '' && in_array($code, $HMONG_CODES, true)) {
        return true;
    }
    $lang = strtolower((string) ($t['lang'] ?? ''));
    if ($lang !== '' && str_contains($lang, 'hmong')) {
        return true;
    }

    return false;
}

/** @param mixed $t */
function gloss_from_translation(array $t): string
{
    $w = trim((string) ($t['word'] ?? ''));
    if ($w !== '') {
        return $w;
    }

    return trim((string) ($t['roman'] ?? $t['alt'] ?? ''));
}

/**
 * @param list<array<mixed,mixed>>|null $translations
 */
function collect_hmong_glosses(?array $translations, array $HMONG_CODES, array &$bucket): void
{
    if ($translations === null || $translations === []) {
        return;
    }
    foreach ($translations as $t) {
        if (!is_array($t)) {
            continue;
        }
        if (is_hmong_translation($t, $HMONG_CODES)) {
            $g = gloss_from_translation($t);
            if ($g !== '') {
                $bucket[strtolower($g)] = $g;
            }
        }
    }
}

$inserted = 0;
$lines = 0;
$entriesWithPair = 0;

$prep = !$opts['dry-run'];
$stmtInsert = null;
if ($prep) {
    $stmtInsert = $db->prepare(
        'INSERT INTO dictionary_entries (dictionary_id, word, telugu, hindi, part_of_speech)
         VALUES (?, ?, ?, \'\', \'\')
         ON DUPLICATE KEY UPDATE telugu = VALUES(telugu)'
    );

    if (!$stmtInsert) {
        fwrite(STDERR, 'Prepare failed: ' . $db->error . "\n");
        exit(1);
    }
}

while (($line = fgets($input)) !== false) {
    $lines++;
    if ($opts['max-lines'] > 0 && $lines > $opts['max-lines']) {
        break;
    }
    if ($lines % 100000 === 0) {
        fwrite(STDERR, "… lines {$lines}, pairs extracted {$entriesWithPair}, rows upserted {$inserted}\n");
    }

    $line = trim($line);
    if ($line === '' || ($line[0] !== '[' && $line[0] !== '{')) {
        continue;
    }

    $row = json_decode($line, true);
    if (!is_array($row)) {
        continue;
    }
    if (($row['lang_code'] ?? '') !== 'en') {
        continue;
    }

    $word = trim((string) ($row['word'] ?? ''));
    if ($word === '' || preg_match('/\s{2,}/', $word)) {
        continue;
    }

    $bucket = []; // keyed lower for dedupe

    collect_hmong_glosses($row['translations'] ?? null, $HMONG_CODES, $bucket);

    foreach (($row['senses'] ?? []) as $sense) {
        if (!is_array($sense)) {
            continue;
        }
        collect_hmong_glosses($sense['translations'] ?? null, $HMONG_CODES, $bucket);
    }

    if ($bucket === []) {
        continue;
    }
    $entriesWithPair++;

    $hmongJoined = implode('; ', array_values($bucket));
    if (strlen($hmongJoined) > 255) {
        $hmongJoined = mb_substr($hmongJoined, 0, 252) . '...';
    }
    // Keep display headword capitalization from source; glossary in primary gloss column
    if (!$opts['dry-run'] && $stmtInsert) {
        $stmtInsert->bind_param('iss', $dictId, $word, $hmongJoined);
        if ($stmtInsert->execute()) {
            $inserted++;
        }
    } else {
        $inserted++;
    }
}
fclose($input);

if ($stmtInsert) {
    $stmtInsert->close();
}

fwrite(STDERR, "Done. JSONL lines scanned: {$lines}. Entries with ≥1 Hmong gloss: {$entriesWithPair}. Upserts: {$inserted}.\n");
if ($opts['dry-run']) {
    fwrite(STDERR, "(dry-run: database was not modified)\n");
}
