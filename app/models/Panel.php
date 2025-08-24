<?php
// app/models/Panel.php
require_once __DIR__ . '/../../includes/db_config.php';

class Panel {
    public static function all(): array {
        $rows = [];
        $sql = "SELECT pc.*, p.first_name, p.last_name, p.employee_code 
                FROM panel_control pc 
                LEFT JOIN personal p ON pc.personal_id = p.id 
                WHERE pc.is_deleted = 0
                ORDER BY pc.date DESC, pc.id DESC";
        $res = mysqli_query($GLOBALS['conn'], $sql);
        if ($res) {
            while($r = mysqli_fetch_assoc($res)) $rows[] = $r;
            mysqli_free_result($res);
        }
        return $rows;
    }

    public static function searchPaginated(?string $q, int $limit, int $offset, int & $total, ?string $personal_id = null, ?string $date_from = null, ?string $date_to = null, string $sort = 'date', string $dir = 'DESC'): array {
        $q = trim($q ?? '');
        $conn = $GLOBALS['conn'];
        $wheres = ['pc.is_deleted = 0'];
        $params = [];
        $types = '';
        
        if ($q !== '') {
            $wheres[] = "(p.employee_code LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ?)";
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like);
            $types .= 'sss';
        }
        
        if ($personal_id !== null && $personal_id !== '') {
            $wheres[] = "pc.personal_id = ?";
            $params[] = $personal_id;
            $types .= 'i';
        }
        
        if ($date_from !== null && $date_from !== '') {
            $wheres[] = "pc.date >= ?";
            $params[] = $date_from;
            $types .= 's';
        }
        
        if ($date_to !== null && $date_to !== '') {
            $wheres[] = "pc.date <= ?";
            $params[] = $date_to;
            $types .= 's';
        }
        
        $where = 'WHERE ' . implode(' AND ', $wheres);
        
        // Ordenar con whitelist
        $allowedSort = ['id', 'date', 'total_hours', 'overtime_hours', 'late_count', 'absence_count'];
        if (!in_array($sort, $allowedSort, true)) { $sort = 'date'; }
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';

        // Total
        $sqlCount = "SELECT COUNT(*) as c FROM panel_control pc LEFT JOIN personal p ON pc.personal_id = p.id " . $where;
        if ($stmt = mysqli_prepare($conn, $sqlCount)) {
            if ($types !== '') { mysqli_stmt_bind_param($stmt, $types, ...$params); }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : ['c'=>0];
            $total = (int)($row['c'] ?? 0);
            mysqli_stmt_close($stmt);
        } else { $total = 0; }

        $rows = [];
        $sql = "SELECT pc.*, p.first_name, p.last_name, p.employee_code 
                FROM panel_control pc 
                LEFT JOIN personal p ON pc.personal_id = p.id 
                " . $where . " ORDER BY pc.$sort $dir LIMIT ? OFFSET ?";
        
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
        $total_hours = (float)($data['total_hours'] ?? 0);
        $overtime_hours = (float)($data['overtime_hours'] ?? 0);
        $late_count = (int)($data['late_count'] ?? 0);
        $absence_count = (int)($data['absence_count'] ?? 0);
        $vacation_days_used = (int)($data['vacation_days_used'] ?? 0);
        $vacation_days_remaining = (int)($data['vacation_days_remaining'] ?? 0);
        
