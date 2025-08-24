<?php
// app/views/jornadas/view_modal.php - Ver Jornada (versión modal)
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { exit('Acceso denegado'); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { exit('ID no válido'); }

// Obtener datos de la jornada
$jornada = null;
$sql = 'SELECT j.*, p.first_name, p.last_name, p.employee_code, p.department, p.position 
        FROM jornadas j 
        INNER JOIN personal p ON j.personal_id = p.id 
        WHERE j.id = ? LIMIT 1';
if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $jornada = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}

if (!$jornada) { exit('Jornada no encontrada'); }
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

.jornada-profile {
  padding: 2rem;
  text-align: center;
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
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

.jornada-date {
  display: inline-block;
  padding: 0.5rem 1rem;
  border-radius: 25px;
  font-size: 1rem;
  font-weight: 600;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.jornada-status {
  display: inline-block;
  padding: 0.5rem 1rem;
  border-radius: 25px;
  font-size: 0.875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-top: 0.5rem;
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

.time-display {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
}

.time-entry {
  color: #059669;
}

.time-exit {
  color: #dc2626;
}

.time-break {
  color: #f59e0b;
}

.hours-display {
  font-size: 1.25rem;
  font-weight: 700;
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
  
  .jornada-profile {
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
    <i class="fa fa-clock"></i>
    <h3>Detalles de la Jornada</h3>
  </div>
  
  <div class="jornada-profile">
    <div class="employee-avatar-large">
      <?= strtoupper(substr($jornada['first_name'] ?? '', 0, 1) . substr($jornada['last_name'] ?? '', 0, 1)) ?>
    </div>
    <div class="employee-name"><?= htmlspecialchars(trim(($jornada['first_name'] ?? '').' '.($jornada['last_name'] ?? ''))) ?></div>
    <div class="employee-code"><?= htmlspecialchars($jornada['employee_code']) ?></div>
    <div class="jornada-date"><?= htmlspecialchars($jornada['date']) ?></div>
    <span class="jornada-status status-<?= strtolower($jornada['status']) ?>">
      <?= htmlspecialchars($jornada['status']) ?>
    </span>
  </div>
  
  <div class="info-grid">
    <div class="info-section">
      <h4><i class="fa fa-clock"></i> Horarios de Trabajo</h4>
      <div class="info-item">
        <span class="info-label">Entrada</span>
        <span class="info-value <?= empty($jornada['entry_time']) ? 'empty' : '' ?>">
          <?php if($jornada['entry_time']): ?>
            <div class="time-display time-entry">
              <i class="fa fa-sign-in"></i> <?= htmlspecialchars($jornada['entry_time']) ?>
            </div>
          <?php else: ?>
            No registrada
          <?php endif; ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Salida</span>
        <span class="info-value <?= empty($jornada['exit_time']) ? 'empty' : '' ?>">
          <?php if($jornada['exit_time']): ?>
            <div class="time-display time-exit">
              <i class="fa fa-sign-out"></i> <?= htmlspecialchars($jornada['exit_time']) ?>
            </div>
          <?php else: ?>
            No registrada
          <?php endif; ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Descanso</span>
        <span class="info-value <?= (empty($jornada['break_start']) || empty($jornada['break_end'])) ? 'empty' : '' ?>">
          <?php if($jornada['break_start'] && $jornada['break_end']): ?>
            <div class="time-display time-break">
              <i class="fa fa-coffee"></i> <?= htmlspecialchars($jornada['break_start']) ?> - <?= htmlspecialchars($jornada['break_end']) ?>
            </div>
          <?php else: ?>
            No registrado
          <?php endif; ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Horas Totales</span>
        <span class="info-value">
          <div class="hours-display">
            <?= $jornada['total_hours'] ? number_format($jornada['total_hours'], 1) . 'h' : '0h' ?>
          </div>
        </span>
      </div>
    </div>
    
    <div class="info-section">
      <h4><i class="fa fa-user"></i> Información del Empleado</h4>
      <div class="info-item">
        <span class="info-label">Código</span>
        <span class="info-value"><?= htmlspecialchars($jornada['employee_code']) ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Departamento</span>
        <span class="info-value"><?= htmlspecialchars($jornada['department'] ?? 'No especificado') ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Posición</span>
        <span class="info-value"><?= htmlspecialchars($jornada['position'] ?? 'No especificada') ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">ID de Jornada</span>
        <span class="info-value"><?= (int)$jornada['id'] ?></span>
      </div>
    </div>
    
    <div class="info-section">
      <h4><i class="fa fa-info-circle"></i> Estado y Validación</h4>
      <div class="info-item">
        <span class="info-label">Estado</span>
        <span class="info-value">
          <span class="jornada-status status-<?= strtolower($jornada['status']) ?>">
            <?= htmlspecialchars($jornada['status']) ?>
          </span>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Jornada Completa</span>
        <span class="info-value">
          <?php if($jornada['total_hours'] >= 8): ?>
            <span style="color: #059669; font-weight: 600;">✓ Sí</span>
          <?php elseif($jornada['total_hours'] > 0): ?>
            <span style="color: #f59e0b; font-weight: 600;">⚠ Parcial</span>
          <?php else: ?>
            <span style="color: #dc2626; font-weight: 600;">✗ No</span>
          <?php endif; ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Fecha de Registro</span>
        <span class="info-value"><?= htmlspecialchars($jornada['created_at']) ?></span>
      </div>
    </div>
  </div>
  
  <div class="modal-actions">
    <button type="button" class="btn-secondary" onclick="closeModal()">
      <i class="fa fa-times"></i> Cerrar
    </button>
    <button type="button" class="btn-primary" onclick="editJornada(<?= (int)$jornada['id'] ?>)">
      <i class="fa fa-pen"></i> Editar
    </button>
  </div>
</div>

<script>
function editJornada(id) {
  // Mostrar indicador de carga
  const editBtn = event.target.closest('button');
  const originalContent = editBtn.innerHTML;
  editBtn.disabled = true;
  editBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Cargando...';
  
  // Cargar formulario de edición en el mismo modal
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
