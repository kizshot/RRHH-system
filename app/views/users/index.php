<?php
// app/views/users/index.php - Listado y modal Agregar Usuario
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /Recursos/index.php?route=login'); exit; }
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$roles = ['SUPER USER','TRABAJADOR','VENDEDOR','RECEPCIONISTA','CHOFER','PROGRAMADOR'];
$statuses = ['ACTIVO','INACTIVO'];
?>
      <div class="card">
        <div class="card-header">
          <h2>Usuarios</h2>
          <button class="button" id="btn-open-modal"><i class="fa fa-user-plus"></i> Agregar Usuario</button>
        </div>
        <?php if($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert success"><?= $success ?></div><?php endif; ?>
        
        <!-- Sistema de búsqueda y filtros unificado -->
        <div class="search-filters-container">
          <!-- Barra de búsqueda principal -->
          <div class="search-row">
            <div class="search-input-wrapper">
              <input class="input search-input" type="text" id="searchInput" placeholder="Buscar usuarios en tiempo real...">
              <i class="fa fa-search search-icon"></i>
            </div>
            
            <!-- Filtro unificado elegante -->
            <div class="filter-dropdown-wrapper">
              <button type="button" id="filterToggle" class="button filter-toggle">
                <i class="fa fa-filter"></i>
                <span id="filterLabel">Filtros</span>
                <i class="fa fa-chevron-down filter-chevron" id="filterChevron"></i>
              </button>
              
              <!-- Menú desplegable de filtros -->
              <div id="filterDropdown" class="filter-dropdown">
                <div class="filter-section">
                  <div class="filter-group">
                    <label class="filter-label">Rol de Usuario</label>
                    <select id="roleFilter" class="input">
                      <option value="">Todos los roles</option>
                      <?php foreach($roles as $r): ?>
                        <option value="<?= $r ?>"><?= $r ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  
                  <div class="filter-group">
                    <label class="filter-label">Estado</label>
                    <select id="statusFilter" class="input">
                      <option value="">Todos los estados</option>
                      <?php foreach($statuses as $s): ?>
                        <option value="<?= $s ?>"><?= $s ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  
                  <div class="filter-controls">
                    <div>
                      <label class="filter-label">Ordenar por</label>
                      <select id="sortFilter" class="input">
                        <?php $sorts = ['id'=>'ID','username'=>'Usuario','email'=>'Email','role'=>'Rol','status'=>'Estado','code'=>'Código','created_at'=>'Fecha']; 
                        foreach($sorts as $k=>$label): ?>
                          <option value="<?= $k ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label class="filter-label">Orden</label>
                      <select id="dirFilter" class="input">
                        <option value="DESC">↓</option>
                        <option value="ASC">↑</option>
                      </select>
                    </div>
                  </div>
                  
                  <div class="filter-actions">
                    <button type="button" id="clearFilters" class="button btn-secondary">
                      <i class="fa fa-times"></i> Limpiar
                    </button>
                    <button type="button" id="applyFilters" class="button">
                      <i class="fa fa-check"></i> Aplicar
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Indicadores de filtros activos -->
          <div id="activeFilters" class="active-filters"></div>
        </div>
        
                  <div class="table-responsive">
            <table class="table" id="users-table">
              <thead>
              <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Nombre</th>
                <th>Rol Usuario</th>
                <th>Estado</th>
                <th>Código</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($users as $u): ?>
                <tr>
                  <td><?= (int)$u['id'] ?></td>
                  <td>
                    <div class="table-row-user">
                      <?php if(!empty($u['avatar'])): ?>
                        <img src="<?= htmlspecialchars($u['avatar']) ?>" alt="avatar">
                      <?php else: ?>
                        <i class="fa fa-user-circle"></i>
                      <?php endif; ?>
                      <div>
                        <div><?= htmlspecialchars($u['username']) ?></div>
                        <small class="helper"><?= htmlspecialchars($u['email']) ?></small>
                      </div>
                    </div>
                  </td>
                  <td><?= htmlspecialchars(trim(($u['first_name'] ?? '').' '.($u['last_name'] ?? ''))) ?></td>
                  <td><?= htmlspecialchars($u['role'] ?? '') ?></td>
                  <td><?= htmlspecialchars($u['status'] ?? '') ?></td>
                  <td><?= htmlspecialchars($u['code'] ?? '') ?></td>
                  <td>
                    <div class="table-row-actions">
                      <button class="button btn-success" data-modal-href="/Recursos/index.php?route=users.view&id=<?= (int)$u['id'] ?>">
                        <i class="fa fa-eye"></i> Ver
                      </button>
                      <button class="button btn-warning" data-modal-href="/Recursos/index.php?route=users.edit&id=<?= (int)$u['id'] ?>">
                        <i class="fa fa-pen"></i> Editar
                      </button>
                      <button class="button btn-info" data-modal-href="/Recursos/index.php?route=users.credentials&id=<?= (int)$u['id'] ?>">
                        <i class="fa fa-key"></i> Credenciales
                      </button>
                      <button class="button btn-danger" onclick="showDeleteModal(<?= (int)$u['id'] ?>, '<?= htmlspecialchars($u['username']) ?>')">
                        <i class="fa fa-trash"></i> Eliminar
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        
        <?php if (($pageCount ?? 1) > 1): ?>
        <div class="pagination-container">
          <?php $qparam = $q ? ('&q=' . urlencode($q)) : ''; ?>
          <?php if (($page ?? 1) > 1): ?>
            <a class="button btn-secondary" href="/Recursos/index.php?route=users.index&page=<?= ($page-1) . $qparam ?>">
              <i class="fa fa-chevron-left"></i> Anterior
            </a>
          <?php endif; ?>
          <span class="helper pagination-info">Página <?= (int)$page ?> de <?= (int)$pageCount ?></span>
          <?php if (($page ?? 1) < ($pageCount ?? 1)): ?>
            <a class="button btn-secondary" href="/Recursos/index.php?route=users.index&page=<?= ($page+1) . $qparam ?>">
              Siguiente <i class="fa fa-chevron-right"></i>
            </a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Ventana dinámica Ver/Editar -->
      <div class="modal-backdrop" id="modal-overlay"></div>
      <div class="modal modal-dynamic" id="modal-dynamic" role="dialog" aria-modal="true" aria-labelledby="modalDynTitle">
        <div class="modal-content">
          <header class="modal-header">
            <h3 id="modalDynTitle" class="modal-title">
              <i class="fa fa-window-restore"></i> Ventana
            </h3>
            <button class="modal-close" id="btn-close-dyn" title="Cerrar">
              <i class="fa fa-times"></i>
            </button>
          </header>
          <div class="modal-body">
            <iframe id="modal-iframe" class="modal-iframe"></iframe>
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
            <div class="modal-message">¿Estás seguro de que quieres eliminar este usuario?</div>
            <div class="modal-description">
              <strong id="delete-username"></strong><br>
              Esta acción no se puede deshacer y se eliminarán todos los datos asociados al usuario.
            </div>
            <div class="modal-actions">
              <button type="button" class="button btn-secondary" onclick="hideDeleteModal()">
                <i class="fa fa-times"></i> Cancelar
              </button>
              <form id="delete-form" action="/Recursos/index.php?route=users.delete" method="post" style="display:inline">
                <input type="hidden" name="id" id="delete-user-id">
                <button type="submit" class="button btn-danger">
                  <i class="fa fa-trash"></i> Eliminar Usuario
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal crear usuario -->
      <div class="modal modal-form" id="modal-create" role="dialog" aria-modal="true" aria-labelledby="modalCreateTitle">
        <div class="modal-content">
          <header class="modal-header">
            <h3 id="modalCreateTitle" class="modal-title">
              <i class="fa fa-user-plus"></i> Crear Usuario
            </h3>
            <button class="modal-close" onclick="hideModal()" title="Cerrar">
              <i class="fa fa-times"></i>
            </button>
          </header>
          <div class="modal-body">
            <form action="/Recursos/index.php?route=users.create" method="post" enctype="multipart/form-data">
              <div class="form-grid-2">
                <div class="form-row">
                  <label for="username">Usuario</label>
                  <input class="input" type="text" id="username" name="username" maxlength="50" required>
                </div>
                <div class="form-row">
                  <label for="email">Email</label>
                  <input class="input" type="email" id="email" name="email" maxlength="100" required>
                </div>
                <div class="form-row">
                  <label for="password">Contraseña</label>
                  <input class="input" type="password" id="password" name="password" maxlength="100" required>
                </div>
                <div class="form-row">
                  <label for="password2">Repite la contraseña</label>
                  <input class="input" type="password" id="password2" name="password2" maxlength="100" required>
                </div>
                <div class="form-row">
                  <label for="first_name">Nombre</label>
                  <input class="input" type="text" id="first_name" name="first_name" maxlength="100">
                </div>
                <div class="form-row">
                  <label for="last_name">Apellidos</label>
                  <input class="input" type="text" id="last_name" name="last_name" maxlength="100">
                </div>
                <div class="form-row">
                  <label for="role">Rol Usuario</label>
                  <select class="input" id="role" name="role" required>
                    <option value="">-- Seleccionar Rol --</option>
                    <option value="SUPER USER">SUPER USER</option>
                    <option value="TRABAJADOR">TRABAJADOR</option>
                    <option value="VENDEDOR">VENDEDOR</option>
                    <option value="RECEPCIONISTA">RECEPCIONISTA</option>
                    <option value="CHOFER">CHOFER</option>
                    <option value="PROGRAMADOR">PROGRAMADOR</option>
                  </select>
                </div>
                <div class="form-row">
                  <label for="status">Estado</label>
                  <select class="input" id="status" name="status">
                    <option value="ACTIVO">ACTIVO</option>
                    <option value="INACTIVO">INACTIVO</option>
                  </select>
                </div>
                <div class="form-row">
                  <label for="code">Código</label>
                  <input class="input" type="text" id="code" name="code" maxlength="32">
                </div>
                <div class="form-row">
                  <label for="avatar">Imagen</label>
                  <input class="input" type="file" id="avatar" name="avatar" accept="image/*">
                </div>
              </div>
              <div class="modal-actions">
                <button type="button" class="button btn-secondary" onclick="hideModal()">
                  <i class="fa fa-times"></i> Cancelar
                </button>
                <button class="button btn-success" type="submit">
                  <i class="fa fa-save"></i> Crear Usuario
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

<style>
/* Animaciones para notificaciones */
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

@keyframes slideOutRight {
  from {
    transform: translateX(0);
    opacity: 1;
  }
  to {
    transform: translateX(100%);
    opacity: 0;
  }
}

/* Estilos para notificaciones */
.notification {
  font-family: inherit;
  font-size: 0.875rem;
  font-weight: 500;
}

.notification-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.notification-content i {
  font-size: 1.125rem;
}

.notification-close {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 4px;
  transition: background-color 0.2s ease;
}

.notification-close:hover {
  background: rgba(255, 255, 255, 0.2);
}

/* Indicador de carga en tabla */
.text-center {
  text-align: center;
}

#users-table tbody tr td {
  padding: 1rem;
}

