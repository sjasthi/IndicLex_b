<?php
// ============================================================
// includes/dictionary_search.php — Shared dictionary search
// Used by public search page and GET /api/search
// ============================================================

/**
 * @return list<string>
 */
function indiclex_valid_search_modes(): array
{
    return ['exact', 'prefix', 'suffix', 'substring'];
}

function indiclex_build_like_pattern(string $mode, string $query): string
{
    return match ($mode) {
        'exact'     => $query,
        'prefix'    => $query . '%',
        'suffix'    => '%' . $query,
        'substring' => '%' . $query . '%',
        default     => '%' . $query . '%',
    };
}

/**
 * Run a dictionary search (same semantics as the public search UI).
 *
 * @param mysqli $db
 * @param string $query   non-empty trimmed query
 * @param string $mode    one of indiclex_valid_search_modes()
 * @param string|int      $dict_id  'all', '', or positive dictionary id
 * @param positive-int    $limit
 * @param non-negative-int $offset
 * @return array{ok:bool,total?:int,rows?:list<array<string,mixed>>,error?:string}
 */
function indiclex_dictionary_search(mysqli $db, string $query, string $mode, $dict_id, int $limit, int $offset): array
{
    $pattern = indiclex_build_like_pattern($mode, $query);
    $limit   = max(1, $limit);
    $offset  = max(0, $offset);

    try {
        if ($dict_id === 'all' || $dict_id === '') {
            $where      = '(de.word LIKE ? OR de.telugu LIKE ? OR de.hindi LIKE ? OR de.transliteration LIKE ?)';
            $count_sql  = "SELECT COUNT(*) AS total FROM dictionary_entries de WHERE {$where}";
            $result_sql = "SELECT de.*, d.name AS dictionary_name
                           FROM dictionary_entries de
                           JOIN dictionaries d ON de.dictionary_id = d.id
                           WHERE {$where}
                           ORDER BY de.word ASC
                           LIMIT ? OFFSET ?";

            $count_stmt = $db->prepare($count_sql);
            if (!$count_stmt) {
                return ['ok' => false, 'error' => $db->error];
            }
            $count_stmt->bind_param('ssss', $pattern, $pattern, $pattern, $pattern);
            $count_stmt->execute();
            $total = (int) $count_stmt->get_result()->fetch_assoc()['total'];
            $count_stmt->close();

            $stmt = $db->prepare($result_sql);
            if (!$stmt) {
                return ['ok' => false, 'error' => $db->error];
            }
            $stmt->bind_param('ssssii', $pattern, $pattern, $pattern, $pattern, $limit, $offset);
        } else {
            $dict_id_int = (int) $dict_id;
            $where       = '(de.word LIKE ? OR de.telugu LIKE ? OR de.hindi LIKE ? OR de.transliteration LIKE ?) AND de.dictionary_id = ?';
            $count_sql   = "SELECT COUNT(*) AS total FROM dictionary_entries de WHERE {$where}";
            $result_sql  = "SELECT de.*, d.name AS dictionary_name
                            FROM dictionary_entries de
                            JOIN dictionaries d ON de.dictionary_id = d.id
                            WHERE {$where}
                            ORDER BY de.word ASC
                            LIMIT ? OFFSET ?";

            $count_stmt = $db->prepare($count_sql);
            if (!$count_stmt) {
                return ['ok' => false, 'error' => $db->error];
            }
            $count_stmt->bind_param('ssssi', $pattern, $pattern, $pattern, $pattern, $dict_id_int);
            $count_stmt->execute();
            $total = (int) $count_stmt->get_result()->fetch_assoc()['total'];
            $count_stmt->close();

            $stmt = $db->prepare($result_sql);
            if (!$stmt) {
                return ['ok' => false, 'error' => $db->error];
            }
            $stmt->bind_param('ssssiii', $pattern, $pattern, $pattern, $pattern, $dict_id_int, $limit, $offset);
        }

        $stmt->execute();
        $res  = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();

        return ['ok' => true, 'total' => $total, 'rows' => $rows];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

/**
 * @return array{ok:true,exists:bool}|array{ok:false,error:string}
 */
function indiclex_dictionary_exists(mysqli $db, int $id): array
{
    $stmt = $db->prepare('SELECT id FROM dictionaries WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return ['ok' => false, 'error' => $db->error];
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $exists = (bool) $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return ['ok' => true, 'exists' => $exists];
}
