<?php
// app/controllers/UserController.php
require_once __DIR__ . '/../../includes/db_config.php';
require_once __DIR__ . '/../models/User.php';

class UserController {
    public static function index(): void {
        // Listar usuarios con búsqueda, filtros y paginación
        $q = $_GET['q'] ?? '';
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        $sort = $_GET['sort'] ?? 'id';
        $dir = $_GET['dir'] ?? 'DESC';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = 10;
        $offset = ($page - 1) * $pageSize;
        $total = 0;
        $users = User::searchPaginated($q, $pageSize, $offset, $total, $role, $status, $sort, $dir);
        $pageCount = max(1, (int)ceil($total / $pageSize));
        include __DIR__ . '/../views/users/index.php';
    }

    public static function create(): void {
        // Manejar alta de usuario via POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /Recursos/index.php?route=users.index'); exit; }

        $includeExisting = isset($_POST['include_existing']) ? (bool)$_POST['include_existing'] : false;
        $existingId = isset($_POST['existing_user_id']) ? (int)$_POST['existing_user_id'] : 0;
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        $status = trim($_POST['status'] ?? 'ACTIVO');

        if ($includeExisting && $existingId > 0) {
            // Asociar existente (no cambiamos credenciales aquí, solo rol/estado/código)
            User::updateMeta($existingId, $role, $status, $code, $first_name, $last_name);
            header('Location: /Recursos/index.php?route=users.index&success=Usuario+actualizado');
            exit;
        }

        if ($username === '' || $email === '' || $password === '' || $password2 === '' || $role === '') {
            header('Location: /Recursos/index.php?route=users.index&error=Campos+obligatorios');
            exit;
        }
        if ($password !== $password2) {
            header('Location: /Recursos/index.php?route=users.index&error=Las+contrase%C3%B1as+no+coinciden');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: /Recursos/index.php?route=users.index&error=Email+inv%C3%A1lido');
            exit;
        }
        if (User::exists($username, $email)) {
            header('Location: /Recursos/index.php?route=users.index&error=Usuario+o+email+ya+existe');
            exit;
        }
        $id = User::create($username, $email, password_hash($password, PASSWORD_DEFAULT));
        // meta
        User::updateMeta($id, $role, $status, $code, $first_name, $last_name);

        // Imagen (opcional)
        if (!empty($_FILES['avatar']['name']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $safe = 'user_' . $id . '.' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $ext));
            $destDir = __DIR__ . '/../../assets/img/avatars';
            if (!is_dir($destDir)) { @mkdir($destDir, 0777, true); }
            $destPath = $destDir . '/' . $safe;
            move_uploaded_file($_FILES['avatar']['tmp_name'], $destPath);
            User::setAvatar($id, '/Recursos/assets/img/avatars/' . $safe);
        }

        header('Location: /Recursos/index.php?route=users.index&success=Usuario+creado');
        exit;
    }

    public static function delete(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) { User::delete($id); }
        header('Location: /Recursos/index.php?route=users.index&success=Eliminado');
        exit;
    }

    public static function view(): void {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) { header('Location: /Recursos/index.php?route=users.index'); exit; }
        $user = User::findById($id);
        include __DIR__ . '/../views/users/view.php';
    }

    public static function edit(): void {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) { header('Location: /Recursos/index.php?route=users.index'); exit; }
        $user = User::findById($id);
        $roles = ['SUPER USER','TRABAJADOR','VENDEDOR','RECEPCIONISTA','CHOFER','PROGRAMADOR'];
        $statuses = ['ACTIVO','INACTIVO'];
        include __DIR__ . '/../views/users/edit.php';
    }

    public static function credentials(): void {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) { header('Location: /Recursos/index.php?route=users.index'); exit; }
        $user = User::findById($id);
        include __DIR__ . '/../views/users/credentials.php';
    }

    public static function credentialsUpdate(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /Recursos/index.php?route=users.index'); exit; }
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) { header('Location: /Recursos/index.php?route=users.index'); exit; }
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pwd = $_POST['password'] ?? '';
        $pwd2 = $_POST['password2'] ?? '';
        if ($username === '' || $email === '') {
            header('Location: /Recursos/index.php?route=users.credentials&id=' . $id . '&error=Usuario+y+email+son+obligatorios'); exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: /Recursos/index.php?route=users.credentials&id=' . $id . '&error=Email+inv%C3%A1lido'); exit;
        }
        if (User::existsForOther($username, $email, $id)) {
            header('Location: /Recursos/index.php?route=users.credentials&id=' . $id . '&error=Usuario+o+email+ya+existe'); exit;
        }
        $hash = null;
        if ($pwd !== '' || $pwd2 !== '') {
            if ($pwd !== $pwd2) {
                header('Location: /Recursos/index.php?route=users.credentials&id=' . $id . '&error=Las+contrase%C3%B1as+no+coinciden'); exit;
            }
            $hash = password_hash($pwd, PASSWORD_DEFAULT);
        }
        User::updateCredentials($id, $username, $email, $hash);
        header('Location: /Recursos/index.php?route=users.view&id=' . $id . '&success=Credenciales+actualizadas');
        exit;
    }

    public static function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /Recursos/index.php?route=users.index'); exit; }
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) { header('Location: /Recursos/index.php?route=users.index'); exit; }
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $status = trim($_POST['status'] ?? 'ACTIVO');
        $code = trim($_POST['code'] ?? '');
        User::updateMeta($id, $role, $status, $code, $first_name, $last_name);
        if (!empty($_FILES['avatar']['name']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $safe = 'user_' . $id . '.' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $ext));
            $destDir = __DIR__ . '/../../assets/img/avatars';
            if (!is_dir($destDir)) { @mkdir($destDir, 0777, true); }
            $destPath = $destDir . '/' . $safe;
            move_uploaded_file($_FILES['avatar']['tmp_name'], $destPath);
            User::setAvatar($id, '/Recursos/assets/img/avatars/' . $safe);
        }
        header('Location: /Recursos/index.php?route=users.view&id=' . $id . '&success=Actualizado');
        exit;
    }
}

