<?php
// app/views/pagos/view_modal.php - Ver Pago (versión modal)
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { exit('Acceso denegado'); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { exit('ID no válido'); }

// Obtener datos del pago
$pago = null;
$sql = 'SELECT p.*, per.first_name, per.last_name, per.employee_code, per.department, per.position 
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

$pagoController = new PagoController($conn);
$months = $pagoController->getMonths();
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

.pago-profile {
  padding: 2rem;
  text-align: center;
  background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
  border-bottom: 1px solid #e5e7eb;
}

.employee-avatar-large {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 1.5rem;
  margin: 0 auto 1rem;
  box-shadow: 0 10px 15px -3px rgba(139, 92, 246, 0.3);
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

.period-badge {
  display: inline-block;
  padding: 0.5rem 1rem;
  border-radius: 25px;
  font-size: 1rem;
  font-weight: 600;
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
  color: white;
  margin-bottom: 0.5rem;
}

.pago-status {
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

.status-pagado {
  background: #dcfce7;
  color: #166534;
}

.status-anulado {
  background: #fee2e2;
  color: #991b1b;
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
  color: #8b5cf6;
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

.salary-display {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
}

.salary-base {
  color: #6b7280;
}

.salary-bonuses {
  color: #059669;
}

.salary-deductions {
  color: #dc2626;
}

.salary-net {
  color: #8b5cf6;
  font-size: 1.1rem;
  font-weight: 700;
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

.salary-summary {
  background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
  padding: 1.5rem;
  border-radius: 12px;
  border: 2px solid #e5e7eb;
  margin-top: 1rem;
}

.salary-summary h5 {
  margin: 0 0 1rem 0;
  color: #8b5cf6;
  font-size: 1.1rem;
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

@media (max-width: 768px) {
  .info-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
    padding: 1rem;
  }
  
  .pago-profile {
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
    <h3>Detalles del Pago</h3>
  </div>
  
  <div class="pago-profile">
    <div class="employee-avatar-large">
      <?= strtoupper(substr($pago['first_name'] ?? '', 0, 1) . substr($pago['last_name'] ?? '', 0, 1)) ?>
    </div>
    <div class="employee-name"><?= htmlspecialchars(trim(($pago['first_name'] ?? '').' '.($pago['last_name'] ?? ''))) ?></div>
    <div class="employee-code"><?= htmlspecialchars($pago['employee_code']) ?></div>
    <div class="period-badge">
      <?= $months[$pago['period_month']] ?? $pago['period_month'] ?> <?= $pago['period_year'] ?>
    </div>
    <span class="pago-status status-<?= strtolower($pago['status']) ?>">
      <?= htmlspecialchars($pago['status']) ?>
    </span>
  </div>
  
  <div class="info-grid">
    <div class="info-section">
      <h4><i class="fa fa-user"></i> Información del Empleado</h4>
      <div class="info-item">
        <span class="info-label">Código</span>
        <span class="info-value"><?= htmlspecialchars($pago['employee_code']) ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Departamento</span>
        <span class="info-value"><?= htmlspecialchars($pago['department'] ?? 'No especificado') ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Posición</span>
        <span class="info-value"><?= htmlspecialchars($pago['position'] ?? 'No especificada') ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">ID de Pago</span>
        <span class="info-value"><?= (int)$pago['id'] ?></span>
      </div>
    </div>
    
    <div class="info-section">
      <h4><i class="fa fa-calendar"></i> Período y Estado</h4>
      <div class="info-item">
        <span class="info-label">Período</span>
        <span class="info-value">
          <?= $months[$pago['period_month']] ?? $pago['period_month'] ?> <?= $pago['period_year'] ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Estado</span>
        <span class="info-value">
          <span class="pago-status status-<?= strtolower($pago['status']) ?>">
            <?= htmlspecialchars($pago['status']) ?>
          </span>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Fecha de Pago</span>
        <span class="info-value <?= empty($pago['payment_date']) ? 'empty' : '' ?>">
          <?= $pago['payment_date'] ? htmlspecialchars($pago['payment_date']) : 'Pendiente' ?>
        </span>
      </div>
      <div class="info-item">
        <span class="info-label">Fecha de Creación</span>
        <span class="info-value"><?= htmlspecialchars($pago['created_at']) ?></span>
      </div>
    </div>
  </div>
  
  <div class="salary-summary">
    <h5><i class="fa fa-calculator"></i> Desglose Salarial</h5>
    <div class="salary-breakdown">
      <div class="salary-item">
        <span>Salario Base</span>
        <span class="salary-display salary-base">
          S/ <?= number_format($pago['base_salary'], 2) ?>
        </span>
      </div>
      <?php if($pago['bonuses'] > 0): ?>
      <div class="salary-item">
        <span>Bonos</span>
        <span class="salary-display salary-bonuses">
          + S/ <?= number_format($pago['bonuses'], 2) ?>
        </span>
      </div>
      <?php endif; ?>
      <?php if($pago['deductions'] > 0): ?>
      <div class="salary-item">
        <span>Deducciones</span>
        <span class="salary-display salary-deductions">
          - S/ <?= number_format($pago['deductions'], 2) ?>
        </span>
      </div>
      <?php endif; ?>
      <div class="salary-item">
        <span>Salario Neto</span>
        <span class="salary-display salary-net">
          S/ <?= number_format($pago['net_salary'], 2) ?>
        </span>
      </div>
    </div>
  </div>
  
  <div class="modal-actions">
    <button type="button" class="btn-secondary" onclick="closeModal()">
      <i class="fa fa-times"></i> Cerrar
    </button>
    <button type="button" class="btn-primary" onclick="editPago(<?= (int)$pago['id'] ?>)">
      <i class="fa fa-pen"></i> Editar
    </button>
  </div>
</div>

<script>
function editPago(id) {
  // Cargar formulario de edición en el mismo modal
  fetch('/Recursos/index.php?route=pagos.edit&id=' + id)
    .then(response => response.text())
    .then(data => {
      document.getElementById('modal-content').innerHTML = data;
      document.getElementById('modal-title').textContent = 'Editar Pago';
    })
    .catch(error => {
      alert('Error al cargar datos: ' + error);
    });
}

function closeModal() {
  var modal = document.getElementById('modal-dynamic');
  var overlay = document.getElementById('modal-overlay');
  if (modal) modal.style.display = 'none';
  if (overlay) overlay.classList.remove('show');
}
</script>
