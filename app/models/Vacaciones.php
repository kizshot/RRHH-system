<?php
// app/models/Vacaciones.php
require_once __DIR__ . '/../../includes/db_config.php';

class Vacaciones {
    public static function all(): array {
        $rows = [];
        $sql = "SELECT v.*, p.first_name, p.last_name, p.employee_code, u.username as approved_by_name 
                FROM vacaciones v 
                LEFT JOIN personal p ON v.personal_id = p.id 
                LEFT JOIN users u ON v.approved_by = u.id 
                WHERE v.is_deleted = 0
                ORDER BY v.id DESC";
        $res = mysqli_query($GLOBALS['conn'], $sql);
        if ($res) {
            while($r = mysqli_fetch_assoc($res)) $rows[] = $r;
            mysqli_free_result($res);
        }
        return $rows;
    }

    public static function searchPaginated(?string $q, int $limit, int $offset, int & $total, ?string $status = null, ?string $personal_id = null, string $sort = 'id', string $dir = 'DESC'): array {
        $q = trim($q ?? '');
        $conn = $GLOBALS['conn'];
        $wheres = ['v.is_deleted = 0'];
        $params = [];
        $types = '';
        
        if ($q !== '') {
            $wheres[] = "(p.employee_code LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR v.reason LIKE ?)";
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like, $like);
            $types .= 'ssss';
        }
        
        if ($status !== null && $status !== '') {
            $wheres[] = "v.status = ?";
            $params[] = $status;
            $types .= 's';
        }
        
        if ($personal_id !== null && $personal_id !== '') {
            $wheres[] = "v.personal_id = ?";
            $params[] = $personal_id;
            $types .= 'i';
        }
        
        $where = 'WHERE ' . implode(' AND ', $wheres);
        
        // Ordenar con whitelist
        $allowedSort = ['id', 'start_date', 'end_date', 'days_requested', 'days_approved', 'status'];
        if (!in_array($sort, $allowedSort, true)) { $sort = 'id'; }
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';

        // Total
        $sqlCount = "SELECT COUNT(*) as c FROM vacaciones v LEFT JOIN personal p ON v.personal_id = p.id " . $where;
        if ($stmt = mysqli_prepare($conn, $sqlCount)) {
            if ($types !== '') { mysqli_stmt_bind_param($stmt, $types, ...$params); }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : ['c'=>0];
            $total = (int)($row['c'] ?? 0);
            mysqli_stmt_close($stmt);
        } else { $total = 0; }

        $rows = [];
        $sql = "SELECT v.*, p.first_name, p.last_name, p.employee_code, u.username as approved_by_name 
                FROM vacaciones v 
                LEFT JOIN personal p ON v.personal_id = p.id 
                LEFT JOIN users u ON v.approved_by = u.id 
                " . $where . " ORDER BY v.$sort $dir LIMIT ? OFFSET ?";
        
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
        $start_date = mysqli_real_escape_string($GLOBALS['conn'], $data['start_date'] ?? '');
        $end_date = mysqli_real_escape_string($GLOBALS['conn'], $data['end_date'] ?? '');
        $days_requested = (int)($data['days_requested'] ?? 0);
        $reason = mysqli_real_escape_string($GLOBALS['conn'], $data['reason'] ?? '');
        $status = mysqli_real_escape_string($GLOBALS['conn'], $data['status'] ?? 'SOLICITADO');
        
        $sql = "INSERT INTO vacaciones (personal_id, start_date, end_date, days_requested, reason, status) VALUES (?, ?, ?, ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'issiis', $personal_id, $start_date, $end_date, $days_requested, $reason, $status);
            if (mysqli_stmt_execute($stmt)) {
                $id = (int)mysqli_insert_id($GLOBALS['conn']);
                mysqli_stmt_close($stmt);
                return $id;
            } else {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo crear la vacación: ' . mysqli_error($GLOBALS['conn']));
            }
        } else {
            throw new Exception('Error preparando la consulta: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function update(int $id, array $data): void {
        $personal_id = (int)($data['personal_id'] ?? 0);
        $start_date = mysqli_real_escape_string($GLOBALS['conn'], $data['start_date'] ?? '');
        $end_date = mysqli_real_escape_string($GLOBALS['conn'], $data['end_date'] ?? '');
        $days_requested = (int)($data['days_requested'] ?? 0);
        $days_approved = !empty($data['days_approved']) ? (int)$data['days_approved'] : 'NULL';
        $reason = mysqli_real_escape_string($GLOBALS['conn'], $data['reason'] ?? '');
        $status = mysqli_real_escape_string($GLOBALS['conn'], $data['status'] ?? 'SOLICITADO');
        $approved_by = !empty($data['approved_by']) ? $data['approved_by'] : 'NULL';
        
        $sql = "UPDATE vacaciones SET personal_id = ?, start_date = ?, end_date = ?, days_requested = ?, days_approved = $days_approved, reason = ?, status = ?, approved_by = $approved_by WHERE id = ? AND is_deleted = 0";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'issiisi', $personal_id, $start_date, $end_date, $days_requested, $reason, $status, $id);
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo actualizar la vacación: ' . mysqli_error($GLOBALS['conn']));
            }
            mysqli_stmt_close($stmt);
        } else {
            throw new Exception('Error preparando la consulta: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function findById(int $id): ?array {
        $sql = 'SELECT v.*, p.first_name, p.last_name, p.employee_code, u.username as approved_by_name FROM vacaciones v LEFT JOIN personal p ON v.personal_id = p.id LEFT JOIN users u ON v.approved_by = u.id WHERE v.id = ? AND v.is_deleted = 0 LIMIT 1';
        
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
        $sql = 'UPDATE vacaciones SET is_deleted = 1 WHERE id = ?';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function getStatuses(): array {
        return ['SOLICITADO', 'APROBADO', 'RECHAZADO', 'EN_CURSO', 'COMPLETADO'];
    }

    public static function getByPersonalId(int $personal_id): array {
        $rows = [];
        $sql = "SELECT v.*, p.first_name, p.last_name, p.employee_code, u.username as approved_by_name 
                FROM vacaciones v 
                LEFT JOIN personal p ON v.personal_id = p.id 
                LEFT JOIN users u ON v.approved_by = u.id 
                WHERE v.personal_id = ? AND v.is_deleted = 0
                ORDER BY v.start_date DESC";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $personal_id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($res && ($r = mysqli_fetch_assoc($res))) { $rows[] = $r; }
            mysqli_stmt_close($stmt);
        }
        return $rows;
    }

    public static function approve(int $id, int $approved_by, int $days_approved): void {
        $sql = 'UPDATE vacaciones SET status = "APROBADO", days_approved = ?, approved_by = ? WHERE id = ? AND is_deleted = 0';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'iii', $days_approved, $approved_by, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function reject(int $id, int $approved_by): void {
        $sql = 'UPDATE vacaciones SET status = "RECHAZADO", approved_by = ? WHERE id = ? AND is_deleted = 0';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ii', $approved_by, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function startVacation(int $id): void {
        $sql = 'UPDATE vacaciones SET status = "EN_CURSO" WHERE id = ? AND is_deleted = 0';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function completeVacation(int $id): void {
        $sql = 'UPDATE vacaciones SET status = "COMPLETADO" WHERE id = ? AND is_deleted = 0';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function calculateDaysBetweenDates(string $start_date, string $end_date): int {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = $start->diff($end);
        return $interval->days + 1; // Incluir el día de inicio
    }
}
