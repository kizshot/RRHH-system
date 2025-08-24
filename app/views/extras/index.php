<?php
// app/views/extras/index.php - Listado y modal Agregar Extras
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /Recursos/index.php?route=login'); exit; }
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$statuses = ['PENDIENTE','APROBADO','RECHAZADO','PAGADO'];
?>

<style>
/* Estilos mejorados para el módulo de Extras */
.extras-header {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  padding: 2rem;
  border-radius: 16px;
  margin-bottom: 2rem;
  box-shadow: 0 10px 25px rgba(59, 130, 246, 0.15);
}

.extras-header h2 {
  margin: 0 0 0.5rem 0;
  font-size: 2rem;
  font-weight: 700;
}

.extras-header p {
  margin: 0;
  opacity: 0.9;
  font-size: 1.1rem;
}

.search-container {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
  border: 1px solid #e5e7eb;
}

.search-form {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
  gap: 1rem;
  align-items: end;
}

.search-input {
  position: relative;
}

.search-input input {
  padding-left: 2.5rem;
  background: #f9fafb;
  border: 2px solid #e5e7eb;
  transition: all 0.3s ease;
}

.search-input input:focus {
  background: white;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-input i {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
}

.table-container {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table thead {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.data-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.data-table td {
  padding: 1rem;
  border-bottom: 1px solid #f3f4f6;
  vertical-align: middle;
}

.data-table tbody tr {
  transition: all 0.2s ease;
}

.data-table tbody tr:hover {
  background: #f9fafb;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.employee-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.employee-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
}

.employee-details h4 {
  margin: 0;
  font-weight: 600;
  color: #111827;
}

.employee-details small {
  color: #6b7280;
  font-size: 0.75rem;
}

.hours-info {
  text-align: center;
}

.hours-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #2563eb;
}

.hours-label {
  font-size: 0.75rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.amount-info {
  text-align: right;
}

.amount-value {
  font-size: 1rem;
  font-weight: 700;
  color: #059669;
}

.amount-label {
  font-size: 0.75rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.status-pendiente {
  background: #fef3c7;
  color: #92400e;
}

.status-aprobado {
  background: #dcfce7;
  color: #166534;
}

.status-rechazado {
  background: #fee2e2;
  color: #991b1b;
}

.status-pagado {
  background: #dbeafe;
  color: #1e40af;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.btn-action {
  padding: 0.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  text-decoration: none;
  color: white;
}

.btn-view {
  background: #3b82f6;
}

.btn-view:hover {
  background: #2563eb;
  transform: translateY(-1px);
}

.btn-edit {
  background: #f59e0b;
}

.btn-edit:hover {
  background: #d97706;
  transform: translateY(-1px);
}

.btn-delete {
  background: #ef4444;
}

.btn-delete:hover {
  background: #dc2626;
  transform: translateY(-1px);
}

.btn-approve {
  background: #10b981;
}

.btn-approve:hover {
  background: #059669;
  transform: translateY(-1px);
}

.btn-reject {
  background: #ef4444;
}

.btn-reject:hover {
  background: #dc2626;
  transform: translateY(-1px);
}

.btn-paid {
  background: #3b82f6;
}

.btn-paid:hover {
  background: #2563eb;
  transform: translateY(-1px);
}

.btn-add {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2);
}

.btn-add:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 15px rgba(59, 130, 246, 0.3);
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
  padding: 1rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.pagination a {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  text-decoration: none;
  color: #374151;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  transition: all 0.2s ease;
}

.pagination a:hover {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.pagination .current {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

/* Responsive */
@media (max-width: 1024px) {
  .search-form {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
  
  .action-buttons {
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .btn-action {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
  }
}

@media (max-width: 768px) {
  .extras-header {
    padding: 1.5rem;
  }
  
  .extras-header h2 {
    font-size: 1.5rem;
  }
  
  .data-table {
    font-size: 0.875rem;
  }
  
  .data-table th,
  .data-table td {
    padding: 0.75rem 0.5rem;
  }
}
</style>

<div class="extras-header">
  <h2><i class="fa fa-clock"></i> Gestión de Horas Extras</h2>
  <p>Administra las horas extras trabajadas por los empleados</p>
</div>

<?php if($error): ?>
  <div class="alert error" style="margin-bottom: 1rem;">
    <i class="fa fa-exclamation-triangle"></i> <?= $error ?>
  </div>
<?php endif; ?>

<?php if($success): ?>
  <div class="alert success" style="margin-bottom: 1rem;">
    <i class="fa fa-check-circle"></i> <?= $success ?>
  </div>
<?php endif; ?>

<div class="search-container">
  <form method="get" action="/Recursos/index.php" class="search-form">
    <input type="hidden" name="route" value="extras.index">
    
    <div class="search-input">
      <i class="fa fa-search"></i>
      <input class="input" type="text" name="q" placeholder="Buscar por empleado, motivo..." value="<?= htmlspecialchars($q ?? '') ?>">
    </div>
    
    <select class="input" name="personal_id">
      <option value="">Todos los empleados</option>
      <?php foreach($personal_list as $p): ?>
        <option value="<?= $p['id'] ?>" <?= (isset($personal_id) && $personal_id==$p['id'])?'selected':'' ?>>
          <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    
    <select class="input" name="status">
      <option value="">Todos los estados</option>
      <?php foreach($statuses as $s): ?>
        <option value="<?= $s ?>" <?= (isset($status) && $status===$s)?'selected':'' ?>><?= $s ?></option>
      <?php endforeach; ?>
    </select>
    
    <select class="input" name="sort">
      <?php $sorts = ['id'=>'ID','personal_id'=>'Empleado','date'=>'Fecha','hours'=>'Horas','total_amount'=>'Monto','status'=>'Estado']; foreach($sorts as $k=>$label): ?>
        <option value="<?= $k ?>" <?= (isset($sort) && $sort===$k)?'selected':'' ?>><?= $label ?></option>
      <?php endforeach; ?>
    </select>
    
    <select class="input" name="dir">
      <option value="DESC" <?= (!isset($dir) || strtoupper($dir)==='DESC')?'selected':'' ?>><i class="fa fa-sort-desc"></i> Desc</option>
      <option value="ASC" <?= (isset($dir) && strtoupper($dir)==='ASC')?'selected':'' ?>><i class="fa fa-sort-asc"></i> Asc</option>
    </select>
    
    <button class="btn-add" type="submit">
      <i class="fa fa-search"></i> Buscar
    </button>
  </form>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
  <h3 style="margin: 0; color: #374151;">Lista de Horas Extras</h3>
  <button class="btn-add" id="btn-open-modal">
    <i class="fa fa-plus"></i> Registrar Horas Extras
  </button>
</div>

<div class="table-container">
  <table class="data-table">
    <thead>
      <tr>
        <th>Empleado</th>
        <th>Fecha</th>
        <th>Horas</th>
        <th>Monto</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($extras as $e): ?>
        <tr>
          <td>
            <div class="employee-info">
              <div class="employee-avatar">
                <?= strtoupper(substr($e['employee_first_name'] ?? '', 0, 1) . substr($e['employee_last_name'] ?? '', 0, 1)) ?>
              </div>
              <div class="employee-details">
                <h4><?= htmlspecialchars(trim(($e['employee_first_name'] ?? '').' '.($e['employee_last_name'] ?? ''))) ?></h4>
                <small><?= htmlspecialchars($e['employee_code'] ?? '') ?></small>
              </div>
            </div>
          </td>
          <td>
            <div>
              <strong><?= htmlspecialchars($e['date'] ?? '') ?></strong>
              <br><small style="color: #6b7280;"><?= htmlspecialchars($e['start_time'] ?? '') ?> - <?= htmlspecialchars($e['end_time'] ?? '') ?></small>
            </div>
          </td>
          <td>
            <div class="hours-info">
              <div class="hours-value"><?= number_format($e['hours'], 1) ?>h</div>
              <div class="hours-label">Extras</div>
            </div>
          </td>
          <td>
            <div class="amount-info">
              <div class="amount-value">S/ <?= number_format($e['total_amount'], 2) ?></div>
              <div class="amount-label">Total</div>
            </div>
          </td>
          <td>
            <span class="status-badge <?= strtolower($e['status']) === 'pendiente' ? 'status-pendiente' : (strtolower($e['status']) === 'aprobado' ? 'status-aprobado' : (strtolower($e['status']) === 'rechazado' ? 'status-rechazado' : 'status-pagado')) ?>">
              <?= htmlspecialchars($e['status']) ?>
            </span>
          </td>
          <td>
            <div class="action-buttons">
              <button class="btn-action btn-view" onclick="openViewModal(<?= (int)$e['id'] ?>)" title="Ver detalles">
                <i class="fa fa-eye"></i>
              </button>
              <button class="btn-action btn-edit" onclick="openEditModal(<?= (int)$e['id'] ?>)" title="Editar">
                <i class="fa fa-pen"></i>
              </button>
              <?php if(strtolower($e['status']) === 'pendiente'): ?>
                <button class="btn-action btn-approve" onclick="approveExtra(<?= (int)$e['id'] ?>)" title="Aprobar">
                  <i class="fa fa-check"></i>
                </button>
                <button class="btn-action btn-reject" onclick="rejectExtra(<?= (int)$e['id'] ?>)" title="Rechazar">
                  <i class="fa fa-times"></i>
                </button>
              <?php elseif(strtolower($e['status']) === 'aprobado'): ?>
                <button class="btn-action btn-paid" onclick="markAsPaid(<?= (int)$e['id'] ?>)" title="Marcar como pagado">
                  <i class="fa fa-money-bill-wave"></i>
                </button>
              <?php endif; ?>
              <button class="btn-action btn-delete" onclick="confirmDelete(<?= (int)$e['id'] ?>, '<?= htmlspecialchars($e['employee_first_name'] . ' ' . $e['employee_last_name']) ?>')" title="Eliminar">
                <i class="fa fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if (($pageCount ?? 1) > 1): ?>
<div class="pagination">
  <?php $qparam = $q ? ('&q=' . urlencode($q)) : ''; ?>
  <?php if (($page ?? 1) > 1): ?>
    <a href="/Recursos/index.php?route=extras.index&page=<?= ($page-1) . $qparam ?>">
      <i class="fa fa-chevron-left"></i> Anterior
    </a>
  <?php endif; ?>
  
  <span style="color: #6b7280; font-weight: 500;">
    Página <?= (int)$page ?> de <?= (int)$pageCount ?>
  </span>
  
  <?php if (($page ?? 1) < ($pageCount ?? 1)): ?>
    <a href="/Recursos/index.php?route=extras.index&page=<?= ($page+1) . $qparam ?>">
      Siguiente <i class="fa fa-chevron-right"></i>
    </a>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Modal dinámico mejorado -->
<div class="modal" id="modal-dynamic" role="dialog" aria-modal="true" aria-labelledby="modalDynTitle" style="display:none">
  <header style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border-radius: 12px 12px 0 0;">
    <h3 id="modalDynTitle" style="margin:0; display: flex; align-items: center; gap: 0.5rem;">
      <i class="fa fa-window-restore"></i> <span id="modal-title">Ventana</span>
    </h3>
    <button class="icon-btn" id="btn-close-dyn" title="Cerrar" style="color: white;">
      <i class="fa fa-times"></i>
    </button>
  </header>
  <div class="modal-body" id="modal-content" style="padding:0">
    <!-- El contenido se cargará aquí dinámicamente -->
  </div>
</div>

<!-- Overlay para todos los modales -->
<div class="modal-backdrop" id="modal-overlay"></div>

<!-- Modal crear extra mejorado -->
<div class="modal" id="modal-create" role="dialog" aria-modal="true" aria-labelledby="modalTitle" style="display:none">
  <header style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border-radius: 12px 12px 0 0;">
    <h3 id="modalTitle" style="margin:0; display: flex; align-items: center; gap: 0.5rem;">
      <i class="fa fa-plus"></i> Registrar Horas Extras
    </h3>
    <button class="icon-btn" id="btn-close-modal" title="Cerrar" style="color: white;">
      <i class="fa fa-times"></i>
    </button>
  </header>
  <form id="create-form" action="/Recursos/index.php?route=extras.create" method="post">
    <div class="modal-body">
      <div class="grid-2">
        <div class="form-row">
          <label for="personal_id">Empleado *</label>
          <select class="input" id="personal_id" name="personal_id" required>
            <option value="">Seleccionar empleado</option>
            <?php foreach($personal_list as $p): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?> (<?= htmlspecialchars($p['employee_code']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <label for="date">Fecha *</label>
          <input class="input" type="date" id="date" name="date" required>
        </div>
        <div class="form-row">
          <label for="start_time">Hora de Inicio *</label>
          <input class="input" type="time" id="start_time" name="start_time" required>
        </div>
        <div class="form-row">
          <label for="end_time">Hora de Fin *</label>
          <input class="input" type="time" id="end_time" name="end_time" required>
        </div>
        <div class="form-row">
          <label for="hours">Horas *</label>
          <input class="input" type="number" id="hours" name="hours" step="0.5" min="0" required>
        </div>
        <div class="form-row">
          <label for="rate_type">Tipo de Tarifa</label>
          <select class="input" id="rate_type" name="rate_type">
            <option value="NORMAL">Normal</option>
            <option value="DOBLE">Doble</option>
            <option value="TRIPLE">Triple</option>
          </select>
        </div>
        <div class="form-row">
          <label for="rate">Tarifa por Hora</label>
          <input class="input" type="number" id="rate" name="rate" step="0.01" min="0">
        </div>
        <div class="form-row">
          <label for="total_amount">Monto Total</label>
          <input class="input" type="number" id="total_amount" name="total_amount" step="0.01" min="0">
        </div>
        <div class="form-row">
          <label for="status">Estado</label>
          <select class="input" id="status" name="status">
            <?php foreach($statuses as $s): ?>
              <option value="<?= $s ?>"><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row" style="grid-column: 1 / -1;">
          <label for="reason">Motivo</label>
          <textarea class="input" id="reason" name="reason" rows="3"></textarea>
        </div>
        <div class="form-row" style="grid-column: 1 / -1;">
          <label for="notes">Notas</label>
          <textarea class="input" id="notes" name="notes" rows="2"></textarea>
        </div>
      </div>
    </div>
    <div class="modal-actions">
      <button type="button" class="button" id="btn-cancel" style="background: #6b7280;">Cancelar</button>
      <button type="submit" class="button" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
        <i class="fa fa-save"></i> Guardar
      </button>
    </div>
  </form>
</div>

<!-- Modal de confirmación de eliminación mejorado -->
<div class="modal" id="modal-delete" role="dialog" aria-modal="true" style="display:none; max-width: 500px;">
  <header style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; border-radius: 12px 12px 0 0;">
    <h3 style="margin:0; display: flex; align-items: center; gap: 0.5rem;">
      <i class="fa fa-exclamation-triangle"></i> Confirmar Eliminación
    </h3>
    <button class="icon-btn" id="btn-close-delete" title="Cerrar" style="color: white;">
      <i class="fa fa-times"></i>
    </button>
  </header>
  <div class="modal-body" style="text-align: center; padding: 2rem;">
    <div style="font-size: 3rem; color: #ef4444; margin-bottom: 1rem;">
      <i class="fa fa-exclamation-triangle"></i>
    </div>
    <p id="delete-message" style="font-size: 1.1rem; margin-bottom: 1rem;">¿Estás seguro de que quieres eliminar estas horas extras?</p>
    <p style="color: #6b7280;"><strong>Esta acción no se puede deshacer.</strong></p>
  </div>
  <div class="modal-actions">
    <button type="button" class="button" id="btn-cancel-delete" style="background: #6b7280;">Cancelar</button>
    <button type="button" class="button" id="btn-confirm-delete" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
      <i class="fa fa-trash"></i> Eliminar
    </button>
  </div>
</div>

    </main>
  </div>
  <link rel="stylesheet" href="/Recursos/assets/css/modal-fixes.css">
  <script src="/Recursos/assets/js/notifications.js"></script>
<script src="/Recursos/assets/js/main.js"></script>
           <script>
      // Variables globales para eliminación
      var deleteId = null;
      var deleteName = null;

      // Función para confirmar eliminación con modal estético
      function confirmDelete(id, name) {
        deleteId = id;
        deleteName = name;
        
        // Actualizar mensaje del modal
        document.getElementById('delete-message').innerHTML = 
          '¿Estás seguro de que quieres eliminar las horas extras de <strong>' + name + '</strong>?';
        
        // Mostrar modal de eliminación
        var deleteModal = document.getElementById('modal-delete');
        var overlay = document.getElementById('modal-overlay');
        
        deleteModal.style.display = 'block';
        overlay.classList.add('show');
        
        // Centrar el modal
        deleteModal.style.top = '50%';
        deleteModal.style.left = '50%';
        deleteModal.style.transform = 'translate(-50%, -50%)';
      }

      // Función para ejecutar eliminación
      function executeDelete() {
        if (deleteId && deleteName) {
          // Crear formulario temporal y enviarlo
          var form = document.createElement('form');
          form.method = 'POST';
          form.action = '/Recursos/index.php?route=extras.delete';
          
          var input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'id';
          input.value = deleteId;
          
          form.appendChild(input);
          document.body.appendChild(form);
          form.submit();
        }
      }

      // Funciones para aprobar, rechazar y marcar como pagado
      function approveExtra(id) {
        if (confirm('¿Estás seguro de que quieres aprobar estas horas extras?')) {
          var form = document.createElement('form');
          form.method = 'POST';
          form.action = '/Recursos/index.php?route=extras.approve';
          
          var input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'id';
          input.value = id;
          
          form.appendChild(input);
          document.body.appendChild(form);
          form.submit();
        }
      }

      function rejectExtra(id) {
        if (confirm('¿Estás seguro de que quieres rechazar estas horas extras?')) {
          var form = document.createElement('form');
          form.method = 'POST';
          form.action = '/Recursos/index.php?route=extras.reject';
          
          var input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'id';
          input.value = id;
          
          form.appendChild(input);
          document.body.appendChild(form);
          form.submit();
        }
      }

      function markAsPaid(id) {
        if (confirm('¿Estás seguro de que quieres marcar estas horas extras como pagadas?')) {
          var form = document.createElement('form');
          form.method = 'POST';
          form.action = '/Recursos/index.php?route=extras.markAsPaid';
          
          var input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'id';
          input.value = id;
          
          form.appendChild(input);
          document.body.appendChild(form);
          form.submit();
        }
      }

      (function(){
        // Modal de crear extra
        var openBtn = document.getElementById('btn-open-modal');
        var closeBtn = document.getElementById('btn-close-modal');
        var cancelBtn = document.getElementById('btn-cancel');
        var modal = document.getElementById('modal-create');
        var overlay = document.getElementById('modal-overlay');

        function showModal(){ 
          modal.style.display = 'block'; 
          overlay.classList.add('show'); 
          // Centrar el modal
          modal.style.top = '50%';
          modal.style.left = '50%';
          modal.style.transform = 'translate(-50%, -50%)';
        }
        function hideModal(){ 
          modal.style.display = 'none'; 
          overlay.classList.remove('show'); 
        }
        openBtn && openBtn.addEventListener('click', showModal);
        closeBtn && closeBtn.addEventListener('click', hideModal);
        cancelBtn && cancelBtn.addEventListener('click', hideModal);
        overlay && overlay.addEventListener('click', hideModal);

        // Manejar envío del formulario de crear via AJAX
        var createForm = document.getElementById('create-form');
        if (createForm) {
          createForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            
            fetch('/Recursos/index.php?route=extras.create', {
              method: 'POST',
              headers: {
                'X-Requested-With': 'XMLHttpRequest'
              },
              body: formData
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // Cerrar modal y recargar página
                hideModal();
                window.location.reload();
              } else {
                // Mostrar error
                alert('Error al crear horas extras: ' + data.message);
              }
            })
            .catch(error => {
              alert('Error: ' + error);
            });
          });
        }

        // Modal de eliminación
        var closeDeleteBtn = document.getElementById('btn-close-delete');
        var cancelDeleteBtn = document.getElementById('btn-cancel-delete');
        var confirmDeleteBtn = document.getElementById('btn-confirm-delete');
        var deleteModal = document.getElementById('modal-delete');

        function hideDeleteModal(){ 
          deleteModal.style.display = 'none'; 
          overlay.classList.remove('show'); 
          deleteId = null;
          deleteName = null;
        }

        closeDeleteBtn && closeDeleteBtn.addEventListener('click', hideDeleteModal);
        cancelDeleteBtn && cancelDeleteBtn.addEventListener('click', hideDeleteModal);
        confirmDeleteBtn && confirmDeleteBtn.addEventListener('click', executeDelete);
        
        // Cerrar modales al hacer clic en overlay
        overlay && overlay.addEventListener('click', function(e) {
          if (e.target === overlay) {
            var dynamicModal = document.getElementById('modal-dynamic');
            if (dynamicModal && dynamicModal.style.display === 'block') {
              dynamicModal.style.display = 'none';
              overlay.classList.remove('show');
            } else if (deleteModal.style.display === 'block') {
              hideDeleteModal();
            } else if (modal.style.display === 'block') {
              hideModal();
            }
          }
        });
        
        // Prevenir que los clics dentro de los modales cierren el modal
        var dynamicModal = document.getElementById('modal-dynamic');
        var createModal = document.getElementById('modal-create');
        var deleteModal = document.getElementById('modal-delete');
        
        if (dynamicModal) {
          dynamicModal.addEventListener('click', function(e) {
            e.stopPropagation();
          });
        }
        if (createModal) {
          createModal.addEventListener('click', function(e) {
            e.stopPropagation();
          });
        }
        if (deleteModal) {
          deleteModal.addEventListener('click', function(e) {
            e.stopPropagation();
          });
        }

        // Cerrar modal dinámico
        var closeDynBtn = document.getElementById('btn-close-dyn');
        if (closeDynBtn) {
          closeDynBtn.addEventListener('click', function() {
            document.getElementById('modal-dynamic').style.display = 'none';
            document.getElementById('modal-overlay').classList.remove('show');
          });
        }
        
        // Cerrar modal dinámico al hacer clic en overlay
        overlay && overlay.addEventListener('click', function(e) {
          if (e.target === overlay) {
            var dynamicModal = document.getElementById('modal-dynamic');
            if (dynamicModal && dynamicModal.style.display === 'block') {
              dynamicModal.style.display = 'none';
              overlay.classList.remove('show');
            }
          }
        });
      })();

      // Función para abrir modal de vista
      function openViewModal(id) {
        fetch('/Recursos/index.php?route=extras.view&id=' + id)
          .then(response => response.text())
          .then(data => {
            document.getElementById('modal-content').innerHTML = data;
            document.getElementById('modal-title').textContent = 'Ver Horas Extras';
            document.getElementById('modal-dynamic').style.display = 'block';
            document.getElementById('modal-overlay').classList.add('show');
          })
          .catch(error => {
            alert('Error al cargar datos: ' + error);
          });
      }

      // Función para abrir modal de edición
      function openEditModal(id) {
        fetch('/Recursos/index.php?route=extras.edit&id=' + id)
          .then(response => response.text())
          .then(data => {
            document.getElementById('modal-content').innerHTML = data;
            document.getElementById('modal-title').textContent = 'Editar Horas Extras';
            document.getElementById('modal-dynamic').style.display = 'block';
            document.getElementById('modal-overlay').classList.add('show');
          })
          .catch(error => {
            alert('Error al cargar datos: ' + error);
          });
      }
      
      // Función global para editar extra (usada desde modal de vista)
      function editExtra(id) {
        // Mostrar indicador de carga si el botón está disponible
        const editBtn = event ? event.target.closest('button') : null;
        let originalContent = '';
        if (editBtn) {
          originalContent = editBtn.innerHTML;
          editBtn.disabled = true;
          editBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Cargando...';
        }
        
        fetch('/Recursos/index.php?route=extras.edit&id=' + id)
          .then(response => response.text())
          .then(data => {
            document.getElementById('modal-content').innerHTML = data;
            document.getElementById('modal-title').textContent = 'Editar Horas Extras';
            // El modal ya está abierto, solo actualizamos el contenido
          })
          .catch(error => {
            alert('Error al cargar el formulario de edición: ' + error);
            // Restaurar botón en caso de error
            if (editBtn) {
              editBtn.disabled = false;
              editBtn.innerHTML = originalContent;
            }
          });
      }
      
      // Función global para cerrar modales
      function closeModal() {
        var dynamicModal = document.getElementById('modal-dynamic');
        var createModal = document.getElementById('modal-create');
        var deleteModal = document.getElementById('modal-delete');
        var overlay = document.getElementById('modal-overlay');
        
        if (dynamicModal && dynamicModal.style.display === 'block') {
          dynamicModal.style.display = 'none';
        }
        if (createModal && createModal.style.display === 'block') {
          createModal.style.display = 'none';
        }
        if (deleteModal && deleteModal.style.display === 'block') {
          deleteModal.style.display = 'none';
        }
        if (overlay) {
          overlay.classList.remove('show');
        }
      }
      
      // Cerrar modales con tecla Escape
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          var overlay = document.getElementById('modal-overlay');
          if (overlay && overlay.classList.contains('show')) {
            closeModal();
          }
        }
      });
    </script>
</body>
</html>
