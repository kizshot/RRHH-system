<?php
// La conexión $conn ya está disponible desde el index.php principal
global $conn;

// Verificar si la conexión existe
if (!$conn) {
    die("Error: No hay conexión a la base de datos");
}

// Obtener roles desde la base de datos
$query = "SELECT id, name, description, permissions, status, created_at FROM roles WHERE is_deleted = 0 ORDER BY id ASC";
$result = $conn->query($query);
$roles = [];

if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
}

// Incluir el header
include __DIR__ . '/../layout/header.php';
// Incluir el sidebar
include __DIR__ . '/../layout/sidebar.php';
?>

<div class="page-content">
    <div class="container">
        <!-- Header con título y botón de crear -->
        <div class="header-section">
            <div class="header-left">
                <button class="button btn-primary" onclick="showModal()">
                    <i class="fa fa-plus"></i> Nuevo Rol
                </button>
            </div>
            <div class="header-center">
                <h1>Gestión de Roles - 2024</h1>
            </div>
            <div class="header-right">
                <div class="search-container">
                    <input type="text" id="searchInput" placeholder="Ingresar dato a buscar" class="search-input">
                    <i class="fa fa-search search-icon"></i>
                </div>
            </div>
        </div>

        <!-- Tabla de roles -->
        <div class="table-container">
            <table class="data-table" id="roles-table">
                <thead>
                    <tr>
                        <th class="sortable">
                            Id <i class="fa fa-sort"></i>
                        </th>
                        <th class="sortable">
                            Nombre Rol <i class="fa fa-sort"></i>
                        </th>
                        <th class="sortable">
                            Fecha creación <i class="fa fa-sort"></i>
                        </th>
                        <th class="sortable">
                            Fecha actualización <i class="fa fa-sort"></i>
                        </th>
                        <th class="sortable">
                            Estado <i class="fa fa-sort"></i>
                        </th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $role): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($role['id']); ?></td>
                        <td><?php echo htmlspecialchars($role['name']); ?></td>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($role['created_at'])); ?></td>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($role['created_at'])); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($role['status']); ?>">
                                <?php echo $role['status']; ?>
                            </span>
                        </td>
                        <td class="action-buttons">
                            <button class="button btn-info" onclick="showDynamicModal('/Recursos/index.php?route=roles.view&id=<?php echo $role['id']; ?>', 'Ver Rol')">
                                <i class="fa fa-eye"></i> Ver
                            </button>
                            <button class="button btn-warning" onclick="showDynamicModal('/Recursos/index.php?route=roles.edit&id=<?php echo $role['id']; ?>', 'Editar Rol')">
                                <i class="fa fa-edit"></i> Editar
                            </button>
                            <button class="button btn-secondary" onclick="showDynamicModal('/Recursos/index.php?route=roles.permissions&id=<?php echo $role['id']; ?>', 'Accesos Usuarios')">
                                <i class="fa fa-cog"></i> Accesos
                            </button>
                            <button class="button btn-danger" onclick="showDeleteModal(<?php echo $role['id']; ?>, '<?php echo htmlspecialchars($role['name']); ?>')">
                                <i class="fa fa-trash"></i> Remover
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="pagination-container">
            <div class="pagination-info">
                Mostrando 1 a <?php echo count($roles); ?> de <?php echo count($roles); ?> entradas
            </div>
            <div class="pagination-controls">
                <button class="pagination-btn" disabled>Previous</button>
                <button class="pagination-btn active">1</button>
                <?php if (count($roles) > 5): ?>
                <button class="pagination-btn">2</button>
                <?php endif; ?>
                <button class="pagination-btn" <?php echo count($roles) <= 5 ? 'disabled' : ''; ?>>Next</button>
            </div>
        </div>
      </div>

      <!-- Modal overlay para todos los modales -->
      <div id="modal-overlay" class="modal-overlay"></div>

      <!-- Modal crear rol -->
      <div class="modal modal-form" id="modal-create" role="dialog" aria-modal="true" aria-labelledby="modalCreateTitle">
        <div class="modal-content">
          <header class="modal-header">
            <h3 id="modalCreateTitle" class="modal-title">
              <i class="fa fa-user-plus"></i> Crear Rol
            </h3>
            <button class="modal-close" onclick="hideModal()" title="Cerrar">
              <i class="fa fa-times"></i>
            </button>
          </header>
          <div class="modal-body">
            <form action="/Recursos/index.php?route=roles.create" method="post">
              <div class="form-grid-2">
                <div class="form-row">
                  <label for="name">Nombre del Rol</label>
                  <input class="input" type="text" id="name" name="name" maxlength="50" required>
                </div>
                <div class="form-row">
                  <label for="status">Estado</label>
                  <select class="input" id="status" name="status">
                    <option value="ACTIVO">ACTIVO</option>
                    <option value="INACTIVO">INACTIVO</option>
                  </select>
                </div>
                <div class="form-row full-width">
                  <label for="description">Descripción</label>
                  <textarea class="input" id="description" name="description" rows="3" maxlength="500"></textarea>
                </div>
              </div>
              <div class="modal-actions">
                <button type="button" class="button btn-secondary" onclick="hideModal()">
                  <i class="fa fa-times"></i> Cancelar
                </button>
                <button class="button btn-success" type="submit">
                  <i class="fa fa-save"></i> Crear Rol
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Modal de confirmación para eliminar -->
      <div class="modal modal-form" id="modal-delete" role="dialog" aria-modal="true" aria-labelledby="modalDeleteTitle">
        <div class="modal-content">
          <header class="modal-header">
            <h3 id="modalDeleteTitle" class="modal-title">
              <i class="fa fa-exclamation-triangle"></i> Confirmar Eliminación
            </h3>
            <button class="modal-close" onclick="hideDeleteModal()" title="Cerrar">
              <i class="fa fa-times"></i>
            </button>
          </header>
          <div class="modal-body">
            <div class="modal-icon">
              <i class="fa fa-exclamation-triangle"></i>
            </div>
            <div class="modal-message">¿Estás seguro de que quieres eliminar este rol?</div>
            <div class="modal-description">
              <strong id="delete-rolename"></strong><br>
              Esta acción no se puede deshacer y se eliminarán todos los datos asociados al rol.
            </div>
            <div class="modal-actions">
              <button type="button" class="button btn-secondary" onclick="hideDeleteModal()">
                <i class="fa fa-times"></i> Cancelar
              </button>
              <form id="delete-form" action="/Recursos/index.php?route=roles.delete" method="post" style="display:inline">
                <input type="hidden" name="id" id="delete-role-id">
                <button type="submit" class="button btn-danger">
                  <i class="fa fa-trash"></i> Eliminar Rol
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal dinámico para contenido -->
      <div class="modal modal-dynamic" id="modal-dynamic" role="dialog" aria-modal="true">
        <div class="modal-content">
          <header class="modal-header">
            <h3 id="modalDynamicTitle" class="modal-title"></h3>
            <button class="modal-close" onclick="hideDynamicModal()" title="Cerrar">
              <i class="fa fa-times"></i>
            </button>
          </header>
          <div class="modal-body">
            <iframe id="modal-iframe" src="" frameborder="0"></iframe>
          </div>
        </div>
      </div>

