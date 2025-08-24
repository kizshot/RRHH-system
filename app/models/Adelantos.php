<?php
// app/models/Adelantos.php
require_once __DIR__ . '/../../includes/db_config.php';

class Adelantos {
    public static function all(): array {
        $rows = [];
        $sql = "SELECT a.*, p.first_name, p.last_name, p.employee_code, u.username as approved_by_name 
                FROM adelantos a 
                LEFT JOIN personal p ON a.personal_id = p.id 
                LEFT JOIN users u ON a.approved_by = u.id 
                WHERE a.is_deleted = 0
                ORDER BY a.id DESC";
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
        $wheres = ['a.is_deleted = 0'];
        $params = [];
        $types = '';
        
        if ($q !== '') {
            $wheres[] = "(p.employee_code LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR a.reason LIKE ?)";
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like, $like);
            $types .= 'ssss';
        }
        
        if ($status !== null && $status !== '') {
            $wheres[] = "a.status = ?";
            $params[] = $status;
            $types .= 's';
        }
        
        if ($personal_id !== null && $personal_id !== '') {
            $wheres[] = "a.personal_id = ?";
            $params[] = $personal_id;
            $types .= 'i';
        }
        
        $where = 'WHERE ' . implode(' AND ', $wheres);
        
        // Ordenar con whitelist
        $allowedSort = ['id', 'amount', 'request_date', 'approval_date', 'payment_date', 'status'];
        if (!in_array($sort, $allowedSort, true)) { $sort = 'id'; }
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';

        // Total
        $sqlCount = "SELECT COUNT(*) as c FROM adelantos a LEFT JOIN personal p ON a.personal_id = p.id " . $where;
        if ($stmt = mysqli_prepare($conn, $sqlCount)) {
            if ($types !== '') { mysqli_stmt_bind_param($stmt, $types, ...$params); }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : ['c'=>0];
            $total = (int)($row['c'] ?? 0);
            mysqli_stmt_close($stmt);
        } else { $total = 0; }

        $rows = [];
        $sql = "SELECT a.*, p.first_name, p.last_name, p.employee_code, u.username as approved_by_name 
                FROM adelantos a 
                LEFT JOIN personal p ON a.personal_id = p.id 
                LEFT JOIN users u ON a.approved_by = u.id 
                " . $where . " ORDER BY a.$sort $dir LIMIT ? OFFSET ?";
        
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
        $amount = (float)($data['amount'] ?? 0);
        $request_date = mysqli_real_escape_string($GLOBALS['conn'], $data['request_date'] ?? '');
        $reason = mysqli_real_escape_string($GLOBALS['conn'], $data['reason'] ?? '');
        $status = mysqli_real_escape_string($GLOBALS['conn'], $data['status'] ?? 'SOLICITADO');
        
        $sql = "INSERT INTO adelantos (personal_id, amount, request_date, reason, status) VALUES (?, ?, ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'idsss', $personal_id, $amount, $request_date, $reason, $status);
            if (mysqli_stmt_execute($stmt)) {
                $id = (int)mysqli_insert_id($GLOBALS['conn']);
                mysqli_stmt_close($stmt);
                return $id;
            } else {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo crear el adelanto: ' . mysqli_error($GLOBALS['conn']));
            }
        } else {
            throw new Exception('Error preparando la consulta: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function update(int $id, array $data): void {
        $personal_id = (int)($data['personal_id'] ?? 0);
        $amount = (float)($data['amount'] ?? 0);
        $request_date = mysqli_real_escape_string($GLOBALS['conn'], $data['request_date'] ?? '');
        $approval_date = !empty($data['approval_date']) ? "'" . mysqli_real_escape_string($GLOBALS['conn'], $data['approval_date']) . "'" : 'NULL';
        $payment_date = !empty($data['payment_date']) ? "'" . mysqli_real_escape_string($GLOBALS['conn'], $data['payment_date']) . "'" : 'NULL';
        $reason = mysqli_real_escape_string($GLOBALS['conn'], $data['reason'] ?? '');
        $status = mysqli_real_escape_string($GLOBALS['conn'], $data['status'] ?? 'SOLICITADO');
        $approved_by = !empty($data['approved_by']) ? $data['approved_by'] : 'NULL';
        
        $sql = "UPDATE adelantos SET personal_id = ?, amount = ?, request_date = ?, approval_date = $approval_date, payment_date = $payment_date, reason = ?, status = ?, approved_by = $approved_by WHERE id = ? AND is_deleted = 0";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'idsssi', $personal_id, $amount, $request_date, $reason, $status, $id);
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo actualizar el adelanto: ' . mysqli_error($GLOBALS['conn']));
            }
            mysqli_stmt_close($stmt);
        } else {
            throw new Exception('Error preparando la consulta: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function findById(int $id): ?array {
        $sql = 'SELECT a.*, p.first_name, p.last_name, p.employee_code, u.username as approved_by_name FROM adelantos a LEFT JOIN personal p ON a.personal_id = p.id LEFT JOIN users u ON a.approved_by = u.id WHERE a.id = ? AND a.is_deleted = 0 LIMIT 1';
        
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
        $sql = 'UPDATE adelantos SET is_deleted = 1 WHERE id = ?';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function getStatuses(): array {
        return ['SOLICITADO', 'APROBADO', 'RECHAZADO', 'PAGADO'];
    }

    public static function getByPersonalId(int $personal_id): array {
        $rows = [];
        $sql = "SELECT a.*, p.first_name, p.last_name, p.employee_code, u.username as approved_by_name 
                FROM adelantos a 
                LEFT JOIN personal p ON a.personal_id = p.id 
                LEFT JOIN users u ON a.approved_by = u.id 
                WHERE a.personal_id = ? AND a.is_deleted = 0
                ORDER BY a.request_date DESC";
        
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
        $sql = 'UPDATE adelantos SET status = "APROBADO", approval_date = CURDATE(), approved_by = ? WHERE id = ? AND is_deleted = 0';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ii', $approved_by, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function reject(int $id, int $approved_by): void {
        $sql = 'UPDATE adelantos SET status = "RECHAZADO", approval_date = CURDATE(), approved_by = ? WHERE id = ? AND is_deleted = 0';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ii', $approved_by, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function markAsPaid(int $id): void {
        $sql = 'UPDATE adelantos SET status = "PAGADO", payment_date = CURDATE() WHERE id = ? AND is_deleted = 0';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}