#users-table tbody tr td i.fa-spinner {
  font-size: 1.5rem;
  color: #667eea;
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
</style>

    </main>
  </div>
  <script src="/Recursos/assets/js/main.js"></script>
  <script>
    (function(){
      // Modal crear usuario
      var openBtn = document.getElementById('btn-open-modal');
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

      // Modal dinámico para Ver/Editar
      var modalDynamic = document.getElementById('modal-dynamic');
      var modalIframe = document.getElementById('modal-iframe');
      var modalTitle = document.getElementById('modalDynTitle');
      var btnCloseDyn = document.getElementById('btn-close-dyn');

      function showDynamicModal(url, title) {
        modalIframe.src = url;
        modalTitle.innerHTML = '<i class="fa fa-window-restore"></i> ' + title;
        modalDynamic.classList.add('show');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
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
      var deleteUserId = document.getElementById('delete-user-id');
      var deleteUsername = document.getElementById('delete-username');

      function showDeleteModal(userId, username) {
        deleteUserId.value = userId;
        deleteUsername.textContent = username;
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

      // Manejar envío del formulario de eliminación
      document.getElementById('delete-form').addEventListener('submit', function(e) {
        var submitBtn = this.querySelector('button[type="submit"]');
        var originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Eliminando...';
        submitBtn.disabled = true;
        
        // El formulario se enviará normalmente
        // Después del envío exitoso, se cerrará el modal
        setTimeout(function() {
          hideDeleteModal();
        }, 500);
      });

      // Función para actualizar la tabla de usuarios
      function updateUserTable() {
        console.log('updateUserTable ejecutándose...');
        
        // Mostrar indicador de carga en la tabla
        var tableBody = document.querySelector('#users-table tbody');
        if (tableBody) {
          tableBody.innerHTML = '<tr><td colspan="7" class="text-center"><i class="fa fa-spinner fa-spin"></i> Actualizando tabla...</td></tr>';
        }
        
        // Recargar solo la tabla usando AJAX
        fetch(window.location.href)
          .then(response => response.text())
          .then(html => {
            // Crear un DOM temporal para extraer la tabla
            var parser = new DOMParser();
            var doc = parser.parseFromString(html, 'text/html');
            var newTableBody = doc.querySelector('#users-table tbody');
            
            if (newTableBody && tableBody) {
              tableBody.innerHTML = newTableBody.innerHTML;
              
              // Mostrar mensaje de éxito temporal
              showNotification('Tabla actualizada correctamente', 'success');
            }
          })
          .catch(error => {
            console.error('Error al actualizar la tabla:', error);
            showNotification('Error al actualizar la tabla', 'error');
            
            // Recargar la página como fallback
            setTimeout(function() {
              window.location.reload();
            }, 2000);
          });
      }

      // Función para mostrar notificaciones
      function showNotification(message, type = 'info') {
        // Crear elemento de notificación
        var notification = document.createElement('div');
        notification.className = 'notification notification-' + type;
        notification.innerHTML = `
          <div class="notification-content">
            <i class="fa fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
          </div>
          <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fa fa-times"></i>
          </button>
        `;
        
        // Agregar estilos
        notification.style.cssText = `
          position: fixed;
          top: 20px;
          right: 20px;
          background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
          color: white;
          padding: 1rem 1.5rem;
          border-radius: 8px;
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
          z-index: 9999;
          display: flex;
          align-items: center;
          gap: 0.75rem;
          max-width: 400px;
          animation: slideInRight 0.3s ease-out;
        `;
        
        // Agregar al DOM
        document.body.appendChild(notification);
        
        // Auto-remover después de 5 segundos
        setTimeout(function() {
          if (notification.parentElement) {
            notification.remove();
          }
        }, 5000);
      }

      // Verificar que la función esté disponible
      console.log('updateUserTable disponible en window:', typeof window.updateUserTable);

      // Manejar formulario de creación de usuario
      document.querySelector('#modal-create form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        var submitBtn = this.querySelector('button[type="submit"]');
        var originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Creando...';
        submitBtn.disabled = true;
        
        var formData = new FormData(this);
        
        fetch(this.action, {
          method: 'POST',
          body: formData
        })
        .then(response => response.text())
        .then(data => {
          submitBtn.innerHTML = '<i class="fa fa-check"></i> ¡Usuario Creado!';
          submitBtn.style.background = '#10b981';
          
          // Actualizar la tabla
          updateUserTable();
          
          // Cerrar el modal
          setTimeout(function() {
            hideModal();
            // Limpiar el formulario
            document.querySelector('#modal-create form').reset();
          }, 1500);
        })
        .catch(error => {
          submitBtn.innerHTML = '<i class="fa fa-exclamation-triangle"></i> Error';
          submitBtn.style.background = '#ef4444';
          submitBtn.disabled = false;
          
          setTimeout(function() {
            submitBtn.innerHTML = originalText;
            submitBtn.style.background = '';
          }, 3000);
        });
      });

      // Manejar botones Ver/Editar
      document.addEventListener('click', function(e) {
        if (e.target.matches('[data-modal-href]') || e.target.closest('[data-modal-href]')) {
          e.preventDefault();
          var button = e.target.matches('[data-modal-href]') ? e.target : e.target.closest('[data-modal-href]');
          var url = button.getAttribute('data-modal-href');
          var title = button.textContent.trim();
          
          if (url) {
            showDynamicModal(url, title);
          }
        }
      });

      // Sistema de filtros unificado con búsqueda en tiempo real
      var searchInput = document.getElementById('searchInput');
      var filterToggle = document.getElementById('filterToggle');
      var filterDropdown = document.getElementById('filterDropdown');
      var filterChevron = document.getElementById('filterChevron');
      var filterLabel = document.getElementById('filterLabel');
      var roleFilter = document.getElementById('roleFilter');
      var statusFilter = document.getElementById('statusFilter');
      var sortFilter = document.getElementById('sortFilter');
      var dirFilter = document.getElementById('dirFilter');
      var clearFilters = document.getElementById('clearFilters');
      var applyFilters = document.getElementById('applyFilters');
      var activeFilters = document.getElementById('activeFilters');
      var tableRows = document.querySelectorAll('tbody tr');
      
      var currentFilters = {
        search: '',
        role: '',
        status: '',
        sort: 'id',
        dir: 'DESC'
      };

      // Toggle menú de filtros
      filterToggle && filterToggle.addEventListener('click', function(e){
        e.stopPropagation();
        var isOpen = filterDropdown.classList.contains('show');
        if(isOpen) {
          filterDropdown.classList.remove('show');
          filterChevron.classList.remove('rotated');
        } else {
          filterDropdown.classList.add('show');
          filterChevron.classList.add('rotated');
        }
      });

      // Cerrar menú al hacer clic fuera
      document.addEventListener('click', function(e){
        if(filterDropdown && !filterToggle.contains(e.target) && !filterDropdown.contains(e.target)){
          filterDropdown.classList.remove('show');
          filterChevron.classList.remove('rotated');
        }
      });

      // Búsqueda en tiempo real
      var searchTimeout;
      searchInput && searchInput.addEventListener('input', function(){
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function(){
          currentFilters.search = searchInput.value.toLowerCase();
          applyRealTimeFilters();
        }, 300);
      });

      // Aplicar filtros
      applyFilters && applyFilters.addEventListener('click', function(){
        currentFilters.role = roleFilter.value;
        currentFilters.status = statusFilter.value;
        currentFilters.sort = sortFilter.value;
        currentFilters.dir = dirFilter.value;
        applyRealTimeFilters();
        updateFilterLabel();
        filterDropdown.classList.remove('show');
        filterChevron.classList.remove('rotated');
      });

      // Limpiar filtros
      clearFilters && clearFilters.addEventListener('click', function(){
        currentFilters = { search: '', role: '', status: '', sort: 'id', dir: 'DESC' };
        searchInput.value = '';
        roleFilter.value = '';
        statusFilter.value = '';
        sortFilter.value = 'id';
        dirFilter.value = 'DESC';
        applyRealTimeFilters();
        updateFilterLabel();
        updateActiveFilters();
      });

      function applyRealTimeFilters(){
        var visibleRows = [];
        
        tableRows.forEach(function(row){
          var cells = row.querySelectorAll('td');
          if(cells.length < 6) return;
          
          var id = cells[0].textContent.trim();
          var username = cells[1].textContent.trim().toLowerCase();
          var name = cells[2].textContent.trim().toLowerCase();
          var role = cells[3].textContent.trim();
          var status = cells[4].textContent.trim();
          var code = cells[5].textContent.trim().toLowerCase();
          
          var showRow = true;
          
          // Filtro de búsqueda
          if(currentFilters.search){
            var searchText = (id + ' ' + username + ' ' + name + ' ' + code).toLowerCase();
            showRow = showRow && searchText.includes(currentFilters.search);
          }
          
          // Filtro de rol
          if(currentFilters.role){
            showRow = showRow && role === currentFilters.role;
          }
          
          // Filtro de estado
          if(currentFilters.status){
            showRow = showRow && status === currentFilters.status;
          }
          
          row.style.display = showRow ? '' : 'none';
          if(showRow) visibleRows.push(row);
        });
        
        // Ordenar filas visibles
        sortVisibleRows(visibleRows);
        updateActiveFilters();
      }

      function sortVisibleRows(rows){
        var tbody = document.querySelector('tbody');
        if(!tbody) return;
        
        rows.sort(function(a, b){
          var aCells = a.querySelectorAll('td');
          var bCells = b.querySelectorAll('td');
          var sortIndex = getSortIndex(currentFilters.sort);
          
          var aValue = aCells[sortIndex] ? aCells[sortIndex].textContent.trim() : '';
          var bValue = bCells[sortIndex] ? bCells[sortIndex].textContent.trim() : '';
          
          // Para números (ID)
          if(currentFilters.sort === 'id'){
            aValue = parseInt(aValue) || 0;
            bValue = parseInt(bValue) || 0;
          }
          
          var comparison = 0;
          if(aValue > bValue) comparison = 1;
          else if(aValue < bValue) comparison = -1;
          
          return currentFilters.dir === 'ASC' ? comparison : -comparison;
        });
        
        // Reordenar en el DOM
        rows.forEach(function(row){
          tbody.appendChild(row);
        });
      }

      function getSortIndex(sortField){
        var fields = { 'id': 0, 'username': 1, 'email': 1, 'role': 3, 'status': 4, 'code': 5, 'created_at': 0 };
        return fields[sortField] || 0;
      }

      function updateFilterLabel(){
        var activeCount = 0;
        if(currentFilters.role) activeCount++;
        if(currentFilters.status) activeCount++;
        if(currentFilters.sort !== 'id' || currentFilters.dir !== 'DESC') activeCount++;
        
        if(activeCount > 0){
          filterLabel.textContent = 'Filtros (' + activeCount + ')';
          filterToggle.classList.add('active');
        } else {
          filterLabel.textContent = 'Filtros';
          filterToggle.classList.remove('active');
        }
      }

      function updateActiveFilters(){
        if(!activeFilters) return;
        activeFilters.innerHTML = '';
        
        if(currentFilters.search){
          addFilterTag('Búsqueda: "' + currentFilters.search + '"', function(){
            currentFilters.search = '';
            searchInput.value = '';
            applyRealTimeFilters();
          });
        }
        
        if(currentFilters.role){
          addFilterTag('Rol: ' + currentFilters.role, function(){
            currentFilters.role = '';
            roleFilter.value = '';
            applyRealTimeFilters();
            updateFilterLabel();
          });
        }
        
        if(currentFilters.status){
          addFilterTag('Estado: ' + currentFilters.status, function(){
            currentFilters.status = '';
            statusFilter.value = '';
            applyRealTimeFilters();
            updateFilterLabel();
          });
        }
      }

      function addFilterTag(text, onRemove){
        var tag = document.createElement('span');
        tag.className = 'filter-tag';
        tag.innerHTML = text + ' <button class="filter-tag-remove" onclick="this.parentElement.remove()">&times;</button>';
        tag.querySelector('button').addEventListener('click', onRemove);
        activeFilters.appendChild(tag);
      }

             // Inicializar
       updateFilterLabel();
       
       // Hacer las funciones disponibles globalmente
       window.updateUserTable = updateUserTable;
       window.showNotification = showNotification;
       window.showModal = showModal;
       window.hideModal = hideModal;
       
       // Verificar que las funciones estén disponibles
       console.log('Funciones disponibles en window:');
       console.log('- updateUserTable:', typeof window.updateUserTable);
       console.log('- showModal:', typeof window.showModal);
       console.log('- hideModal:', typeof window.hideModal);
     })();
  </script>
</body>
</html>

