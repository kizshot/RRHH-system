<?php
require_once __DIR__ . '/../../includes/db_config.php';

class RolesController {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Mostrar la lista de roles
     */
    public function index() {
        $query = "SELECT id, name, description, permissions, status, created_at FROM roles WHERE is_deleted = 0 ORDER BY id ASC";
        $result = $this->conn->query($query);
        
        if (!$result) {
            return ['error' => 'Error al obtener roles: ' . $this->conn->error];
        }
        
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
        
        return ['success' => true, 'data' => $roles];
    }
    
    /**
     * Mostrar un rol específico
     */
    public function view($id) {
        $query = "SELECT id, name, description, permissions, status, created_at FROM roles WHERE id = ? AND is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $role = $result->fetch_assoc();
        $stmt->close();
        
        if (!$role) {
            return ['error' => 'Rol no encontrado'];
        }
        
        return ['success' => true, 'data' => $role];
    }
    
    /**
     * Crear un nuevo rol
     */
    public function create() {
        // Validar datos de entrada
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = trim($_POST['permissions'] ?? '');
        $status = $_POST['status'] ?? 'ACTIVO';
        
        if (empty($name)) {
            return ['error' => 'El nombre del rol es obligatorio'];
        }
        
        // Verificar si el nombre ya existe
        $checkQuery = "SELECT id FROM roles WHERE name = ? AND is_deleted = 0";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bind_param("s", $name);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $checkStmt->close();
            return ['error' => 'Ya existe un rol con ese nombre'];
        }
        $checkStmt->close();
        
        // Generar permisos por defecto si no se proporcionan
        if (empty($permissions)) {
            $permissionName = strtoupper(str_replace(' ', '_', $name));
            $permissions = "VIEW_OWN_DATA,VIEW_{$permissionName}";
            
            // Agregar permisos básicos para módulos principales
            $basicPermissions = [
                'VIEW_PERSONAL',
                'VIEW_JORNADAS', 
                'VIEW_ASISTENCIA',
                'VIEW_REPORTES'
            ];
            
            // Solo agregar si no es un rol de administrador
            if (stripos($name, 'admin') === false && stripos($name, 'administrador') === false) {
                $permissions = implode(',', array_merge([$permissions], $basicPermissions));
            }
        }
        
