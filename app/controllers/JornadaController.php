<?php
// app/controllers/JornadaController.php
require_once __DIR__ . '/../../includes/db_config.php';

class JornadaController {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Listar jornadas con filtros y paginación
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Recursos/index.php?route=login');
            exit;
        }
        
        // Parámetros de búsqueda y filtros
        $q = $_GET['q'] ?? '';
        $personal_id = $_GET['personal_id'] ?? '';
        $date_from = $_GET['date_from'] ?? '';
        $date_to = $_GET['date_to'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Construir consulta base
        $sql = "SELECT j.*, p.first_name, p.last_name, p.employee_code 
                FROM jornadas j 
                INNER JOIN personal p ON j.personal_id = p.id 
                WHERE 1=1";
        $params = [];
        $types = '';
        
        // Aplicar filtros
        if ($q) {
            $sql .= " AND (p.first_name LIKE ? OR p.last_name LIKE ? OR p.employee_code LIKE ?)";
            $searchTerm = "%$q%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'sss';
        }
        
        if ($personal_id) {
            $sql .= " AND j.personal_id = ?";
            $params[] = $personal_id;
            $types .= 'i';
        }
        
        if ($date_from) {
            $sql .= " AND j.date >= ?";
            $params[] = $date_from;
            $types .= 's';
        }
        
        if ($date_to) {
            $sql .= " AND j.date <= ?";
            $params[] = $date_to;
            $types .= 's';
        }
        
        if ($status) {
            $sql .= " AND j.status = ?";
            $params[] = $status;
            $types .= 's';
        }
        
        // Ordenamiento
        $sort = $_GET['sort'] ?? 'date';
        $dir = strtoupper($_GET['dir'] ?? 'DESC');
        $allowedSorts = ['id', 'date', 'personal_id', 'status', 'total_hours'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'date';
        $dir = in_array($dir, ['ASC', 'DESC']) ? $dir : 'DESC';
        $sql .= " ORDER BY j.$sort $dir";
        
        // Contar total de registros
        $countSql = str_replace("SELECT j.*, p.first_name, p.last_name, p.employee_code", "SELECT COUNT(*)", $sql);
        $countSql = preg_replace('/ORDER BY.*$/', '', $countSql);
        
        $stmt = mysqli_prepare($this->conn, $countSql);
        if ($params) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $total = mysqli_fetch_array(mysqli_stmt_get_result($stmt))[0];
        mysqli_stmt_close($stmt);
        
        // Obtener datos paginados
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
        
        $stmt = mysqli_prepare($this->conn, $sql);
        if ($params) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $jornadas = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        
        // Obtener lista de personal para filtros
        $personalList = $this->getPersonalList();
        
        // Calcular paginación
        $pageCount = ceil($total / $limit);
        
        // Incluir vista
        include __DIR__ . '/../views/jornadas/index.php';
    }
    
    // Mostrar formulario de crear jornada
    public function create() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Recursos/index.php?route=login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }
        
        $personalList = $this->getPersonalList();
        include __DIR__ . '/../views/jornadas/create.php';
    }
    
    // Guardar nueva jornada
    public function store() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Recursos/index.php?route=login');
            exit;
        }
        
        $personal_id = $_POST['personal_id'] ?? '';
        $date = $_POST['date'] ?? '';
        $entry_time = $_POST['entry_time'] ?? '';
        $exit_time = $_POST['exit_time'] ?? '';
        $break_start = $_POST['break_start'] ?? '';
        $break_end = $_POST['break_end'] ?? '';
        $status = $_POST['status'] ?? 'COMPLETA';
        
        // Validaciones
        $errors = [];
        if (!$personal_id) $errors[] = 'El empleado es requerido';
        if (!$date) $errors[] = 'La fecha es requerida';
        
        if (empty($errors)) {
            // Calcular horas totales
            $total_hours = $this->calculateTotalHours($entry_time, $exit_time, $break_start, $break_end);
            
            $sql = "INSERT INTO jornadas (personal_id, date, entry_time, exit_time, break_start, break_end, total_hours, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, 'isssssds', $personal_id, $date, $entry_time, $exit_time, $break_start, $break_end, $total_hours, $status);
            
            if (mysqli_stmt_execute($stmt)) {
                $jornada_id = mysqli_insert_id($this->conn);
                mysqli_stmt_close($stmt);
                
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    echo json_encode(['success' => true, 'message' => 'Jornada creada correctamente']);
                } else {
                    header('Location: /Recursos/index.php?route=jornadas.index&success=Jornada creada correctamente');
                }
                exit;
            } else {
                $errors[] = 'Error al crear la jornada: ' . mysqli_error($this->conn);
            }
            mysqli_stmt_close($stmt);
        }
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        } else {
            header('Location: /Recursos/index.php?route=jornadas.create&error=' . urlencode(implode(', ', $errors)));
        }
        exit;
    }
    
    // Mostrar jornada específica
    public function show($id) {
        if (!isset($_SESSION['user_id'])) {
            exit('Acceso denegado');
        }
        
        $sql = "SELECT j.*, p.first_name, p.last_name, p.employee_code, p.department, p.position 
                FROM jornadas j 
                INNER JOIN personal p ON j.personal_id = p.id 
                WHERE j.id = ?";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $jornada = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        
        if (!$jornada) {
            exit('Jornada no encontrada');
        }
        
        include __DIR__ . '/../views/jornadas/view_modal.php';
    }
    
    // Mostrar formulario de editar jornada
    public function edit($id) {
        if (!isset($_SESSION['user_id'])) {
            exit('Acceso denegado');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update($id);
            return;
        }
        
        $sql = "SELECT * FROM jornadas WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $jornada = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        
        if (!$jornada) {
            exit('Jornada no encontrada');
        }
        
        $personalList = $this->getPersonalList();
        include __DIR__ . '/../views/jornadas/edit_modal.php';
    }
    
    // Actualizar jornada
    public function update($id) {
        if (!isset($_SESSION['user_id'])) {
            exit('Acceso denegado');
        }
        
        // Aceptar id desde POST si no vino por query
        if (!$id) {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        }
        if (!$id) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'ID de jornada inválido']);
                exit;
            }
            header('Location: /Recursos/index.php?route=jornadas.index&error=ID de jornada inválido');
            exit;
        }

        $personal_id = $_POST['personal_id'] ?? '';
        $date = $_POST['date'] ?? '';
        $entry_time = $_POST['entry_time'] ?? '';
        $exit_time = $_POST['exit_time'] ?? '';
        $break_start = $_POST['break_start'] ?? '';
        $break_end = $_POST['break_end'] ?? '';
        $status = $_POST['status'] ?? 'COMPLETA';
        
        // Validaciones
        $errors = [];
        if (!$personal_id) $errors[] = 'El empleado es requerido';
        if (!$date) $errors[] = 'La fecha es requerida';
        
        if (empty($errors)) {
            // Calcular horas totales
            $total_hours = $this->calculateTotalHours($entry_time, $exit_time, $break_start, $break_end);
            
            $sql = "UPDATE jornadas SET personal_id = ?, date = ?, entry_time = ?, exit_time = ?, 
                    break_start = ?, break_end = ?, total_hours = ?, status = ? WHERE id = ?";
            
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, 'isssssdsi', $personal_id, $date, $entry_time, $exit_time, $break_start, $break_end, $total_hours, $status, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    echo json_encode(['success' => true, 'message' => 'Jornada actualizada correctamente']);
                } else {
                    header('Location: /Recursos/index.php?route=jornadas.index&success=Jornada actualizada correctamente');
                }
                exit;
            } else {
                $errors[] = 'Error al actualizar la jornada: ' . mysqli_error($this->conn);
            }
            mysqli_stmt_close($stmt);
        }
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        } else {
            header('Location: /Recursos/index.php?route=jornadas.edit&id=' . $id . '&error=' . urlencode(implode(', ', $errors)));
        }
        exit;
    }
    
    // Eliminar jornada
    public function delete() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Recursos/index.php?route=login');
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        
        if ($id) {
            $sql = "DELETE FROM jornadas WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, 'i', $id);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: /Recursos/index.php?route=jornadas.index&success=Jornada eliminada correctamente');
            } else {
                mysqli_stmt_close($stmt);
                header('Location: /Recursos/index.php?route=jornadas.index&error=Error al eliminar la jornada');
            }
        } else {
            header('Location: /Recursos/index.php?route=jornadas.index&error=ID de jornada no válido');
        }
        exit;
    }
    
    // Métodos auxiliares
    private function getPersonalList() {
        $sql = "SELECT id, first_name, last_name, employee_code, department 
                FROM personal 
                WHERE status = 'ACTIVO' 
                ORDER BY first_name, last_name";
        
        $result = mysqli_query($this->conn, $sql);
        $personal = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $personal[] = $row;
        }
        return $personal;
    }
    
    private function calculateTotalHours($entry_time, $exit_time, $break_start, $break_end) {
        if (!$entry_time || !$exit_time) {
            return 0;
        }

        $entry = strtotime($entry_time);
        $exit = strtotime($exit_time);
        if ($entry === false || $exit === false) {
            return 0;
        }

        // Diferencia en minutos, soportando cruce de medianoche
        $total_minutes = ($exit - $entry) / 60;
        if ($total_minutes < 0) {
            $total_minutes += 24 * 60; // suma 24h si cruzó medianoche
        }

        // Restar tiempo de descanso si existe, también soportando cruce de medianoche
        if ($break_start && $break_end) {
            $break_start_time = strtotime($break_start);
            $break_end_time = strtotime($break_end);
            if ($break_start_time !== false && $break_end_time !== false) {
                $break_minutes = ($break_end_time - $break_start_time) / 60;
                if ($break_minutes < 0) {
                    $break_minutes += 24 * 60;
                }
                $total_minutes -= max(0, $break_minutes);
            }
        }

        $hours = max(0, $total_minutes) / 60;
        return round($hours, 2);
    }
}
