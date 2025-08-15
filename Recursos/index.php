<?php
// index.php - Front Controller MVC
require_once __DIR__ . '/includes/db_config.php';
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/UserController.php';

$route = $_GET['route'] ?? '';

switch ($route) {
  case 'login':
    AuthController::showLogin();
    break;
  case 'do_login':
    AuthController::login();
    break;
  case 'register':
    AuthController::showRegister();
    break;
  case 'do_register':
    AuthController::register();
    break;
  case 'logout':
    AuthController::logout();
    break;
  case 'dashboard':
    require __DIR__ . '/app/views/dashboard/index.php';
    break;
  case 'users.index':
    UserController::index();
    break;
  case 'users.create':
    UserController::create();
    break;
  case 'users.delete':
    UserController::delete();
    break;
  case 'users.view':
    UserController::view();
    break;
  case 'users.edit':
    UserController::edit();
    break;
  case 'users.update':
    UserController::update();
    break;
  case 'users.credentials':
    UserController::credentials();
    break;
  case 'users.credentialsUpdate':
    UserController::credentialsUpdate();
    break;
  default:
    // raíz: si logueado, dashboard; si no, login
    if (isset($_SESSION['user_id'])) {
      header('Location: /Recursos/index.php?route=dashboard');
      exit;
    }
    header('Location: /Recursos/index.php?route=login');
    exit;
}

