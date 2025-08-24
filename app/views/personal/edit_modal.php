<?php
// app/views/personal/edit_modal.php - Modal de edición de personal
if (!isset($_SESSION['user_id'])) { exit; }

$personal = $personal ?? [];
$statuses = ['ACTIVO','INACTIVO','VACACIONES','LICENCIA'];
?>

<form id="edit-form" action="/Recursos/index.php?route=personal.update" method="post">
  <input type="hidden" name="id" value="<?= (int)($personal['id'] ?? 0) ?>">
  
  <div class="row g-3">
    <div class="col-md-6">
      <label for="edit_employee_code" class="form-label">Código de Empleado *</label>
      <input type="text" class="form-control" id="edit_employee_code" name="employee_code" 
             value="<?= htmlspecialchars($personal['employee_code'] ?? '') ?>" maxlength="20" required>
    </div>
    <div class="col-md-6">
      <label for="edit_dni" class="form-label">DNI</label>
      <input type="text" class="form-control" id="edit_dni" name="dni" 
             value="<?= htmlspecialchars($personal['dni'] ?? '') ?>" maxlength="20">
    </div>
    <div class="col-md-6">
      <label for="edit_first_name" class="form-label">Nombre *</label>
      <input type="text" class="form-control" id="edit_first_name" name="first_name" 
             value="<?= htmlspecialchars($personal['first_name'] ?? '') ?>" maxlength="100" required>
    </div>
    <div class="col-md-6">
      <label for="edit_last_name" class="form-label">Apellidos *</label>
      <input type="text" class="form-control" id="edit_last_name" name="last_name" 
             value="<?= htmlspecialchars($personal['last_name'] ?? '') ?>" maxlength="100" required>
    </div>
    <div class="col-md-6">
      <label for="edit_birth_date" class="form-label">Fecha de Nacimiento</label>
      <input type="date" class="form-control" id="edit_birth_date" name="birth_date" 
             value="<?= htmlspecialchars($personal['birth_date'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label for="edit_hire_date" class="form-label">Fecha de Contratación *</label>
      <input type="date" class="form-control" id="edit_hire_date" name="hire_date" 
             value="<?= htmlspecialchars($personal['hire_date'] ?? '') ?>" required>
    </div>
    <div class="col-md-6">
      <label for="edit_position" class="form-label">Posición</label>
      <input type="text" class="form-control" id="edit_position" name="position" 
             value="<?= htmlspecialchars($personal['position'] ?? '') ?>" maxlength="100">
    </div>
    <div class="col-md-6">
      <label for="edit_department" class="form-label">Departamento</label>
      <input type="text" class="form-control" id="edit_department" name="department" 
             value="<?= htmlspecialchars($personal['department'] ?? '') ?>" maxlength="100">
    </div>
    <div class="col-md-6">
      <label for="edit_salary" class="form-label">Salario</label>
      <input type="number" class="form-control" id="edit_salary" name="salary" 
             value="<?= htmlspecialchars($personal['salary'] ?? '') ?>" step="0.01" min="0">
    </div>
    <div class="col-md-6">
      <label for="edit_status" class="form-label">Estado</label>
      <select class="form-select" id="edit_status" name="status">
        <?php foreach($statuses as $s): ?>
          <option value="<?= $s ?>" <?= ($personal['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label for="edit_phone" class="form-label">Teléfono</label>
      <input type="tel" class="form-control" id="edit_phone" name="phone" 
             value="<?= htmlspecialchars($personal['phone'] ?? '') ?>" maxlength="20">
    </div>
    <div class="col-12">
      <label for="edit_address" class="form-label">Dirección</label>
      <textarea class="form-control" id="edit_address" name="address" rows="2"><?= htmlspecialchars($personal['address'] ?? '') ?></textarea>
    </div>
    <div class="col-md-6">
      <label for="edit_emergency_contact" class="form-label">Contacto de Emergencia</label>
      <input type="text" class="form-control" id="edit_emergency_contact" name="emergency_contact" 
             value="<?= htmlspecialchars($personal['emergency_contact'] ?? '') ?>" maxlength="100">
    </div>
    <div class="col-md-6">
      <label for="edit_emergency_phone" class="form-label">Teléfono de Emergencia</label>
      <input type="tel" class="form-control" id="edit_emergency_phone" name="emergency_phone" 
             value="<?= htmlspecialchars($personal['emergency_phone'] ?? '') ?>" maxlength="20">
    </div>
  </div>
  
  <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    <button type="submit" class="btn btn-primary">
      <i class="fa fa-save"></i> Actualizar
    </button>
  </div>
</form>

<script>
// Manejar formulario de edición
document.addEventListener('DOMContentLoaded', function() {
  const editForm = document.getElementById('edit-form');
  if (editForm) {
    editForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      const submitBtn = this.querySelector('[type="submit"]');
      const originalText = submitBtn.innerHTML;
      
      // Mostrar loading
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Procesando...';
      
      fetch('/Recursos/index.php?route=personal.update', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        try {
          const result = JSON.parse(data);
          if (result.success) {
            // Mostrar notificación de éxito
            if (window.HR365 && window.HR365.showNotification) {
              window.HR365.showNotification('success', result.message || 'Personal actualizado exitosamente');
            }
            
            // Cerrar modal y recargar página
            const modal = bootstrap.Modal.getInstance(document.getElementById('modal-dynamic'));
            if (modal) modal.hide();
            
            setTimeout(() => {
              window.location.reload();
            }, 1500);
          } else {
            // Mostrar error
            if (window.HR365 && window.HR365.showNotification) {
              window.HR365.showNotification('error', result.message || 'Error al actualizar personal');
            } else {
              alert('Error: ' + (result.message || 'Error al actualizar personal'));
            }
          }
        } catch (e) {
          // Si no es JSON, verificar si contiene mensajes de éxito/error
          if (data.includes('success') || data.includes('exitosamente')) {
            if (window.HR365 && window.HR365.showNotification) {
              window.HR365.showNotification('success', 'Personal actualizado exitosamente');
            }
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('modal-dynamic'));
            if (modal) modal.hide();
            
            setTimeout(() => {
              window.location.reload();
            }, 1500);
          } else {
            if (window.HR365 && window.HR365.showNotification) {
              window.HR365.showNotification('error', 'Error al actualizar personal');
            } else {
              alert('Error al actualizar personal');
            }
          }
        }
      })
      .catch(error => {
        console.error('Error:', error);
        if (window.HR365 && window.HR365.showNotification) {
          window.HR365.showNotification('error', 'Error de conexión');
        } else {
          alert('Error de conexión: ' + error.message);
        }
      })
      .finally(() => {
        // Rehabilitar botón
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      });
    });
  }
});
</script>
