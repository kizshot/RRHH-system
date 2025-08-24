<?php
// app/models/Permisos.php
require_once __DIR__ . '/../../includes/db_config.php';

class Permisos {
    public static function all(): array {
        $rows = [];
        $sql = "SELECT p.*, per.first_name, per.last_name, per.employee_code, u.username as approved_by_name 
                FROM permisos p 
                LEFT JOIN personal per ON p.personal_id = per.id 
                LEFT JOIN users u ON p.approved_by = u.id 
                WHERE p.is_deleted = 0
                ORDER BY p.id DESC";
        $res = mysqli_query($GLOBALS['conn'], $sql);
        if ($res) {
            while($r = mysqli_fetch_assoc($res)) $rows[] = $r;
            mysqli_free_result($res);
        }
        return $rows;
    }

    public static function searchPaginated(?string $q, int $limit, int $offset, int & $total, ?string $status = null, ?string $permission_type = null, ?string $personal_id = null, string $sort = 'id', string $dir = 'DESC'): array {
        $q = trim($q ?? '');
        $conn = $GLOBALS['conn'];
        $wheres = ['p.is_deleted = 0'];
        $params = [];
        $types = '';
        
        if ($q !== '') {
            $wheres[] = "(per.employee_code LIKE ? OR per.first_name LIKE ? OR per.last_name LIKE ? OR p.reason LIKE ?)";
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like, $like);
            $types .= 'ssss';
        }
        
        if ($status !== null && $status !== '') {
            $wheres[] = "p.status = ?";
            $params[] = $status;
            $types .= 's';
        }
        
        if ($permission_type !== null && $permission_type !== '') {
            $wheres[] = "p.permission_type = ?";
            $params[] = $permission_type;
            $types .= 's';
        }
        
        if ($personal_id !== null && $personal_id !== '') {
            $wheres[] = "p.personal_id = ?";
            $params[] = $personal_id;
            $types .= 'i';
        }
        
        $where = 'WHERE ' . implode(' AND ', $wheres);
        
        // Ordenar con whitelist
        $allowedSort = ['id', 'start_date', 'end_date', 'permission_type', 'status'];
        if (!in_array($sort, $allowedSort, true)) { $sort = 'id'; }
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';

        // Total
        $sqlCount = "SELECT COUNT(*) as c FROM permisos p LEFT JOIN personal per ON p.personal_id = per.id " . $where;
        if ($stmt = mysqli_prepare($conn, $sqlCount)) {
            if ($types !== '') { mysqli_stmt_bind_param($stmt, $types, ...$params); }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : ['c'=>0];
            $total = (int)($row['c'] ?? 0);
            mysqli_stmt_close($stmt);
        } else { $total = 0; }

        $rows = [];
        $sql = "SELECT p.*, per.first_name, per.last_name, per.employee_code, u.username as approved_by_name 
                FROM permisos p 
                LEFT JOIN personal per ON p.personal_id = per.id 
                LEFT JOIN users u ON p.approved_by = u.id 
                " . $where . " ORDER BY p.$sort $dir LIMIT ? OFFSET ?";
        
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
        $start_time = !empty($data['start_time']) ? "'" . mysqli_real_escape_string($GLOBALS['conn'], $data['start_time']) . "'" : 'NULL';
        $end_time = !empty($data['end_time']) ? "'" . mysqli_real_escape_string($GLOBALS['conn'], $data['end_time']) . "'" : 'NULL';
        $total_hours = !empty($data['total_hours']) ? (float)$data['total_hours'] : 'NULL';
        $reason = mysqli_real_escape_string($GLOBALS['conn'], $data['reason'] ?? '');
        $permission_type = mysqli_real_escape_string($GLOBALS['conn'], $data['permission_type'] ?? 'PERSONAL');
        $status = mysqli_real_escape_string($GLOBALS['conn'], $data['status'] ?? 'SOLICITADO');
        