        $sql = "INSERT INTO panel_control (personal_id, date, total_hours, overtime_hours, late_count, absence_count, vacation_days_used, vacation_days_remaining) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'isddiiii', $personal_id, $date, $total_hours, $overtime_hours, $late_count, $absence_count, $vacation_days_used, $vacation_days_remaining);
            if (mysqli_stmt_execute($stmt)) {
                $id = (int)mysqli_insert_id($GLOBALS['conn']);
                mysqli_stmt_close($stmt);
                return $id;
            } else {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo crear el panel: ' . mysqli_error($GLOBALS['conn']));
            }
        } else {
            throw new Exception('Error preparando la consulta: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function update(int $id, array $data): void {
        $personal_id = (int)($data['personal_id'] ?? 0);
        $date = mysqli_real_escape_string($GLOBALS['conn'], $data['date'] ?? '');
        $total_hours = (float)($data['total_hours'] ?? 0);
        $overtime_hours = (float)($data['overtime_hours'] ?? 0);
        $late_count = (int)($data['late_count'] ?? 0);
        $absence_count = (int)($data['absence_count'] ?? 0);
        $vacation_days_used = (int)($data['vacation_days_used'] ?? 0);
        $vacation_days_remaining = (int)($data['vacation_days_remaining'] ?? 0);
        
        $sql = "UPDATE panel_control SET personal_id = ?, date = ?, total_hours = ?, overtime_hours = ?, late_count = ?, absence_count = ?, vacation_days_used = ?, vacation_days_remaining = ? WHERE id = ? AND is_deleted = 0";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'isddiiiii', $personal_id, $date, $total_hours, $overtime_hours, $late_count, $absence_count, $vacation_days_used, $vacation_days_remaining, $id);
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo actualizar el panel: ' . mysqli_error($GLOBALS['conn']));
            }
            mysqli_stmt_close($stmt);
        } else {
            throw new Exception('Error preparando la consulta: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function findById(int $id): ?array {
        $sql = 'SELECT pc.*, p.first_name, p.last_name, p.employee_code FROM panel_control pc LEFT JOIN personal p ON pc.personal_id = p.id WHERE pc.id = ? AND pc.is_deleted = 0 LIMIT 1';
        
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
        $sql = 'UPDATE panel_control SET is_deleted = 1 WHERE id = ?';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function getByPersonalId(int $personal_id, ?string $date_from = null, ?string $date_to = null): array {
        $rows = [];
        $wheres = ['pc.personal_id = ?', 'pc.is_deleted = 0'];
        $params = [$personal_id];
        $types = 'i';
        
        if ($date_from !== null && $date_from !== '') {
            $wheres[] = "pc.date >= ?";
            $params[] = $date_from;
            $types .= 's';
        }
        
        if ($date_to !== null && $date_to !== '') {
            $wheres[] = "pc.date <= ?";
            $params[] = $date_to;
            $types .= 's';
        }
        
        $where = 'WHERE ' . implode(' AND ', $wheres);
        
        $sql = "SELECT pc.*, p.first_name, p.last_name, p.employee_code 
                FROM panel_control pc 
                LEFT JOIN personal p ON pc.personal_id = p.id 
                $where ORDER BY pc.date DESC";
        
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
        $sql = "SELECT pc.*, p.first_name, p.last_name, p.employee_code 
                FROM panel_control pc 
                LEFT JOIN personal p ON pc.personal_id = p.id 
                WHERE pc.date = ? AND pc.is_deleted = 0
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
        $sql = 'SELECT id FROM panel_control WHERE personal_id = ? AND date = ? AND is_deleted = 0 LIMIT 1';
        
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

    public static function generateDailyPanel(int $personal_id, string $date): array {
        // Obtener datos de asistencia del día
        require_once __DIR__ . '/Asistencias.php';
        $asistencias = Asistencias::getByPersonalId($personal_id, $date, $date);
        
        // Obtener datos de extras del día
        require_once __DIR__ . '/Extras.php';
        $extras = Extras::getByPersonalId($personal_id);
        $day_extras = array_filter($extras, function($extra) use ($date) {
            return $extra['date'] === $date;
        });
        
        // Calcular total de horas extras del día
        $overtime_hours = 0;
        foreach ($day_extras as $extra) {
            if ($extra['status'] === 'APROBADO') {
                $overtime_hours += $extra['hours'];
            }
        }
        
        // Calcular tardanzas y ausencias
        $late_count = 0;
        $absence_count = 0;
        $total_hours = 0;
        
        foreach ($asistencias as $asistencia) {
            if ($asistencia['status'] === 'TARDANZA') {
                $late_count++;
            } elseif ($asistencia['status'] === 'AUSENTE') {
                $absence_count++;
            } elseif ($asistencia['status'] === 'PRESENTE' && $asistencia['entry_time'] && $asistencia['exit_time']) {
                // Calcular horas trabajadas
                $entry = new DateTime($asistencia['entry_time']);
                $exit = new DateTime($asistencia['exit_time']);
                $interval = $entry->diff($exit);
                $total_hours += $interval->h + ($interval->i / 60);
            }
        }
        
        // Obtener datos de vacaciones
        require_once __DIR__ . '/Vacaciones.php';
        $vacaciones = Vacaciones::getByPersonalId($personal_id);
        $vacation_days_used = 0;
        $vacation_days_remaining = 30; // Asumir 30 días por año
        
        foreach ($vacaciones as $vacacion) {
            if ($vacacion['status'] === 'COMPLETADO') {
                $vacation_days_used += $vacacion['days_approved'] ?? 0;
            }
        }
        
        $vacation_days_remaining = max(0, $vacation_days_remaining - $vacation_days_used);
        
        return [
            'personal_id' => $personal_id,
            'date' => $date,
            'total_hours' => $total_hours,
            'overtime_hours' => $overtime_hours,
            'late_count' => $late_count,
            'absence_count' => $absence_count,
            'vacation_days_used' => $vacation_days_used,
            'vacation_days_remaining' => $vacation_days_remaining
        ];
    }

    public static function updateOrCreateDailyPanel(int $personal_id, string $date): void {
        $panel_data = self::generateDailyPanel($personal_id, $date);
        
        if (self::exists($personal_id, $date)) {
            // Actualizar panel existente
            $sql = "UPDATE panel_control SET 
                        total_hours = ?, 
                        overtime_hours = ?, 
                        late_count = ?, 
                        absence_count = ?, 
                        vacation_days_used = ?, 
                        vacation_days_remaining = ? 
                    WHERE personal_id = ? AND date = ? AND is_deleted = 0";
            
            if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
                mysqli_stmt_bind_param($stmt, 'ddiiiiis', 
                    $panel_data['total_hours'], 
                    $panel_data['overtime_hours'], 
                    $panel_data['late_count'], 
                    $panel_data['absence_count'], 
                    $panel_data['vacation_days_used'], 
                    $panel_data['vacation_days_remaining'], 
                    $personal_id, 
                    $date
                );
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        } else {
            // Crear nuevo panel
            self::create($panel_data);
        }
    }

    public static function getMonthlySummary(int $personal_id, string $month, string $year): array {
        $rows = [];
        $sql = "SELECT 
                    SUM(total_hours) as total_hours_month,
                    SUM(overtime_hours) as total_overtime_month,
                    SUM(late_count) as total_late_month,
                    SUM(absence_count) as total_absence_month,
                    MAX(vacation_days_remaining) as vacation_days_remaining
                FROM panel_control 
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
