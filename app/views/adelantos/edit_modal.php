<?php
// app/views/adelantos/edit_modal.php - Editar Adelanto (versión modal)
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { exit('Acceso denegado'); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { exit('ID no válido'); }

// Obtener datos del adelanto
$adelanto = null;
$sql = 'SELECT * FROM adelantos WHERE id = ? LIMIT 1';
if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $adelanto = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}

if (!$adelanto) { exit('Adelanto no encontrado'); }

// Obtener lista de empleados para el select
$personal_list = [];
$sql = 'SELECT id, first_name, last_name, employee_code FROM personal WHERE is_deleted = 0 ORDER BY first_name, last_name';
if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $personal_list[] = $row;
    }
    mysqli_stmt_close($stmt);
}

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$statuses = ['PENDIENTE','APROBADO','RECHAZADO','PAGADO'];
?>

<style>
.modal-content {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal-header {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
  color: #f59e0b;
  width: 16px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.form-row {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-row label {
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
}

.form-row label.required::after {
  content: ' *';
  color: #ef4444;
}

.form-row input,
.form-row select,
.form-row textarea {
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.875rem;
  transition: all 0.2s ease;
  background: #f9fafb;
}

.form-row input:focus,
.form-row select:focus,
.form-row textarea:focus {
  outline: none;
  border-color: #f59e0b;
  background: white;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

.form-row input.error,
.form-row select.error,
.form-row textarea.error {
  border-color: #ef4444;
  background: #fef2f2;
}

.form-row .error-message {
  color: #ef4444;
  font-size: 0.75rem;
  margin-top: 0.25rem;
}

.form-row .helper-text {
  color: #6b7280;
  font-size: 0.75rem;
  margin-top: 0.25rem;
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

.alert {
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.alert.error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #991b1b;
}

.alert.success {
  background: #ecfdf5;
  border: 1px solid #a7f3d0;
  color: #065f46;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .edit-form {
    padding: 1rem;
  }
  
  .modal-actions {
    flex-direction: column;
    padding: 1rem;
  }
}
</style>

<div class="modal-content">
  <div class="modal-header">
    <i class="fa fa-pen"></i>
    <h3>Editar Adelanto</h3>
  </div>
  
  <?php if($error): ?>
    <div class="alert error">
      <i class="fa fa-exclamation-triangle"></i> <?= $error ?>
    </div>
  <?php endif; ?>
  
  <?php if($success): ?>
    <div class="alert success">
      <i class="fa fa-check-circle"></i> <?= $success ?>
    </div>
  <?php endif; ?>
  
  <form id="edit-form" action="/Recursos/index.php?route=adelantos.update" method="post" class="edit-form">
    <input type="hidden" name="id" value="<?= (int)$adelanto['id'] ?>">
    
    <div class="form-section">
      <h4><i class="fa fa-user"></i> Información del Empleado</h4>
      <div class="form-grid">
        <div class="form-row">
          <label for="personal_id" class="required">Empleado</label>
          <select class="input" id="personal_id" name="personal_id" required>
            <option value="">Seleccionar empleado</option>
            <?php foreach($personal_list as $p): ?>
              <option value="<?= $p['id'] ?>" <?= ($adelanto['personal_id'] == $p['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?> (<?= htmlspecialchars($p['employee_code']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
          <div class="helper-text">Empleado que solicita el adelanto</div>
        </div>
        <div class="form-row">
          <label for="amount" class="required">Monto</label>
          <input type="number" id="amount" name="amount" step="0.01" min="0" required value="<?= htmlspecialchars($adelanto['amount']) ?>">
          <div class="helper-text">Monto del adelanto en soles</div>
        </div>
      </div>
    </div>
    
    <div class="form-section">
      <h4><i class="fa fa-calendar"></i> Fechas y Estado</h4>
      <div class="form-grid">
        <div class="form-row">
          <label for="request_date" class="required">Fecha de Solicitud</label>
          <input type="date" id="request_date" name="request_date" required value="<?= htmlspecialchars($adelanto['request_date']) ?>">
        </div>
        <div class="form-row">
          <label for="status">Estado</label>
          <select id="status" name="status">
            <?php foreach($statuses as $s): ?>
              <option value="<?= $s ?>" <?= ($adelanto['status'] === $s) ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <label for="approved_date">Fecha de Aprobación</label>
          <input type="date" id="approved_date" name="approved_date" value="<?= htmlspecialchars($adelanto['approved_date'] ?? '') ?>">
          <div class="helper-text">Fecha cuando se aprobó el adelanto</div>
        </div>
        <div class="form-row">
          <label for="payment_date">Fecha de Pago</label>
          <input type="date" id="payment_date" name="payment_date" value="<?= htmlspecialchars($adelanto['payment_date'] ?? '') ?>">
          <div class="helper-text">Fecha cuando se pagó el adelanto</div>
        </div>
      </div>
    </div>
    
    <div class="form-section">
      <h4><i class="fa fa-file-text"></i> Detalles del Adelanto</h4>
      <div class="form-grid">
        <div class="form-row" style="grid-column: 1 / -1;">
          <label for="reason" class="required">Motivo</label>
          <textarea id="reason" name="reason" rows="3" required><?= htmlspecialchars($adelanto['reason'] ?? '') ?></textarea>
          <div class="helper-text">Motivo por el cual se solicita el adelanto</div>
        </div>
        <div class="form-row" style="grid-column: 1 / -1;">
          <label for="notes">Notas</label>
          <textarea id="notes" name="notes" rows="2"><?= htmlspecialchars($adelanto['notes'] ?? '') ?></textarea>
          <div class="helper-text">Notas adicionales sobre el adelanto</div>
        </div>
      </div>
    </div>
    
    <div class="modal-actions">
      <button type="button" class="btn-secondary" onclick="closeModal()">
        <i class="fa fa-times"></i> Cancelar
      </button>
      <button type="submit" class="btn-primary" id="submit-btn">
        <i class="fa fa-save"></i> Actualizar
      </button>
    </div>
  </form>
</div>

<script>
document.getElementById('edit-form').addEventListener('submit', function(e) {
  e.preventDefault();
  
  // Deshabilitar botón durante el envío
  const submitBtn = document.getElementById('submit-btn');
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Actualizando...';
  
  var formData = new FormData(this);
  
  fetch('/Recursos/index.php?route=adelantos.update', {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Mostrar mensaje de éxito
      const successAlert = document.createElement('div');
      successAlert.className = 'alert success';
      successAlert.innerHTML = '<i class="fa fa-check-circle"></i> Adelanto actualizado correctamente';
      
      const form = document.getElementById('edit-form');
      form.insertBefore(successAlert, form.firstChild);
      
      // Cerrar modal y recargar página después de 1 segundo
      setTimeout(() => {
        closeModal();
        window.location.reload();
      }, 1000);
    } else {
      // Mostrar error
      const errorAlert = document.createElement('div');
      errorAlert.className = 'alert error';
      errorAlert.innerHTML = '<i class="fa fa-exclamation-triangle"></i> Error al actualizar: ' + data.message;
      
      const form = document.getElementById('edit-form');
      form.insertBefore(errorAlert, form.firstChild);
      
      // Rehabilitar botón
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="fa fa-save"></i> Actualizar';
    }
  })
  .catch(error => {
    // Mostrar error de red
    const errorAlert = document.createElement('div');
    errorAlert.className = 'alert error';
    errorAlert.innerHTML = '<i class="fa fa-exclamation-triangle"></i> Error de conexión: ' + error;
    
    const form = document.getElementById('edit-form');
    form.insertBefore(errorAlert, form.firstChild);
    
    // Rehabilitar botón
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="fa fa-save"></i> Actualizar';
  });
});

function closeModal() {
  var modal = document.getElementById('modal-dynamic');
  var overlay = document.getElementById('modal-overlay');
  if (modal) modal.style.display = 'none';
  if (overlay) overlay.classList.remove('show');
}
</script>
