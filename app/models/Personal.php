<?php
// app/models/Personal.php
require_once __DIR__ . '/../../includes/db_config.php';

class Personal {
    public static function all(): array {
        $rows = [];
        $sql = "SELECT p.*, u.username, u.email, u.role as user_role 
                FROM personal p 
                LEFT JOIN users u ON p.user_id = u.id 
                WHERE p.is_deleted = 0
                ORDER BY p.id DESC";
        $res = mysqli_query($GLOBALS['conn'], $sql);
        if ($res) {
            while($r = mysqli_fetch_assoc($res)) $rows[] = $r;
            mysqli_free_result($res);
        }
        return $rows;
    }

    public static function searchPaginated(?string $q, int $limit, int $offset, int & $total, ?string $department = null, ?string $status = null, string $sort = 'id', string $dir = 'DESC'): array {
        $q = trim($q ?? '');
        $conn = $GLOBALS['conn'];
        $wheres = ['p.is_deleted = 0'];
        $params = [];
        $types = '';
        
        if ($q !== '') {
            $wheres[] = "(p.employee_code LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR p.dni LIKE ? OR p.position LIKE ?)";
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like, $like, $like);
            $types .= 'sssss';
        }
        
        if ($department !== null && $department !== '') {
            $wheres[] = "p.department = ?";
            $params[] = $department;
            $types .= 's';
        }
        
        if ($status !== null && $status !== '') {
            $wheres[] = "p.status = ?";
            $params[] = $status;
            $types .= 's';
        }
        
        $where = 'WHERE ' . implode(' AND ', $wheres);
        
        // Ordenar con whitelist
        $allowedSort = ['id', 'employee_code', 'first_name', 'last_name', 'department', 'position', 'hire_date', 'status'];
        if (!in_array($sort, $allowedSort, true)) { $sort = 'id'; }
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';

        // Total
        $sqlCount = "SELECT COUNT(*) as c FROM personal p " . $where;
        if ($stmt = mysqli_prepare($conn, $sqlCount)) {
            if ($types !== '') { mysqli_stmt_bind_param($stmt, $types, ...$params); }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : ['c'=>0];
            $total = (int)($row['c'] ?? 0);
            mysqli_stmt_close($stmt);
        } else { $total = 0; }

        $rows = [];
        $sql = "SELECT p.*, u.username, u.email, u.role as user_role 
                FROM personal p 
                LEFT JOIN users u ON p.user_id = u.id 
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
        // Preparar valores, manejando nulls correctamente
        $user_id = !empty($data['user_id']) ? $data['user_id'] : 'NULL';
        $employee_code = mysqli_real_escape_string($GLOBALS['conn'], $data['employee_code'] ?? '');
        $first_name = mysqli_real_escape_string($GLOBALS['conn'], $data['first_name'] ?? '');
        $last_name = mysqli_real_escape_string($GLOBALS['conn'], $data['last_name'] ?? '');
        $dni = mysqli_real_escape_string($GLOBALS['conn'], $data['dni'] ?? '');
        $birth_date = !empty($data['birth_date']) ? "'" . mysqli_real_escape_string($GLOBALS['conn'], $data['birth_date']) . "'" : 'NULL';
        $hire_date = mysqli_real_escape_string($GLOBALS['conn'], $data['hire_date'] ?? '');
        $position = mysqli_real_escape_string($GLOBALS['conn'], $data['position'] ?? '');
        $department = mysqli_real_escape_string($GLOBALS['conn'], $data['department'] ?? '');
        $salary = !empty($data['salary']) ? $data['salary'] : 'NULL';
        $status = mysqli_real_escape_string($GLOBALS['conn'], $data['status'] ?? 'ACTIVO');
        $phone = mysqli_real_escape_string($GLOBALS['conn'], $data['phone'] ?? '');
        $address = mysqli_real_escape_string($GLOBALS['conn'], $data['address'] ?? '');
        $emergency_contact = mysqli_real_escape_string($GLOBALS['conn'], $data['emergency_contact'] ?? '');
        $emergency_phone = mysqli_real_escape_string($GLOBALS['conn'], $data['emergency_phone'] ?? '');
        
        // Construir SQL directamente
        $sql = "INSERT INTO personal (user_id, employee_code, first_name, last_name, dni, birth_date, hire_date, position, department, salary, status, phone, address, emergency_contact, emergency_phone) VALUES ($user_id, '$employee_code', '$first_name', '$last_name', '$dni', $birth_date, '$hire_date', '$position', '$department', $salary, '$status', '$phone', '$address', '$emergency_contact', '$emergency_phone')";
        