<style>
/* Estilos específicos para la página de roles */
.header-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1.5rem 2rem;
  border-radius: 12px;
  margin-bottom: 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
}

.header-left .button {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.header-left .button:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

.header-center h1 {
  margin: 0;
  font-size: 1.8rem;
  font-weight: 600;
  text-align: center;
  flex-grow: 1;
}

.search-container {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border-radius: 8px;
  width: 300px;
  transition: all 0.3s ease;
}

.search-input::placeholder {
  color: rgba(255, 255, 255, 0.7);
}

.search-input:focus {
  background: rgba(255, 255, 255, 0.3);
  outline: none;
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.search-icon {
  position: absolute;
  left: 1rem;
  color: rgba(255, 255, 255, 0.7);
}

/* Estilos para la tabla de roles */
.table-container {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
  margin-bottom: 2rem;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
}

.data-table thead {
  background: #f8fafc;
  border-bottom: 2px solid #e5e7eb;
}

.data-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 1px solid #e5e7eb;
}

.data-table th.sortable {
  cursor: pointer;
  user-select: none;
  transition: color 0.2s ease;
}

.data-table th.sortable:hover {
  color: #667eea;
}

.data-table th.sortable i {
  margin-left: 0.5rem;
  color: #9ca3af;
  transition: color 0.2s ease;
}

.data-table th.sortable:hover i {
  color: #667eea;
}

.data-table tbody tr {
  transition: background-color 0.2s ease;
  border-bottom: 1px solid #f3f4f6;
}

.data-table tbody tr:hover {
  background-color: #f9fafb;
}

.data-table td {
  padding: 1rem;
  color: #374151;
  font-size: 0.875rem;
  vertical-align: middle;
}

.data-table .action-buttons {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  align-items: center;
}

.data-table .action-buttons .button {
  padding: 0.5rem 0.75rem;
  font-size: 0.75rem;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.data-table .action-buttons .button:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Estilos específicos para los botones de acción */
.btn-info {
  background: #0ea5e9 !important;
  color: white !important;
  border: 1px solid #0284c7 !important;
}

.btn-warning {
  background: #f59e0b !important;
  color: white !important;
  border: 1px solid #d97706 !important;
}

.btn-secondary {
  background: #6b7280 !important;
  color: white !important;
  border: 1px solid #4b5563 !important;
}

.btn-danger {
  background: #ef4444 !important;
  color: white !important;
  border: 1px solid #dc2626 !important;
}

.btn-info:hover {
  background: #0284c7 !important;
  border-color: #0369a1 !important;
}

.btn-warning:hover {
  background: #d97706 !important;
  border-color: #b45309 !important;
}

.btn-secondary:hover {
  background: #4b5563 !important;
  border-color: #374151 !important;
}

.btn-danger:hover {
  background: #dc2626 !important;
  border-color: #b91c1c !important;
}

/* Badges de estado */
.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.status-activo {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #a7f3d0;
}

.status-inactivo {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

/* Paginación */
.pagination-container {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.pagination-info {
  color: #6b7280;
  font-size: 0.875rem;
}

.pagination-controls {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.pagination-btn {
  padding: 0.5rem 1rem;
  border: 1px solid #d1d5db;
  background: white;
  color: #374151;
  border-radius: 6px;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: #f9fafb;
  border-color: #9ca3af;
}

.pagination-btn.active {
  background: #667eea;
  color: white;
  border-color: #667eea;
}

.pagination-btn:disabled {
  background: #f3f4f6;
  color: #9ca3af;
  cursor: not-allowed;
  border-color: #e5e7eb;
}

/* Estilos para el modal dinámico - ajuste automático de tamaño */
#modal-dynamic {
  display: none !important;
  align-items: flex-start !important;
  justify-content: center !important;
  padding: 3rem 2rem !important;
}

#modal-dynamic.show {
  display: flex !important;
}

                #modal-dynamic .modal-content {
                  max-width: 98vw !important;
                  max-height: 98vh !important;
                  width: auto !important;
                  height: auto !important;
                  min-width: 900px !important;
                  min-height: 700px !important;
                  margin: auto !important;
                  overflow: visible !important;
                  transition: all 0.3s ease !important;
                }

#modal-dynamic .modal-body {
  padding: 0 !important;
  overflow: visible !important;
  height: auto !important;
}

                #modal-dynamic #modal-iframe {
                  width: 100% !important;
                  height: auto !important;
                  min-height: 700px !important;
                  border: none !important;
                  display: block !important;
                  overflow: visible !important;
                  transition: height 0.3s ease !important;
                }

