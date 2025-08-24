<?php
// app/models/User.php
require_once __DIR__ . '/../../includes/db_config.php';

class User {
    // Asegurar columnas meta si no existen
    private static function ensureMeta(): void {
        $conn = $GLOBALS['conn'];
        $columns = [
            'first_name' => "ALTER TABLE users ADD COLUMN first_name VARCHAR(100) NULL",
            'last_name'  => "ALTER TABLE users ADD COLUMN last_name VARCHAR(100) NULL",
            'role'       => "ALTER TABLE users ADD COLUMN role VARCHAR(32) NULL",
            'status'     => "ALTER TABLE users ADD COLUMN status VARCHAR(16) NULL DEFAULT 'ACTIVO'",
            'code'       => "ALTER TABLE users ADD COLUMN code VARCHAR(32) NULL",
            'avatar'     => "ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL",
        ];
        foreach ($columns as $name => $ddl) {
            $existsSql = "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = '" . mysqli_real_escape_string($conn, $name) . "' LIMIT 1";
            $res = @mysqli_query($conn, $existsSql);
            $has = ($res && mysqli_fetch_row($res));
            if ($res) { mysqli_free_result($res); }
            if (!$has) { @mysqli_query($conn, $ddl); }
        }
    }

    public static function all(): array {
        self::ensureMeta();
        $rows = [];
        $res = mysqli_query($GLOBALS['conn'], "SELECT id, username, email, role, status, code, first_name, last_name, avatar, created_at FROM users ORDER BY id DESC");
        if ($res) {
            while($r = mysqli_fetch_assoc($res)) $rows[] = $r;
            mysqli_free_result($res);
        }
        return $rows;
    }

    public static function searchPaginated(?string $q, int $limit, int $offset, int & $total, ?string $roleFilter = null, ?string $statusFilter = null, string $sort = 'id', string $dir = 'DESC'): array {
        self::ensureMeta();
        $q = trim($q ?? '');
        $conn = $GLOBALS['conn'];
        $wheres = [];
        $params = [];
        $types = '';
        if ($q !== '') {
            $wheres[] = "(username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR code LIKE ?)";
            $like = '%' . $q . '%';
            array_push($params, $like,$like,$like,$like,$like);
            $types .= 'sssss';
        }
        if ($roleFilter !== null && $roleFilter !== '') {
            $wheres[] = "role = ?";
            $params[] = $roleFilter; $types .= 's';
        }
        if ($statusFilter !== null && $statusFilter !== '') {
            $wheres[] = "status = ?";
            $params[] = $statusFilter; $types .= 's';
        }
        $where = $wheres ? ('WHERE ' . implode(' AND ', $wheres)) : '';
        // ordenar con whitelist
        $allowedSort = ['id','username','email','role','status','code','created_at'];
        if (!in_array($sort, $allowedSort, true)) { $sort = 'id'; }
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';

        // total
        $sqlCount = "SELECT COUNT(*) as c FROM users " . $where;
        if ($stmt = mysqli_prepare($conn, $sqlCount)) {
            if ($types !== '') { mysqli_stmt_bind_param($stmt, $types, ...$params); }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : ['c'=>0];
            $total = (int)($row['c'] ?? 0);
            mysqli_stmt_close($stmt);
        } else { $total = 0; }

        $rows = [];
        $sql = "SELECT id, username, email, role, status, code, first_name, last_name, avatar, created_at FROM users " . $where . " ORDER BY $sort $dir LIMIT ? OFFSET ?";
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

    public static function existsForOther(string $username, string $email, int $excludeId): bool {
        $sql = 'SELECT id FROM users WHERE (username = ? OR email = ?) AND id <> ? LIMIT 1';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ssi', $username, $email, $excludeId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $exists = mysqli_stmt_num_rows($stmt) > 0;
            mysqli_stmt_close($stmt);
            return $exists;
        }
        return true;
    }

    public static function updateCredentials(int $id, string $username, string $email, ?string $passwordHash): void {
        if ($passwordHash) {
            $sql = 'UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?';
            if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
                mysqli_stmt_bind_param($stmt, 'sssi', $username, $email, $passwordHash, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        } else {
            $sql = 'UPDATE users SET username = ?, email = ? WHERE id = ?';
            if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
                mysqli_stmt_bind_param($stmt, 'ssi', $username, $email, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }

    public static function findByUsernameOrEmail(string $value): ?array {
        $sql = 'SELECT id, username, email, password FROM users WHERE username = ? OR email = ? LIMIT 1';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ss', $value, $value);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $user = $res ? mysqli_fetch_assoc($res) : null;
            mysqli_stmt_close($stmt);
            return $user ?: null;
        }
        return null;
    }

    public static function exists(string $username, string $email): bool {
        $sql = 'SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'ss', $username, $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $exists = mysqli_stmt_num_rows($stmt) > 0;
            mysqli_stmt_close($stmt);
            return $exists;
        }
        return true;
    }

    public static function create(string $username, string $email, string $hash): int {
        self::ensureMeta();
        $ins = 'INSERT INTO users (username, email, password) VALUES (?,?,?)';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $ins)) {
            mysqli_stmt_bind_param($stmt, 'sss', $username, $email, $hash);
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                throw new Exception('No se pudo crear el usuario');
            }
            $id = (int)mysqli_insert_id($GLOBALS['conn']);
            mysqli_stmt_close($stmt);
            return $id;
        }
        throw new Exception('Error preparando la inserción');
    }

    public static function updateMeta(int $id, ?string $role, ?string $status, ?string $code, ?string $first, ?string $last): void {
        self::ensureMeta();
        $sql = 'UPDATE users SET role = ?, status = ?, code = ?, first_name = ?, last_name = ? WHERE id = ?';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'sssssi', $role, $status, $code, $first, $last, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function setAvatar(int $id, string $path): void {
        self::ensureMeta();
        $sql = 'UPDATE users SET avatar = ? WHERE id = ?';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'si', $path, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function delete(int $id): void {
        $sql = 'DELETE FROM users WHERE id = ?';
        if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    public static function findById(int $id): ?array {
        $sql = 'SELECT id, username, email, role, status, code, first_name, last_name, avatar, created_at FROM users WHERE id = ? LIMIT 1';
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
}

