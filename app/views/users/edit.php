<?php
// app/views/users/edit.php - Editar usuario (versión para modal)
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { 
  echo '<div class="alert error">Sesión expirada. Por favor, recarga la página.</div>';
  exit; 
}

$roles = $roles ?? ['SUPER USER','TRABAJADOR','VENDEDOR','RECEPCIONISTA','CHOFER','PROGRAMADOR'];
$statuses = $statuses ?? ['ACTIVO','INACTIVO'];
?>
      <div class="card">
        <h2 style="margin-top:0; color:#374151; border-bottom:2px solid #e5e7eb; padding-bottom:0.5rem;">Editar Usuario</h2>
        <?php if(!$user): ?>
          <div class="alert error">Usuario no encontrado</div>
        <?php else: ?>
        <form action="/Recursos/index.php?route=users.update" method="post" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
          
          <div class="form-section" style="margin-bottom:2rem;">
            <h4 style="margin:0 0 1rem 0; color:#667eea; font-size:1.125rem; border-bottom:2px solid #e5e7eb; padding-bottom:0.5rem;">Información Personal</h4>
            <div class="grid-2">
              <div class="form-row">
                <label for="first_name">Nombre</label>
                <input class="input" type="text" id="first_name" name="first_name" maxlength="100" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" placeholder="Ingrese el nombre">
              </div>
              <div class="form-row">
                <label for="last_name">Apellidos</label>
                <input class="input" type="text" id="last_name" name="last_name" maxlength="100" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" placeholder="Ingrese los apellidos">
              </div>
            </div>
          </div>

          <div class="form-section" style="margin-bottom:2rem;">
            <h4 style="margin:0 0 1rem 0; color:#667eea; font-size:1.125rem; border-bottom:2px solid #e5e7eb; padding-bottom:0.5rem;">Configuración del Usuario</h4>
            <div class="grid-2">
              <div class="form-row">
                <label for="role">Rol Usuario</label>
                <select class="input" id="role" name="role" required>
                  <option value="">-- Seleccionar Rol --</option>
                  <?php foreach($roles as $r): ?>
                    <option value="<?= $r ?>" <?= ($user['role']??'')===$r?'selected':'' ?>><?= $r ?></option>
                  <?php endforeach; ?>
                </select>
                <small>Seleccione el rol que tendrá este usuario en el sistema</small>
              </div>
              <div class="form-row">
                <label for="status">Estado</label>
                <select class="input" id="status" name="status">
                  <?php foreach($statuses as $s): ?>
                    <option value="<?= $s ?>" <?= ($user['status']??'')===$s?'selected':'' ?>><?= $s ?></option>
                  <?php endforeach; ?>
                </select>
                <small>Determine si el usuario estará activo o inactivo</small>
              </div>
              <div class="form-row">
                <label for="code">Código</label>
                <input class="input" type="text" id="code" name="code" maxlength="32" value="<?= htmlspecialchars($user['code'] ?? '') ?>" placeholder="Código interno del usuario">
                <small>Código de identificación interno (opcional)</small>
              </div>
              <div class="form-row">
                <label for="avatar">Imagen de Perfil</label>
                <input class="input" type="file" id="avatar" name="avatar" accept="image/*">
                <small>Formatos aceptados: JPG, PNG, GIF. Máximo 2MB</small>
              </div>
            </div>
          </div>
          
          <div class="modal-actions">
            <button type="button" class="button btn-secondary" onclick="closeModal()">
              <i class="fa fa-times"></i> Cancelar
            </button>
            <button class="button btn-primary" type="submit">
              <i class="fa fa-save"></i> Guardar Cambios
            </button>
          </div>
        </form>
        <?php endif; ?>
      </div>

<script>
function closeModal() {
  // Cerrar el modal desde el iframe
  if (window.parent && window.parent.hideDynamicModal) {
    window.parent.hideDynamicModal();
  }
}

