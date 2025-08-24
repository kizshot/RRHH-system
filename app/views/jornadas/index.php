<?php
// app/views/jornadas/index.php - Listado de Jornadas
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /Recursos/index.php?route=login'); exit; }
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$statuses = ['COMPLETA','INCOMPLETA','AUSENTE','TARDANZA'];
?>

<style>
/* Estilos para el módulo de Jornadas */
.jornadas-header {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  padding: 2rem;
  border-radius: 16px;
  margin-bottom: 2rem;
  box-shadow: 0 10px 25px rgba(16, 185, 129, 0.15);
}

.jornadas-header h2 {
  margin: 0 0 0.5rem 0;
  font-size: 2rem;
  font-weight: 700;
}

.jornadas-header p {
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
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
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
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
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
  background: #f0fdf4;
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
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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

.time-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.time-entry {
  color: #059669;
  font-weight: 500;
}

.time-exit {
  color: #dc2626;
  font-weight: 500;
}

.time-break {
  color: #f59e0b;
  font-size: 0.75rem;
}

.hours-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-align: center;
  min-width: 60px;
}

.hours-complete {
  background: #dcfce7;
  color: #166534;
}

.hours-incomplete {
  background: #fef3c7;
  color: #92400e;
}

.hours-absent {
  background: #fee2e2;
  color: #991b1b;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.status-completa {
  background: #dcfce7;
  color: #166534;
}

.status-incompleta {
  background: #fef3c7;
  color: #92400e;
}

.status-ausente {
  background: #fee2e2;
  color: #991b1b;
}

.status-tardanza {
  background: #dbeafe;
  color: #1e40af;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
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
  background: #10b981;
}

.btn-view:hover {
  background: #059669;
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

.btn-add {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
  box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);
}

.btn-add:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 15px rgba(16, 185, 129, 0.3);
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
  background: #10b981;
  color: white;
  border-color: #10b981;
}

