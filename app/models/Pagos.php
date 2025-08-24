<?php
// app/models/Pagos.php
require_once __DIR__ . '/../../includes/db_config.php';

class Pagos {
    public static function all(): array {
        $pagos = [];
        $sql = 'SELECT p.*, pe.first_name as employee_first_name, pe.last_name as employee_last_name, pe.employee_code FROM pagos p LEFT JOIN personal pe ON p.personal_id = pe.id WHERE p.is_deleted = 0 ORDER BY p.id DESC';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($res)) {
                $pagos[] = $row;
            }
            mysqli_stmt_close($stmt);
        }
        return $pagos;
    }

    public static function searchPaginated(string $q, int $pageSize, int $offset, int &$total, string $personal_id = '', string $status = '', string $sort = 'id', string $dir = 'DESC'): array {
        $pagos = [];
        $whereConditions = ['p.is_deleted = 0'];
        $params = [];
        $types = '';

        if (!empty($q)) {
            $whereConditions[] = '(pe.first_name LIKE ? OR pe.last_name LIKE ? OR pe.employee_code LIKE ?)';
            $searchTerm = '%' . $q . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'sss';
        }

        if (!empty($personal_id)) {
            $whereConditions[] = 'p.personal_id = ?';
            $params[] = $personal_id;
            $types .= 'i';
        }

        if (!empty($status)) {
            $whereConditions[] = 'p.status = ?';
            $params[] = $status;
            $types .= 's';
        }

        $whereClause = implode(' AND ', $whereConditions);
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total FROM pagos p LEFT JOIN personal pe ON p.personal_id = pe.id WHERE $whereClause";
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $countSql)) {
            if (!empty($params)) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $total = mysqli_fetch_assoc($res)['total'];
            mysqli_stmt_close($stmt);
        }

        // Query principal
        $sql = "SELECT p.*, pe.first_name as employee_first_name, pe.last_name as employee_last_name, pe.employee_code FROM pagos p LEFT JOIN personal pe ON p.personal_id = pe.id WHERE $whereClause ORDER BY p.$sort $dir LIMIT ? OFFSET ?";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            $params[] = $pageSize;
            $params[] = $offset;
            $types .= 'ii';
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($res)) {
                $pagos[] = $row;
            }
            mysqli_stmt_close($stmt);
        }
        
        return $pagos;
    }

    public static function create(array $data): int {
        $sql = 'INSERT INTO pagos (personal_id, period_month, period_year, base_salary, bonuses, deductions, net_salary, payment_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'iiiddddss', 
                $data['personal_id'],
                $data['period_month'],
                $data['period_year'],
                $data['base_salary'],
                $data['bonuses'],
                $data['deductions'],
                $data['net_salary'],
                $data['payment_date'],
                $data['status']
            );
            mysqli_stmt_execute($stmt);
            $id = mysqli_insert_id($GLOBALS['conn']);
            mysqli_stmt_close($stmt);
            return $id;
        }
        return 0;
    }

    public static function update(int $id, array $data): void {
        $sql = 'UPDATE pagos SET personal_id = ?, period_month = ?, period_year = ?, base_salary = ?, bonuses = ?, deductions = ?, net_salary = ?, payment_date = ?, status = ? WHERE id = ? AND is_deleted = 0';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'iiiddddssi', 
                $data['personal_id'],
                $data['period_month'],
                $data['period_year'],
                $data['base_salary'],
                $data['bonuses'],
                $data['deductions'],
                $data['net_salary'],
                $data['payment_date'],
                $data['status'],
                $id
            );
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function findById(int $id): ?array {
        $sql = 'SELECT p.*, pe.first_name as employee_first_name, pe.last_name as employee_last_name, pe.employee_code FROM pagos p LEFT JOIN personal pe ON p.personal_id = pe.id WHERE p.id = ? AND p.is_deleted = 0 LIMIT 1';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $pago = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);
            return $pago ?: null;
        }
        return null;
    }

    public static function delete(int $id): void {
        // Soft delete - marcar como eliminado en lugar de borrar físicamente
        $sql = 'UPDATE pagos SET is_deleted = 1 WHERE id = ?';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function getStatuses(): array {
        return ['PENDIENTE', 'PAGADO', 'ANULADO'];
    }

    public static function getByPersonalId(int $personal_id): array {
        $pagos = [];
        $sql = 'SELECT p.*, pe.first_name as employee_first_name, pe.last_name as employee_last_name, pe.employee_code FROM pagos p LEFT JOIN personal pe ON p.personal_id = pe.id WHERE p.personal_id = ? AND p.is_deleted = 0 ORDER BY p.period_year DESC, p.period_month DESC';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $personal_id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($res)) {
                $pagos[] = $row;
            }
            mysqli_stmt_close($stmt);
        }
        return $pagos;
    }

    public static function getByPeriod(int $month, int $year): array {
        $pagos = [];
        $sql = 'SELECT p.*, pe.first_name as employee_first_name, pe.last_name as employee_last_name, pe.employee_code FROM pagos p LEFT JOIN personal pe ON p.personal_id = pe.id WHERE p.period_month = ? AND p.period_year = ? AND p.is_deleted = 0 ORDER BY pe.first_name, pe.last_name';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ii', $month, $year);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($res)) {
                $pagos[] = $row;
            }
            mysqli_stmt_close($stmt);
        }
        return $pagos;
    }

    public static function markAsPaid(int $id): void {
        $sql = 'UPDATE pagos SET status = "PAGADO", payment_date = CURDATE() WHERE id = ? AND is_deleted = 0';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function cancel(int $id): void {
        $sql = 'UPDATE pagos SET status = "ANULADO" WHERE id = ? AND is_deleted = 0';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function calculateNetSalary(float $base_salary, float $bonuses, float $deductions): float {
        return $base_salary + $bonuses - $deductions;
    }

    public static function getMonthlySummary(int $month, int $year): array {
        $sql = 'SELECT 
                    COUNT(*) as total_pagos,
                    SUM(CASE WHEN status = "PAGADO" THEN 1 ELSE 0 END) as pagos_pagados,
                    SUM(CASE WHEN status = "PENDIENTE" THEN 1 ELSE 0 END) as pagos_pendientes,
                    SUM(CASE WHEN status = "ANULADO" THEN 1 ELSE 0 END) as pagos_anulados,
                    SUM(net_salary) as total_pagado,
                    AVG(net_salary) as promedio_salario
                FROM pagos 
                WHERE period_month = ? AND period_year = ? AND is_deleted = 0';
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ii', $month, $year);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $summary = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);
            return $summary ?: [];
        }
        return [];
    }

    public static function exists(int $personal_id, int $month, int $year): bool {
        $sql = 'SELECT COUNT(*) as count FROM pagos WHERE personal_id = ? AND period_month = ? AND period_year = ? AND is_deleted = 0';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'iii', $personal_id, $month, $year);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $count = mysqli_fetch_assoc($res)['count'];
            mysqli_stmt_close($stmt);
            return $count > 0;
        }
        return false;
    }
}