// Manejar el envío del formulario
document.querySelector('form').addEventListener('submit', function(e) {
  e.preventDefault(); // Prevenir el envío normal del formulario
  
  // Mostrar indicador de carga
  var submitBtn = this.querySelector('button[type="submit"]');
  var originalText = submitBtn.innerHTML;
  submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Guardando...';
  submitBtn.disabled = true;
  
  // Obtener los datos del formulario
  var formData = new FormData(this);
  
  // Enviar la petición AJAX
  fetch(this.action, {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then(data => {
    // Mostrar mensaje de éxito
    submitBtn.innerHTML = '<i class="fa fa-check"></i> ¡Guardado!';
    submitBtn.style.background = '#10b981';
    
    // Actualizar la tabla en tiempo real
    if (window.parent && window.parent.updateUserTable) {
      window.parent.updateUserTable();
    }
    
    // Cerrar el modal después de un breve delay
    setTimeout(function() {
      if (window.parent && window.parent.hideDynamicModal) {
        window.parent.hideDynamicModal();
      }
    }, 1500);
  })
  .catch(error => {
    // Mostrar error
    submitBtn.innerHTML = '<i class="fa fa-exclamation-triangle"></i> Error';
    submitBtn.style.background = '#ef4444';
    submitBtn.disabled = false;
    
    // Restaurar el botón después de un delay
    setTimeout(function() {
      submitBtn.innerHTML = originalText;
      submitBtn.style.background = '';
    }, 3000);
  });
});
</script>

<style>
.form-section {
  background: #f9fafb;
  padding: 1.5rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.form-section h4 {
  color: #667eea !important;
  margin: 0 0 1rem 0 !important;
  font-size: 1.125rem !important;
  border-bottom: 2px solid #e5e7eb !important;
  padding-bottom: 0.5rem !important;
}

.form-row {
  margin-bottom: 1rem !important;
}

.form-row:last-child {
  margin-bottom: 0 !important;
}

.form-row label {
  display: block !important;
  margin-bottom: 0.5rem !important;
  font-weight: 600 !important;
  color: #374151 !important;
  font-size: 0.875rem !important;
  text-transform: uppercase !important;
  letter-spacing: 0.05em !important;
}

.form-row input,
.form-row select {
  width: 100% !important;
  padding: 0.75rem 1rem !important;
  border: 2px solid #e5e7eb !important;
  border-radius: 8px !important;
  font-size: 1rem !important;
  color: #374151 !important;
  background: white !important;
  transition: all 0.2s ease !important;
  box-sizing: border-box !important;
}

.form-row input:focus,
.form-row select:focus {
  outline: none !important;
  border-color: #667eea !important;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
}

.form-row input:hover,
.form-row select:hover {
  border-color: #d1d5db !important;
}

.form-row small {
  display: block !important;
  margin-top: 0.25rem !important;
  color: #6b7280 !important;
  font-size: 0.875rem !important;
  font-style: italic !important;
}

.grid-2 {
  display: grid !important;
  grid-template-columns: repeat(2, 1fr) !important;
  gap: 1rem !important;
}

.modal-actions {
  display: flex !important;
  gap: 1rem !important;
  justify-content: flex-end !important;
  align-items: center !important;
  flex-wrap: wrap !important;
  padding: 1rem 0 !important;
  border-top: 1px solid #e5e7eb !important;
  margin-top: 2rem !important;
}

.button {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 0.5rem !important;
  padding: 0.75rem 1.5rem !important;
  border: none !important;
  border-radius: 8px !important;
  font-size: 0.875rem !important;
  font-weight: 600 !important;
  text-decoration: none !important;
  cursor: pointer !important;
  transition: all 0.2s ease !important;
  min-width: 120px !important;
}

.button:hover {
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

.button:active {
  transform: translateY(0) !important;
}

.btn-primary {
  background: #667eea !important;
  color: white !important;
}

.btn-primary:hover {
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3) !important;
}

.btn-secondary {
  background: #6b7280 !important;
  color: white !important;
}

.btn-secondary:hover {
  box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3) !important;
}

@media (max-width: 768px) {
  .grid-2 {
    grid-template-columns: 1fr !important;
  }
  
  .modal-actions {
    flex-direction: column !important;
  }
  
  .modal-actions .button {
    width: 100% !important;
  }
}
</style>

