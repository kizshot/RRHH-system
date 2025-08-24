<?php
// app/controllers/AdelantosController.php
require_once __DIR__ . '/../../includes/db_config.php';
require_once __DIR__ . '/../models/Adelantos.php';
require_once __DIR__ . '/../models/Personal.php';

class AdelantosController {
    public static function index(): void {
        // Listar adelantos con búsqueda, filtros y paginación
        $q = $_GET['q'] ?? '';
        $personal_id = $_GET['personal_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $sort = $_GET['sort'] ?? 'id';
        $dir = $_GET['dir'] ?? 'DESC';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = 10;
        $offset = ($page - 1) * $pageSize;
        $total = 0;
        
        $adelantos = Adelantos::searchPaginated($q, $pageSize, $offset, $total, $personal_id, $status, $sort, $dir);
        $pageCount = max(1, (int)ceil($total / $pageSize));
        
        // Obtener listas para filtros
        $personal_list = Personal::all();
        $statuses = Adelantos::getStatuses();
        
        include __DIR__ . '/../views/adelantos/index.php';
    }

    public static function create(): void {
        // Manejar alta de adelanto via POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
            header('Location: /Recursos/index.php?route=adelantos.index'); 
            exit; 
        }

        $personal_id = !empty($_POST['personal_id']) ? (int)$_POST['personal_id'] : null;
        $amount = !empty($_POST['amount']) ? (float)$_POST['amount'] : null;
        $request_date = trim($_POST['request_date'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $status = trim($_POST['status'] ?? 'PENDIENTE');
        $approved_by = !empty($_POST['approved_by']) ? (int)$_POST['approved_by'] : null;
        $approved_date = trim($_POST['approved_date'] ?? '');
        $payment_date = trim($_POST['payment_date'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // Validaciones
        if (!$personal_id || !$amount || $request_date === '') {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes']);
                exit;
            }
            header('Location: /Recursos/index.php?route=adelantos.index&error=Campos+obligatorios+faltantes');
            exit;
        }

        if ($amount <= 0) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'El monto debe ser mayor a 0']);
                exit;
            }
            header('Location: /Recursos/index.php?route=adelantos.index&error=El+monto+debe+ser+mayor+a+0');
            exit;
        }

        try {
            $data = [
                'personal_id' => $personal_id,
                'amount' => $amount,
                'request_date' => $request_date,
                'reason' => $reason,
                'status' => $status,
                'approved_by' => $approved_by,
                'approved_date' => $approved_date ?: null,
                'payment_date' => $payment_date ?: null,
                'notes' => $notes
            ];

            $id = Adelantos::create($data);
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => true, 'message' => 'Adelanto creado exitosamente']);
                exit;
            }
            
            header('Location: /Recursos/index.php?route=adelantos.index&success=Adelanto+creado+exitosamente');
            exit;
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Error al crear adelanto: ' . $e->getMessage()]);
                exit;
            }
            header('Location: /Recursos/index.php?route=adelantos.index&error=Error+al+crear+adelanto');
            exit;
        }
    }

    public static function delete(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) { 
            Adelantos::delete($id); 
        }
        header('Location: /Recursos/index.php?route=adelantos.index&success=Adelanto+eliminado');
        exit;
    }

    public static function view(): void {
        // Para modales - solo mostrar datos
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) { exit('ID no válido'); }
        
        $adelanto = Adelantos::findById($id);
        if (!$adelanto) { exit('Adelanto no encontrado'); }
        
        include __DIR__ . '/../views/adelantos/view_modal.php';
    }

    public static function edit(): void {
        // Para modales - solo mostrar formulario
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) { exit('ID no válido'); }
        
        $adelanto = Adelantos::findById($id);
        if (!$adelanto) { exit('Adelanto no encontrado'); }
        
        include __DIR__ . '/../views/adelantos/edit_modal.php';
    }

    public static function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
            header('Location: /Recursos/index.php?route=adelantos.index'); 
            exit; 
        }
        
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) { 
            header('Location: /Recursos/index.php?route=adelantos.index'); 
            exit; 
        }

        $personal_id = !empty($_POST['personal_id']) ? (int)$_POST['personal_id'] : null;
        $amount = !empty($_POST['amount']) ? (float)$_POST['amount'] : null;
        $request_date = trim($_POST['request_date'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $status = trim($_POST['status'] ?? 'PENDIENTE');
        $approved_by = !empty($_POST['approved_by']) ? (int)$_POST['approved_by'] : null;
        $approved_date = trim($_POST['approved_date'] ?? '');
        $payment_date = trim($_POST['payment_date'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // Validaciones
        if (!$personal_id || !$amount || $request_date === '') {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes']);
                exit;
            }
            header('Location: /Recursos/index.php?route=adelantos.index&error=Campos+obligatorios+faltantes');
            exit;
        }

        if ($amount <= 0) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'El monto debe ser mayor a 0']);
                exit;
            }
            header('Location: /Recursos/index.php?route=adelantos.index&error=El+monto+debe+ser+mayor+a+0');
            exit;
        }

        try {
            $data = [
                'personal_id' => $personal_id,
                'amount' => $amount,
                'request_date' => $request_date,
                'reason' => $reason,
                'status' => $status,
                'approved_by' => $approved_by,
                'approved_date' => $approved_date ?: null,
                'payment_date' => $payment_date ?: null,
                'notes' => $notes
            ];

            Adelantos::update($id, $data);
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => true, 'message' => 'Adelanto actualizado exitosamente']);
                exit;
            }
            
            header('Location: /Recursos/index.php?route=adelantos.index&success=Adelanto+actualizado+exitosamente');
            exit;
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar adelanto: ' . $e->getMessage()]);
                exit;
            }
            header('Location: /Recursos/index.php?route=adelantos.index&error=Error+al+actualizar+adelanto');
            exit;
        }
    }

    public static function approve(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) { 
            Adelantos::approve($id, $_SESSION['user_id'] ?? null); 
        }
        header('Location: /Recursos/index.php?route=adelantos.index&success=Adelanto+aprobado');
        exit;
    }

    public static function reject(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) { 
            Adelantos::reject($id, $_SESSION['user_id'] ?? null); 
        }
        header('Location: /Recursos/index.php?route=adelantos.index&success=Adelanto+rechazado');
        exit;
    }

    public static function markAsPaid(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) { 
            Adelantos::markAsPaid($id); 
        }
        header('Location: /Recursos/index.php?route=adelantos.index&success=Adelanto+marcado+como+pagado');
        exit;
    }
}
