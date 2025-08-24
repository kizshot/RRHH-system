<?php
// app/models/Asistencias.php
require_once __DIR__ . '/../../includes/db_config.php';

class Asistencias {
    public static function all(): array {
        $rows = [];
        $sql = "SELECT a.*, p.first_name, p.last_name, p.employee_code 
                FROM asistencias a 
                LEFT JOIN personal p ON a.personal_id = p.id 
                WHERE a.is_deleted = 0
                ORDER BY a.date DESC, a.id DESC";
        $res = mysqli_query($GLOBALS['conn'], $sql);
        if ($res) {
            while($r = mysqli_fetch_assoc($res)) $rows[] = $r;
            mysqli_free_result($res);
        }
        return $rows;
    }

    public static function searchPaginated(?string $q, int $limit, int $offset, int & $total, ?string $status = null, ?string $personal_id = null, ?string $date_from = null, ?string $date_to = null, string $sort = 'date', string $dir = 'DESC'): array {
        $q = trim($q ?? '');
        $conn = $GLOBALS['conn'];
        $wheres = ['a.is_deleted = 0'];
        $params = [];
        $types = '';
        
        if ($q !== '') {
            $wheres[] = "(p.employee_code LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ?)";
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like);
            $types .= 'sss';
        }
        
        if ($status !== null && $status !== '') {
            $wheres[] = "a.status = ?";
            $params[] = $status;
            $types .= 's';
        }
        
        if ($personal_id !== null && $personal_id !== '') {
            $wheres[] = "a.personal_id = ?";
            $params[] = $personal_id;
            $types .= 'i';
        }
        
        if ($date_from !== null && $date_from !== '') {
            $wheres[] = "a.date >= ?";
            $params[] = $date_from;
            $types .= 's';
        }
        
        if ($date_to !== null && $date_to !== '') {
            $wheres[] = "a.date <= ?";
            $params[] = $date_to;
            $types .= 's';
        }
        
        $where = 'WHERE ' . implode(' AND ', $wheres);
        
        // Ordenar con whitelist
        $allowedSort = ['id', 'date', 'entry_time', 'exit_time', 'status'];
        if (!in_array($sort, $allowedSort, true)) { $sort = 'date'; }
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';

        // Total
        $sqlCount = "SELECT COUNT(*) as c FROM asistencias a LEFT JOIN personal p ON a.personal_id = p.id " . $where;
        if ($stmt = mysqli_prepare($conn, $sqlCount)) {
            if ($types !== '') { mysqli_stmt_bind_param($stmt, $types, ...$params); }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : ['c'=>0];
            $total = (int)($row['c'] ?? 0);
            mysqli_stmt_close($stmt);
        } else { $total = 0; }