/* Sobrescribir estilos generales del modal para el modal dinámico */
#modal-dynamic.modal {
  overflow: visible !important;
}

#modal-dynamic .modal-content {
  overflow: visible !important;
  max-height: none !important;
}

/* Estilos específicos para los modales de roles */
.role-view, .role-edit, .permissions-view {
  max-width: none !important;
  width: 100% !important;
  margin: 0 !important;
  padding: 1rem !important;
}

/* Asegurar que el contenido se ajuste al modal */
#modal-dynamic .modal-body {
  max-height: none !important;
  overflow: visible !important;
}

/* Responsive para el modal dinámico */
@media (max-width: 768px) {
                    #modal-dynamic {
                    padding: 2rem 1rem !important;
                  }
  
                    #modal-dynamic .modal-content {
                    min-width: 98vw !important;
                    margin: 1rem !important;
                  }
  
                    #modal-dynamic #modal-iframe {
                    min-height: 800px !important;
                  }
}

/* Estilos para campos de solo lectura */
input[readonly], textarea[readonly] {
  background-color: #f8f9fa !important;
  color: #6c757d !important;
  cursor: not-allowed !important;
  border-color: #dee2e6 !important;
}

.helper-text {
  color: #6c7280 !important;
  font-size: 0.8rem !important;
  font-style: italic !important;
  margin-top: 0.25rem !important;
  display: block !important;
}

/* Asegurar que el overlay esté oculto por defecto */
#modal-overlay {
  display: none !important;
  opacity: 0 !important;
  visibility: hidden !important;
}

#modal-overlay.show {
  display: block !important;
  opacity: 1 !important;
  visibility: visible !important;
}

