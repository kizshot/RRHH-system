<?php
// app/models/Jornada.php
require_once __DIR__ . '/../../includes/db_config.php';

class Jornada {
    public static function all(): array {
        $rows = [];
        $sql = "SELECT j.*, p.first_name, p.last_name, p.employee_code 
                FROM jornadas j 
                JOIN personal p ON j.personal_id = p.id 
                ORDER BY j.date DESC, j.personal_id";
        $res = mysqli_query($GLOBALS['conn'], $sql);
        if ($res) {
            while($r = mysqli_fetch_assoc($res)) $rows[] = $r;
            mysqli_free_result($res);
        }
        return $rows;
    }

    public static function findByPersonal(int $personal_id, string $start_date, string $end_date): array {
        $rows = [];
        $sql = 'SELECT * FROM jornadas WHERE personal_id = ? AND date BETWEEN ? AND ? ORDER BY date';
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'iss', $personal_id, $start_date, $end_date);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($res && ($r = mysqli_fetch_assoc($res))) { $rows[] = $r; }
            mysqli_stmt_close($stmt);
        }
        return $rows;
    }

    public static function create(array $data): int {
        $sql = 'INSERT INTO jornadas (personal_id, date, entry_time, exit_time, break_start, break_end, total_hours, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'isssssds', 
                $data['personal_id'], $data['date'], $data['entry_time'], 
                $data['exit_time'], $data['break_start'], $data['break_end'], 
                $data['total_hours'], $data['status']
            );
            
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo crear la jornada');
            }
            
            $id = (int)mysqli_insert_id($GLOBALS['conn']);
            mysqli_stmt_close($stmt);
            return $id;
        }
        throw new Exception('Error preparando la inserción');
    }

    public static function update(int $id, array $data): void {
        $sql = 'UPDATE jornadas SET entry_time = ?, exit_time = ?, break_start = ?, break_end = ?, total_hours = ?, status = ? WHERE id = ?';
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ssssdsi', 
                $data['entry_time'], $data['exit_time'], $data['break_start'], 
                $data['break_end'], $data['total_hours'], $data['status'], $id
            );
            
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function findById(int $id): ?array {
        $sql = 'SELECT j.*, p.first_name, p.last_name, p.employee_code FROM jornadas j JOIN personal p ON j.personal_id = p.id WHERE j.id = ? LIMIT 1';
        
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
        $sql = 'DELETE FROM jornadas WHERE id = ?';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function exists(int $personal_id, string $date): bool {
        $sql = 'SELECT id FROM jornadas WHERE personal_id = ? AND date = ? LIMIT 1';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'is', $personal_id, $date);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $exists = mysqli_stmt_num_rows($stmt) > 0;
            mysqli_stmt_close($stmt);
            return $exists;
        }
        return true;
    }

    public static function getStatuses(): array {
        return ['COMPLETA', 'INCOMPLETA', 'AUSENTE', 'TARDANZA'];
    }

    public static function calculateHours(string $entry_time, string $exit_time, string $break_start = null, string $break_end = null): float {
        if (!$entry_time || !$exit_time) return 0.0;

        $entry = strtotime($entry_time);
        $exit = strtotime($exit_time);
        if ($entry === false || $exit === false) return 0.0;

        $total_minutes = ($exit - $entry) / 60;
        if ($total_minutes < 0) {
            $total_minutes += 24 * 60; // Soporta cruce de medianoche
        }

        if ($break_start && $break_end) {
            $break_start_time = strtotime($break_start);
            $break_end_time = strtotime($break_end);
            if ($break_start_time !== false && $break_end_time !== false) {
                $break_minutes = ($break_end_time - $break_start_time) / 60;
                if ($break_minutes < 0) {
                    $break_minutes += 24 * 60;
                }
                $total_minutes -= max(0, $break_minutes);
            }
        }

        return round(max(0, $total_minutes) / 60, 2);
    }
}
