<?php
// app/views/personal/view_modal.php - Ver Personal (versión modal)
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { exit('Acceso denegado'); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { exit('ID no válido'); }

// Obtener datos del personal
$personal = null;
$sql = 'SELECT p.*, u.username, u.email FROM personal p LEFT JOIN users u ON p.user_id = u.id WHERE p.id = ? LIMIT 1';
if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $personal = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}

if (!$personal) { exit('Personal no encontrado'); }
?>

<style>
.modal-content {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

.employee-profile {
  padding: 2rem;
  text-align: center;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-bottom: 1px solid #e5e7eb;
}

.employee-avatar-large {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 1.5rem;
  margin: 0 auto 1rem;
  box-shadow: 0 10px 15px -3px rgba(102, 126, 234, 0.3);
}

.employee-name {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.5rem;
}

.employee-code {
  color: #6b7280;
  font-size: 1rem;
  margin-bottom: 1rem;
}

.employee-status {
  display: inline-block;
  padding: 0.5rem 1rem;
  border-radius: 25px;
  font-size: 0.875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.status-active {
  background: #dcfce7;
  color: #166534;
}

.status-vacation {
  background: #fef3c7;
  color: #92400e;
}

.status-inactive {
  background: #fee2e2;
  color: #991b1b;
}

.status-license {
  background: #dbeafe;
  color: #1e40af;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
  padding: 2rem;
}

.info-section {
  background: #f9fafb;
  padding: 1.5rem;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

.info-section h4 {
  margin: 0 0 1rem 0;
  color: #374151;
  font-size: 1rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.info-section h4 i {
  color: #667eea;
  width: 16px;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f3f4f6;
}

.info-item:last-child {
  border-bottom: none;
}

.info-label {
  font-weight: 500;
  color: #6b7280;
  font-size: 0.875rem;
}

.info-value {
  font-weight: 600;
  color: #111827;
  text-align: right;
}

.info-value.empty {
  color: #9ca3af;
  font-style: italic;
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

@media (max-width: 768px) {
  .info-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
    padding: 1rem;
  }
  
  .employee-profile {
    padding: 1.5rem;
  }
  
  .modal-actions {
    flex-direction: column;
    padding: 1rem;
  }
}
</style>

<div class="modal-content">
  <div class="modal-header">
    <i class="fa fa-eye"></i>
    <h3>Detalles del Empleado</h3>
  </div>
  
  <div class="employee-profile">
    <div class="employee-avatar-large">
      <?= strtoupper(substr($personal['first_name'] ?? '', 0, 1) . substr($personal['last_name'] ?? '', 0, 1)) ?>
    </div>
    <div class="employee-name"><?= htmlspecialchars(trim(($personal['first_name'] ?? '').' '.($personal['last_name'] ?? ''))) ?></div>
    <div class="employee-code"><?= htmlspecialchars($personal['employee_code']) ?></div>
    <span class="employee-status <?= strtolower($personal['status']) === 'activo' ? 'status-active' : (strtolower($personal['status']) === 'vacaciones' ? 'status-vacation' : (strtolower($personal['status']) === 'licencia' ? 'status-license' : 'status-inactive')) ?>">
      <?= htmlspecialchars($personal['status']) ?>
    </span>
  </div>
  
  <div class="info-grid">
    <div class="info-section">
      <h4><i class="fa fa-id-card"></i> Información Personal</h4>
      <div class="info-item">
        <span class="info-label">DNI</span>
        <span class="info-value <?= empty($personal['dni']) ? 'empty' : '' ?>">
          <?= htmlspecialchars($personal['dni'] ?? 'No especificado') ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Fecha de Nacimiento</span>
        <span class="info-value <?= empty($personal['birth_date']) ? 'empty' : '' ?>">
          <?= htmlspecialchars($personal['birth_date'] ?? 'No especificada') ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Teléfono</span>
        <span class="info-value <?= empty($personal['phone']) ? 'empty' : '' ?>">
          <?= htmlspecialchars($personal['phone'] ?? 'No especificado') ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Dirección</span>
        <span class="info-value <?= empty($personal['address']) ? 'empty' : '' ?>">
          <?= htmlspecialchars($personal['address'] ?? 'No especificada') ?>
        </span>
      </div>
    </div>
    
    <div class="info-section">
      <h4><i class="fa fa-briefcase"></i> Información Laboral</h4>
      <div class="info-item">
        <span class="info-label">Posición</span>
        <span class="info-value <?= empty($personal['position']) ? 'empty' : '' ?>">
          <?= htmlspecialchars($personal['position'] ?? 'No especificada') ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Departamento</span>
        <span class="info-value <?= empty($personal['department']) ? 'empty' : '' ?>">
          <?= htmlspecialchars($personal['department'] ?? 'No especificado') ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Fecha de Contratación</span>
        <span class="info-value">
          <?= htmlspecialchars($personal['hire_date']) ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Salario</span>
        <span class="info-value <?= empty($personal['salary']) ? 'empty' : '' ?>">
          <?= $personal['salary'] ? 'S/ ' . number_format($personal['salary'], 2) : 'No especificado' ?>
        </span>
      </div>
    </div>
    
    <div class="info-section">
      <h4><i class="fa fa-phone"></i> Contacto de Emergencia</h4>
      <div class="info-item">
        <span class="info-label">Contacto</span>
        <span class="info-value <?= empty($personal['emergency_contact']) ? 'empty' : '' ?>">
          <?= htmlspecialchars($personal['emergency_contact'] ?? 'No especificado') ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Teléfono</span>
        <span class="info-value <?= empty($personal['emergency_phone']) ? 'empty' : '' ?>">
          <?= htmlspecialchars($personal['emergency_phone'] ?? 'No especificado') ?>
        </span>
      </div>
    </div>
    
    <div class="info-section">
      <h4><i class="fa fa-user"></i> Información del Sistema</h4>
      <div class="info-item">
        <span class="info-label">ID</span>
        <span class="info-value"><?= (int)$personal['id'] ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Fecha de Creación</span>
        <span class="info-value"><?= htmlspecialchars($personal['created_at']) ?></span>
      </div>
      <?php if($personal['user_id']): ?>
      <div class="info-item">
        <span class="info-label">Usuario Asociado</span>
        <span class="info-value">
          <?= htmlspecialchars($personal['username'] ?? 'Usuario #' . $personal['user_id']) ?>
          <br><small style="color: #6b7280; font-size: 0.75rem;"><?= htmlspecialchars($personal['email'] ?? '') ?></small>
        </span>
      </div>
      <?php endif; ?>
    </div>
  </div>
  
  <div class="modal-actions">
    <button type="button" class="btn-secondary" onclick="closeModal()">
      <i class="fa fa-times"></i> Cerrar
    </button>
    <button type="button" class="btn-primary" onclick="editPersonal(<?= (int)$personal['id'] ?>)">
      <i class="fa fa-pen"></i> Editar
    </button>
  </div>
</div>

<script>
function editPersonal(id) {
  // Mostrar indicador de carga
  const editBtn = event.target.closest('button');
  const originalContent = editBtn.innerHTML;
  editBtn.disabled = true;
  editBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Cargando...';
  
  // Cargar formulario de edición en el modal dinámico
  fetch('/Recursos/index.php?route=personal.edit&id=' + id)
    .then(response => response.text())
    .then(data => {
      document.getElementById('modal-content').innerHTML = data;
      document.getElementById('modal-title').textContent = 'Editar Personal';
      // El modal ya está abierto, solo actualizamos el contenido
    })
    .catch(error => {
      alert('Error al cargar el formulario de edición: ' + error);
      // Restaurar botón en caso de error
      editBtn.disabled = false;
      editBtn.innerHTML = originalContent;
    });
}

function closeModal() {
  var modal = document.getElementById('modal-dynamic');
  var overlay = document.getElementById('modal-overlay');
  if (modal) modal.style.display = 'none';
  if (overlay) overlay.classList.remove('show');
}
</script>
