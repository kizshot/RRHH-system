<?php
// app/controllers/PagoController.php
require_once __DIR__ . '/../../includes/db_config.php';

class PagoController {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Listar pagos con filtros y paginación
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Recursos/index.php?route=login');
            exit;
        }
        
        // Parámetros de búsqueda y filtros
        $q = $_GET['q'] ?? '';
        $personal_id = $_GET['personal_id'] ?? '';
        $period_month = $_GET['period_month'] ?? '';
        $period_year = $_GET['period_year'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Construir consulta base
        $sql = "SELECT p.*, per.first_name, per.last_name, per.employee_code, per.department 
                FROM pagos p 
                INNER JOIN personal per ON p.personal_id = per.id 
                WHERE 1=1";
        $params = [];
        $types = '';
        
        // Aplicar filtros
        if ($q) {
            $sql .= " AND (per.first_name LIKE ? OR per.last_name LIKE ? OR per.employee_code LIKE ?)";
            $searchTerm = "%$q%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'sss';
        }
        
        if ($personal_id) {
            $sql .= " AND p.personal_id = ?";
            $params[] = $personal_id;
            $types .= 'i';
        }
        
        if ($period_month) {
            $sql .= " AND p.period_month = ?";
            $params[] = $period_month;
            $types .= 'i';
        }
        
        if ($period_year) {
            $sql .= " AND p.period_year = ?";
            $params[] = $period_year;
            $types .= 'i';
        }
        
        if ($status) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
            $types .= 's';
        }
        
        // Ordenamiento
        $sort = $_GET['sort'] ?? 'period_year';
        $dir = strtoupper($_GET['dir'] ?? 'DESC');
        $allowedSorts = ['id', 'period_year', 'period_month', 'personal_id', 'status', 'net_salary'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'period_year';
        $dir = in_array($dir, ['ASC', 'DESC']) ? $dir : 'DESC';
        $sql .= " ORDER BY p.$sort $dir, p.period_month DESC";
        
        // Contar total de registros
        $countSql = str_replace("SELECT p.*, per.first_name, per.last_name, per.employee_code, per.department", "SELECT COUNT(*)", $sql);
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
        $pagos = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        
        // Obtener lista de personal para filtros
        $personalList = $this->getPersonalList();
        
        // Calcular paginación
        $pageCount = ceil($total / $limit);
        
        // Incluir vista
        include __DIR__ . '/../views/pagos/index.php';
    }
    
    // Mostrar formulario de crear pago
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
        include __DIR__ . '/../views/pagos/create.php';
    }
    
    // Guardar nuevo pago
    public function store() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Recursos/index.php?route=login');
            exit;
        }
        
        $personal_id = $_POST['personal_id'] ?? '';
        $period_month = $_POST['period_month'] ?? '';
        $period_year = $_POST['period_year'] ?? '';
        $base_salary = $_POST['base_salary'] ?? 0;
        $bonuses = $_POST['bonuses'] ?? 0;
        $deductions = $_POST['deductions'] ?? 0;
        $payment_date = $_POST['payment_date'] ?? '';
        $status = $_POST['status'] ?? 'PENDIENTE';
        
        // Validaciones
        $errors = [];
        if (!$personal_id) $errors[] = 'El empleado es requerido';
        if (!$period_month) $errors[] = 'El mes es requerido';
        if (!$period_year) $errors[] = 'El año es requerido';
        if ($base_salary < 0) $errors[] = 'El salario base no puede ser negativo';
        if ($bonuses < 0) $errors[] = 'Los bonos no pueden ser negativos';
        if ($deductions < 0) $errors[] = 'Las deducciones no pueden ser negativas';
        
        if (empty($errors)) {
            // Calcular salario neto
            $net_salary = $base_salary + $bonuses - $deductions;
            
            // Verificar si ya existe un pago para este empleado y período
            $checkSql = "SELECT id FROM pagos WHERE personal_id = ? AND period_month = ? AND period_year = ?";
            $checkStmt = mysqli_prepare($this->conn, $checkSql);
            mysqli_stmt_bind_param($checkStmt, 'iii', $personal_id, $period_month, $period_year);
            mysqli_stmt_execute($checkStmt);
            $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($checkStmt));
            mysqli_stmt_close($checkStmt);
            
            if ($existing) {
                $errors[] = 'Ya existe un pago para este empleado en el período especificado';
            } else {
                $sql = "INSERT INTO pagos (personal_id, period_month, period_year, base_salary, bonuses, deductions, net_salary, payment_date, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = mysqli_prepare($this->conn, $sql);
                mysqli_stmt_bind_param($stmt, 'iiidddds', $personal_id, $period_month, $period_year, $base_salary, $bonuses, $deductions, $net_salary, $payment_date, $status);
                
                if (mysqli_stmt_execute($stmt)) {
                    $pago_id = mysqli_insert_id($this->conn);
                    mysqli_stmt_close($stmt);
                    
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        echo json_encode(['success' => true, 'message' => 'Pago creado correctamente']);
                    } else {
                        header('Location: /Recursos/index.php?route=pagos.index&success=Pago creado correctamente');
                    }
                    exit;
                } else {
                    $errors[] = 'Error al crear el pago: ' . mysqli_error($this->conn);
                }
                mysqli_stmt_close($stmt);
            }
        }
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        } else {
            header('Location: /Recursos/index.php?route=pagos.create&error=' . urlencode(implode(', ', $errors)));
        }
        exit;
    }
    
    // Mostrar pago específico
    public function show($id) {
        if (!isset($_SESSION['user_id'])) {
            exit('Acceso denegado');
        }
        
        $sql = "SELECT p.*, per.first_name, per.last_name, per.employee_code, per.department, per.position 
                FROM pagos p 
                INNER JOIN personal per ON p.personal_id = per.id 
                WHERE p.id = ?";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $pago = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        
        if (!$pago) {
            exit('Pago no encontrado');
        }
        
        include __DIR__ . '/../views/pagos/view_modal.php';
    }
    
    // Mostrar formulario de editar pago
    public function edit($id) {
        if (!isset($_SESSION['user_id'])) {
            exit('Acceso denegado');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update($id);
            return;
        }
        
        $sql = "SELECT * FROM pagos WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $pago = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        
        if (!$pago) {
            exit('Pago no encontrado');
        }
        
        $personalList = $this->getPersonalList();
        include __DIR__ . '/../views/pagos/edit_modal.php';
    }
    
    // Actualizar pago
    public function update($id) {
        if (!isset($_SESSION['user_id'])) {
            exit('Acceso denegado');
        }
        
        // Permitir id por POST si no vino en query
        if (!$id) {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        }
        if (!$id) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'ID de pago inválido']);
                exit;
            }
            header('Location: /Recursos/index.php?route=pagos.index&error=ID de pago inválido');
            exit;
        }

        $personal_id = $_POST['personal_id'] ?? '';
        $period_month = $_POST['period_month'] ?? '';
        $period_year = $_POST['period_year'] ?? '';
        $base_salary = $_POST['base_salary'] ?? 0;
        $bonuses = $_POST['bonuses'] ?? 0;
        $deductions = $_POST['deductions'] ?? 0;
        $payment_date = $_POST['payment_date'] ?? '';
        $status = $_POST['status'] ?? 'PENDIENTE';
        
        // Validaciones
        $errors = [];
        if (!$personal_id) $errors[] = 'El empleado es requerido';
        if (!$period_month) $errors[] = 'El mes es requerido';
        if (!$period_year) $errors[] = 'El año es requerido';
        if ($base_salary < 0) $errors[] = 'El salario base no puede ser negativo';
        if ($bonuses < 0) $errors[] = 'Los bonos no pueden ser negativos';
        if ($deductions < 0) $errors[] = 'Las deducciones no pueden ser negativas';
        
        if (empty($errors)) {
            // Calcular salario neto
            $net_salary = $base_salary + $bonuses - $deductions;
            
            // Verificar si ya existe otro pago para este empleado y período (excluyendo el actual)
            $checkSql = "SELECT id FROM pagos WHERE personal_id = ? AND period_month = ? AND period_year = ? AND id != ?";
            $checkStmt = mysqli_prepare($this->conn, $checkSql);
            mysqli_stmt_bind_param($checkStmt, 'iiii', $personal_id, $period_month, $period_year, $id);
            mysqli_stmt_execute($checkStmt);
            $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($checkStmt));
            mysqli_stmt_close($checkStmt);
            
            if ($existing) {
                $errors[] = 'Ya existe otro pago para este empleado en el período especificado';
            } else {
                $sql = "UPDATE pagos SET personal_id = ?, period_month = ?, period_year = ?, base_salary = ?, 
                        bonuses = ?, deductions = ?, net_salary = ?, payment_date = ?, status = ? WHERE id = ?";
                
                $stmt = mysqli_prepare($this->conn, $sql);
                mysqli_stmt_bind_param($stmt, 'iiiddddsi', $personal_id, $period_month, $period_year, $base_salary, $bonuses, $deductions, $net_salary, $payment_date, $status, $id);
                
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        echo json_encode(['success' => true, 'message' => 'Pago actualizado correctamente']);
                    } else {
                        header('Location: /Recursos/index.php?route=pagos.index&success=Pago actualizado correctamente');
                    }
                    exit;
                } else {
                    $errors[] = 'Error al actualizar el pago: ' . mysqli_error($this->conn);
                }
                mysqli_stmt_close($stmt);
            }
        }
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        } else {
            header('Location: /Recursos/index.php?route=pagos.edit&id=' . $id . '&error=' . urlencode(implode(', ', $errors)));
        }
        exit;
    }
    
    // Eliminar pago
    public function delete() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Recursos/index.php?route=login');
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        
        if ($id) {
            $sql = "DELETE FROM pagos WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, 'i', $id);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: /Recursos/index.php?route=pagos.index&success=Pago eliminado correctamente');
            } else {
                mysqli_stmt_close($stmt);
                header('Location: /Recursos/index.php?route=pagos.index&error=Error al eliminar el pago');
            }
        } else {
            header('Location: /Recursos/index.php?route=pagos.index&error=ID de pago no válido');
        }
        exit;
    }
    
    // Generar pago automático basado en salario del empleado
    public function generatePayment() {
        if (!isset($_SESSION['user_id'])) {
            exit('Acceso denegado');
        }
        
        $personal_id = $_POST['personal_id'] ?? '';
        $period_month = $_POST['period_month'] ?? '';
        $period_year = $_POST['period_year'] ?? '';
        
        if (!$personal_id || !$period_month || !$period_year) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
            exit;
        }
        
        // Obtener salario del empleado
        $sql = "SELECT salary FROM personal WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $personal_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        
        if (!$result || !$result['salary']) {
            echo json_encode(['success' => false, 'message' => 'El empleado no tiene un salario configurado']);
            exit;
        }
        
        $base_salary = $result['salary'];
        $net_salary = $base_salary; // Sin bonos ni deducciones por defecto
        
        // Verificar si ya existe un pago para este período
        $checkSql = "SELECT id FROM pagos WHERE personal_id = ? AND period_month = ? AND period_year = ?";
        $checkStmt = mysqli_prepare($this->conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, 'iii', $personal_id, $period_month, $period_year);
        mysqli_stmt_execute($checkStmt);
        $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($checkStmt));
        mysqli_stmt_close($checkStmt);
        
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'Ya existe un pago para este empleado en el período especificado']);
            exit;
        }
        
        // Crear el pago
        $sql = "INSERT INTO pagos (personal_id, period_month, period_year, base_salary, bonuses, deductions, net_salary, status) 
                VALUES (?, ?, ?, ?, 0, 0, ?, 'PENDIENTE')";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'iiidd', $personal_id, $period_month, $period_year, $base_salary, $net_salary);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Pago generado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al generar el pago']);
        }
        mysqli_stmt_close($stmt);
        exit;
    }
    
    // Métodos auxiliares
    private function getPersonalList() {
        $sql = "SELECT id, first_name, last_name, employee_code, department, salary 
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
    
    // Obtener meses para filtros
    public function getMonths() {
        return [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
    }
    
    // Obtener años para filtros
    public function getYears() {
        $currentYear = date('Y');
        $years = [];
        for ($i = $currentYear - 5; $i <= $currentYear + 1; $i++) {
            $years[] = $i;
        }
        return $years;
    }
}