        // Insertar nuevo rol
        $query = "INSERT INTO roles (name, description, permissions, status) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssss", $name, $description, $permissions, $status);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Rol creado exitosamente'];
        } else {
            $stmt->close();
            return ['error' => 'Error al crear el rol: ' . $this->conn->error];
        }
    }
    
    /**
     * Actualizar un rol existente
     */
    public function update() {
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = trim($_POST['permissions'] ?? '');
        $status = $_POST['status'] ?? 'ACTIVO';
        
        if (!$id) {
            return ['error' => 'ID de rol no especificado'];
        }
        
        if (empty($name)) {
            return ['error' => 'El nombre del rol es obligatorio'];
        }
        
        // Verificar si el nombre ya existe en otro rol
        $checkQuery = "SELECT id FROM roles WHERE name = ? AND id != ? AND is_deleted = 0";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bind_param("si", $name, $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $checkStmt->close();
            return ['error' => 'Ya existe otro rol con ese nombre'];
        }
        $checkStmt->close();
        
        // Actualizar rol
        $query = "UPDATE roles SET name = ?, description = ?, permissions = ?, status = ? WHERE id = ? AND is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssi", $name, $description, $permissions, $status, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Rol actualizado exitosamente'];
        } else {
            $stmt->close();
            return ['error' => 'Error al actualizar el rol: ' . $this->conn->error];
        }
    }
    
    /**
     * Actualizar solo los permisos de un rol
     */
    public function updatePermissions() {
        $roleId = $_POST['role_id'] ?? null;
        $permissions = $_POST['permissions'] ?? [];
        
        if (!$roleId) {
            return ['error' => 'ID de rol no especificado'];
        }
        
        // Convertir array de permisos a string
        $permissionsString = implode(',', $permissions);
        
        // Actualizar permisos
        $query = "UPDATE roles SET permissions = ? WHERE id = ? AND is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $permissionsString, $roleId);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Permisos actualizados exitosamente'];
        } else {
            $stmt->close();
            return ['error' => 'Error al actualizar los permisos: ' . $this->conn->error];
        }
    }
    
    /**
     * Eliminar un rol (soft delete)
     */
    public function delete() {
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            return ['error' => 'ID de rol no especificado'];
        }
        
        // Verificar si el rol está siendo usado por usuarios
        $checkQuery = "SELECT COUNT(*) as count FROM users WHERE role = (SELECT name FROM roles WHERE id = ?) AND is_deleted = 0";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $userCount = $checkResult->fetch_assoc()['count'];
        $checkStmt->close();
        
        if ($userCount > 0) {
            return ['error' => "No se puede eliminar el rol porque está siendo usado por {$userCount} usuario(s)"];
        }
        
        // Soft delete del rol
        $query = "UPDATE roles SET is_deleted = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Rol eliminado exitosamente'];
        } else {
            $stmt->close();
            return ['error' => 'Error al eliminar el rol: ' . $this->conn->error];
        }
    }
    
    /**
     * Obtener todos los roles para select
     */
    public function getAllForSelect() {
        $query = "SELECT id, name FROM roles WHERE status = 'ACTIVO' AND is_deleted = 0 ORDER BY name ASC";
        $result = $this->conn->query($query);
        
        if (!$result) {
            return ['error' => 'Error al obtener roles: ' . $this->conn->error];
        }
        
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
        
        return ['success' => true, 'data' => $roles];
    }
    
    /**
     * Verificar permisos de un usuario
     */
    public function checkUserPermissions($userId, $permission) {
        $query = "SELECT r.permissions FROM users u 
                  JOIN roles r ON u.role = r.name 
                  WHERE u.id = ? AND u.is_deleted = 0 AND r.is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $role = $result->fetch_assoc();
        $stmt->close();
        
        if (!$role) {
            return false;
        }
        
        $permissions = explode(',', $role['permissions']);
        $permissions = array_map('trim', $permissions);
        
        // Si tiene ALL, tiene todos los permisos
        if (in_array('ALL', $permissions)) {
            return true;
        }
        
        // Verificar permiso específico
        return in_array($permission, $permissions);
    }
    
    /**
     * Obtener permisos de un usuario
     */
    public function getUserPermissions($userId) {
        $query = "SELECT r.permissions FROM users u 
                  JOIN roles r ON u.role = r.name 
                  WHERE u.id = ? AND u.is_deleted = 0 AND r.is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $role = $result->fetch_assoc();
        $stmt->close();
        
        if (!$role) {
            return [];
        }
        
        $permissions = explode(',', $role['permissions']);
        return array_map('trim', $permissions);
    }
}

// Manejar rutas si se accede directamente
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $controller = new RolesController();
    
    $route = $_GET['route'] ?? '';
    $id = $_GET['id'] ?? null;
    
    switch ($route) {
        case 'index':
            $result = $controller->index();
            break;
            
        case 'view':
            if ($id) {
                $result = $controller->view($id);
            } else {
                $result = ['error' => 'ID no especificado'];
            }
            break;
            
        case 'create':
            $result = $controller->create();
            break;
            
        case 'update':
            $result = $controller->update();
            break;
            
        case 'updatePermissions':
            $result = $controller->updatePermissions();
            break;
            
        case 'delete':
            $result = $controller->delete();
            break;
            
        case 'getAllForSelect':
            $result = $controller->getAllForSelect();
            break;
            
        default:
            $result = ['error' => 'Ruta no válida'];
            break;
    }
    
    // Devolver respuesta JSON
    header('Content-Type: application/json');
    echo json_encode($result);
}
?>
