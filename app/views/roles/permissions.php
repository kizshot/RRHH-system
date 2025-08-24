<?php
// La conexión $conn ya está disponible desde el index.php principal
global $conn;

// Debug: Verificar que la conexión esté disponible
if (!isset($conn)) {
    error_log("Error en roles/permissions.php: Variable \$conn no está disponible");
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
    $query = "SELECT id, name, description, permissions, status FROM roles WHERE id = ? AND is_deleted = 0";
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

// Obtener permisos actuales del rol
$currentPermissions = [];
if ($role['permissions']) {
    $currentPermissions = array_map('trim', explode(',', $role['permissions']));
}

// Lista de módulos disponibles en el sistema
$modules = [
    'personal' => 'Personal',
    'jornadas' => 'Jornadas',
    'pagos' => 'Pagos',
    'adelantos' => 'Adelantos',
    'horas_extras' => 'Horas Extras',
    'vacaciones' => 'Vacaciones',
    'asistencia' => 'Asistencia',
    'reportes' => 'Reportes',
    'turnos' => 'Turnos',
    'panel' => 'Panel',
    'resumen_general' => 'Resumen G.',
    'empresa' => 'Empresa',
    'permisos' => 'Permisos',
    'usuarios' => 'Usuarios',
    'roles' => 'Roles'
];
?>

<div class="permissions-view">
    <div class="permissions-header">
        <h2 class="page-title">
            <i class="fa fa-cog"></i> Accesos Usuarios: <?php echo htmlspecialchars($role['name']); ?>
        </h2>
        <p class="permissions-description">
            Gestiona los permisos de acceso para el rol <strong><?php echo htmlspecialchars($role['name']); ?></strong>
        </p>
    </div>

    <form action="/Recursos/index.php?route=roles.updatePermissions" method="post" class="permissions-form">
        <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($role['id']); ?>">
        
        <div class="modules-section">
            <h3><i class="fa fa-list"></i> Módulos del Sistema</h3>
            <div class="modules-grid">
                <?php foreach ($modules as $moduleKey => $moduleName): ?>
                <div class="module-item">
                    <div class="module-info">
                        <span class="module-name"><?php echo htmlspecialchars($moduleName); ?></span>
                        <div class="module-actions">
                            <label class="toggle-switch">
                                <input type="checkbox" 
                                       name="permissions[]" 
                                       value="<?php echo strtoupper($moduleKey); ?>"
                                       <?php echo in_array(strtoupper($moduleKey), $currentPermissions) || in_array('ALL', $currentPermissions) ? 'checked' : ''; ?>
                                       <?php echo in_array('ALL', $currentPermissions) ? 'disabled' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="special-permissions">
            <h3><i class="fa fa-star"></i> Permisos Especiales</h3>
            <div class="special-grid">
                <div class="module-item special">
                    <div class="module-info">
                        <span class="module-name">Acceso Completo</span>
                        <small class="module-description">Otorga todos los permisos del sistema</small>
                        <div class="module-actions">
                            <label class="toggle-switch">
                                <input type="checkbox" 
                                       name="permissions[]" 
                                       value="ALL"
                                       <?php echo in_array('ALL', $currentPermissions) ? 'checked' : ''; ?>
                                       id="all-permissions">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="permissions-summary">
            <h3><i class="fa fa-info-circle"></i> Resumen de Permisos</h3>
            <div class="summary-content">
                <p><strong>Permisos activos:</strong> <span id="active-permissions-count">0</span></p>
                <p><strong>Total de módulos:</strong> <?php echo count($modules); ?></p>
                <div id="permissions-list" class="permissions-list">
                    <!-- Se llena dinámicamente -->
                </div>
            </div>
        </div>

        <div class="modal-actions">
            <button type="button" class="button btn-secondary" onclick="window.parent.hideDynamicModal()">
                <i class="fa fa-times"></i> Cerrar
            </button>
            <button type="submit" class="button btn-primary">
                <i class="fa fa-save"></i> Guardar
            </button>
        </div>
    </form>
</div>

<style>
.permissions-view {
    padding: 1rem;
    max-width: none;
    width: 100%;
    margin: 0;
}

.permissions-header {
    text-align: center;
    margin-bottom: 2rem;
}

.page-title {
    margin: 0 0 0.5rem 0;
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

.permissions-description {
    color: #6b7280;
    margin: 0;
}

.permissions-form {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.modules-section, .special-permissions, .permissions-summary {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.modules-section:last-of-type, .special-permissions:last-of-type, .permissions-summary:last-of-type {
    border-bottom: none;
}

.modules-section h3, .special-permissions h3, .permissions-summary h3 {
    margin: 0 0 1rem 0;
    color: #374151;
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modules-section h3 i, .special-permissions h3 i, .permissions-summary h3 i {
    color: #667eea;
}

.modules-grid, .special-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.module-item {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 1rem;
    transition: all 0.2s ease;
}

.module-item:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.module-item.special {
    background: #dbeafe;
    border-color: #3b82f6;
}

.module-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.module-name {
    font-weight: 500;
    color: #374151;
    flex-grow: 1;
}

.module-description {
    color: #6b7280;
    font-size: 0.8rem;
    font-style: italic;
}

.module-actions {
    flex-shrink: 0;
}

/* Toggle Switch */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: #667eea;
}

input:checked + .toggle-slider:before {
    transform: translateX(26px);
}

input:disabled + .toggle-slider {
    background-color: #10b981;
    cursor: not-allowed;
}

.permissions-summary {
    background: #f9fafb;
}

.summary-content {
    color: #374151;
}

.summary-content p {
    margin: 0.5rem 0;
}

.permissions-list {
    margin-top: 1rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.permission-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: #e0e7ff;
    border: 1px solid #c7d2fe;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    color: #3730a3;
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
    .modules-grid, .special-grid {
        grid-template-columns: 1fr;
    }
    
    .module-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .module-actions {
        align-self: flex-end;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.permissions-form');
    const allPermissionsCheckbox = document.getElementById('all-permissions');
    const moduleCheckboxes = document.querySelectorAll('input[name="permissions[]"]:not([value="ALL"])');
    const activePermissionsCount = document.getElementById('active-permissions-count');
    const permissionsList = document.getElementById('permissions-list');
    
    // Función para actualizar el resumen de permisos
    function updatePermissionsSummary() {
        const checkedPermissions = Array.from(document.querySelectorAll('input[name="permissions[]"]:checked'))
            .map(cb => cb.value);
        
        const activeCount = checkedPermissions.length;
        activePermissionsCount.textContent = activeCount;
        
        // Actualizar lista de permisos
        permissionsList.innerHTML = '';
        checkedPermissions.forEach(permission => {
            const badge = document.createElement('span');
            badge.className = 'permission-badge';
            badge.textContent = permission;
            permissionsList.appendChild(badge);
        });
    }
    
    // Función para manejar el checkbox de "Acceso Completo"
    function handleAllPermissionsChange() {
        const isAllChecked = allPermissionsCheckbox.checked;
        
        moduleCheckboxes.forEach(checkbox => {
            checkbox.checked = isAllChecked;
            checkbox.disabled = isAllChecked;
        });
        
        updatePermissionsSummary();
    }
    
    // Función para manejar cambios en módulos individuales
    function handleModuleChange() {
        if (allPermissionsCheckbox.checked) {
            allPermissionsCheckbox.checked = false;
        }
        updatePermissionsSummary();
    }
    
    // Event listeners
    allPermissionsCheckbox.addEventListener('change', handleAllPermissionsChange);
    
    moduleCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', handleModuleChange);
    });
    
        // Manejar envío del formulario
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
        .then(response => {
          // Intentar parsear como JSON primero
          const contentType = response.headers.get('content-type');
          if (contentType && contentType.includes('application/json')) {
            return response.json();
          } else {
            return response.text().then(text => {
              // Si no es JSON, intentar extraer información del texto
              if (text.includes('success') || text.includes('exitoso')) {
                return { success: true, message: 'Permisos actualizados correctamente' };
              } else if (text.includes('error') || text.includes('Error')) {
                return { error: 'Error al actualizar los permisos' };
              } else {
                return { success: true, message: 'Permisos actualizados correctamente' };
              }
            });
          }
        })
        .then(data => {
          if (data.success) {
            // Mostrar mensaje de éxito
            const successMsg = document.createElement('div');
            successMsg.className = 'success-message';
            successMsg.innerHTML = `<i class="fa fa-check-circle"></i> ${data.message || 'Permisos actualizados correctamente'}`;
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
            errorMsg.innerHTML = `<i class="fa fa-exclamation-circle"></i> ${data.error || 'Error al actualizar los permisos'}`;
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
    
    // Inicializar resumen
    updatePermissionsSummary();
});
</script>
