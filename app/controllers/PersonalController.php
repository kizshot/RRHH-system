<?php
// app/controllers/PersonalController.php
require_once __DIR__ . '/../../includes/db_config.php';
require_once __DIR__ . '/../models/Personal.php';
require_once __DIR__ . '/../models/User.php';

class PersonalController {
    public static function index(): void {
        // Listar personal con búsqueda, filtros y paginación
        $q = $_GET['q'] ?? '';
        $department = $_GET['department'] ?? '';
        $status = $_GET['status'] ?? '';
        $sort = $_GET['sort'] ?? 'id';
        $dir = $_GET['dir'] ?? 'DESC';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = 10;
        $offset = ($page - 1) * $pageSize;
        $total = 0;
        
        $personal = Personal::searchPaginated($q, $pageSize, $offset, $total, $department, $status, $sort, $dir);
        $pageCount = max(1, (int)ceil($total / $pageSize));
        
        // Obtener listas para filtros
        $departments = Personal::getDepartments();
        $statuses = Personal::getStatuses();
        
        include __DIR__ . '/../views/personal/index.php';
    }

    public static function create(): void {
        // Manejar alta de personal via POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
            header('Location: /Recursos/index.php?route=personal.index'); 
            exit; 
        }

        $user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
        $employee_code = trim($_POST['employee_code'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $dni = trim($_POST['dni'] ?? '');
        $birth_date = trim($_POST['birth_date'] ?? '');
        $hire_date = trim($_POST['hire_date'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $salary = !empty($_POST['salary']) ? (float)$_POST['salary'] : null;
        $status = trim($_POST['status'] ?? 'ACTIVO');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $emergency_contact = trim($_POST['emergency_contact'] ?? '');
        $emergency_phone = trim($_POST['emergency_phone'] ?? '');

        // Validaciones
        if ($employee_code === '' || $first_name === '' || $last_name === '' || $hire_date === '') {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes']);
                exit;
            }
            header('Location: /Recursos/index.php?route=personal.index&error=Campos+obligatorios+faltantes');
            exit;
        }

        if (Personal::exists($employee_code, $dni)) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Código de empleado o DNI ya existe']);
                exit;
            }
            header('Location: /Recursos/index.php?route=personal.index&error=Código+de+empleado+o+DNI+ya+existe');
            exit;
        }

        try {
            $data = [
                'user_id' => $user_id,
                'employee_code' => $employee_code,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'dni' => $dni,
                'birth_date' => $birth_date ?: null,
                'hire_date' => $hire_date,
                'position' => $position,
                'department' => $department,
                'salary' => $salary,
                'status' => $status,
                'phone' => $phone,
                'address' => $address,
                'emergency_contact' => $emergency_contact,
                'emergency_phone' => $emergency_phone
            ];

            $id = Personal::create($data);
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => true, 'message' => 'Personal creado exitosamente']);
                exit;
            }
            
            header('Location: /Recursos/index.php?route=personal.index&success=Personal+creado+exitosamente');
            exit;
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Error al crear personal: ' . $e->getMessage()]);
                exit;
            }
            header('Location: /Recursos/index.php?route=personal.index&error=Error+al+crear+personal');
            exit;
        }
    }

    public static function delete(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) { 
            Personal::delete($id); 
        }
        header('Location: /Recursos/index.php?route=personal.index&success=Personal+eliminado');
        exit;
    }

    public static function view(): void {
        // Para modales - solo mostrar datos
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) { exit('ID no válido'); }
        
        $personal = Personal::findById($id);
        if (!$personal) { exit('Personal no encontrado'); }
        
        include __DIR__ . '/../views/personal/view_modal.php';
    }

    public static function edit(): void {
        // Para modales - solo mostrar formulario
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) { exit('ID no válido'); }
        
        $personal = Personal::findById($id);
        if (!$personal) { exit('Personal no encontrado'); }
        
        include __DIR__ . '/../views/personal/edit_modal.php';
    }

    public static function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
            header('Location: /Recursos/index.php?route=personal.index'); 
            exit; 
        }
        
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) { 
            header('Location: /Recursos/index.php?route=personal.index'); 
            exit; 
        }

        $user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
        $employee_code = trim($_POST['employee_code'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $dni = trim($_POST['dni'] ?? '');
        $birth_date = trim($_POST['birth_date'] ?? '');
        $hire_date = trim($_POST['hire_date'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $salary = !empty($_POST['salary']) ? (float)$_POST['salary'] : null;
        $status = trim($_POST['status'] ?? 'ACTIVO');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $emergency_contact = trim($_POST['emergency_contact'] ?? '');
        $emergency_phone = trim($_POST['emergency_phone'] ?? '');

        // Validaciones
        if ($employee_code === '' || $first_name === '' || $last_name === '' || $hire_date === '') {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes']);
                exit;
            }
            header('Location: /Recursos/index.php?route=personal.index&error=Campos+obligatorios+faltantes');
            exit;
        }

        try {
            $data = [
                'user_id' => $user_id,
                'employee_code' => $employee_code,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'dni' => $dni,
                'birth_date' => $birth_date ?: null,
                'hire_date' => $hire_date,
                'position' => $position,
                'department' => $department,
                'salary' => $salary,
                'status' => $status,
                'phone' => $phone,
                'address' => $address,
                'emergency_contact' => $emergency_contact,
                'emergency_phone' => $emergency_phone
            ];

            Personal::update($id, $data);
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => true, 'message' => 'Personal actualizado exitosamente']);
                exit;
            }
            
            header('Location: /Recursos/index.php?route=personal.index&success=Personal+actualizado+exitosamente');
            exit;
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar personal: ' . $e->getMessage()]);
                exit;
            }
            header('Location: /Recursos/index.php?route=personal.index&error=Error+al+actualizar+personal');
            exit;
        }
    }
}
