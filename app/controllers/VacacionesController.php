<?php
// app/controllers/VacacionesController.php
require_once __DIR__ . '/../../includes/db_config.php';
require_once __DIR__ . '/../models/Vacaciones.php';
require_once __DIR__ . '/../models/Personal.php';

class VacacionesController {
    public static function index(): void {
        // Listar vacaciones con búsqueda, filtros y paginación
        $q = $_GET['q'] ?? '';
        $personal_id = $_GET['personal_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $sort = $_GET['sort'] ?? 'id';
        $dir = $_GET['dir'] ?? 'DESC';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = 10;
        $offset = ($page - 1) * $pageSize;
        $total = 0;
        
        $vacaciones = Vacaciones::searchPaginated($q, $pageSize, $offset, $total, $personal_id, $status, $sort, $dir);
        $pageCount = max(1, (int)ceil($total / $pageSize));
        
        // Obtener listas para filtros
        $personal_list = Personal::all();
        $statuses = Vacaciones::getStatuses();
        
        include __DIR__ . '/../views/vacaciones/index.php';
    }

    public static function create(): void {
        // Manejar alta de vacaciones via POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
            header('Location: /Recursos/index.php?route=vacaciones.index'); 
            exit; 
        }

        $personal_id = !empty($_POST['personal_id']) ? (int)$_POST['personal_id'] : null;
        $start_date = trim($_POST['start_date'] ?? '');
        $end_date = trim($_POST['end_date'] ?? '');
        $days_requested = !empty($_POST['days_requested']) ? (int)$_POST['days_requested'] : null;
        $days_approved = !empty($_POST['days_approved']) ? (int)$_POST['days_approved'] : null;
        $days_taken = !empty($_POST['days_taken']) ? (int)$_POST['days_taken'] : null;
        $status = trim($_POST['status'] ?? 'PENDIENTE');
        $approved_by = !empty($_POST['approved_by']) ? (int)$_POST['approved_by'] : null;
        $approved_date = trim($_POST['approved_date'] ?? '');
        $start_vacation_date = trim($_POST['start_vacation_date'] ?? '');
        $end_vacation_date = trim($_POST['end_vacation_date'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // Validaciones
        if (!$personal_id || $start_date === '' || $end_date === '') {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes']);
                exit;
            }
            header('Location: /Recursos/index.php?route=vacaciones.index&error=Campos+obligatorios+faltantes');
            exit;
        }

        if ($start_date >= $end_date) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'La fecha de inicio debe ser anterior a la fecha de fin']);
                exit;
            }
            header('Location: /Recursos/index.php?route=vacaciones.index&error=La+fecha+de+inicio+debe+ser+anterior+a+la+fecha+de+fin');
            exit;
        }

        try {
            $data = [
                'personal_id' => $personal_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'days_requested' => $days_requested,
                'days_approved' => $days_approved,
                'days_taken' => $days_taken,
                'status' => $status,
                'approved_by' => $approved_by,
                'approved_date' => $approved_date ?: null,
                'start_vacation_date' => $start_vacation_date ?: null,
                'end_vacation_date' => $end_vacation_date ?: null,
                'reason' => $reason,
                'notes' => $notes
            ];

            $id = Vacaciones::create($data);
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => true, 'message' => 'Vacaciones creadas exitosamente']);
                exit;
            }
            
            header('Location: /Recursos/index.php?route=vacaciones.index&success=Vacaciones+creadas+exitosamente');
            exit;
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Error al crear vacaciones: ' . $e->getMessage()]);
                exit;
            }
            header('Location: /Recursos/index.php?route=vacaciones.index&error=Error+al+crear+vacaciones');
            exit;
        }
    }

    public static function delete(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) { 
            Vacaciones::delete($id); 
        }
        header('Location: /Recursos/index.php?route=vacaciones.index&success=Vacaciones+eliminadas');
        exit;
    }

    public static function view(): void {
        // Para modales - solo mostrar datos
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) { exit('ID no válido'); }
        
        $vacacion = Vacaciones::findById($id);
        if (!$vacacion) { exit('Vacaciones no encontradas'); }
        
        include __DIR__ . '/../views/vacaciones/view_modal.php';
    }

    public static function edit(): void {
        // Para modales - solo mostrar formulario
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) { exit('ID no válido'); }
        
        $vacacion = Vacaciones::findById($id);
        if (!$vacacion) { exit('Vacaciones no encontradas'); }
        
        include __DIR__ . '/../views/vacaciones/edit_modal.php';
    }

    public static function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
            header('Location: /Recursos/index.php?route=vacaciones.index'); 
            exit; 
        }
        
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) { 
            header('Location: /Recursos/index.php?route=vacaciones.index'); 
            exit; 
        }

        $personal_id = !empty($_POST['personal_id']) ? (int)$_POST['personal_id'] : null;
        $start_date = trim($_POST['start_date'] ?? '');
        $end_date = trim($_POST['end_date'] ?? '');
        $days_requested = !empty($_POST['days_requested']) ? (int)$_POST['days_requested'] : null;
        $days_approved = !empty($_POST['days_approved']) ? (int)$_POST['days_approved'] : null;
        $days_taken = !empty($_POST['days_taken']) ? (int)$_POST['days_taken'] : null;
        $status = trim($_POST['status'] ?? 'PENDIENTE');
        $approved_by = !empty($_POST['approved_by']) ? (int)$_POST['approved_by'] : null;
        $approved_date = trim($_POST['approved_date'] ?? '');
        $start_vacation_date = trim($_POST['start_vacation_date'] ?? '');
        $end_vacation_date = trim($_POST['end_vacation_date'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // Validaciones
        if (!$personal_id || $start_date === '' || $end_date === '') {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes']);
                exit;
            }
            header('Location: /Recursos/index.php?route=vacaciones.index&error=Campos+obligatorios+faltantes');
            exit;
        }

        if ($start_date >= $end_date) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'La fecha de inicio debe ser anterior a la fecha de fin']);
                exit;
            }
            header('Location: /Recursos/index.php?route=vacaciones.index&error=La+fecha+de+inicio+debe+ser+anterior+a+la+fecha+de+fin');
            exit;
        }

        try {
            $data = [
                'personal_id' => $personal_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'days_requested' => $days_requested,
                'days_approved' => $days_approved,
                'days_taken' => $days_taken,
                'status' => $status,
                'approved_by' => $approved_by,
                'approved_date' => $approved_date ?: null,
                'start_vacation_date' => $start_vacation_date ?: null,
                'end_vacation_date' => $end_vacation_date ?: null,
                'reason' => $reason,
                'notes' => $notes
            ];

            Vacaciones::update($id, $data);
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => true, 'message' => 'Vacaciones actualizadas exitosamente']);
                exit;
            }
            
            header('Location: /Recursos/index.php?route=vacaciones.index&success=Vacaciones+actualizadas+exitosamente');
            exit;
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar vacaciones: ' . $e->getMessage()]);
                exit;
            }
            header('Location: /Recursos/index.php?route=vacaciones.index&error=Error+al+actualizar+vacaciones');
            exit;
        }
    }

    public static function approve(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) { 
            Vacaciones::approve($id, $_SESSION['user_id'] ?? null); 
        }
        header('Location: /Recursos/index.php?route=vacaciones.index&success=Vacaciones+aprobadas');
        exit;
    }

    public static function reject(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) { 
            Vacaciones::reject($id, $_SESSION['user_id'] ?? null); 
        }
        header('Location: /Recursos/index.php?route=vacaciones.index&success=Vacaciones+rechazadas');
        exit;
    }

    public static function startVacation(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) { 
            Vacaciones::startVacation($id); 
        }
        header('Location: /Recursos/index.php?route=vacaciones.index&success=Vacaciones+iniciadas');
        exit;
    }

    public static function completeVacation(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) { 
            Vacaciones::completeVacation($id); 
        }
        header('Location: /Recursos/index.php?route=vacaciones.index&success=Vacaciones+completadas');
        exit;
    }
}
