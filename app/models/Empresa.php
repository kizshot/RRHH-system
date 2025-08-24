<?php
// app/models/Empresa.php
require_once __DIR__ . '/../../includes/db_config.php';

class Empresa {
    public static function all(): array {
        $rows = [];
        $sql = "SELECT * FROM empresa WHERE is_deleted = 0 ORDER BY id DESC";
        $res = mysqli_query($GLOBALS['conn'], $sql);
        if ($res) {
            while($r = mysqli_fetch_assoc($res)) $rows[] = $r;
            mysqli_free_result($res);
        }
        return $rows;
    }

    public static function searchPaginated(?string $q, int $limit, int $offset, int & $total, string $sort = 'id', string $dir = 'DESC'): array {
        $q = trim($q ?? '');
        $conn = $GLOBALS['conn'];
        $wheres = ['is_deleted = 0'];
        $params = [];
        $types = '';
        
        if ($q !== '') {
            $wheres[] = "(name LIKE ? OR ruc LIKE ? OR email LIKE ?)";
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like);
            $types .= 'sss';
        }
        
        $where = 'WHERE ' . implode(' AND ', $wheres);
        
        // Ordenar con whitelist
        $allowedSort = ['id', 'name', 'ruc', 'email', 'created_at'];
        if (!in_array($sort, $allowedSort, true)) { $sort = 'id'; }
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';

        // Total
        $sqlCount = "SELECT COUNT(*) as c FROM empresa " . $where;
        if ($stmt = mysqli_prepare($conn, $sqlCount)) {
            if ($types !== '') { mysqli_stmt_bind_param($stmt, $types, ...$params); }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : ['c'=>0];
            $total = (int)($row['c'] ?? 0);
            mysqli_stmt_close($stmt);
        } else { $total = 0; }

        $rows = [];
        $sql = "SELECT * FROM empresa " . $where . " ORDER BY $sort $dir LIMIT ? OFFSET ?";
        
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
        $name = mysqli_real_escape_string($GLOBALS['conn'], $data['name'] ?? '');
        $ruc = mysqli_real_escape_string($GLOBALS['conn'], $data['ruc'] ?? '');
        $address = mysqli_real_escape_string($GLOBALS['conn'], $data['address'] ?? '');
        $phone = mysqli_real_escape_string($GLOBALS['conn'], $data['phone'] ?? '');
        $email = mysqli_real_escape_string($GLOBALS['conn'], $data['email'] ?? '');
        $website = mysqli_real_escape_string($GLOBALS['conn'], $data['website'] ?? '');
        $logo = mysqli_real_escape_string($GLOBALS['conn'], $data['logo'] ?? '');
        $business_hours = mysqli_real_escape_string($GLOBALS['conn'], $data['business_hours'] ?? '');
        $policies = mysqli_real_escape_string($GLOBALS['conn'], $data['policies'] ?? '');
        
        $sql = "INSERT INTO empresa (name, ruc, address, phone, email, website, logo, business_hours, policies) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'sssssssss', $name, $ruc, $address, $phone, $email, $website, $logo, $business_hours, $policies);
            if (mysqli_stmt_execute($stmt)) {
                $id = (int)mysqli_insert_id($GLOBALS['conn']);
                mysqli_stmt_close($stmt);
                return $id;
            } else {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo crear la empresa: ' . mysqli_error($GLOBALS['conn']));
            }
        } else {
            throw new Exception('Error preparando la consulta: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function update(int $id, array $data): void {
        $name = mysqli_real_escape_string($GLOBALS['conn'], $data['name'] ?? '');
        $ruc = mysqli_real_escape_string($GLOBALS['conn'], $data['ruc'] ?? '');
        $address = mysqli_real_escape_string($GLOBALS['conn'], $data['address'] ?? '');
        $phone = mysqli_real_escape_string($GLOBALS['conn'], $data['phone'] ?? '');
        $email = mysqli_real_escape_string($GLOBALS['conn'], $data['email'] ?? '');
        $website = mysqli_real_escape_string($GLOBALS['conn'], $data['website'] ?? '');
        $logo = mysqli_real_escape_string($GLOBALS['conn'], $data['logo'] ?? '');
        $business_hours = mysqli_real_escape_string($GLOBALS['conn'], $data['business_hours'] ?? '');
        $policies = mysqli_real_escape_string($GLOBALS['conn'], $data['policies'] ?? '');
        
        $sql = "UPDATE empresa SET name = ?, ruc = ?, address = ?, phone = ?, email = ?, website = ?, logo = ?, business_hours = ?, policies = ? WHERE id = ? AND is_deleted = 0";
        
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'sssssssssi', $name, $ruc, $address, $phone, $email, $website, $logo, $business_hours, $policies, $id);
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo actualizar la empresa: ' . mysqli_error($GLOBALS['conn']));
            }
            mysqli_stmt_close($stmt);
        } else {
            throw new Exception('Error preparando la consulta: ' . mysqli_error($GLOBALS['conn']));
        }
    }

    public static function findById(int $id): ?array {
        $sql = 'SELECT * FROM empresa WHERE id = ? AND is_deleted = 0 LIMIT 1';
        
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
        $sql = 'UPDATE empresa SET is_deleted = 1 WHERE id = ?';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function getFirst(): ?array {
        $sql = 'SELECT * FROM empresa WHERE is_deleted = 0 ORDER BY id ASC LIMIT 1';
        
        $res = mysqli_query($GLOBALS['conn'], $sql);
        if ($res) {
            $row = mysqli_fetch_assoc($res);
            mysqli_free_result($res);
            return $row ?: null;
        }
        return null;
    }

    public static function exists(string $ruc, ?int $exclude_id = null): bool {
        $sql = 'SELECT id FROM empresa WHERE ruc = ? AND is_deleted = 0';
        $params = [$ruc];
        $types = 's';
        
        if ($exclude_id) {
            $sql .= ' AND id != ?';
            $params[] = $exclude_id;
            $types .= 'i';
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

    public static function updateLogo(int $id, string $logo_path): void {
        $sql = 'UPDATE empresa SET logo = ? WHERE id = ? AND is_deleted = 0';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'si', $logo_path, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function getCompanyInfo(): array {
        $company = self::getFirst();
        if (!$company) {
            return [
                'name' => 'Empresa',
                'ruc' => '',
                'address' => '',
                'phone' => '',
                'email' => '',
                'website' => '',
                'logo' => '',
                'business_hours' => '',
                'policies' => ''
            ];
        }
        return $company;
    }
}
