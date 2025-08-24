<?php
// app/controllers/AuthController.php
require_once __DIR__ . '/../../includes/db_config.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    public static function showLogin(): void {
        if (isset($_SESSION['user_id'])) { header('Location: /Recursos/index.php?route=dashboard'); exit; }
        include __DIR__ . '/../views/auth/login.php';
    }

    public static function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /Recursos/index.php?route=login'); exit; }
        $usernameOrEmail = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($usernameOrEmail === '' || $password === '') {
            header('Location: /Recursos/index.php?route=login&error=Credenciales+requeridas'); exit;
        }
        $user = User::findByUsernameOrEmail($usernameOrEmail);
        if (!$user || !password_verify($password, $user['password'])) {
            header('Location: /Recursos/index.php?route=login&error=Usuario+o+contrase%C3%B1a+inv%C3%A1lidos'); exit;
        }
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: /Recursos/index.php?route=dashboard');
        exit;
    }

    public static function showRegister(): void {
        if (isset($_SESSION['user_id'])) { header('Location: /Recursos/index.php?route=dashboard'); exit; }
        include __DIR__ . '/../views/auth/register.php';
    }

    public static function register(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /Recursos/index.php?route=register'); exit; }
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($username === '' || $email === '' || $password === '') {
            header('Location: /Recursos/index.php?route=register&error=Campos+obligatorios'); exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: /Recursos/index.php?route=register&error=Email+inv%C3%A1lido'); exit;
        }
        if (strlen($username) > 50 || strlen($email) > 100) {
            header('Location: /Recursos/index.php?route=register&error=Longitudes+inv%C3%A1lidas'); exit;
        }
        if (User::exists($username, $email)) {
            header('Location: /Recursos/index.php?route=register&error=Usuario+o+email+ya+existe'); exit;
        }
        $newId = User::create($username, $email, password_hash($password, PASSWORD_DEFAULT));
        $_SESSION['user_id'] = $newId;
        $_SESSION['username'] = $username;
        header('Location: /Recursos/index.php?route=dashboard');
        exit;
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        header('Location: /Recursos/index.php?route=login');
        exit;
    }
}

