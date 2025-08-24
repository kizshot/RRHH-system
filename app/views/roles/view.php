<?php
// La conexión $conn ya está disponible desde el index.php principal
global $conn;

// Debug: Verificar que la conexión esté disponible
if (!isset($conn)) {
    error_log("Error en roles/view.php: Variable \$conn no está disponible");
    echo '<div class="error-message">Error: Conexión a la base de datos no disponible</div>';
    exit;
}

// Obtener el ID del rol desde la URL
$roleId = $_GET['id'] ?? null;

if (!$roleId) {
    echo '<div class="error-message">ID de rol no especificado</div>';
    exit;
}

// Obtener datos del rol
try {
    $query = "SELECT id, name, description, permissions, status, created_at FROM roles WHERE id = ? AND is_deleted = 0";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conn->error);
    }
    
    $stmt->bind_param("i", $roleId);
    $stmt->execute();
    $result = $stmt->get_result();
    $role = $result->fetch_assoc();

    if (!$role) {
        echo '<div class="error-message">Rol no encontrado</div>';
        exit;
    }

    $stmt->close();
} catch (Exception $e) {
    echo '<div class="error-message">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit;
}
?>

<div class="role-view">
    <div class="role-header">
        <div class="role-icon">
            <i class="fa fa-user-shield"></i>
        </div>
        <div class="role-info">
            <h2 class="page-title"><?php echo htmlspecialchars($role['name']); ?></h2>
            <div class="role-meta">
                <span class="role-id">ID: <?php echo htmlspecialchars($role['id']); ?></span>
                <span class="role-status status-<?php echo strtolower($role['status']); ?>">
                    <?php echo htmlspecialchars($role['status']); ?>
                </span>
            </div>
        </div>
    </div>

    <div class="role-content">
        <div class="info-section">
            <h3><i class="fa fa-info-circle"></i> Información del Rol</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value"><?php echo htmlspecialchars($role['name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Estado:</span>
                    <span class="info-value">
                        <span class="status-badge status-<?php echo strtolower($role['status']); ?>">
                            <?php echo htmlspecialchars($role['status']); ?>
                        </span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fecha de Creación:</span>
                    <span class="info-value"><?php echo date('d/m/Y H:i:s', strtotime($role['created_at'])); ?></span>
                </div>
            </div>
        </div>

        <div class="info-section">
            <h3><i class="fa fa-file-text"></i> Descripción</h3>
            <div class="description-content">
                <?php if ($role['description']): ?>
                    <p><?php echo nl2br(htmlspecialchars($role['description'])); ?></p>
                <?php else: ?>
                    <p class="no-description">Sin descripción disponible</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="info-section">
            <h3><i class="fa fa-key"></i> Permisos</h3>
            <div class="permissions-content">
                <?php if ($role['permissions']): ?>
                    <div class="permissions-list">
                        <?php 
                        $permissions = explode(',', $role['permissions']);
                        foreach ($permissions as $permission): 
                            $permission = trim($permission);
                            if ($permission === 'ALL') {
                                echo '<span class="permission-badge permission-all">TODOS LOS PERMISOS</span>';
                                break;
                            }
                        ?>
                            <span class="permission-badge"><?php echo htmlspecialchars($permission); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-permissions">Sin permisos asignados</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.role-view {
    padding: 1rem;
    max-width: none;
    width: 100%;
    margin: 0;
}

.role-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    color: white;
}

.role-icon {
    font-size: 3rem;
    opacity: 0.9;
}

.role-info {
    flex-grow: 1;
}

.page-title {
    margin: 0 0 0.5rem 0;
    font-size: 1.8rem;
    font-weight: 600;
}

.role-meta {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.role-id {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.9rem;
}

.role-status {
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 500;
}

.status-activo {
    background: #10b981;
    color: white;
}

.status-inactivo {
    background: #6b7280;
    color: white;
}

.role-content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.info-section {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.info-section h3 {
    margin: 0 0 1rem 0;
    color: #374151;
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-section h3 i {
    color: #667eea;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-label {
    font-weight: 500;
    color: #6b7280;
    font-size: 0.9rem;
}

.info-value {
    color: #374151;
    font-weight: 500;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    text-transform: uppercase;
}

.description-content, .permissions-content {
    color: #374151;
    line-height: 1.6;
}

.no-description, .no-permissions {
    color: #9ca3af;
    font-style: italic;
}

.permissions-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.permission-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    color: #374151;
}

.permission-all {
    background: #dbeafe;
    border-color: #3b82f6;
    color: #1e40af;
    font-weight: 600;
}

@media (max-width: 768px) {
    .role-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .role-meta {
        justify-content: center;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
// Cerrar la conexión si es necesario
if (isset($stmt)) {
    $stmt->close();
}
?>
