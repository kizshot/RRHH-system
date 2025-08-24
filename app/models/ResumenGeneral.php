<?php
// app/models/ResumenGeneral.php
require_once __DIR__ . '/../../includes/db_config.php';

class ResumenGeneral {
    public static function all(): array {
        $rows = [];
        $sql = "SELECT rg.*, u.username as generated_by_name 
                FROM resumen_general rg 
                LEFT JOIN users u ON rg.generated_by = u.id 
                WHERE rg.is_deleted = 0
                ORDER BY rg.period_year DESC, rg.period_month DESC";
        $res = mysqli_query($GLOBALS['conn'], $sql);
        if ($res) {
            while($r = mysqli_fetch_assoc($res)) $rows[] = $r;
            mysqli_free_result($res);
        }
        return $rows;
    }

    public static function searchPaginated(?string $q, int $limit, int $offset, int & $total, ?string $period_month = null, ?string $period_year = null, string $sort = 'period_year', string $dir = 'DESC'): array {
        $q = trim($q ?? '');
        $conn = $GLOBALS['conn'];
        $wheres = ['rg.is_deleted = 0'];
        $params = [];
        $types = '';
        
        if ($q !== '') {
            $wheres[] = "(u.username LIKE ?)";
            $like = '%' . $q . '%';
            array_push($params, $like);
            $types .= 's';
        }
        
        if ($period_month !== null && $period_month !== '') {
            $wheres[] = "rg.period_month = ?";
            $params[] = $period_month;
            $types .= 'i';
        }
        
        if ($period_year !== null && $period_year !== '') {
            $wheres[] = "rg.period_year = ?";
            $params[] = $period_year;
            $types .= 'i';
        }
        
        $where = 'WHERE ' . implode(' AND ', $wheres);
        
        // Ordenar con whitelist
        $allowedSort = ['id', 'period_month', 'period_year', 'total_employees', 'total_hours_worked', 'total_salary_paid'];
        if (!in_array($sort, $allowedSort, true)) { $sort = 'period_year'; }
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';

        // Total
        $sqlCount = "SELECT COUNT(*) as c FROM resumen_general rg LEFT JOIN users u ON rg.generated_by = u.id " . $where;
        if ($stmt = mysqli_prepare($conn, $sqlCount)) {
            if ($types !== '') { mysqli_stmt_bind_param($stmt, $types, ...$params); }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : ['c'=>0];
            $total = (int)($row['c'] ?? 0);
            mysqli_stmt_close($stmt);
        } else { $total = 0; }

        $rows = [];
        $sql = "SELECT rg.*, u.username as generated_by_name 
                FROM resumen_general rg 
                LEFT JOIN users u ON rg.generated_by = u.id 
                " . $where . " ORDER BY rg.$sort $dir LIMIT ? OFFSET ?";
        
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
        $period_month = (int)($data['period_month'] ?? 0);
        $period_year = (int)($data['period_year'] ?? 0);
        $total_employees = (int)($data['total_employees'] ?? 0);
        $total_hours_worked = (float)($data['total_hours_worked'] ?? 0);
        $total_overtime_hours = (float)($data['total_overtime_hours'] ?? 0);
        $total_salary_paid = (float)($data['total_salary_paid'] ?? 0);
        $total_vacations_taken = (int)($data['total_vacations_taken'] ?? 0);
        $total_absences = (int)($data['total_absences'] ?? 0);
        $generated_by = (int)($data['generated_by'] ?? 0);
        
        $sql = "INSERT INTO resumen_general (period_month, period_year, total_employees, total_hours_worked, total_overtime_hours, total_salary_paid, total_vacations_taken, total_absences, generated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'iiidddiii', $period_month, $period_year, $total_employees, $total_hours_worked, $total_overtime_hours, $total_salary_paid, $total_vacations_taken, $total_absences, $generated_by);
            if (mysqli_stmt_execute($stmt)) {
                $id = (int)mysqli_insert_id($GLOBALS['conn']);
                mysqli_stmt_close($stmt);
                return $id;
            } else {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo crear el resumen: ' . mysqli_error($GLOBALS['conn']));
            }
        } else {
            throw new Exception('Error preparando la consulta: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function update(int $id, array $data): void {
        $period_month = (int)($data['period_month'] ?? 0);
        $period_year = (int)($data['period_year'] ?? 0);
        $total_employees = (int)($data['total_employees'] ?? 0);
        $total_hours_worked = (float)($data['total_hours_worked'] ?? 0);
        $total_overtime_hours = (float)($data['total_overtime_hours'] ?? 0);
        $total_salary_paid = (float)($data['total_salary_paid'] ?? 0);
        $total_vacations_taken = (int)($data['total_vacations_taken'] ?? 0);
        $total_absences = (int)($data['total_absences'] ?? 0);
        $generated_by = (int)($data['generated_by'] ?? 0);
        
        $sql = "UPDATE resumen_general SET period_month = ?, period_year = ?, total_employees = ?, total_hours_worked = ?, total_overtime_hours = ?, total_salary_paid = ?, total_vacations_taken = ?, total_absences = ?, generated_by = ? WHERE id = ? AND is_deleted = 0";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'iiidddiiii', $period_month, $period_year, $total_employees, $total_hours_worked, $total_overtime_hours, $total_salary_paid, $total_vacations_taken, $total_absences, $generated_by, $id);
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo actualizar el resumen: ' . mysqli_error($GLOBALS['conn']));
            }
            mysqli_stmt_close($stmt);
        } else {
            throw new Exception('Error preparando la consulta: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function findById(int $id): ?array {
        $sql = 'SELECT rg.*, u.username as generated_by_name FROM resumen_general rg LEFT JOIN users u ON rg.generated_by = u.id WHERE rg.id = ? AND rg.is_deleted = 0 LIMIT 1';
        
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
        $sql = 'UPDATE resumen_general SET is_deleted = 1 WHERE id = ?';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function getByGeneratedBy(int $generated_by): array {
        $rows = [];
        $sql = "SELECT rg.*, u.username as generated_by_name 
                FROM resumen_general rg 
                LEFT JOIN users u ON rg.generated_by = u.id 
                WHERE rg.generated_by = ? AND rg.is_deleted = 0
                ORDER BY rg.period_year DESC, rg.period_month DESC";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $generated_by);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($res && ($r = mysqli_fetch_assoc($res))) { $rows[] = $r; }
            mysqli_stmt_close($stmt);
        }
        return $rows;
    }

    public static function exists(int $period_month, int $period_year): bool {
        $sql = 'SELECT id FROM resumen_general WHERE period_month = ? AND period_year = ? AND is_deleted = 0 LIMIT 1';
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ii', $period_month, $period_year);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $exists = mysqli_num_rows($res) > 0;
            mysqli_stmt_close($stmt);
            return $exists;
        }
        return false;
    }

    public static function generateMonthlyResumen(int $period_month, int $period_year, int $generated_by): array {
        // Obtener total de empleados activos
        require_once __DIR__ . '/Personal.php';
        $total_employees = count(Personal::all());
        
        // Obtener total de horas trabajadas del mes
        require_once __DIR__ . '/Panel.php';
        $sql = "SELECT 
                    SUM(total_hours) as total_hours_worked,
                    SUM(overtime_hours) as total_overtime_hours
                FROM panel_control 
                WHERE MONTH(date) = ? AND YEAR(date) = ? AND is_deleted = 0";
        
        $total_hours_worked = 0;
        $total_overtime_hours = 0;
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ii', $period_month, $period_year);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : null;
            if ($row) {
                $total_hours_worked = (float)($row['total_hours_worked'] ?? 0);
                $total_overtime_hours = (float)($row['total_overtime_hours'] ?? 0);
            }
            mysqli_stmt_close($stmt);
        }
        
        // Obtener total de salarios pagados del mes
        require_once __DIR__ . '/Pago.php';
        $sql = "SELECT SUM(net_salary) as total_salary_paid 
                FROM pagos 
                WHERE period_month = ? AND period_year = ? AND status = 'PAGADO' AND is_deleted = 0";
        
        $total_salary_paid = 0;
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ii', $period_month, $period_year);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : null;
            if ($row) {
                $total_salary_paid = (float)($row['total_salary_paid'] ?? 0);
            }
            mysqli_stmt_close($stmt);
        }
        
        // Obtener total de vacaciones tomadas del mes
        require_once __DIR__ . '/Vacaciones.php';
        $sql = "SELECT SUM(days_approved) as total_vacations_taken 
                FROM vacaciones 
                WHERE MONTH(start_date) = ? AND YEAR(start_date) = ? AND status = 'COMPLETADO' AND is_deleted = 0";
        
        $total_vacations_taken = 0;
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ii', $period_month, $period_year);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : null;
            if ($row) {
                $total_vacations_taken = (int)($row['total_vacations_taken'] ?? 0);
            }
            mysqli_stmt_close($stmt);
        }
        
        // Obtener total de ausencias del mes
        require_once __DIR__ . '/Asistencias.php';
        $sql = "SELECT COUNT(*) as total_absences 
                FROM asistencias 
                WHERE MONTH(date) = ? AND YEAR(date) = ? AND status = 'AUSENTE' AND is_deleted = 0";
        
        $total_absences = 0;
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ii', $period_month, $period_year);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : null;
            if ($row) {
                $total_absences = (int)($row['total_absences'] ?? 0);
            }
            mysqli_stmt_close($stmt);
        }
        
        return [
            'period_month' => $period_month,
            'period_year' => $period_year,
            'total_employees' => $total_employees,
            'total_hours_worked' => $total_hours_worked,
            'total_overtime_hours' => $total_overtime_hours,
            'total_salary_paid' => $total_salary_paid,
            'total_vacations_taken' => $total_vacations_taken,
            'total_absences' => $total_absences,
            'generated_by' => $generated_by
        ];
    }

    public static function updateOrCreateMonthlyResumen(int $period_month, int $period_year, int $generated_by): void {
        $resumen_data = self::generateMonthlyResumen($period_month, $period_year, $generated_by);
        
        if (self::exists($period_month, $period_year)) {
            // Actualizar resumen existente
            $sql = "UPDATE resumen_general SET 
                        total_employees = ?, 
                        total_hours_worked = ?, 
                        total_overtime_hours = ?, 
                        total_salary_paid = ?, 
                        total_vacations_taken = ?, 
                        total_absences = ?, 
                        generated_by = ? 
                    WHERE period_month = ? AND period_year = ? AND is_deleted = 0";
            
            if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
                mysqli_stmt_bind_param($stmt, 'idddiiiii', 
                    $resumen_data['total_employees'], 
                    $resumen_data['total_hours_worked'], 
                    $resumen_data['total_overtime_hours'], 
                    $resumen_data['total_salary_paid'], 
                    $resumen_data['total_vacations_taken'], 
                    $resumen_data['total_absences'], 
                    $resumen_data['generated_by'], 
                    $period_month, 
                    $period_year
                );
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        } else {
            // Crear nuevo resumen
            self::create($resumen_data);
        }
    }

    public static function getYearlySummary(int $year): array {
        $rows = [];
        $sql = "SELECT 
                    period_month,
                    total_employees,
                    total_hours_worked,
                    total_overtime_hours,
                    total_salary_paid,
                    total_vacations_taken,
                    total_absences
                FROM resumen_general 
                WHERE period_year = ? AND is_deleted = 0
                ORDER BY period_month";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $year);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($res && ($r = mysqli_fetch_assoc($res))) { $rows[] = $r; }
            mysqli_stmt_close($stmt);
        }
        return $rows;
    }
}
