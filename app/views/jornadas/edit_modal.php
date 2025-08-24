<?php
// app/views/jornadas/edit_modal.php - Editar Jornada (versión modal)
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { exit('Acceso denegado'); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { exit('ID no válido'); }

// Obtener datos de la jornada
$jornada = null;
$sql = 'SELECT j.*, per.first_name, per.last_name, per.employee_code 
        FROM jornadas j 
        INNER JOIN personal per ON j.personal_id = per.id 
        WHERE j.id = ? LIMIT 1';
if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $jornada = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}

if (!$jornada) { exit('Jornada no encontrada'); }

// Obtener lista de empleados
$personal = [];
$sql = 'SELECT id, first_name, last_name, employee_code FROM personal WHERE status = "ACTIVO" ORDER BY first_name, last_name';
$res = mysqli_query($GLOBALS['conn'], $sql);
while ($row = mysqli_fetch_assoc($res)) {
    $personal[] = $row;
}
?>

<style>
.modal-content {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal-header {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.edit-form {
  padding: 2rem;
}

.form-section {
  margin-bottom: 2rem;
}

.form-section h4 {
  margin: 0 0 1rem 0;
  color: #374151;
  font-size: 1.1rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e5e7eb;
}

.form-section h4 i {
  color: #10b981;
  width: 16px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-label {
  font-weight: 500;
  color: #374151;
  font-size: 0.875rem;
}

.form-input,
.form-select,
.form-textarea {
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.875rem;
  transition: all 0.2s ease;
  background: #f9fafb;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  outline: none;
  border-color: #10b981;
  background: white;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.form-input.error,
.form-select.error,
.form-textarea.error {
  border-color: #ef4444;
  background: #fef2f2;
}

.form-input.error:focus,
.form-select.error:focus,
.form-textarea.error:focus {
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.form-helper {
  font-size: 0.75rem;
  color: #6b7280;
  margin-top: 0.25rem;
}

.form-error {
  font-size: 0.75rem;
  color: #ef4444;
  margin-top: 0.25rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.time-inputs {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.time-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.time-label {
  font-weight: 500;
  color: #374151;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.time-label i {
  color: #10b981;
  width: 14px;
}

.time-input {
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.875rem;
  transition: all 0.2s ease;
  background: #f9fafb;
}

.time-input:focus {
  outline: none;
  border-color: #10b981;
  background: white;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.status-options {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.status-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  background: #f9fafb;
}

.status-option:hover {
  border-color: #10b981;
  background: #f0fdf4;
}

.status-option input[type="radio"] {
  margin: 0;
  accent-color: #10b981;
}

.status-option.selected {
  border-color: #10b981;
  background: #f0fdf4;
  color: #10b981;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding: 1.5rem 2rem;
  background: #f9fafb;
  border-top: 1px solid #e5e7eb;
}

.btn-secondary {
  background: #6b7280;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-secondary:hover {
  background: #4b5563;
  transform: translateY(-1px);
}

.btn-primary {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.loading-spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid transparent;
  border-top: 2px solid currentColor;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.alert {
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.alert-success {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #166534;
}

.alert-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #991b1b;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .time-inputs {
    grid-template-columns: 1fr;
  }
  
  .status-options {
    flex-direction: column;
  }
  
  .modal-actions {
    flex-direction: column;
    padding: 1rem;
  }
}
</style>

<div class="modal-content">
  <div class="modal-header">
    <i class="fa fa-clock"></i>
    <h3>Editar Jornada</h3>
  </div>
  
  <form id="editJornadaForm" class="edit-form">
    <input type="hidden" name="id" value="<?= (int)$jornada['id'] ?>">
    
    <div class="form-section">
      <h4><i class="fa fa-user"></i> Información del Empleado</h4>
      <div class="form-grid">
        <div class="form-group full-width">
          <label class="form-label">Empleado</label>
          <select name="personal_id" class="form-select" required>
            <option value="">Seleccionar empleado</option>
            <?php foreach ($personal as $emp): ?>
              <option value="<?= (int)$emp['id'] ?>" <?= $emp['id'] == $jornada['personal_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['employee_code'] . ')') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-group">
          <label class="form-label">Fecha</label>
          <input type="date" name="date" class="form-input" value="<?= htmlspecialchars($jornada['date']) ?>" required>
        </div>
        
        <div class="form-group">
          <label class="form-label">Estado</label>
          <div class="status-options">
            <label class="status-option <?= $jornada['status'] == 'COMPLETA' ? 'selected' : '' ?>">
              <input type="radio" name="status" value="COMPLETA" <?= $jornada['status'] == 'COMPLETA' ? 'checked' : '' ?>>
              <i class="fa fa-check-circle"></i> Completa
            </label>
            <label class="status-option <?= $jornada['status'] == 'INCOMPLETA' ? 'selected' : '' ?>">
              <input type="radio" name="status" value="INCOMPLETA" <?= $jornada['status'] == 'INCOMPLETA' ? 'checked' : '' ?>>
              <i class="fa fa-clock"></i> Incompleta
            </label>
            <label class="status-option <?= $jornada['status'] == 'AUSENTE' ? 'selected' : '' ?>">
              <input type="radio" name="status" value="AUSENTE" <?= $jornada['status'] == 'AUSENTE' ? 'checked' : '' ?>>
              <i class="fa fa-times-circle"></i> Ausente
            </label>
            <label class="status-option <?= $jornada['status'] == 'TARDANZA' ? 'selected' : '' ?>">
              <input type="radio" name="status" value="TARDANZA" <?= $jornada['status'] == 'TARDANZA' ? 'checked' : '' ?>>
              <i class="fa fa-hourglass-half"></i> Tardanza
            </label>
          </div>
        </div>
      </div>
    </div>
    
    <div class="form-section">
      <h4><i class="fa fa-clock"></i> Horarios de Trabajo</h4>
      <div class="time-inputs">
        <div class="time-group">
          <label class="time-label">
            <i class="fa fa-sign-in-alt"></i> Hora de Entrada
          </label>
          <input type="time" name="entry_time" class="time-input" value="<?= htmlspecialchars($jornada['entry_time']) ?>">
        </div>
        
        <div class="time-group">
          <label class="time-label">
            <i class="fa fa-sign-out-alt"></i> Hora de Salida
          </label>
          <input type="time" name="exit_time" class="time-input" value="<?= htmlspecialchars($jornada['exit_time']) ?>">
        </div>
        
        <div class="time-group">
          <label class="time-label">
            <i class="fa fa-coffee"></i> Inicio de Descanso
          </label>
          <input type="time" name="break_start" class="time-input" value="<?= htmlspecialchars($jornada['break_start']) ?>">
        </div>
        
        <div class="time-group">
          <label class="time-label">
            <i class="fa fa-coffee"></i> Fin de Descanso
          </label>
          <input type="time" name="break_end" class="time-input" value="<?= htmlspecialchars($jornada['break_end']) ?>">
        </div>
      </div>
      
      <div class="form-group" style="margin-top: 1rem;">
        <label class="form-label">Horas Totales</label>
        <input type="number" name="total_hours" class="form-input" value="<?= htmlspecialchars($jornada['total_hours']) ?>" step="0.25" min="0" max="24" readonly>
        <div class="form-helper">Se calcula automáticamente según entrada/salida y descanso (formato decimal, ej: 8.5)</div>
      </div>
    </div>
    
    <div id="form-alert" style="display: none;"></div>
  </form>
  
  <div class="modal-actions">
    <button type="button" class="btn-secondary" onclick="if (parent && parent.closeModal) parent.closeModal();">
      <i class="fa fa-times"></i> Cancelar
    </button>
    <button type="button" class="btn-primary" id="submitBtn" onclick="submitEditForm()">
      <i class="fa fa-save"></i> Actualizar
    </button>
  </div>
</div>

<script>
function submitEditForm() {
  const form = document.getElementById('editJornadaForm');
  const submitBtn = document.getElementById('submitBtn');
  const originalText = submitBtn.innerHTML;
  const alertDiv = document.getElementById('form-alert');

  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span class="loading-spinner"></span> Actualizando...';
  alertDiv.style.display = 'none';

  const formData = new FormData(form);
  const totalInput = form.querySelector('input[name="total_hours"]');
  if (totalInput && totalInput.value) {
    formData.set('total_hours', totalInput.value);
  }

  fetch('/Recursos/index.php?route=jornadas.update&id=<?= (int)$jornada['id'] ?>', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: formData
  })
    .then(response => response.json())
    .then(json => {
      if (json && json.success) {
        alertDiv.className = 'alert alert-success';
        alertDiv.innerHTML = '<i class="fa fa-check-circle"></i> ' + (json.message || 'Jornada actualizada correctamente');
        alertDiv.style.display = 'block';

        setTimeout(() => {
          if (parent && parent.closeModal) parent.closeModal();
          if (parent && parent.location) parent.location.reload();
        }, 600);
      } else {
        throw new Error((json && json.message) || 'No se pudo actualizar la jornada');
      }
    })
    .catch(error => {
      alertDiv.className = 'alert alert-error';
      alertDiv.innerHTML = '<i class="fa fa-exclamation-circle"></i> Error: ' + error.message;
      alertDiv.style.display = 'block';

      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
    });
}

document.getElementById('editJornadaForm').addEventListener('submit', function(e) {
  e.preventDefault();
  submitEditForm();
});

document.getElementById('submitBtn').addEventListener('click', function() {
  submitEditForm();
});

// Manejar selección de status
document.querySelectorAll('input[name="status"]').forEach(radio => {
  radio.addEventListener('change', function() {
    document.querySelectorAll('.status-option').forEach(option => {
      option.classList.remove('selected');
    });
    this.closest('.status-option').classList.add('selected');
  });
});

// Recalcular horas totales automáticamente
function parseTimeToMinutes(value) {
  if (!value) return null;
  // value puede venir como HH:MM o HH:MM:SS
  const parts = value.split(':').map(p => parseInt(p, 10));
  if (parts.length < 2 || isNaN(parts[0]) || isNaN(parts[1])) return null;
  const hours = parts[0];
  const minutes = parts[1];
  return hours * 60 + minutes;
}

function computeTotalHours() {
  const entryEl = document.querySelector('input[name="entry_time"]');
  const exitEl = document.querySelector('input[name="exit_time"]');
  const breakStartEl = document.querySelector('input[name="break_start"]');
  const breakEndEl = document.querySelector('input[name="break_end"]');
  const totalEl = document.querySelector('input[name="total_hours"]');

  const entryMin = parseTimeToMinutes(entryEl && entryEl.value);
  const exitMin = parseTimeToMinutes(exitEl && exitEl.value);
  if (entryMin === null || exitMin === null) {
    if (totalEl) totalEl.value = '';
    return;
  }

  // Soportar cruce de medianoche
  let worked = exitMin - entryMin;
  if (worked < 0) worked += 24 * 60;

  const breakStartMin = parseTimeToMinutes(breakStartEl && breakStartEl.value);
  const breakEndMin = parseTimeToMinutes(breakEndEl && breakEndEl.value);
  if (breakStartMin !== null && breakEndMin !== null) {
    let breakDur = breakEndMin - breakStartMin;
    if (breakDur < 0) breakDur += 24 * 60;
    worked -= Math.max(0, breakDur);
  }

  const hours = Math.max(0, worked) / 60;
  // Redondeo a 2 decimales
  const rounded = Math.round(hours * 100) / 100;
  if (totalEl) totalEl.value = rounded.toFixed(2);
}

['input', 'change'].forEach(evt => {
  ['entry_time', 'exit_time', 'break_start', 'break_end'].forEach(name => {
    const el = document.querySelector(`input[name="${name}"]`);
    if (el) el.addEventListener(evt, computeTotalHours);
  });
});

// cálculo inicial al cargar el modal
computeTotalHours();

</script>