/* Estilos para las notificaciones */
.notification {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  color: white;
  font-weight: 500;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  animation: slideInRight 0.3s ease-out;
  max-width: 400px;
}

.notification-success {
  background: #10b981;
  border-left: 4px solid #059669;
}

.notification-error {
  background: #ef4444;
  border-left: 4px solid #dc2626;
}

.notification-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.notification-close {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  font-size: 1.2rem;
  opacity: 0.8;
  transition: opacity 0.2s ease;
}

.notification-close:hover {
  opacity: 1;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Asegurar que todos los modales estén ocultos por defecto */
.modal {
  display: none !important;
}

.modal.show {
  display: flex !important;
}

/* Estilos específicos para el modal de eliminación */
#modal-delete .modal-body {
  text-align: center;
  padding: 2rem;
}

#modal-delete .modal-icon {
  font-size: 4rem;
  margin-bottom: 1.5rem;
  color: #ef4444;
}

#modal-delete .modal-message {
  font-size: 1.25rem;
  margin-bottom: 1rem;
  color: #374151;
  font-weight: 600;
}

#modal-delete .modal-description {
  color: #6b7280;
  font-size: 1rem;
  line-height: 1.5;
  margin-bottom: 2rem;
}

#modal-delete .modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
  padding: 1rem 0;
  border-top: 1px solid #e5e7eb;
}

/* Responsive */
@media (max-width: 768px) {
  .header-section {
    flex-direction: column;
    text-align: center;
  }
  
  .header-center h1 {
    order: -1;
    margin-bottom: 1rem;
  }
  
  .search-input {
    width: 100%;
    max-width: 300px;
  }
}
</style>

