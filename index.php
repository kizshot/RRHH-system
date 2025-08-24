<?php
// index.php - Front Controller MVC
require_once __DIR__ . '/includes/db_config.php';
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/UserController.php';
require_once __DIR__ . '/app/controllers/PersonalController.php';
require_once __DIR__ . '/app/controllers/JornadaController.php';
require_once __DIR__ . '/app/controllers/PagoController.php';
require_once __DIR__ . '/app/controllers/AdelantosController.php';
require_once __DIR__ . '/app/controllers/ExtrasController.php';
require_once __DIR__ . '/app/controllers/VacacionesController.php';
require_once __DIR__ . '/app/controllers/AsistenciasController.php';
require_once __DIR__ . '/app/controllers/DashboardController.php';
require_once __DIR__ . '/app/controllers/RolesController.php';

$route = $_GET['route'] ?? '';

// Asegurar que $conn esté disponible para los controladores que lo reciben por inyección
$conn = $GLOBALS['conn'];

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
    $controller = new DashboardController($conn);
    $controller->index();
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
  case 'personal.index':
    PersonalController::index();
    break;
  case 'personal.create':
    PersonalController::create();
    break;
  case 'personal.delete':
    PersonalController::delete();
    break;
  case 'personal.view':
    PersonalController::view();
    break;
  case 'personal.edit':
    PersonalController::edit();
    break;
  case 'personal.update':
    PersonalController::update();
    break;
  // Rutas para Jornadas
  case 'jornadas.index':
    $controller = new JornadaController($conn);
    $controller->index();
    break;
  case 'jornadas.create':
    $controller = new JornadaController($conn);
    $controller->create();
    break;
  case 'jornadas.delete':
    $controller = new JornadaController($conn);
    $controller->delete();
    break;
  case 'jornadas.view':
    $controller = new JornadaController($conn);
    $id = $_GET['id'] ?? 0;
    $controller->show($id);
    break;
  case 'jornadas.edit':
    $controller = new JornadaController($conn);
    $id = $_GET['id'] ?? 0;
    $controller->edit($id);
    break;
  case 'jornadas.update':
    $controller = new JornadaController($conn);
    $id = $_GET['id'] ?? 0;
    $controller->update($id);
    break;
  // Rutas para Pagos
  case 'pagos.index':
    $controller = new PagoController($conn);
    $controller->index();
    break;
  case 'pagos.create':
    $controller = new PagoController($conn);
    $controller->create();
    break;
  case 'pagos.delete':
    $controller = new PagoController($conn);
    $controller->delete();
    break;
  case 'pagos.view':
    $controller = new PagoController($conn);
    $id = $_GET['id'] ?? 0;
    $controller->show($id);
    break;
  case 'pagos.edit':
    $controller = new PagoController($conn);
    $id = $_GET['id'] ?? 0;
    $controller->edit($id);
    break;
  case 'pagos.update':
    $controller = new PagoController($conn);
    $id = $_GET['id'] ?? 0;
    $controller->update($id);
    break;
  case 'pagos.generatePayment':
    $controller = new PagoController($conn);
    $controller->generatePayment();
    break;
  // Rutas para Adelantos
  case 'adelantos.index':
    AdelantosController::index();
    break;
  case 'adelantos.create':
    AdelantosController::create();
    break;
  case 'adelantos.delete':
    AdelantosController::delete();
    break;
  case 'adelantos.view':
    AdelantosController::view();
    break;
  case 'adelantos.edit':
    AdelantosController::edit();
    break;
  case 'adelantos.update':
    AdelantosController::update();
    break;
  case 'adelantos.approve':
    AdelantosController::approve();
    break;
  case 'adelantos.reject':
    AdelantosController::reject();
    break;
  case 'adelantos.markAsPaid':
    AdelantosController::markAsPaid();
    break;
  // Rutas para Extras
  case 'extras.index':
    ExtrasController::index();
    break;
  case 'extras.create':
    ExtrasController::create();
    break;
  case 'extras.delete':
    ExtrasController::delete();
    break;
  case 'extras.view':
    ExtrasController::view();
    break;
  case 'extras.edit':
    ExtrasController::edit();
    break;
  case 'extras.update':
    ExtrasController::update();
    break;
  case 'extras.approve':
    ExtrasController::approve();
    break;
  case 'extras.reject':
    ExtrasController::reject();
    break;
  case 'extras.markAsPaid':
    ExtrasController::markAsPaid();
    break;
  // Rutas para Vacaciones
  case 'vacaciones.index':
    VacacionesController::index();
    break;
  case 'vacaciones.create':
    VacacionesController::create();
    break;
  case 'vacaciones.delete':
    VacacionesController::delete();
    break;
  case 'vacaciones.view':
    VacacionesController::view();
    break;
  case 'vacaciones.edit':
    VacacionesController::edit();
    break;
  case 'vacaciones.update':
    VacacionesController::update();
    break;
  case 'vacaciones.approve':
    VacacionesController::approve();
    break;
  case 'vacaciones.reject':
    VacacionesController::reject();
    break;
  case 'vacaciones.startVacation':
    VacacionesController::startVacation();
    break;
  case 'vacaciones.completeVacation':
    VacacionesController::completeVacation();
    break;
  // Rutas para Asistencias
  case 'asistencias.index':
    AsistenciasController::index();
    break;
  case 'asistencias.create':
    AsistenciasController::create();
    break;
  case 'asistencias.delete':
    AsistenciasController::delete();
    break;
  case 'asistencias.view':
    AsistenciasController::view();
    break;
  case 'asistencias.edit':
    AsistenciasController::edit();
    break;
  case 'asistencias.update':
    AsistenciasController::update();
    break;
  case 'asistencias.markEntry':
    AsistenciasController::markEntry();
    break;
  case 'asistencias.markExit':
    AsistenciasController::markExit();
    break;
  case 'asistencias.getAttendanceSummary':
    AsistenciasController::getAttendanceSummary();
    break;
  // Rutas para Roles
  case 'roles.index':
    include __DIR__ . '/app/views/roles/index.php';
    break;
  case 'roles.view':
    include __DIR__ . '/app/views/roles/view.php';
    break;
  case 'roles.edit':
    include __DIR__ . '/app/views/roles/edit.php';
    break;
  case 'roles.permissions':
    include __DIR__ . '/app/views/roles/permissions.php';
    break;
  case 'roles.create':
    $controller = new RolesController();
    $controller->create();
    break;
  case 'roles.update':
    $controller = new RolesController();
    $controller->update();
    break;
  case 'roles.updatePermissions':
    $controller = new RolesController();
    $controller->updatePermissions();
    break;
  case 'roles.delete':
    $controller = new RolesController();
    $controller->delete();
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