.pagination .current {
  background: #10b981;
  color: white;
  border-color: #10b981;
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
  .jornadas-header {
    padding: 1.5rem;
  }
  
  .jornadas-header h2 {
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

<div class="jornadas-header">
  <h2><i class="fa fa-clock"></i> Control de Jornadas</h2>
  <p>Gestiona los horarios de trabajo, entradas, salidas y tiempos de descanso</p>
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

<?php
// Helper para calcular horas totales para mostrar en la tabla
function hr_calculate_hours($entry_time, $exit_time, $break_start = null, $break_end = null) {
  if (!$entry_time || !$exit_time) return 0.0;
  $entry = strtotime($entry_time);
  $exit = strtotime($exit_time);
  if ($entry === false || $exit === false) return 0.0;
  $total_minutes = ($exit - $entry) / 60;
  if ($total_minutes < 0) $total_minutes += 24 * 60; // cruce de medianoche

  $break_minutes = 0;
  if ($break_start && $break_end) {
    $bs = strtotime($break_start);
    $be = strtotime($break_end);
    if ($bs !== false && $be !== false) {
      $break_minutes = ($be - $bs) / 60;
      if ($break_minutes < 0) $break_minutes += 24 * 60; // cruce de medianoche en descanso
    }
  }

  // No permitir que el descanso exceda la jornada trabajada
  $break_minutes = max(0, min($break_minutes, $total_minutes));
  $worked = max(0, $total_minutes - $break_minutes);
  return round($worked / 60, 2);
}
?>

<div class="search-container">
  <form method="get" action="/Recursos/index.php" class="search-form">
    <input type="hidden" name="route" value="jornadas.index">
    
    <div class="search-input">
      <i class="fa fa-search"></i>
      <input class="input" type="text" name="q" placeholder="Buscar por empleado..." value="<?= htmlspecialchars($q ?? '') ?>">
    </div>
    
    <select class="input" name="personal_id">
      <option value="">Todos los empleados</option>
      <?php foreach($personalList as $p): ?>
        <option value="<?= $p['id'] ?>" <?= (isset($personal_id) && $personal_id==$p['id'])?'selected':'' ?>>
          <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    
    <input class="input" type="date" name="date_from" placeholder="Desde" value="<?= htmlspecialchars($date_from ?? '') ?>">
    
    <input class="input" type="date" name="date_to" placeholder="Hasta" value="<?= htmlspecialchars($date_to ?? '') ?>">
    
    <select class="input" name="status">
      <option value="">Todos los estados</option>
      <?php foreach($statuses as $s): ?>
        <option value="<?= $s ?>" <?= (isset($status) && $status===$s)?'selected':'' ?>><?= $s ?></option>
      <?php endforeach; ?>
    </select>
    
    <button class="btn-add" type="submit">
      <i class="fa fa-search"></i> Buscar
    </button>
  </form>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
  <h3 style="margin: 0; color: #374151;">Registro de Jornadas</h3>
  <button class="btn-add" id="btn-open-modal">
    <i class="fa fa-plus"></i> Registrar Jornada
  </button>
</div>

<div class="table-container">
  <table class="data-table">
    <thead>
      <tr>
        <th>Empleado</th>
        <th>Fecha</th>
        <th>Horarios</th>
        <th>Horas</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($jornadas as $j): ?>
        <tr>
          <td>
            <div class="employee-info">
              <div class="employee-avatar">
                <?= strtoupper(substr($j['first_name'] ?? '', 0, 1) . substr($j['last_name'] ?? '', 0, 1)) ?>
              </div>
              <div class="employee-details">
                <h4><?= htmlspecialchars(trim(($j['first_name'] ?? '').' '.($j['last_name'] ?? ''))) ?></h4>
                <small><?= htmlspecialchars($j['employee_code']) ?></small>
              </div>
            </div>
          </td>
          <td>
            <strong><?= htmlspecialchars($j['date']) ?></strong>
          </td>
          <td>
            <div class="time-info">
              <?php if($j['entry_time']): ?>
                <div class="time-entry">
                  <i class="fa fa-sign-in"></i> <?= htmlspecialchars($j['entry_time']) ?>
                </div>
              <?php endif; ?>
              <?php if($j['exit_time']): ?>
                <div class="time-exit">
                  <i class="fa fa-sign-out"></i> <?= htmlspecialchars($j['exit_time']) ?>
                </div>
              <?php endif; ?>
              <?php if($j['break_start'] && $j['break_end']): ?>
                <div class="time-break">
                  <i class="fa fa-coffee"></i> <?= htmlspecialchars($j['break_start']) ?> - <?= htmlspecialchars($j['break_end']) ?>
                </div>
              <?php endif; ?>
            </div>
          </td>
          <td>
            <?php $calcHours = hr_calculate_hours($j['entry_time'], $j['exit_time'], $j['break_start'], $j['break_end']); ?>
            <span class="hours-badge <?= $calcHours >= 8 ? 'hours-complete' : ($calcHours > 0 ? 'hours-incomplete' : 'hours-absent') ?>">
              <?= $calcHours ? number_format($calcHours, 1) . 'h' : '0h' ?>
            </span>
          </td>
          <td>
            <span class="status-badge status-<?= strtolower($j['status']) ?>">
              <?= htmlspecialchars($j['status']) ?>
            </span>
          </td>
          <td>
            <div class="action-buttons">
              <button class="btn-action btn-view" onclick="openViewModal(<?= (int)$j['id'] ?>)" title="Ver detalles">
                <i class="fa fa-eye"></i>
              </button>
              <button class="btn-action btn-edit" onclick="openEditModal(<?= (int)$j['id'] ?>)" title="Editar">
                <i class="fa fa-pen"></i>
              </button>
              <button class="btn-action btn-delete" onclick="confirmDelete(<?= (int)$j['id'] ?>, '<?= htmlspecialchars($j['first_name'] . ' ' . $j['last_name']) ?> - ' + '<?= htmlspecialchars($j['date']) ?>')" title="Eliminar">
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
    <a href="/Recursos/index.php?route=jornadas.index&page=<?= ($page-1) . $qparam ?>">
      <i class="fa fa-chevron-left"></i> Anterior
    </a>
  <?php endif; ?>
  
  <span style="color: #6b7280; font-weight: 500;">
    Página <?= (int)$page ?> de <?= (int)$pageCount ?>
  </span>
  
  <?php if (($page ?? 1) < ($pageCount ?? 1)): ?>
    <a href="/Recursos/index.php?route=jornadas.index&page=<?= ($page+1) . $qparam ?>">
      Siguiente <i class="fa fa-chevron-right"></i>
    </a>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Modal dinámico -->
<div class="modal" id="modal-dynamic" role="dialog" aria-modal="true" aria-labelledby="modalDynTitle" style="display:none">
  <header style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-radius: 12px 12px 0 0;">
    <h3 id="modalDynTitle" style="margin:0; display: flex; align-items: center; gap: 0.5rem;">
      <i class="fa fa-clock"></i> <span id="modal-title">Jornada</span>
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

<!-- Modal crear jornada -->
<div class="modal" id="modal-create" role="dialog" aria-modal="true" aria-labelledby="modalTitle" style="display:none">
  <header style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-radius: 12px 12px 0 0;">
    <h3 id="modalTitle" style="margin:0; display: flex; align-items: center; gap: 0.5rem;">
      <i class="fa fa-plus"></i> Registrar Jornada
    </h3>
    <button class="icon-btn" id="btn-close-modal" title="Cerrar" style="color: white;">
      <i class="fa fa-times"></i>
    </button>
  </header>
  <form id="create-form" action="/Recursos/index.php?route=jornadas.create" method="post">
    <div class="modal-body">
      <div class="grid-2">
        <div class="form-row">
          <label for="personal_id" class="required">Empleado *</label>
          <select class="input" id="personal_id" name="personal_id" required>
            <option value="">Seleccionar empleado</option>
            <?php foreach($personalList as $p): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?> (<?= htmlspecialchars($p['employee_code']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <label for="date" class="required">Fecha *</label>
          <input class="input" type="date" id="date" name="date" required value="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-row">
          <label for="entry_time">Hora de Entrada</label>
          <input class="input" type="time" id="entry_time" name="entry_time">
        </div>
        <div class="form-row">
          <label for="exit_time">Hora de Salida</label>
          <input class="input" type="time" id="exit_time" name="exit_time">
        </div>
        <div class="form-row">
          <label for="break_start">Inicio de Descanso</label>
          <input class="input" type="time" id="break_start" name="break_start">
        </div>
        <div class="form-row">
          <label for="break_end">Fin de Descanso</label>
          <input class="input" type="time" id="break_end" name="break_end">
        </div>
        <div class="form-row">
          <label for="total_hours">Horas Totales</label>
          <input class="input" type="number" id="total_hours" name="total_hours" step="0.25" min="0" max="24" readonly>
          <small style="color: #6b7280;">Se calcula automáticamente a partir de entrada, salida y descanso.</small>
        </div>
        <div class="form-row">
          <label for="status">Estado</label>
          <select class="input" id="status" name="status">
            <?php foreach($statuses as $s): ?>
              <option value="<?= $s ?>"><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>
    <div class="modal-actions">
      <button type="button" class="button" id="btn-cancel" style="background: #6b7280;">Cancelar</button>
      <button type="submit" class="button" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
        <i class="fa fa-save"></i> Guardar
      </button>
    </div>
  </form>
</div>

<!-- Modal de confirmación de eliminación -->
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
    <p id="delete-message" style="font-size: 1.1rem; margin-bottom: 1rem;">¿Estás seguro de que quieres eliminar esta jornada?</p>
    <p style="color: #6b7280;"><strong>Esta acción no se puede deshacer.</strong></p>
  </div>
  <div class="modal-actions">
    <button type="button" class="button" id="btn-cancel-delete" style="background: #6b7280;">Cancelar</button>
    <button type="button" class="button" id="btn-confirm-delete" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
      <i class="fa fa-trash"></i> Eliminar
    </button>
  </div>
</div>

<script>
// Variables globales para eliminación
var deleteId = null;
var deleteName = null;

// Función para confirmar eliminación
function confirmDelete(id, name) {
  deleteId = id;
  deleteName = name;
  
  document.getElementById('delete-message').innerHTML = 
    '¿Estás seguro de que quieres eliminar la jornada de <strong>' + name + '</strong>?';
  
  var deleteModal = document.getElementById('modal-delete');
  var overlay = document.getElementById('modal-overlay');
  
  deleteModal.style.display = 'block';
  overlay.classList.add('show');
  
  deleteModal.style.top = '50%';
  deleteModal.style.left = '50%';
  deleteModal.style.transform = 'translate(-50%, -50%)';
}

// Función para ejecutar eliminación
function executeDelete() {
  if (deleteId && deleteName) {
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/Recursos/index.php?route=jornadas.delete';
    
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'id';
    input.value = deleteId;
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
  }
}

(function(){
  // Modal de crear jornada
  var openBtn = document.getElementById('btn-open-modal');
  var closeBtn = document.getElementById('btn-close-modal');
  var cancelBtn = document.getElementById('btn-cancel');
  var modal = document.getElementById('modal-create');
  var overlay = document.getElementById('modal-overlay');

  function showModal(){ 
    modal.style.display = 'block'; 
    overlay.classList.add('show'); 
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
    // Cálculo automático de horas totales en modal de crear
    function parseTimeToMinutesCreate(value){
      if(!value) return null;
      var parts = value.split(':');
      if(parts.length < 2) return null;
      var h = parseInt(parts[0],10), m = parseInt(parts[1],10);
      if(isNaN(h) || isNaN(m)) return null;
      return h*60 + m;
    }
    function computeTotalHoursCreate(){
      var entry = document.getElementById('entry_time') ? document.getElementById('entry_time').value : '';
      var exit = document.getElementById('exit_time') ? document.getElementById('exit_time').value : '';
      var bstart = document.getElementById('break_start') ? document.getElementById('break_start').value : '';
      var bend = document.getElementById('break_end') ? document.getElementById('break_end').value : '';
      var totalEl = document.getElementById('total_hours');
      var em = parseTimeToMinutesCreate(entry);
      var xm = parseTimeToMinutesCreate(exit);
      if(em === null || xm === null){ if(totalEl) totalEl.value=''; return; }
      var worked = xm - em; if(worked < 0) worked += 24*60;
      var bsm = parseTimeToMinutesCreate(bstart), bem = parseTimeToMinutesCreate(bend);
      if(bsm !== null && bem !== null){ var bd = bem - bsm; if(bd < 0) bd += 24*60; worked -= Math.max(0, bd); }
      var hrs = Math.max(0, worked)/60; var rounded = Math.round(hrs*100)/100;
      if(totalEl) totalEl.value = rounded.toFixed(2);
    }
    ['entry_time','exit_time','break_start','break_end'].forEach(function(id){
      var el = document.getElementById(id);
      if(el){ ['input','change'].forEach(function(evt){ el.addEventListener(evt, computeTotalHoursCreate); }); }
    });
    // cálculo inicial
    computeTotalHoursCreate();

    createForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      var formData = new FormData(this);
      // Adjuntar el total calculado si está presente (opcional, el servidor recalcula)
      var totalEl = document.getElementById('total_hours');
      if (totalEl && totalEl.value) { formData.set('total_hours', totalEl.value); }
      
      fetch('/Recursos/index.php?route=jornadas.create', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          hideModal();
          window.location.reload();
        } else {
          alert('Error al crear jornada: ' + data.message);
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
})();

// Rehidratar scripts embebidos en contenido HTML inyectado
function rehydrateModalScripts(container) {
  if (!container) return;
  const scripts = container.querySelectorAll('script');
  scripts.forEach(oldScript => {
    const newScript = document.createElement('script');
    // Copiar atributos (por si hay tipo/module)
    for (let i = 0; i < oldScript.attributes.length; i++) {
      const attr = oldScript.attributes[i];
      newScript.setAttribute(attr.name, attr.value);
    }
    newScript.text = oldScript.text;
    oldScript.parentNode.replaceChild(newScript, oldScript);
  });
}

// Función para abrir modal de vista
function openViewModal(id) {
  fetch('/Recursos/index.php?route=jornadas.view&id=' + id)
    .then(response => response.text())
    .then(data => {
      const container = document.getElementById('modal-content');
      container.innerHTML = data;
      rehydrateModalScripts(container);
      document.getElementById('modal-title').textContent = 'Ver Jornada';
      document.getElementById('modal-dynamic').style.display = 'block';
      document.getElementById('modal-overlay').classList.add('show');
    })
    .catch(error => {
      alert('Error al cargar datos: ' + error);
    });
}

// Función para abrir modal de edición
function openEditModal(id) {
  fetch('/Recursos/index.php?route=jornadas.edit&id=' + id)
    .then(response => response.text())
    .then(data => {
      const container = document.getElementById('modal-content');
      container.innerHTML = data;
      rehydrateModalScripts(container);
      document.getElementById('modal-title').textContent = 'Editar Jornada';
      document.getElementById('modal-dynamic').style.display = 'block';
      document.getElementById('modal-overlay').classList.add('show');
    })
    .catch(error => {
      alert('Error al cargar datos: ' + error);
    });
}

// Función global para editar jornada (usada desde modal de vista)
function editJornada(id) {
  // Mostrar indicador de carga si el botón está disponible
  const editBtn = event ? event.target.closest('button') : null;
  let originalContent = '';
  if (editBtn) {
    originalContent = editBtn.innerHTML;
    editBtn.disabled = true;
    editBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Cargando...';
  }
  
  fetch('/Recursos/index.php?route=jornadas.edit&id=' + id)
    .then(response => response.text())
    .then(data => {
      document.getElementById('modal-content').innerHTML = data;
      document.getElementById('modal-title').textContent = 'Editar Jornada';
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
  <link rel="stylesheet" href="/Recursos/assets/css/modal-fixes.css">
  <script src="/Recursos/assets/js/notifications.js"></script>
  <script src="/Recursos/assets/js/main.js"></script>