        $rows = [];
        $sql = "SELECT a.*, p.first_name, p.last_name, p.employee_code 
                FROM asistencias a 
                LEFT JOIN personal p ON a.personal_id = p.id 
                " . $where . " ORDER BY a.$sort $dir LIMIT ? OFFSET ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            if ($types !== '') {
                $types2 = $types . 'ii';
                $params2 = array_merge($params, [$limit, $offset]);
                mysqli_stmt_bind_param($stmt, $types2, ...$params2);
            } else {
                mysqli_stmt_bind_param($stmt, 'ii', $limit, $offset);
            }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($res && ($r = mysqli_fetch_assoc($res))) { $rows[] = $r; }
            mysqli_stmt_close($stmt);
        }
        return $rows;
    }

    public static function create(array $data): int {
        $personal_id = (int)($data['personal_id'] ?? 0);
        $date = mysqli_real_escape_string($GLOBALS['conn'], $data['date'] ?? '');
        $entry_time = !empty($data['entry_time']) ? "'" . mysqli_real_escape_string($GLOBALS['conn'], $data['entry_time']) . "'" : 'NULL';
        $exit_time = !empty($data['exit_time']) ? "'" . mysqli_real_escape_string($GLOBALS['conn'], $data['exit_time']) . "'" : 'NULL';
        $late_minutes = (int)($data['late_minutes'] ?? 0);
        $early_exit_minutes = (int)($data['early_exit_minutes'] ?? 0);
        $status = mysqli_real_escape_string($GLOBALS['conn'], $data['status'] ?? 'PRESENTE');
        
        $sql = "INSERT INTO asistencias (personal_id, date, entry_time, exit_time, late_minutes, early_exit_minutes, status) VALUES (?, ?, $entry_time, $exit_time, ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'isiiis', $personal_id, $date, $late_minutes, $early_exit_minutes, $status);
            if (mysqli_stmt_execute($stmt)) {
                $id = (int)mysqli_insert_id($GLOBALS['conn']);
                mysqli_stmt_close($stmt);
                return $id;
            } else {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo crear la asistencia: ' . mysqli_error($GLOBALS['conn']));
            }
        } else {
            throw new Exception('Error preparando la consulta: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function update(int $id, array $data): void {
        $personal_id = (int)($data['personal_id'] ?? 0);
        $date = mysqli_real_escape_string($GLOBALS['conn'], $data['date'] ?? '');
        $entry_time = !empty($data['entry_time']) ? "'" . mysqli_real_escape_string($GLOBALS['conn'], $data['entry_time']) . "'" : 'NULL';
        $exit_time = !empty($data['exit_time']) ? "'" . mysqli_real_escape_string($GLOBALS['conn'], $data['exit_time']) . "'" : 'NULL';
        $late_minutes = (int)($data['late_minutes'] ?? 0);
        $early_exit_minutes = (int)($data['early_exit_minutes'] ?? 0);
        $status = mysqli_real_escape_string($GLOBALS['conn'], $data['status'] ?? 'PRESENTE');
        
        $sql = "UPDATE asistencias SET personal_id = ?, date = ?, entry_time = $entry_time, exit_time = $exit_time, late_minutes = ?, early_exit_minutes = ?, status = ? WHERE id = ? AND is_deleted = 0";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'isiiisi', $personal_id, $date, $late_minutes, $early_exit_minutes, $status, $id);
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo actualizar la asistencia: ' . mysqli_error($GLOBALS['conn']));
            }
            mysqli_stmt_close($stmt);
        } else {
            throw new Exception('Error preparando la consulta: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function findById(int $id): ?array {
        $sql = 'SELECT a.*, p.first_name, p.last_name, p.employee_code FROM asistencias a LEFT JOIN personal p ON a.personal_id = p.id WHERE a.id = ? AND a.is_deleted = 0 LIMIT 1';
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : null;
            mysqli_stmt_close($stmt);
            return $row ?: null;
        }
        return null;
    }

    public static function delete(int $id): void {
        // Soft delete
        $sql = 'UPDATE asistencias SET is_deleted = 1 WHERE id = ?';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function getStatuses(): array {
        return ['PRESENTE', 'AUSENTE', 'TARDANZA', 'SALIDA_TEMPRANA', 'LICENCIA'];
    }

    public static function getByPersonalId(int $personal_id, ?string $date_from = null, ?string $date_to = null): array {
        $rows = [];
        $wheres = ['a.personal_id = ?', 'a.is_deleted = 0'];
        $params = [$personal_id];
        $types = 'i';
        
        if ($date_from !== null && $date_from !== '') {
            $wheres[] = "a.date >= ?";
            $params[] = $date_from;
            $types .= 's';
        }
        
        if ($date_to !== null && $date_to !== '') {
            $wheres[] = "a.date <= ?";
            $params[] = $date_to;
            $types .= 's';
        }
        
        $where = 'WHERE ' . implode(' AND ', $wheres);
        
        $sql = "SELECT a.*, p.first_name, p.last_name, p.employee_code 
                FROM asistencias a 
                LEFT JOIN personal p ON a.personal_id = p.id 
                $where ORDER BY a.date DESC";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($res && ($r = mysqli_fetch_assoc($res))) { $rows[] = $r; }
            mysqli_stmt_close($stmt);
        }
        return $rows;
    }

    public static function getByDate(string $date): array {
        $rows = [];
        $sql = "SELECT a.*, p.first_name, p.last_name, p.employee_code 
                FROM asistencias a 
                LEFT JOIN personal p ON a.personal_id = p.id 
                WHERE a.date = ? AND a.is_deleted = 0
                ORDER BY p.first_name, p.last_name";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 's', $date);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($res && ($r = mysqli_fetch_assoc($res))) { $rows[] = $r; }
            mysqli_stmt_close($stmt);
        }
        return $rows;
    }

    public static function exists(int $personal_id, string $date): bool {
        $sql = 'SELECT id FROM asistencias WHERE personal_id = ? AND date = ? AND is_deleted = 0 LIMIT 1';
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'is', $personal_id, $date);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $exists = mysqli_num_rows($res) > 0;
            mysqli_stmt_close($stmt);
            return $exists;
        }
        return false;
    }

    public static function markEntry(int $personal_id, string $date, string $entry_time): void {
        $late_minutes = 0; // Calcular tardanza si es necesario
        $status = 'PRESENTE';
        
        if (self::exists($personal_id, $date)) {
            // Actualizar entrada existente
            $sql = 'UPDATE asistencias SET entry_time = ?, late_minutes = ?, status = ? WHERE personal_id = ? AND date = ? AND is_deleted = 0';
            if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
                mysqli_stmt_bind_param($stmt, 'sisis', $entry_time, $late_minutes, $status, $personal_id, $date);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        } else {
            // Crear nueva asistencia
            $data = [
                'personal_id' => $personal_id,
                'date' => $date,
                'entry_time' => $entry_time,
                'late_minutes' => $late_minutes,
                'status' => $status
            ];
            self::create($data);
        }
    }

    public static function markExit(int $personal_id, string $date, string $exit_time): void {
        $sql = 'UPDATE asistencias SET exit_time = ? WHERE personal_id = ? AND date = ? AND is_deleted = 0';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'sis', $exit_time, $personal_id, $date);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function getAttendanceSummary(int $personal_id, string $month, string $year): array {
        $rows = [];
        $sql = "SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'PRESENTE' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN status = 'AUSENTE' THEN 1 ELSE 0 END) as absent_days,
                    SUM(CASE WHEN status = 'TARDANZA' THEN 1 ELSE 0 END) as late_days,
                    SUM(late_minutes) as total_late_minutes,
                    SUM(early_exit_minutes) as total_early_exit_minutes
                FROM asistencias 
                WHERE personal_id = ? AND MONTH(date) = ? AND YEAR(date) = ? AND is_deleted = 0";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'iss', $personal_id, $month, $year);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : null;
            mysqli_stmt_close($stmt);
            return $row ?: [];
        }
        return [];
    }
}
