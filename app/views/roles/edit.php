<?php
// La conexión $conn ya está disponible desde el index.php principal
global $conn;

// Debug: Verificar que la conexión esté disponible
if (!isset($conn)) {
    error_log("Error en roles/edit.php: Variable \$conn no está disponible");
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

<div class="role-edit">
    <div class="edit-header">
        <h2 class="page-title">
            <i class="fa fa-edit"></i> Editar Rol: <?php echo htmlspecialchars($role['name']); ?>
        </h2>
    </div>

    <form action="/Recursos/index.php?route=roles.update" method="post" class="edit-form">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($role['id']); ?>">
        
        <div class="form-section">
            <h3><i class="fa fa-info-circle"></i> Información Básica</h3>
            <div class="grid-2">
                <div class="form-row">
                    <label for="name">Nombre del Rol *</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($role['name']); ?>" maxlength="50" required>
                    <small class="helper-text">Nombre único para identificar el rol</small>
                </div>
                <div class="form-row">
                    <label for="status">Estado *</label>
                    <select id="status" name="status" required>
                        <option value="ACTIVO" <?php echo $role['status'] === 'ACTIVO' ? 'selected' : ''; ?>>ACTIVO</option>
                        <option value="INACTIVO" <?php echo $role['status'] === 'INACTIVO' ? 'selected' : ''; ?>>INACTIVO</option>
                    </select>
                    <small class="helper-text">Estado actual del rol en el sistema</small>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3><i class="fa fa-file-text"></i> Descripción</h3>
            <div class="form-row">
                <label for="description">Descripción del Rol</label>
                <textarea id="description" name="description" rows="3" maxlength="500" placeholder="Describe las responsabilidades y alcance de este rol"><?php echo htmlspecialchars($role['description'] ?? ''); ?></textarea>
                <small class="helper-text">Descripción opcional del rol (máximo 500 caracteres)</small>
            </div>
        </div>

        <div class="form-section">
            <h3><i class="fa fa-key"></i> Permisos del Sistema</h3>
            <div class="form-row">
                <label for="permissions">Permisos</label>
                <textarea id="permissions" name="permissions" rows="4" maxlength="500" placeholder="Separar permisos con comas (ej: VIEW_OWN_DATA,VIEW_SALES,EDIT_USERS)"><?php echo htmlspecialchars($role['permissions'] ?? ''); ?></textarea>
                <small class="helper-text">Lista de permisos separados por comas. Usar 'ALL' para acceso completo</small>
            </div>
            
            <div class="permissions-help">
                <h4>Permisos Disponibles:</h4>
                <div class="permissions-grid">
                    <div class="permission-group">
                        <strong>Usuarios:</strong>
                        <span class="permission-example">VIEW_USERS, EDIT_USERS, DELETE_USERS</span>
                    </div>
                    <div class="permission-group">
                        <strong>Personal:</strong>
                        <span class="permission-example">VIEW_PERSONAL, EDIT_PERSONAL, DELETE_PERSONAL</span>
                    </div>
                    <div class="permission-group">
                        <strong>Reportes:</strong>
                        <span class="permission-example">VIEW_REPORTS, GENERATE_REPORTS</span>
                    </div>
                    <div class="permission-group">
                        <strong>Configuración:</strong>
                        <span class="permission-example">VIEW_CONFIG, EDIT_CONFIG</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-actions">
            <button type="button" class="button btn-secondary" onclick="window.parent.hideDynamicModal()">
                <i class="fa fa-times"></i> Cancelar
            </button>
            <button type="submit" class="button btn-success">
                <i class="fa fa-save"></i> Guardar Cambios
            </button>
        </div>
    </form>
</div>

<style>
.role-edit {
    padding: 1rem;
    max-width: none;
    width: 100%;
    margin: 0;
}

.edit-header {
    margin-bottom: 2rem;
    text-align: center;
}

.page-title {
    margin: 0;
    color: #374151;
    font-size: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.page-title i {
    color: #667eea;
}

.edit-form {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.form-section {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.form-section:last-of-type {
    border-bottom: none;
}

.form-section h3 {
    margin: 0 0 1rem 0;
    color: #374151;
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-section h3 i {
    color: #667eea;
}

.grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.form-row {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-row.full-width {
    grid-column: 1 / -1;
}

.form-row label {
    font-weight: 500;
    color: #374151;
    font-size: 0.9rem;
}

.form-row input,
.form-row select,
.form-row textarea {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.9rem;
    transition: border-color 0.2s ease;
}

.form-row input:focus,
.form-row select:focus,
.form-row textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-row textarea {
    resize: vertical;
    min-height: 80px;
}

.helper-text {
    color: #6b7280;
    font-size: 0.8rem;
    font-style: italic;
}

.permissions-help {
    margin-top: 1rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.permissions-help h4 {
    margin: 0 0 0.75rem 0;
    color: #374151;
    font-size: 0.9rem;
    font-weight: 600;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.75rem;
}

.permission-group {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.permission-group strong {
    color: #374151;
    font-size: 0.8rem;
}

.permission-example {
    color: #6b7280;
    font-size: 0.75rem;
    font-family: monospace;
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    padding: 1.5rem;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    border-radius: 0 0 8px 8px;
}

@media (max-width: 768px) {
    .grid-2 {
        grid-template-columns: 1fr;
    }
    
    .permissions-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.edit-form');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Mostrar spinner
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Guardando...';
        submitBtn.disabled = true;
        
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes('success') || data.includes('exitoso')) {
                // Mostrar mensaje de éxito
                const successMsg = document.createElement('div');
                successMsg.className = 'success-message';
                successMsg.innerHTML = '<i class="fa fa-check-circle"></i> Rol actualizado correctamente';
                successMsg.style.cssText = 'background: #10b981; color: white; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; text-align: center;';
                
                form.insertBefore(successMsg, form.firstChild);
                
                // Actualizar tabla en la ventana padre
                if (window.parent.updateRolesTable) {
                    window.parent.updateRolesTable();
                }
                
                // Cerrar modal después de un delay
                setTimeout(() => {
                    window.parent.hideDynamicModal();
                }, 1500);
            } else {
                // Mostrar mensaje de error
                const errorMsg = document.createElement('div');
                errorMsg.className = 'error-message';
                errorMsg.innerHTML = '<i class="fa fa-exclamation-circle"></i> Error al actualizar el rol';
                errorMsg.style.cssText = 'background: #ef4444; color: white; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; text-align: center;';
                
                form.insertBefore(errorMsg, form.firstChild);
                
                // Remover mensaje después de 5 segundos
                setTimeout(() => {
                    if (errorMsg.parentElement) {
                        errorMsg.remove();
                    }
                }, 5000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            const errorMsg = document.createElement('div');
            errorMsg.className = 'error-message';
            errorMsg.innerHTML = '<i class="fa fa-exclamation-circle"></i> Error de conexión';
            errorMsg.style.cssText = 'background: #ef4444; color: white; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; text-align: center;';
            
            form.insertBefore(errorMsg, form.firstChild);
            
            setTimeout(() => {
                if (errorMsg.parentElement) {
                    errorMsg.remove();
                }
            }, 5000);
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
});
</script>
