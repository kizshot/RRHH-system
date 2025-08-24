<?php
// app/models/Pago.php
require_once __DIR__ . '/../../includes/db_config.php';

class Pago {
    public static function all(): array {
        $rows = [];
        $sql = "SELECT p.*, pe.first_name, pe.last_name, pe.employee_code 
                FROM pagos p 
                JOIN personal pe ON p.personal_id = pe.id 
                ORDER BY p.period_year DESC, p.period_month DESC, pe.last_name";
        $res = mysqli_query($GLOBALS['conn'], $sql);
        if ($res) {
            while($r = mysqli_fetch_assoc($res)) $rows[] = $r;
            mysqli_free_result($res);
        }
        return $rows;
    }

    public static function findByPersonal(int $personal_id, int $year = null): array {
        $rows = [];
        $sql = 'SELECT * FROM pagos WHERE personal_id = ?';
        $params = [$personal_id];
        $types = 'i';
        
        if ($year) {
            $sql .= ' AND period_year = ?';
            $params[] = $year;
            $types .= 'i';
        }
        
        $sql .= ' ORDER BY period_year DESC, period_month DESC';
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($res && ($r = mysqli_fetch_assoc($res))) { $rows[] = $r; }
            mysqli_stmt_close($stmt);
        }
        return $rows;
    }

    public static function create(array $data): int {
        $sql = 'INSERT INTO pagos (personal_id, period_month, period_year, base_salary, bonuses, deductions, net_salary, payment_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            $payment_date = $data['payment_date'] ?: null;
            
            mysqli_stmt_bind_param($stmt, 'iiidddsss', 
                $data['personal_id'], $data['period_month'], $data['period_year'], 
                $data['base_salary'], $data['bonuses'], $data['deductions'], 
                $data['net_salary'], $payment_date, $data['status']
            );
            
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo crear el pago');
            }
            
            $id = (int)mysqli_insert_id($GLOBALS['conn']);
            mysqli_stmt_close($stmt);
            return $id;
        }
        throw new Exception('Error preparando la inserción');
    }

    public static function update(int $id, array $data): void {
        $sql = 'UPDATE pagos SET base_salary = ?, bonuses = ?, deductions = ?, net_salary = ?, payment_date = ?, status = ? WHERE id = ?';
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            $payment_date = $data['payment_date'] ?: null;
            
            mysqli_stmt_bind_param($stmt, 'ddddssi', 
                $data['base_salary'], $data['bonuses'], $data['deductions'], 
                $data['net_salary'], $payment_date, $data['status'], $id
            );
            
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function findById(int $id): ?array {
        $sql = 'SELECT p.*, pe.first_name, pe.last_name, pe.employee_code FROM pagos p JOIN personal pe ON p.personal_id = pe.id WHERE p.id = ? LIMIT 1';
        
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
        $sql = 'DELETE FROM pagos WHERE id = ?';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function exists(int $personal_id, int $month, int $year): bool {
        $sql = 'SELECT id FROM pagos WHERE personal_id = ? AND period_month = ? AND period_year = ? LIMIT 1';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'iii', $personal_id, $month, $year);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $exists = mysqli_stmt_num_rows($stmt) > 0;
            mysqli_stmt_close($stmt);
            return $exists;
        }
        return true;
    }

    public static function getStatuses(): array {
        return ['PENDIENTE', 'PAGADO', 'ANULADO'];
    }

    public static function getMonths(): array {
        return [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
    }

    public static function calculateNetSalary(float $base_salary, float $bonuses = 0, float $deductions = 0): float {
        return $base_salary + $bonuses - $deductions;
    }

    public static function getTotalByPeriod(int $month, int $year): array {
        $sql = 'SELECT COUNT(*) as total_employees, SUM(net_salary) as total_paid, SUM(bonuses) as total_bonuses, SUM(deductions) as total_deductions FROM pagos WHERE period_month = ? AND period_year = ? AND status = "PAGADO"';
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ii', $month, $year);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : ['total_employees' => 0, 'total_paid' => 0, 'total_bonuses' => 0, 'total_deductions' => 0];
            mysqli_stmt_close($stmt);
            return $row;
        }
        return ['total_employees' => 0, 'total_paid' => 0, 'total_bonuses' => 0, 'total_deductions' => 0];
    }
}
