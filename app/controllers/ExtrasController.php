<?php
// app/controllers/ExtrasController.php
require_once __DIR__ . '/../../includes/db_config.php';
require_once __DIR__ . '/../models/Extras.php';
require_once __DIR__ . '/../models/Personal.php';

class ExtrasController {
    public static function index(): void {
        // Listar extras con búsqueda, filtros y paginación
        $q = $_GET['q'] ?? '';
        $personal_id = $_GET['personal_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $sort = $_GET['sort'] ?? 'id';
        $dir = $_GET['dir'] ?? 'DESC';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = 10;
        $offset = ($page - 1) * $pageSize;
        $total = 0;
        
        $extras = Extras::searchPaginated($q, $pageSize, $offset, $total, $personal_id, $status, $sort, $dir);
        $pageCount = max(1, (int)ceil($total / $pageSize));
        
        // Obtener listas para filtros
        $personal_list = Personal::all();
        $statuses = Extras::getStatuses();
        
        include __DIR__ . '/../views/extras/index.php';
    }

    public static function create(): void {
        // Manejar alta de extra via POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
            header('Location: /Recursos/index.php?route=extras.index'); 
            exit; 
        }

        $personal_id = !empty($_POST['personal_id']) ? (int)$_POST['personal_id'] : null;
        $date = trim($_POST['date'] ?? '');
        $start_time = trim($_POST['start_time'] ?? '');
        $end_time = trim($_POST['end_time'] ?? '');
        $hours = !empty($_POST['hours']) ? (float)$_POST['hours'] : null;
        $rate_type = trim($_POST['rate_type'] ?? 'NORMAL');
        $rate = !empty($_POST['rate']) ? (float)$_POST['rate'] : null;
        $total_amount = !empty($_POST['total_amount']) ? (float)$_POST['total_amount'] : null;
        $status = trim($_POST['status'] ?? 'PENDIENTE');
        $approved_by = !empty($_POST['approved_by']) ? (int)$_POST['approved_by'] : null;
        $approved_date = trim($_POST['approved_date'] ?? '');
        $payment_date = trim($_POST['payment_date'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // Validaciones
        if (!$personal_id || $date === '' || $start_time === '' || $end_time === '') {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes']);
                exit;
            }
            header('Location: /Recursos/index.php?route=extras.index&error=Campos+obligatorios+faltantes');
            exit;
        }

        if ($hours <= 0) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Las horas deben ser mayores a 0']);
                exit;
            }
            header('Location: /Recursos/index.php?route=extras.index&error=Las+horas+deben+ser+mayores+a+0');
            exit;
        }

        try {
            $data = [
                'personal_id' => $personal_id,
                'date' => $date,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'hours' => $hours,
                'rate_type' => $rate_type,
                'rate' => $rate,
                'total_amount' => $total_amount,
                'status' => $status,
                'approved_by' => $approved_by,
                'approved_date' => $approved_date ?: null,
                'payment_date' => $payment_date ?: null,
                'reason' => $reason,
                'notes' => $notes
            ];

            $id = Extras::create($data);
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => true, 'message' => 'Extra creado exitosamente']);
                exit;
            }
            
            header('Location: /Recursos/index.php?route=extras.index&success=Extra+creado+exitosamente');
            exit;
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Error al crear extra: ' . $e->getMessage()]);
                exit;
            }
            header('Location: /Recursos/index.php?route=extras.index&error=Error+al+crear+extra');
            exit;
        }
    }

    public static function delete(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) { 
            Extras::delete($id); 
        }
        header('Location: /Recursos/index.php?route=extras.index&success=Extra+eliminado');
        exit;
    }

    public static function view(): void {
        // Para modales - solo mostrar datos
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) { exit('ID no válido'); }
        
        $extra = Extras::findById($id);
        if (!$extra) { exit('Extra no encontrado'); }
        
        include __DIR__ . '/../views/extras/view_modal.php';
    }

    public static function edit(): void {
        // Para modales - solo mostrar formulario
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) { exit('ID no válido'); }
        
        $extra = Extras::findById($id);
        if (!$extra) { exit('Extra no encontrado'); }
        
        include __DIR__ . '/../views/extras/edit_modal.php';
    }

    public static function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
            header('Location: /Recursos/index.php?route=extras.index'); 
            exit; 
        }
        
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) { 
            header('Location: /Recursos/index.php?route=extras.index'); 
            exit; 
        }

        $personal_id = !empty($_POST['personal_id']) ? (int)$_POST['personal_id'] : null;
        $date = trim($_POST['date'] ?? '');
        $start_time = trim($_POST['start_time'] ?? '');
        $end_time = trim($_POST['end_time'] ?? '');
        $hours = !empty($_POST['hours']) ? (float)$_POST['hours'] : null;
        $rate_type = trim($_POST['rate_type'] ?? 'NORMAL');
        $rate = !empty($_POST['rate']) ? (float)$_POST['rate'] : null;
        $total_amount = !empty($_POST['total_amount']) ? (float)$_POST['total_amount'] : null;
        $status = trim($_POST['status'] ?? 'PENDIENTE');
        $approved_by = !empty($_POST['approved_by']) ? (int)$_POST['approved_by'] : null;
        $approved_date = trim($_POST['approved_date'] ?? '');
        $payment_date = trim($_POST['payment_date'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // Validaciones
        if (!$personal_id || $date === '' || $start_time === '' || $end_time === '') {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes']);
                exit;
            }
            header('Location: /Recursos/index.php?route=extras.index&error=Campos+obligatorios+faltantes');
            exit;
        }

        if ($hours <= 0) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Las horas deben ser mayores a 0']);
                exit;
            }
            header('Location: /Recursos/index.php?route=extras.index&error=Las+horas+deben+ser+mayores+a+0');
            exit;
        }

        try {
            $data = [
                'personal_id' => $personal_id,
                'date' => $date,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'hours' => $hours,
                'rate_type' => $rate_type,
                'rate' => $rate,
                'total_amount' => $total_amount,
                'status' => $status,
                'approved_by' => $approved_by,
                'approved_date' => $approved_date ?: null,
                'payment_date' => $payment_date ?: null,
                'reason' => $reason,
                'notes' => $notes
            ];

            Extras::update($id, $data);
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => true, 'message' => 'Extra actualizado exitosamente']);
                exit;
            }
            
            header('Location: /Recursos/index.php?route=extras.index&success=Extra+actualizado+exitosamente');
            exit;
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar extra: ' . $e->getMessage()]);
                exit;
            }
            header('Location: /Recursos/index.php?route=extras.index&error=Error+al+actualizar+extra');
            exit;
        }
    }

    public static function approve(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) { 
            Extras::approve($id, $_SESSION['user_id'] ?? null); 
        }
        header('Location: /Recursos/index.php?route=extras.index&success=Extra+aprobado');
        exit;
    }

    public static function reject(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) { 
            Extras::reject($id, $_SESSION['user_id'] ?? null); 
        }
        header('Location: /Recursos/index.php?route=extras.index&success=Extra+rechazado');
        exit;
    }

    public static function markAsPaid(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) { 
            Extras::markAsPaid($id); 
        }
        header('Location: /Recursos/index.php?route=extras.index&success=Extra+marcado+como+pagado');
        exit;
    }
}
