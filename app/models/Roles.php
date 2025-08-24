<?php
require_once __DIR__ . '/../../includes/db_config.php';

class Roles {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Obtener todos los roles
     */
    public function getAll($includeDeleted = false) {
        $whereClause = $includeDeleted ? "" : "WHERE is_deleted = 0";
        $query = "SELECT * FROM roles {$whereClause} ORDER BY name ASC";
        $result = $this->conn->query($query);
        
        if (!$result) {
            return false;
        }
        
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
        
        return $roles;
    }
    
    /**
     * Obtener rol por ID
     */
    public function getById($id) {
        $query = "SELECT * FROM roles WHERE id = ? AND is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $role = $result->fetch_assoc();
        $stmt->close();
        
        return $role;
    }
    
    /**
     * Obtener rol por nombre
     */
    public function getByName($name) {
        $query = "SELECT * FROM roles WHERE name = ? AND is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $role = $result->fetch_assoc();
        $stmt->close();
        
        return $role;
    }
    
    /**
     * Crear nuevo rol
     */
    public function create($data) {
        $query = "INSERT INTO roles (name, description, permissions, status) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssss", 
            $data['name'], 
            $data['description'], 
            $data['permissions'], 
            $data['status']
        );
        
        if ($stmt->execute()) {
            $roleId = $stmt->insert_id;
            $stmt->close();
            return $roleId;
        }
        
        $stmt->close();
        return false;
    }
    
    /**
     * Actualizar rol existente
     */
    public function update($id, $data) {
        $query = "UPDATE roles SET name = ?, description = ?, permissions = ?, status = ? WHERE id = ? AND is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssi", 
            $data['name'], 
            $data['description'], 
            $data['permissions'], 
            $data['status'], 
            $id
        );
        
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    /**
     * Actualizar solo permisos
     */
    public function updatePermissions($id, $permissions) {
        $query = "UPDATE roles SET permissions = ? WHERE id = ? AND is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $permissions, $id);
        
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    /**
     * Eliminar rol (soft delete)
     */
    public function delete($id) {
        $query = "UPDATE roles SET is_deleted = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    /**
     * Restaurar rol eliminado
     */
    public function restore($id) {
        $query = "UPDATE roles SET is_deleted = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    /**
     * Verificar si el nombre del rol ya existe
     */
    public function nameExists($name, $excludeId = null) {
        $whereClause = $excludeId ? "AND id != ?" : "";
        $query = "SELECT id FROM roles WHERE name = ? AND is_deleted = 0 {$whereClause}";
        $stmt = $this->conn->prepare($query);
        
        if ($excludeId) {
            $stmt->bind_param("si", $name, $excludeId);
        } else {
            $stmt->bind_param("s", $name);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        
        return $exists;
    }
    
    /**
     * Obtener roles activos para select
     */
    public function getActiveForSelect() {
        $query = "SELECT id, name FROM roles WHERE status = 'ACTIVO' AND is_deleted = 0 ORDER BY name ASC";
        $result = $this->conn->query($query);
        
        if (!$result) {
            return [];
        }
        
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
        
        return $roles;
    }
    
    /**
     * Contar usuarios que usan un rol específico
     */
    public function countUsersByRole($roleName) {
        $query = "SELECT COUNT(*) as count FROM users WHERE role = ? AND is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $roleName);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        $stmt->close();
        
        return $count;
    }
    
    /**
     * Obtener estadísticas de roles
     */
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_roles,
                    SUM(CASE WHEN status = 'ACTIVO' THEN 1 ELSE 0 END) as active_roles,
                    SUM(CASE WHEN status = 'INACTIVO' THEN 1 ELSE 0 END) as inactive_roles,
                    SUM(CASE WHEN is_deleted = 1 THEN 1 ELSE 0 END) as deleted_roles
                  FROM roles";
        
        $result = $this->conn->query($query);
        
        if (!$result) {
            return false;
        }
        
        return $result->fetch_assoc();
    }
    
    /**
     * Buscar roles por término
     */
    public function search($term) {
        $searchTerm = "%{$term}%";
        $query = "SELECT * FROM roles WHERE (name LIKE ? OR description LIKE ?) AND is_deleted = 0 ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
        
        $stmt->close();
        return $roles;
    }
    
    /**
     * Obtener roles con paginación
     */
    public function getPaginated($page = 1, $perPage = 10, $search = '') {
        $offset = ($page - 1) * $perPage;
        
        // Construir consulta base
        $whereClause = "WHERE is_deleted = 0";
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $whereClause .= " AND (name LIKE ? OR description LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }
        
        // Consulta para contar total
        $countQuery = "SELECT COUNT(*) as total FROM roles {$whereClause}";
        if (!empty($params)) {
            $countStmt = $this->conn->prepare($countQuery);
            $countStmt->bind_param($types, ...$params);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $total = $countResult->fetch_assoc()['total'];
            $countStmt->close();
        } else {
            $countResult = $this->conn->query($countQuery);
            $total = $countResult->fetch_assoc()['total'];
        }
        
        // Consulta para datos
        $dataQuery = "SELECT * FROM roles {$whereClause} ORDER BY name ASC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $this->conn->prepare($dataQuery);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
        
        $stmt->close();
        
        return [
            'data' => $roles,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page,
            'per_page' => $perPage
        ];
    }
}
?>
