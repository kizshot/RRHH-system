<?php
// app/views/adelantos/view_modal.php - Ver Adelanto (versión modal)
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { exit('Acceso denegado'); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { exit('ID no válido'); }

// Obtener datos del adelanto
$adelanto = null;
$sql = 'SELECT a.*, p.first_name as employee_first_name, p.last_name as employee_last_name, p.employee_code, p.department, p.position, u.username as approver_username FROM adelantos a LEFT JOIN personal p ON a.personal_id = p.id LEFT JOIN users u ON a.approved_by = u.id WHERE a.id = ? LIMIT 1';
if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $adelanto = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}

if (!$adelanto) { exit('Adelanto no encontrado'); }
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

.adelanto-profile {
  padding: 2rem;
  text-align: center;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-bottom: 1px solid #e5e7eb;
}

.employee-avatar-large {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 1.5rem;
  margin: 0 auto 1rem;
  box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
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

.amount-display {
  font-size: 2rem;
  font-weight: 700;
  color: #059669;
  margin-bottom: 1rem;
}

.adelanto-status {
  display: inline-block;
  padding: 0.5rem 1rem;
  border-radius: 25px;
  font-size: 0.875rem;
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
  color: #10b981;
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
  
  .adelanto-profile {
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
    <i class="fa fa-money-bill-wave"></i>
    <h3>Detalles del Adelanto</h3>
  </div>
  
  <div class="adelanto-profile">
    <div class="employee-avatar-large">
      <?= strtoupper(substr($adelanto['employee_first_name'] ?? '', 0, 1) . substr($adelanto['employee_last_name'] ?? '', 0, 1)) ?>
    </div>
    <div class="employee-name"><?= htmlspecialchars(trim(($adelanto['employee_first_name'] ?? '').' '.($adelanto['employee_last_name'] ?? ''))) ?></div>
    <div class="employee-code"><?= htmlspecialchars($adelanto['employee_code'] ?? '') ?></div>
    <div class="amount-display">S/ <?= number_format($adelanto['amount'], 2) ?></div>
    <span class="adelanto-status <?= strtolower($adelanto['status']) === 'pendiente' ? 'status-pendiente' : (strtolower($adelanto['status']) === 'aprobado' ? 'status-aprobado' : (strtolower($adelanto['status']) === 'rechazado' ? 'status-rechazado' : 'status-pagado')) ?>">
      <?= htmlspecialchars($adelanto['status']) ?>
    </span>
  </div>
  
  <div class="info-grid">
    <div class="info-section">
      <h4><i class="fa fa-user"></i> Información del Empleado</h4>
      <div class="info-item">
        <span class="info-label">Código</span>
        <span class="info-value"><?= htmlspecialchars($adelanto['employee_code'] ?? 'No especificado') ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Departamento</span>
        <span class="info-value <?= empty($adelanto['department']) ? 'empty' : '' ?>">
          <?= htmlspecialchars($adelanto['department'] ?? 'No especificado') ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Posición</span>
        <span class="info-value <?= empty($adelanto['position']) ? 'empty' : '' ?>">
          <?= htmlspecialchars($adelanto['position'] ?? 'No especificada') ?>
        </span>
      </div>
    </div>
    
    <div class="info-section">
      <h4><i class="fa fa-calendar"></i> Fechas</h4>
      <div class="info-item">
        <span class="info-label">Fecha de Solicitud</span>
        <span class="info-value"><?= htmlspecialchars($adelanto['request_date']) ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Fecha de Aprobación</span>
        <span class="info-value <?= empty($adelanto['approved_date']) ? 'empty' : '' ?>">
          <?= htmlspecialchars($adelanto['approved_date'] ?? 'No aprobado') ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Fecha de Pago</span>
        <span class="info-value <?= empty($adelanto['payment_date']) ? 'empty' : '' ?>">
          <?= htmlspecialchars($adelanto['payment_date'] ?? 'No pagado') ?>
        </span>
      </div>
    </div>
    
    <div class="info-section">
      <h4><i class="fa fa-file-text"></i> Detalles del Adelanto</h4>
      <div class="info-item">
        <span class="info-label">Motivo</span>
        <span class="info-value <?= empty($adelanto['reason']) ? 'empty' : '' ?>">
          <?= htmlspecialchars($adelanto['reason'] ?? 'No especificado') ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Notas</span>
        <span class="info-value <?= empty($adelanto['notes']) ? 'empty' : '' ?>">
          <?= htmlspecialchars($adelanto['notes'] ?? 'Sin notas') ?>
        </span>
      </div>
    </div>
    
    <div class="info-section">
      <h4><i class="fa fa-user-check"></i> Información de Aprobación</h4>
      <div class="info-item">
        <span class="info-label">Estado</span>
        <span class="info-value"><?= htmlspecialchars($adelanto['status']) ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Aprobado por</span>
        <span class="info-value <?= empty($adelanto['approver_username']) ? 'empty' : '' ?>">
          <?= htmlspecialchars($adelanto['approver_username'] ?? 'No aprobado') ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">ID del Adelanto</span>
        <span class="info-value"><?= (int)$adelanto['id'] ?></span>
      </div>
    </div>
  </div>
  
  <div class="modal-actions">
    <button type="button" class="btn-secondary" onclick="closeModal()">
      <i class="fa fa-times"></i> Cerrar
    </button>
    <button type="button" class="btn-primary" onclick="editAdelanto(<?= (int)$adelanto['id'] ?>)">
      <i class="fa fa-pen"></i> Editar
    </button>
  </div>
</div>

<script>
function editAdelanto(id) {
  // Mostrar indicador de carga
  const editBtn = event.target.closest('button');
  const originalContent = editBtn.innerHTML;
  editBtn.disabled = true;
  editBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Cargando...';
  
  // Cargar formulario de edición en el modal dinámico
  fetch('/Recursos/index.php?route=adelantos.edit&id=' + id)
    .then(response => response.text())
    .then(data => {
      document.getElementById('modal-content').innerHTML = data;
      document.getElementById('modal-title').textContent = 'Editar Adelanto';
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