        if (mysqli_query($GLOBALS['conn'], $sql)) {
            return (int)mysqli_insert_id($GLOBALS['conn']);
        } else {
            throw new Exception('No se pudo crear el personal: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function update(int $id, array $data): void {
        // Preparar valores, manejando nulls correctamente
        $user_id = !empty($data['user_id']) ? $data['user_id'] : 'NULL';
        $employee_code = mysqli_real_escape_string($GLOBALS['conn'], $data['employee_code'] ?? '');
        $first_name = mysqli_real_escape_string($GLOBALS['conn'], $data['first_name'] ?? '');
        $last_name = mysqli_real_escape_string($GLOBALS['conn'], $data['last_name'] ?? '');
        $dni = mysqli_real_escape_string($GLOBALS['conn'], $data['dni'] ?? '');
        $birth_date = !empty($data['birth_date']) ? "'" . mysqli_real_escape_string($GLOBALS['conn'], $data['birth_date']) . "'" : 'NULL';
        $hire_date = mysqli_real_escape_string($GLOBALS['conn'], $data['hire_date'] ?? '');
        $position = mysqli_real_escape_string($GLOBALS['conn'], $data['position'] ?? '');
        $department = mysqli_real_escape_string($GLOBALS['conn'], $data['department'] ?? '');
        $salary = !empty($data['salary']) ? $data['salary'] : 'NULL';
        $status = mysqli_real_escape_string($GLOBALS['conn'], $data['status'] ?? 'ACTIVO');
        $phone = mysqli_real_escape_string($GLOBALS['conn'], $data['phone'] ?? '');
        $address = mysqli_real_escape_string($GLOBALS['conn'], $data['address'] ?? '');
        $emergency_contact = mysqli_real_escape_string($GLOBALS['conn'], $data['emergency_contact'] ?? '');
        $emergency_phone = mysqli_real_escape_string($GLOBALS['conn'], $data['emergency_phone'] ?? '');
        
        // Construir SQL directamente
        $sql = "UPDATE personal SET user_id = $user_id, employee_code = '$employee_code', first_name = '$first_name', last_name = '$last_name', dni = '$dni', birth_date = $birth_date, hire_date = '$hire_date', position = '$position', department = '$department', salary = $salary, status = '$status', phone = '$phone', address = '$address', emergency_contact = '$emergency_contact', emergency_phone = '$emergency_phone' WHERE id = $id AND is_deleted = 0";
        
        if (!mysqli_query($GLOBALS['conn'], $sql)) {
            throw new Exception('No se pudo actualizar el personal: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function findById(int $id): ?array {
        $sql = 'SELECT p.*, u.username, u.email, u.role as user_role FROM personal p LEFT JOIN users u ON p.user_id = u.id WHERE p.id = ? AND p.is_deleted = 0 LIMIT 1';
        
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
        // Soft delete - marcar como eliminado en lugar de borrar físicamente
        $sql = 'UPDATE personal SET is_deleted = 1 WHERE id = ?';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function exists(string $employee_code, ?string $dni = null): bool {
        $sql = 'SELECT id FROM personal WHERE is_deleted = 0 AND employee_code = ?';
        $params = [$employee_code];
        $types = 's';
        
        if ($dni && $dni !== '') {
            $sql .= ' OR dni = ?';
            $params[] = $dni;
            $types .= 's';
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

    public static function getDepartments(): array {
        $departments = [];
        $sql = 'SELECT DISTINCT department FROM personal WHERE is_deleted = 0 AND department IS NOT NULL AND department != "" ORDER BY department';
        $res = mysqli_query($GLOBALS['conn'], $sql);
        if ($res) {
            while($r = mysqli_fetch_assoc($res)) {
                $departments[] = $r['department'];
            }
            mysqli_free_result($res);
        }
        return $departments;
    }

    public static function getPositions(): array {
        $positions = [];
        $sql = 'SELECT DISTINCT position FROM personal WHERE is_deleted = 0 AND position IS NOT NULL AND position != "" ORDER BY position';
        $res = mysqli_query($GLOBALS['conn'], $sql);
        if ($res) {
            while($r = mysqli_fetch_assoc($res)) {
                $positions[] = $r['position'];
            }
            mysqli_free_result($res);
        }
        return $positions;
    }

    public static function getStatuses(): array {
        return ['ACTIVO', 'INACTIVO', 'VACACIONES', 'LICENCIA'];
    }
}