        $sql = "INSERT INTO permisos (personal_id, start_date, end_date, start_time, end_time, total_hours, reason, permission_type, status) VALUES (?, ?, ?, $start_time, $end_time, $total_hours, ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'isssss', $personal_id, $start_date, $end_date, $reason, $permission_type, $status);
            if (mysqli_stmt_execute($stmt)) {
                $id = (int)mysqli_insert_id($GLOBALS['conn']);
                mysqli_stmt_close($stmt);
                return $id;
            } else {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo crear el permiso: ' . mysqli_error($GLOBALS['conn']));
            }
        } else {
            throw new Exception('Error preparando la consulta: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function update(int $id, array $data): void {
        $personal_id = (int)($data['personal_id'] ?? 0);
        $start_date = mysqli_real_escape_string($GLOBALS['conn'], $data['start_date'] ?? '');
        $end_date = mysqli_real_escape_string($GLOBALS['conn'], $data['end_date'] ?? '');
        $start_time = !empty($data['start_time']) ? "'" . mysqli_real_escape_string($GLOBALS['conn'], $data['start_time']) . "'" : 'NULL';
        $end_time = !empty($data['end_time']) ? "'" . mysqli_real_escape_string($GLOBALS['conn'], $data['end_time']) . "'" : 'NULL';
        $total_hours = !empty($data['total_hours']) ? (float)$data['total_hours'] : 'NULL';
        $reason = mysqli_real_escape_string($GLOBALS['conn'], $data['reason'] ?? '');
        $permission_type = mysqli_real_escape_string($GLOBALS['conn'], $data['permission_type'] ?? 'PERSONAL');
        $status = mysqli_real_escape_string($GLOBALS['conn'], $data['status'] ?? 'SOLICITADO');
        $approved_by = !empty($data['approved_by']) ? $data['approved_by'] : 'NULL';
        
        $sql = "UPDATE permisos SET personal_id = ?, start_date = ?, end_date = ?, start_time = $start_time, end_time = $end_time, total_hours = $total_hours, reason = ?, permission_type = ?, status = ?, approved_by = $approved_by WHERE id = ? AND is_deleted = 0";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'issssi', $personal_id, $start_date, $end_date, $reason, $permission_type, $status, $id);
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo actualizar el permiso: ' . mysqli_error($GLOBALS['conn']));
            }
            mysqli_stmt_close($stmt);
        } else {
            throw new Exception('Error preparando la consulta: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function findById(int $id): ?array {
        $sql = 'SELECT p.*, per.first_name, per.last_name, per.employee_code, u.username as approved_by_name FROM permisos p LEFT JOIN personal per ON p.personal_id = per.id LEFT JOIN users u ON p.approved_by = u.id WHERE p.id = ? AND p.is_deleted = 0 LIMIT 1';
        
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
        $sql = 'UPDATE permisos SET is_deleted = 1 WHERE id = ?';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function getStatuses(): array {
        return ['SOLICITADO', 'APROBADO', 'RECHAZADO', 'COMPLETADO'];
    }

    public static function getPermissionTypes(): array {
        return ['PERSONAL', 'MEDICO', 'ESTUDIO', 'OTRO'];
    }

    public static function getByPersonalId(int $personal_id): array {
        $rows = [];
        $sql = "SELECT p.*, per.first_name, per.last_name, per.employee_code, u.username as approved_by_name 
                FROM permisos p 
                LEFT JOIN personal per ON p.personal_id = per.id 
                LEFT JOIN users u ON p.approved_by = u.id 
                WHERE p.personal_id = ? AND p.is_deleted = 0
                ORDER BY p.start_date DESC";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $personal_id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($res && ($r = mysqli_fetch_assoc($res))) { $rows[] = $r; }
            mysqli_stmt_close($stmt);
        }
        return $rows;
    }

    public static function approve(int $id, int $approved_by): void {
        $sql = 'UPDATE permisos SET status = "APROBADO", approved_by = ? WHERE id = ? AND is_deleted = 0';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ii', $approved_by, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function reject(int $id, int $approved_by): void {
        $sql = 'UPDATE permisos SET status = "RECHAZADO", approved_by = ? WHERE id = ? AND is_deleted = 0';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ii', $approved_by, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function complete(int $id): void {
        $sql = 'UPDATE permisos SET status = "COMPLETADO" WHERE id = ? AND is_deleted = 0';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function calculateHours(string $start_time, string $end_time): float {
        $start = new DateTime($start_time);
        $end = new DateTime($end_time);
        
        // Si el permiso cruza la medianoche
        if ($end < $start) {
            $end->add(new DateInterval('P1D'));
        }
        
        $interval = $start->diff($end);
        return $interval->h + ($interval->i / 60);
    }

    public static function getActivePermissions(int $personal_id, string $date): array {
        $rows = [];
        $sql = "SELECT p.*, per.first_name, per.last_name, per.employee_code 
                FROM permisos p 
                LEFT JOIN personal per ON p.personal_id = per.id 
                WHERE p.personal_id = ? AND p.status = 'APROBADO' AND p.is_deleted = 0 
                AND p.start_date <= ? AND p.end_date >= ?
                ORDER BY p.start_date";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'iss', $personal_id, $date, $date);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($res && ($r = mysqli_fetch_assoc($res))) { $rows[] = $r; }
            mysqli_stmt_close($stmt);
        }
        return $rows;
    }

    public static function getMonthlySummary(int $personal_id, string $month, string $year): array {
        $rows = [];
        $sql = "SELECT 
                    permission_type,
                    COUNT(*) as total_permissions,
                    SUM(total_hours) as total_hours,
                    SUM(CASE WHEN status = 'APROBADO' THEN 1 ELSE 0 END) as approved_count,
                    SUM(CASE WHEN status = 'RECHAZADO' THEN 1 ELSE 0 END) as rejected_count
                FROM permisos 
                WHERE personal_id = ? AND MONTH(start_date) = ? AND YEAR(start_date) = ? AND is_deleted = 0
                GROUP BY permission_type";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'iss', $personal_id, $month, $year);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($res && ($r = mysqli_fetch_assoc($res))) { $rows[] = $r; }
            mysqli_stmt_close($stmt);
        }
        return $rows;
    }
}