<script src="/Recursos/assets/js/main.js"></script>
<script>
(function(){
  console.log('Script de roles cargado');
  
  // Modal crear rol
  var openBtn = document.querySelector('[onclick="showModal()"]');
  var modal = document.getElementById('modal-create');
  var overlay = document.getElementById('modal-overlay');

  function showModal(){ 
    modal.classList.add('show');
    overlay.classList.add('show');
    document.body.style.overflow = 'hidden'; // Prevenir scroll del body
  }
  function hideModal(){ 
    modal.classList.remove('show');
    overlay.classList.remove('show');
    document.body.style.overflow = ''; // Restaurar scroll del body
  }
  openBtn && openBtn.addEventListener('click', showModal);
  overlay && overlay.addEventListener('click', function(e){ 
    if(e.target === overlay) hideModal(); 
  });
  
  // Cerrar modal con tecla Escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modal.classList.contains('show')) {
      hideModal();
    }
  });

  // Modal dinámico para Ver/Editar/Accesos
  var modalDynamic = document.getElementById('modal-dynamic');
  var modalIframe = document.getElementById('modal-iframe');
  var modalTitle = document.getElementById('modalDynamicTitle');
  var btnCloseDyn = document.querySelector('.modal-close'); // Assuming the close button is the modal-close class

  function showDynamicModal(url, title) {
    modalIframe.src = url;
    modalTitle.innerHTML = '<i class="fa fa-window-restore"></i> ' + title;
    modalDynamic.classList.add('show');
    overlay.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    // Ajustar tamaño del modal después de cargar el contenido
    modalIframe.onload = function() {
      try {
        // Obtener el contenido del iframe
        const iframeDoc = modalIframe.contentDocument || modalIframe.contentWindow.document;
        const iframeBody = iframeDoc.body;
        
        if (iframeBody) {
          // Calcular dimensiones del contenido
          const contentHeight = iframeBody.scrollHeight;
          const contentWidth = iframeBody.scrollWidth;
          
          // Ajustar altura del iframe
          modalIframe.style.height = Math.max(700, contentHeight + 150) + 'px';
          
                                  // Ajustar ancho del modal si es necesario
                        const modalContent = modalDynamic.querySelector('.modal-content');
                        if (modalContent) {
                          const maxWidth = Math.min(95, Math.max(60, (contentWidth / window.innerWidth) * 100));
                          modalContent.style.maxWidth = maxWidth + 'vw';
                        }
        }
      } catch (e) {
        // Si hay error de CORS, usar dimensiones por defecto
        modalIframe.style.height = '800px';
      }
    };
  }

  function hideDynamicModal() {
    modalDynamic.classList.remove('show');
    overlay.classList.remove('show');
    document.body.style.overflow = '';
    modalIframe.src = '';
  }

  // Hacer las funciones disponibles globalmente
  window.showDynamicModal = showDynamicModal;
  window.hideDynamicModal = hideDynamicModal;

  btnCloseDyn && btnCloseDyn.addEventListener('click', hideDynamicModal);

  // Cerrar modal dinámico con Escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modalDynamic.classList.contains('show')) {
      hideDynamicModal();
    }
  });

  // Modal de confirmación para eliminar
  var modalDelete = document.getElementById('modal-delete');
  var deleteRoleId = document.getElementById('delete-role-id');
  var deleteRoleName = document.getElementById('delete-rolename');

  function showDeleteModal(roleId, roleName) {
    deleteRoleId.value = roleId;
    deleteRoleName.textContent = roleName;
    modalDelete.classList.add('show');
    overlay.classList.add('show');
    document.body.style.overflow = 'hidden';
  }

  function hideDeleteModal() {
    modalDelete.classList.remove('show');
    overlay.classList.remove('show');
    document.body.style.overflow = '';
  }

  // Hacer las funciones disponibles globalmente
  window.showDeleteModal = showDeleteModal;
  window.hideDeleteModal = hideDeleteModal;

  // Cerrar modal de eliminación con Escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modalDelete.classList.contains('show')) {
      hideDeleteModal();
    }
  });

  // Función para actualizar la tabla de roles
  function updateRolesTable() {
    const tableBody = document.querySelector('#roles-table tbody');
    if (!tableBody) return;

    // Mostrar spinner de carga
    tableBody.innerHTML = '<tr><td colspan="6" class="text-center"><i class="fa fa-spinner fa-spin"></i> Actualizando...</td></tr>';

    // Hacer petición AJAX para obtener la tabla actualizada
    fetch('/Recursos/index.php?route=roles.index')
      .then(response => response.text())
      .then(html => {
        // Extraer solo el tbody de la respuesta
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newTableBody = doc.querySelector('#roles-table tbody');
        
        if (newTableBody) {
          tableBody.innerHTML = newTableBody.innerHTML;
          showNotification('Tabla de roles actualizada correctamente', 'success');
        } else {
          tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error al actualizar la tabla</td></tr>';
        }
      })
      .catch(error => {
        console.error('Error:', error);
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error al actualizar la tabla</td></tr>';
        showNotification('Error al actualizar la tabla', 'error');
      });
  }

  // Función para mostrar notificaciones
  function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
      <div class="notification-content">
        <span>${message}</span>
        <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
          <i class="fa fa-times"></i>
        </button>
      </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
      if (notification.parentElement) {
        notification.remove();
      }
    }, 5000);
  }



  // Manejar envío del formulario de crear rol
  document.addEventListener('DOMContentLoaded', function() {
    const createForm = document.querySelector('#modal-create form');
    if (createForm) {

      
      createForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Mostrar spinner
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Creando...';
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
                return { success: true, message: 'Rol creado correctamente' };
              } else if (text.includes('error') || text.includes('Error')) {
                return { error: 'Error al crear el rol' };
              } else {
                return { success: true, message: 'Rol creado correctamente' };
              }
            });
          }
        })
        .then(data => {
          if (data.success) {
            showNotification(data.message || 'Rol creado correctamente', 'success');
            updateRolesTable();
            hideModal();
            this.reset();
          } else {
            showNotification(data.error || 'Error al crear el rol', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showNotification('Error al crear el rol', 'error');
        })
        .finally(() => {
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        });
      });
    }

    // Manejar envío del formulario de eliminar
    const deleteForm = document.getElementById('delete-form');
    if (deleteForm) {
      deleteForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Mostrar spinner
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Eliminando...';
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
                return { success: true, message: 'Rol eliminado correctamente' };
              } else if (text.includes('error') || text.includes('Error')) {
                return { error: 'Error al eliminar el rol' };
              } else {
                return { success: true, message: 'Rol eliminado correctamente' };
              }
            });
          }
        })
        .then(data => {
          if (data.success) {
            showNotification(data.message || 'Rol eliminado correctamente', 'success');
            updateRolesTable();
            hideDeleteModal();
          } else {
            showNotification(data.error || 'Error al eliminar el rol', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showNotification('Error al eliminar el rol', 'error');
        })
        .finally(() => {
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        });
      });
    }
  });

  // Hacer funciones globales
  window.showModal = showModal;
  window.hideModal = hideModal;
  window.showDeleteModal = showDeleteModal;
  window.hideDeleteModal = hideDeleteModal;
  window.updateRolesTable = updateRolesTable;
  window.showNotification = showNotification;
})();
</script>

<?php
// Incluir el footer
include __DIR__ . '/../layout/footer.php';
?>
