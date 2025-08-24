<?php
// app/models/Reportes.php
require_once __DIR__ . '/../../includes/db_config.php';

class Reportes {
    public static function all(): array {
        $rows = [];
        $sql = "SELECT r.*, u.username as generated_by_name 
                FROM reportes r 
                LEFT JOIN users u ON r.generated_by = u.id 
                WHERE r.is_deleted = 0
                ORDER BY r.id DESC";
        $res = mysqli_query($GLOBALS['conn'], $sql);
        if ($res) {
            while($r = mysqli_fetch_assoc($res)) $rows[] = $r;
            mysqli_free_result($res);
        }
        return $rows;
    }

    public static function searchPaginated(?string $q, int $limit, int $offset, int & $total, ?string $report_type = null, ?string $status = null, string $sort = 'id', string $dir = 'DESC'): array {
        $q = trim($q ?? '');
        $conn = $GLOBALS['conn'];
        $wheres = ['r.is_deleted = 0'];
        $params = [];
        $types = '';
        
        if ($q !== '') {
            $wheres[] = "(r.title LIKE ? OR r.description LIKE ?)";
            $like = '%' . $q . '%';
            array_push($params, $like, $like);
            $types .= 'ss';
        }
        
        if ($report_type !== null && $report_type !== '') {
            $wheres[] = "r.report_type = ?";
            $params[] = $report_type;
            $types .= 's';
        }
        
        if ($status !== null && $status !== '') {
            $wheres[] = "r.status = ?";
            $params[] = $status;
            $types .= 's';
        }
        
        $where = 'WHERE ' . implode(' AND ', $wheres);
        
        // Ordenar con whitelist
        $allowedSort = ['id', 'title', 'report_type', 'status', 'created_at'];
        if (!in_array($sort, $allowedSort, true)) { $sort = 'id'; }
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';

        // Total
        $sqlCount = "SELECT COUNT(*) as c FROM reportes r " . $where;
        if ($stmt = mysqli_prepare($conn, $sqlCount)) {
            if ($types !== '') { mysqli_stmt_bind_param($stmt, $types, ...$params); }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : ['c'=>0];
            $total = (int)($row['c'] ?? 0);
            mysqli_stmt_close($stmt);
        } else { $total = 0; }

        $rows = [];
        $sql = "SELECT r.*, u.username as generated_by_name 
                FROM reportes r 
                LEFT JOIN users u ON r.generated_by = u.id 
                " . $where . " ORDER BY r.$sort $dir LIMIT ? OFFSET ?";
        
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
        $title = mysqli_real_escape_string($GLOBALS['conn'], $data['title'] ?? '');
        $description = mysqli_real_escape_string($GLOBALS['conn'], $data['description'] ?? '');
        $report_type = mysqli_real_escape_string($GLOBALS['conn'], $data['report_type'] ?? 'GENERAL');
        $parameters = mysqli_real_escape_string($GLOBALS['conn'], $data['parameters'] ?? '');
        $generated_by = (int)($data['generated_by'] ?? 0);
        $file_path = mysqli_real_escape_string($GLOBALS['conn'], $data['file_path'] ?? '');
        $status = mysqli_real_escape_string($GLOBALS['conn'], $data['status'] ?? 'PENDIENTE');
        
        $sql = "INSERT INTO reportes (title, description, report_type, parameters, generated_by, file_path, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ssssiss', $title, $description, $report_type, $parameters, $generated_by, $file_path, $status);
            if (mysqli_stmt_execute($stmt)) {
                $id = (int)mysqli_insert_id($GLOBALS['conn']);
                mysqli_stmt_close($stmt);
                return $id;
            } else {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo crear el reporte: ' . mysqli_error($GLOBALS['conn']));
            }
        } else {
            throw new Exception('Error preparando la consulta: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function update(int $id, array $data): void {
        $title = mysqli_real_escape_string($GLOBALS['conn'], $data['title'] ?? '');
        $description = mysqli_real_escape_string($GLOBALS['conn'], $data['description'] ?? '');
        $report_type = mysqli_real_escape_string($GLOBALS['conn'], $data['report_type'] ?? 'GENERAL');
        $parameters = mysqli_real_escape_string($GLOBALS['conn'], $data['parameters'] ?? '');
        $generated_by = (int)($data['generated_by'] ?? 0);
        $file_path = mysqli_real_escape_string($GLOBALS['conn'], $data['file_path'] ?? '');
        $status = mysqli_real_escape_string($GLOBALS['conn'], $data['status'] ?? 'PENDIENTE');
        
        $sql = "UPDATE reportes SET title = ?, description = ?, report_type = ?, parameters = ?, generated_by = ?, file_path = ?, status = ? WHERE id = ? AND is_deleted = 0";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ssssissi', $title, $description, $report_type, $parameters, $generated_by, $file_path, $status, $id);
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo actualizar el reporte: ' . mysqli_error($GLOBALS['conn']));
            }
            mysqli_stmt_close($stmt);
        } else {
            throw new Exception('Error preparando la consulta: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function findById(int $id): ?array {
        $sql = 'SELECT r.*, u.username as generated_by_name FROM reportes r LEFT JOIN users u ON r.generated_by = u.id WHERE r.id = ? AND r.is_deleted = 0 LIMIT 1';
        
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
        $sql = 'UPDATE reportes SET is_deleted = 1 WHERE id = ?';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function getReportTypes(): array {
        return ['ASISTENCIA', 'PAGOS', 'VACACIONES', 'EXTRAS', 'GENERAL'];
    }

    public static function getStatuses(): array {
        return ['PENDIENTE', 'GENERADO', 'ERROR'];
    }

    public static function getByGeneratedBy(int $generated_by): array {
        $rows = [];
        $sql = "SELECT r.*, u.username as generated_by_name 
                FROM reportes r 
                LEFT JOIN users u ON r.generated_by = u.id 
                WHERE r.generated_by = ? AND r.is_deleted = 0
                ORDER BY r.created_at DESC";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $generated_by);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($res && ($r = mysqli_fetch_assoc($res))) { $rows[] = $r; }
            mysqli_stmt_close($stmt);
        }
        return $rows;
    }

    public static function markAsGenerated(int $id, string $file_path): void {
        $sql = 'UPDATE reportes SET status = "GENERADO", file_path = ? WHERE id = ? AND is_deleted = 0';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'si', $file_path, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function markAsError(int $id): void {
        $sql = 'UPDATE reportes SET status = "ERROR" WHERE id = ? AND is_deleted = 0';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function generateAttendanceReport(array $parameters): array {
        // Lógica para generar reporte de asistencia
        $date_from = $parameters['date_from'] ?? date('Y-m-01');
        $date_to = $parameters['date_to'] ?? date('Y-m-t');
        $personal_id = $parameters['personal_id'] ?? null;
        
        $sql = "SELECT 
                    a.date,
                    p.employee_code,
                    p.first_name,
                    p.last_name,
                    a.entry_time,
                    a.exit_time,
                    a.late_minutes,
                    a.early_exit_minutes,
                    a.status
                FROM asistencias a 
                LEFT JOIN personal p ON a.personal_id = p.id 
                WHERE a.date BETWEEN ? AND ? AND a.is_deleted = 0";
        
        $params = [$date_from, $date_to];
        $types = 'ss';
        
        if ($personal_id) {
            $sql .= " AND a.personal_id = ?";
            $params[] = $personal_id;
            $types .= 'i';
        }
        
        $sql .= " ORDER BY a.date DESC, p.first_name, p.last_name";
        
        $rows = [];
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($res && ($r = mysqli_fetch_assoc($res))) { $rows[] = $r; }
            mysqli_stmt_close($stmt);
        }
        
        return $rows;
    }

    public static function generatePayrollReport(array $parameters): array {
        // Lógica para generar reporte de nómina
        $month = $parameters['month'] ?? date('n');
        $year = $parameters['year'] ?? date('Y');
        $personal_id = $parameters['personal_id'] ?? null;
        
        $sql = "SELECT 
                    p.employee_code,
                    p.first_name,
                    p.last_name,
                    p.department,
                    p.salary,
                    pg.base_salary,
                    pg.bonuses,
                    pg.deductions,
                    pg.net_salary,
                    pg.status
                FROM pagos pg 
                LEFT JOIN personal p ON pg.personal_id = p.id 
                WHERE pg.period_month = ? AND pg.period_year = ? AND pg.is_deleted = 0";
        
        $params = [$month, $year];
        $types = 'ii';
        
        if ($personal_id) {
            $sql .= " AND pg.personal_id = ?";
            $params[] = $personal_id;
            $types .= 'i';
        }
        
        $sql .= " ORDER BY p.first_name, p.last_name";
        
        $rows = [];
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($res && ($r = mysqli_fetch_assoc($res))) { $rows[] = $r; }
            mysqli_stmt_close($stmt);
        }
        
        return $rows;
    }

    public static function generateVacationReport(array $parameters): array {
        // Lógica para generar reporte de vacaciones
        $year = $parameters['year'] ?? date('Y');
        $status = $parameters['status'] ?? null;
        $personal_id = $parameters['personal_id'] ?? null;
        
        $sql = "SELECT 
                    p.employee_code,
                    p.first_name,
                    p.last_name,
                    p.department,
                    v.start_date,
                    v.end_date,
                    v.days_requested,
                    v.days_approved,
                    v.reason,
                    v.status,
                    u.username as approved_by_name
                FROM vacaciones v 
                LEFT JOIN personal p ON v.personal_id = p.id 
                LEFT JOIN users u ON v.approved_by = u.id 
                WHERE YEAR(v.start_date) = ? AND v.is_deleted = 0";
        
        $params = [$year];
        $types = 'i';
        
        if ($status) {
            $sql .= " AND v.status = ?";
            $params[] = $status;
            $types .= 's';
        }
        
        if ($personal_id) {
            $sql .= " AND v.personal_id = ?";
            $params[] = $personal_id;
            $types .= 'i';
        }
        
        $sql .= " ORDER BY v.start_date DESC";
        
        $rows = [];
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($res && ($r = mysqli_fetch_assoc($res))) { $rows[] = $r; }
            mysqli_stmt_close($stmt);
        }
        
        return $rows;
    }
}
