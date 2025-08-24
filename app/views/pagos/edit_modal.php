<?php
// app/views/pagos/edit_modal.php - Editar Pago (versión modal)
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { exit('Acceso denegado'); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { exit('ID no válido'); }

// Obtener datos del pago
$pago = null;
$sql = 'SELECT p.*, per.first_name, per.last_name, per.employee_code, per.salary 
        FROM pagos p 
        INNER JOIN personal per ON p.personal_id = per.id 
        WHERE p.id = ? LIMIT 1';
if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $pago = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}

if (!$pago) { exit('Pago no encontrado'); }

// Obtener lista de empleados
$personal = [];
$sql = 'SELECT id, first_name, last_name, employee_code, salary FROM personal WHERE status = "ACTIVO" ORDER BY first_name, last_name';
$res = mysqli_query($GLOBALS['conn'], $sql);
while ($row = mysqli_fetch_assoc($res)) {
    $personal[] = $row;
}

$pagoController = new PagoController($conn);
$months = $pagoController->getMonths();
$years = $pagoController->getYears();
?>

<style>
.modal-content {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal-header {
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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
  color: #8b5cf6;
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
  border-color: #8b5cf6;
  background: white;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
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

.salary-inputs {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.salary-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.salary-label {
  font-weight: 500;
  color: #374151;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.salary-label i {
  color: #8b5cf6;
  width: 14px;
}

.salary-input {
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.875rem;
  transition: all 0.2s ease;
  background: #f9fafb;
}

.salary-input:focus {
  outline: none;
  border-color: #8b5cf6;
  background: white;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
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
  border-color: #8b5cf6;
  background: #faf5ff;
}

.status-option input[type="radio"] {
  margin: 0;
  accent-color: #8b5cf6;
}

.status-option.selected {
  border-color: #8b5cf6;
  background: #faf5ff;
  color: #8b5cf6;
}

.salary-preview {
  background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
  padding: 1.5rem;
  border-radius: 12px;
  border: 2px solid #e5e7eb;
  margin-top: 1rem;
}

.salary-preview h5 {
  margin: 0 0 1rem 0;
  color: #8b5cf6;
  font-size: 1rem;
  font-weight: 600;
  text-align: center;
}

.salary-breakdown {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.salary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #e5e7eb;
}

.salary-item:last-child {
  border-bottom: none;
  border-top: 2px solid #8b5cf6;
  margin-top: 0.5rem;
  padding-top: 1rem;
  font-weight: 700;
  font-size: 1.1rem;
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
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
  box-shadow: 0 4px 6px rgba(245, 158, 11, 0.3);
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
  
  .salary-inputs {
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
    <i class="fa fa-money-bill-wave"></i>
    <h3>Editar Pago</h3>
  </div>
  
  <form id="editPagoForm" class="edit-form">
    <input type="hidden" name="id" value="<?= (int)$pago['id'] ?>">
    
    <div class="form-section">
      <h4><i class="fa fa-user"></i> Información del Empleado</h4>
      <div class="form-grid">
        <div class="form-group full-width">
          <label class="form-label">Empleado</label>
          <select name="personal_id" class="form-select" required>
            <option value="">Seleccionar empleado</option>
            <?php foreach ($personal as $emp): ?>
              <option value="<?= (int)$emp['id'] ?>" 
                      data-salary="<?= (float)$emp['salary'] ?>"
                      <?= $emp['id'] == $pago['personal_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['employee_code'] . ')') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-group">
          <label class="form-label">Mes</label>
          <select name="period_month" class="form-select" required>
            <option value="">Seleccionar mes</option>
            <?php foreach ($months as $num => $name): ?>
              <option value="<?= $num ?>" <?= $num == $pago['period_month'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($name) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-group">
          <label class="form-label">Año</label>
          <select name="period_year" class="form-select" required>
            <option value="">Seleccionar año</option>
            <?php foreach ($years as $year): ?>
              <option value="<?= $year ?>" <?= $year == $pago['period_year'] ? 'selected' : '' ?>>
                <?= $year ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-group">
          <label class="form-label">Estado</label>
          <div class="status-options">
            <label class="status-option <?= $pago['status'] == 'PENDIENTE' ? 'selected' : '' ?>">
              <input type="radio" name="status" value="PENDIENTE" <?= $pago['status'] == 'PENDIENTE' ? 'checked' : '' ?>>
              <i class="fa fa-clock"></i> Pendiente
            </label>
            <label class="status-option <?= $pago['status'] == 'PAGADO' ? 'selected' : '' ?>">
              <input type="radio" name="status" value="PAGADO" <?= $pago['status'] == 'PAGADO' ? 'checked' : '' ?>>
              <i class="fa fa-check-circle"></i> Pagado
            </label>
            <label class="status-option <?= $pago['status'] == 'ANULADO' ? 'selected' : '' ?>">
              <input type="radio" name="status" value="ANULADO" <?= $pago['status'] == 'ANULADO' ? 'checked' : '' ?>>
              <i class="fa fa-times-circle"></i> Anulado
            </label>
          </div>
        </div>
      </div>
    </div>
    
    <div class="form-section">
      <h4><i class="fa fa-calculator"></i> Información Salarial</h4>
      <div class="salary-inputs">
        <div class="salary-group">
          <label class="salary-label">
            <i class="fa fa-dollar-sign"></i> Salario Base
          </label>
          <input type="number" name="base_salary" class="salary-input" 
                 value="<?= htmlspecialchars($pago['base_salary']) ?>" 
                 step="0.01" min="0" required>
        </div>
        
        <div class="salary-group">
          <label class="salary-label">
            <i class="fa fa-plus-circle"></i> Bonos
          </label>
          <input type="number" name="bonuses" class="salary-input" 
                 value="<?= htmlspecialchars($pago['bonuses']) ?>" 
                 step="0.01" min="0">
        </div>
        
        <div class="salary-group">
          <label class="salary-label">
            <i class="fa fa-minus-circle"></i> Deducciones
          </label>
          <input type="number" name="deductions" class="salary-input" 
                 value="<?= htmlspecialchars($pago['deductions']) ?>" 
                 step="0.01" min="0">
        </div>
        
        <div class="salary-group">
          <label class="salary-label">
            <i class="fa fa-calendar"></i> Fecha de Pago
          </label>
          <input type="date" name="payment_date" class="salary-input" 
                 value="<?= htmlspecialchars($pago['payment_date']) ?>">
        </div>
      </div>
      
      <div class="salary-preview">
        <h5><i class="fa fa-eye"></i> Vista Previa del Salario</h5>
        <div class="salary-breakdown">
          <div class="salary-item">
            <span>Salario Base</span>
            <span id="preview-base">S/ <?= number_format($pago['base_salary'], 2) ?></span>
          </div>
          <div class="salary-item" id="preview-bonuses" style="<?= $pago['bonuses'] > 0 ? '' : 'display: none;' ?>">
            <span>Bonos</span>
            <span>+ S/ <?= number_format($pago['bonuses'], 2) ?></span>
          </div>
          <div class="salary-item" id="preview-deductions" style="<?= $pago['deductions'] > 0 ? '' : 'display: none;' ?>">
            <span>Deducciones</span>
            <span>- S/ <?= number_format($pago['deductions'], 2) ?></span>
          </div>
          <div class="salary-item">
            <span>Salario Neto</span>
            <span id="preview-net">S/ <?= number_format($pago['net_salary'], 2) ?></span>
          </div>
        </div>
      </div>
    </div>
    
    <div id="form-alert" style="display: none;"></div>
  </form>
  
  <div class="modal-actions">
    <button type="button" class="btn-secondary" onclick="if (parent && parent.closeModal) parent.closeModal();">
      <i class="fa fa-times"></i> Cancelar
    </button>
    <button type="submit" form="editPagoForm" class="btn-primary" id="submitBtn">
      <i class="fa fa-save"></i> Actualizar
    </button>
  </div>
</div>

<script>
// Auto-popular salario base cuando se selecciona empleado
document.querySelector('select[name="personal_id"]').addEventListener('change', function() {
  const selectedOption = this.options[this.selectedIndex];
  const salary = selectedOption.dataset.salary;
  if (salary) {
    document.querySelector('input[name="base_salary"]').value = salary;
    updateSalaryPreview();
  }
});

// Actualizar vista previa del salario
function updateSalaryPreview() {
  const baseSalary = parseFloat(document.querySelector('input[name="base_salary"]').value) || 0;
  const bonuses = parseFloat(document.querySelector('input[name="bonuses"]').value) || 0;
  const deductions = parseFloat(document.querySelector('input[name="deductions"]').value) || 0;
  const netSalary = baseSalary + bonuses - deductions;
  
  document.getElementById('preview-base').textContent = 'S/ ' + baseSalary.toFixed(2);
  document.getElementById('preview-net').textContent = 'S/ ' + netSalary.toFixed(2);
  
  // Mostrar/ocultar bonos y deducciones
  const bonusesPreview = document.getElementById('preview-bonuses');
  const deductionsPreview = document.getElementById('preview-deductions');
  
  if (bonuses > 0) {
    bonusesPreview.style.display = 'flex';
    bonusesPreview.querySelector('span:last-child').textContent = '+ S/ ' + bonuses.toFixed(2);
  } else {
    bonusesPreview.style.display = 'none';
  }
  
  if (deductions > 0) {
    deductionsPreview.style.display = 'flex';
    deductionsPreview.querySelector('span:last-child').textContent = '- S/ ' + deductions.toFixed(2);
  } else {
    deductionsPreview.style.display = 'none';
  }
}

// Event listeners para actualizar vista previa
document.querySelector('input[name="base_salary"]').addEventListener('input', updateSalaryPreview);
document.querySelector('input[name="bonuses"]').addEventListener('input', updateSalaryPreview);
document.querySelector('input[name="deductions"]').addEventListener('input', updateSalaryPreview);

document.getElementById('editPagoForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const submitBtn = document.getElementById('submitBtn');
  const originalText = submitBtn.innerHTML;
  const alertDiv = document.getElementById('form-alert');
  
  // Deshabilitar botón y mostrar loading
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span class="loading-spinner"></span> Actualizando...';
  alertDiv.style.display = 'none';
  
  const formData = new FormData(this);
  
  fetch('/Recursos/index.php?route=pagos.update&id=<?= (int)$pago['id'] ?>', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: formData
  })
  .then(response => response.json())
  .then(json => {
    if (json && json.success) {
      alertDiv.className = 'alert alert-success';
      alertDiv.innerHTML = '<i class="fa fa-check-circle"></i> ' + (json.message || 'Pago actualizado correctamente');
      alertDiv.style.display = 'block';
      
      setTimeout(() => {
        if (parent && parent.closeModal) parent.closeModal();
        if (parent && parent.location) parent.location.reload();
      }, 800);
    } else {
      throw new Error((json && json.message) || 'No se pudo actualizar el pago');
    }
  })
  .catch(error => {
    alertDiv.className = 'alert alert-error';
    alertDiv.innerHTML = '<i class="fa fa-exclamation-circle"></i> Error: ' + error.message;
    alertDiv.style.display = 'block';
    
    // Rehabilitar botón
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalText;
  });
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

// cierre del modal desde el contexto padre
function closeModal() {
  if (parent && parent.closeModal) parent.closeModal();
}
</script>
