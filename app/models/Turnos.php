<?php
// app/models/Turnos.php
require_once __DIR__ . '/../../includes/db_config.php';

class Turnos {
    public static function all(): array {
        $rows = [];
        $sql = "SELECT * FROM turnos WHERE is_deleted = 0 ORDER BY id DESC";
        $res = mysqli_query($GLOBALS['conn'], $sql);
        if ($res) {
            while($r = mysqli_fetch_assoc($res)) $rows[] = $r;
            mysqli_free_result($res);
        }
        return $rows;
    }

    public static function searchPaginated(?string $q, int $limit, int $offset, int & $total, ?string $status = null, string $sort = 'id', string $dir = 'DESC'): array {
        $q = trim($q ?? '');
        $conn = $GLOBALS['conn'];
        $wheres = ['is_deleted = 0'];
        $params = [];
        $types = '';
        
        if ($q !== '') {
            $wheres[] = "(name LIKE ? OR description LIKE ?)";
            $like = '%' . $q . '%';
            array_push($params, $like, $like);
            $types .= 'ss';
        }
        
        if ($status !== null && $status !== '') {
            $wheres[] = "status = ?";
            $params[] = $status;
            $types .= 's';
        }
        
        $where = 'WHERE ' . implode(' AND ', $wheres);
        
        // Ordenar con whitelist
        $allowedSort = ['id', 'name', 'start_time', 'end_time', 'status'];
        if (!in_array($sort, $allowedSort, true)) { $sort = 'id'; }
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';

        // Total
        $sqlCount = "SELECT COUNT(*) as c FROM turnos " . $where;
        if ($stmt = mysqli_prepare($conn, $sqlCount)) {
            if ($types !== '') { mysqli_stmt_bind_param($stmt, $types, ...$params); }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : ['c'=>0];
            $total = (int)($row['c'] ?? 0);
            mysqli_stmt_close($stmt);
        } else { $total = 0; }

        $rows = [];
        $sql = "SELECT * FROM turnos " . $where . " ORDER BY $sort $dir LIMIT ? OFFSET ?";
        
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
        $name = mysqli_real_escape_string($GLOBALS['conn'], $data['name'] ?? '');
        $start_time = mysqli_real_escape_string($GLOBALS['conn'], $data['start_time'] ?? '');
        $end_time = mysqli_real_escape_string($GLOBALS['conn'], $data['end_time'] ?? '');
        $break_start = !empty($data['break_start']) ? "'" . mysqli_real_escape_string($GLOBALS['conn'], $data['break_start']) . "'" : 'NULL';
        $break_end = !empty($data['break_end']) ? "'" . mysqli_real_escape_string($GLOBALS['conn'], $data['break_end']) . "'" : 'NULL';
        $description = mysqli_real_escape_string($GLOBALS['conn'], $data['description'] ?? '');
        $status = mysqli_real_escape_string($GLOBALS['conn'], $data['status'] ?? 'ACTIVO');
        
        $sql = "INSERT INTO turnos (name, start_time, end_time, break_start, break_end, description, status) VALUES (?, ?, ?, $break_start, $break_end, ?, ?)";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'sssss', $name, $start_time, $end_time, $description, $status);
            if (mysqli_stmt_execute($stmt)) {
                $id = (int)mysqli_insert_id($GLOBALS['conn']);
                mysqli_stmt_close($stmt);
                return $id;
            } else {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo crear el turno: ' . mysqli_error($GLOBALS['conn']));
            }
        } else {
            throw new Exception('Error preparando la consulta: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function update(int $id, array $data): void {
        $name = mysqli_real_escape_string($GLOBALS['conn'], $data['name'] ?? '');
        $start_time = mysqli_real_escape_string($GLOBALS['conn'], $data['start_time'] ?? '');
        $end_time = mysqli_real_escape_string($GLOBALS['conn'], $data['end_time'] ?? '');
        $break_start = !empty($data['break_start']) ? "'" . mysqli_real_escape_string($GLOBALS['conn'], $data['break_start']) . "'" : 'NULL';
        $break_end = !empty($data['break_end']) ? "'" . mysqli_real_escape_string($GLOBALS['conn'], $data['break_end']) . "'" : 'NULL';
        $description = mysqli_real_escape_string($GLOBALS['conn'], $data['description'] ?? '');
        $status = mysqli_real_escape_string($GLOBALS['conn'], $data['status'] ?? 'ACTIVO');
        
        $sql = "UPDATE turnos SET name = ?, start_time = ?, end_time = ?, break_start = $break_start, break_end = $break_end, description = ?, status = ? WHERE id = ? AND is_deleted = 0";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'sssssi', $name, $start_time, $end_time, $description, $status, $id);
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo actualizar el turno: ' . mysqli_error($GLOBALS['conn']));
            }
            mysqli_stmt_close($stmt);
        } else {
            throw new Exception('Error preparando la consulta: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function findById(int $id): ?array {
        $sql = 'SELECT * FROM turnos WHERE id = ? AND is_deleted = 0 LIMIT 1';
        
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
        $sql = 'UPDATE turnos SET is_deleted = 1 WHERE id = ?';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function getStatuses(): array {
        return ['ACTIVO', 'INACTIVO'];
    }

    public static function getActive(): array {
        $rows = [];
        $sql = "SELECT * FROM turnos WHERE status = 'ACTIVO' AND is_deleted = 0 ORDER BY name";
        $res = mysqli_query($GLOBALS['conn'], $sql);
        if ($res) {
            while($r = mysqli_fetch_assoc($res)) $rows[] = $r;
            mysqli_free_result($res);
        }
        return $rows;
    }

    public static function exists(string $name, ?int $exclude_id = null): bool {
        $sql = 'SELECT id FROM turnos WHERE name = ? AND is_deleted = 0';
        $params = [$name];
        $types = 's';
        
        if ($exclude_id) {
            $sql .= ' AND id != ?';
            $params[] = $exclude_id;
            $types .= 'i';
        }
        
        $sql .= ' LIMIT 1';
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $exists = mysqli_num_rows($res) > 0;
            mysqli_stmt_close($stmt);
            return $exists;
        }
        return false;
    }

    public static function calculateHours(string $start_time, string $end_time): float {
        $start = new DateTime($start_time);
        $end = new DateTime($end_time);
        
        // Si el turno cruza la medianoche
        if ($end < $start) {
            $end->add(new DateInterval('P1D'));
        }
        
        $interval = $start->diff($end);
        return $interval->h + ($interval->i / 60);
    }

    public static function getTurnoAsignaciones(int $turno_id): array {
        $rows = [];
        $sql = "SELECT ta.*, p.first_name, p.last_name, p.employee_code, t.name as turno_name 
                FROM turno_asignaciones ta 
                LEFT JOIN personal p ON ta.personal_id = p.id 
                LEFT JOIN turnos t ON ta.turno_id = t.id 
                WHERE ta.turno_id = ? AND ta.is_deleted = 0
                ORDER BY ta.start_date DESC";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $turno_id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($res && ($r = mysqli_fetch_assoc($res))) { $rows[] = $r; }
            mysqli_stmt_close($stmt);
        }
        return $rows;
    }

    public static function getPersonalTurno(int $personal_id, string $date): ?array {
        $sql = "SELECT ta.*, t.name, t.start_time, t.end_time, t.break_start, t.break_end 
                FROM turno_asignaciones ta 
                LEFT JOIN turnos t ON ta.turno_id = t.id 
                WHERE ta.personal_id = ? AND ta.status = 'ACTIVO' AND ta.is_deleted = 0 
                AND (ta.end_date IS NULL OR ta.end_date >= ?) AND ta.start_date <= ?
                ORDER BY ta.start_date DESC LIMIT 1";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'iss', $personal_id, $date, $date);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : null;
            mysqli_stmt_close($stmt);
            return $row ?: null;
        }
        return null;
    }
}
