<?php
// app/controllers/AsistenciasController.php
require_once __DIR__ . '/../../includes/db_config.php';
require_once __DIR__ . '/../models/Asistencias.php';
require_once __DIR__ . '/../models/Personal.php';

class AsistenciasController {
    public static function index(): void {
        // Listar asistencias con búsqueda, filtros y paginación
        $q = $_GET['q'] ?? '';
        $personal_id = $_GET['personal_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $date = $_GET['date'] ?? '';
        $sort = $_GET['sort'] ?? 'id';
        $dir = $_GET['dir'] ?? 'DESC';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = 10;
        $offset = ($page - 1) * $pageSize;
        $total = 0;
        
        $asistencias = Asistencias::searchPaginated($q, $pageSize, $offset, $total, $personal_id, $status, $date, $sort, $dir);
        $pageCount = max(1, (int)ceil($total / $pageSize));
        
        // Obtener listas para filtros
        $personal_list = Personal::all();
        $statuses = Asistencias::getStatuses();
        
        include __DIR__ . '/../views/asistencias/index.php';
    }

    public static function create(): void {
        // Manejar alta de asistencia via POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
            header('Location: /Recursos/index.php?route=asistencias.index'); 
            exit; 
        }

        $personal_id = !empty($_POST['personal_id']) ? (int)$_POST['personal_id'] : null;
        $date = trim($_POST['date'] ?? '');
        $entry_time = trim($_POST['entry_time'] ?? '');
        $exit_time = trim($_POST['exit_time'] ?? '');
        $hours_worked = !empty($_POST['hours_worked']) ? (float)$_POST['hours_worked'] : null;
        $status = trim($_POST['status'] ?? 'PRESENTE');
        $notes = trim($_POST['notes'] ?? '');

        // Validaciones
        if (!$personal_id || $date === '') {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes']);
                exit;
            }
            header('Location: /Recursos/index.php?route=asistencias.index&error=Campos+obligatorios+faltantes');
            exit;
        }

        // Verificar si ya existe una asistencia para este empleado en esta fecha
        if (Asistencias::exists($personal_id, $date)) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Ya existe una asistencia para este empleado en esta fecha']);
                exit;
            }
            header('Location: /Recursos/index.php?route=asistencias.index&error=Ya+existe+una+asistencia+para+este+empleado+en+esta+fecha');
            exit;
        }

        try {
            $data = [
                'personal_id' => $personal_id,
                'date' => $date,
                'entry_time' => $entry_time ?: null,
                'exit_time' => $exit_time ?: null,
                'hours_worked' => $hours_worked,
                'status' => $status,
                'notes' => $notes
            ];

            $id = Asistencias::create($data);
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => true, 'message' => 'Asistencia creada exitosamente']);
                exit;
            }
            
            header('Location: /Recursos/index.php?route=asistencias.index&success=Asistencia+creada+exitosamente');
            exit;
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Error al crear asistencia: ' . $e->getMessage()]);
                exit;
            }
            header('Location: /Recursos/index.php?route=asistencias.index&error=Error+al+crear+asistencia');
            exit;
        }
    }

    public static function delete(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) { 
            Asistencias::delete($id); 
        }
        header('Location: /Recursos/index.php?route=asistencias.index&success=Asistencia+eliminada');
        exit;
    }

    public static function view(): void {
        // Para modales - solo mostrar datos
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) { exit('ID no válido'); }
        
        $asistencia = Asistencias::findById($id);
        if (!$asistencia) { exit('Asistencia no encontrada'); }
        
        include __DIR__ . '/../views/asistencias/view_modal.php';
    }

    public static function edit(): void {
        // Para modales - solo mostrar formulario
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) { exit('ID no válido'); }
        
        $asistencia = Asistencias::findById($id);
        if (!$asistencia) { exit('Asistencia no encontrada'); }
        
        include __DIR__ . '/../views/asistencias/edit_modal.php';
    }

    public static function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
            header('Location: /Recursos/index.php?route=asistencias.index'); 
            exit; 
        }
        
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) { 
            header('Location: /Recursos/index.php?route=asistencias.index'); 
            exit; 
        }

        $personal_id = !empty($_POST['personal_id']) ? (int)$_POST['personal_id'] : null;
        $date = trim($_POST['date'] ?? '');
        $entry_time = trim($_POST['entry_time'] ?? '');
        $exit_time = trim($_POST['exit_time'] ?? '');
        $hours_worked = !empty($_POST['hours_worked']) ? (float)$_POST['hours_worked'] : null;
        $status = trim($_POST['status'] ?? 'PRESENTE');
        $notes = trim($_POST['notes'] ?? '');

        // Validaciones
        if (!$personal_id || $date === '') {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes']);
                exit;
            }
            header('Location: /Recursos/index.php?route=asistencias.index&error=Campos+obligatorios+faltantes');
            exit;
        }

        try {
            $data = [
                'personal_id' => $personal_id,
                'date' => $date,
                'entry_time' => $entry_time ?: null,
                'exit_time' => $exit_time ?: null,
                'hours_worked' => $hours_worked,
                'status' => $status,
                'notes' => $notes
            ];

            Asistencias::update($id, $data);
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => true, 'message' => 'Asistencia actualizada exitosamente']);
                exit;
            }
            
            header('Location: /Recursos/index.php?route=asistencias.index&success=Asistencia+actualizada+exitosamente');
            exit;
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar asistencia: ' . $e->getMessage()]);
                exit;
            }
            header('Location: /Recursos/index.php?route=asistencias.index&error=Error+al+actualizar+asistencia');
            exit;
        }
    }

    public static function markEntry(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) { 
            Asistencias::markEntry($id); 
        }
        header('Location: /Recursos/index.php?route=asistencias.index&success=Entrada+marcada');
        exit;
    }

    public static function markExit(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) { 
            Asistencias::markExit($id); 
        }
        header('Location: /Recursos/index.php?route=asistencias.index&success=Salida+marcada');
        exit;
    }

    public static function getAttendanceSummary(): void {
        $personal_id = isset($_GET['personal_id']) ? (int)$_GET['personal_id'] : 0;
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        
        if (!$personal_id || !$start_date || !$end_date) {
            echo json_encode(['success' => false, 'message' => 'Parámetros faltantes']);
            exit;
        }
        
        $summary = Asistencias::getAttendanceSummary($personal_id, $start_date, $end_date);
        echo json_encode(['success' => true, 'data' => $summary]);
        exit;
    }
}
