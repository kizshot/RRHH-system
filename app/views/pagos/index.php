<?php
// app/views/pagos/index.php - Listado de Pagos
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /Recursos/index.php?route=login'); exit; }
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$statuses = ['PENDIENTE','PAGADO','ANULADO'];

$pagoController = new PagoController($conn);
$months = $pagoController->getMonths();
$years = $pagoController->getYears();
?>

<style>
/* Estilos para el módulo de Pagos */
.pagos-header {
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
  color: white;
  padding: 2rem;
  border-radius: 16px;
  margin-bottom: 2rem;
  box-shadow: 0 10px 25px rgba(139, 92, 246, 0.15);
}

.pagos-header h2 {
  margin: 0 0 0.5rem 0;
  font-size: 2rem;
  font-weight: 700;
}

.pagos-header p {
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
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
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
  background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
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
  background: #faf5ff;
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
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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

.period-badge {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
  color: white;
  text-align: center;
  min-width: 80px;
}

.salary-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.salary-base {
  color: #6b7280;
  font-size: 0.75rem;
}

.salary-net {
  font-weight: 700;
  color: #059669;
  font-size: 1.1rem;
}

.salary-bonuses {
  color: #059669;
  font-size: 0.75rem;
}

.salary-deductions {
  color: #dc2626;
  font-size: 0.75rem;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
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
  background: #8b5cf6;
}

.btn-view:hover {
  background: #7c3aed;
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
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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
  box-shadow: 0 4px 6px rgba(139, 92, 246, 0.2);
}

.btn-add:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 15px rgba(139, 92, 246, 0.3);
}

.btn-generate {
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

.btn-generate:hover {
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
  background: #8b5cf6;
  color: white;
  border-color: #8b5cf6;
}

.pagination .current {
  background: #8b5cf6;
  color: white;
  border-color: #8b5cf6;
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
  .pagos-header {
    padding: 1.5rem;
  }
  
  .pagos-header h2 {
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

<div class="pagos-header">
  <h2><i class="fa fa-money-bill-wave"></i> Gestión de Pagos</h2>
  <p>Administra los pagos de salarios, bonos, deducciones y estados de pago</p>
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

<div class="search-container">
  <form method="get" action="/Recursos/index.php" class="search-form">
    <input type="hidden" name="route" value="pagos.index">
    
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
    
    <select class="input" name="period_month">
      <option value="">Todos los meses</option>
      <?php foreach($months as $num => $name): ?>
        <option value="<?= $num ?>" <?= (isset($period_month) && $period_month==$num)?'selected':'' ?>><?= $name ?></option>
      <?php endforeach; ?>
    </select>
    
    <select class="input" name="period_year">
      <option value="">Todos los años</option>
      <?php foreach($years as $year): ?>
        <option value="<?= $year ?>" <?= (isset($period_year) && $period_year==$year)?'selected':'' ?>><?= $year ?></option>
      <?php endforeach; ?>
    </select>
    
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
  <h3 style="margin: 0; color: #374151;">Registro de Pagos</h3>
  <div style="display: flex; gap: 1rem;">
    <button class="btn-generate" id="btn-generate-modal">
      <i class="fa fa-magic"></i> Generar Pago
    </button>
    <button class="btn-add" id="btn-open-modal">
      <i class="fa fa-plus"></i> Crear Pago
    </button>
  </div>
</div>

<div class="table-container">
  <table class="data-table">
    <thead>
      <tr>
        <th>Empleado</th>
        <th>Período</th>
        <th>Salario</th>
        <th>Estado</th>
        <th>Fecha de Pago</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($pagos as $p): ?>
        <tr>
          <td>
            <div class="employee-info">
              <div class="employee-avatar">
                <?= strtoupper(substr($p['first_name'] ?? '', 0, 1) . substr($p['last_name'] ?? '', 0, 1)) ?>
              </div>
              <div class="employee-details">
                <h4><?= htmlspecialchars(trim(($p['first_name'] ?? '').' '.($p['last_name'] ?? ''))) ?></h4>
                <small><?= htmlspecialchars($p['employee_code']) ?> • <?= htmlspecialchars($p['department']) ?></small>
              </div>
            </div>
          </td>
          <td>
            <span class="period-badge">
              <?= $months[$p['period_month']] ?? $p['period_month'] ?> <?= $p['period_year'] ?>
            </span>
          </td>
          <td>
            <div class="salary-info">
              <div class="salary-base">Base: S/ <?= number_format($p['base_salary'], 2) ?></div>
              <?php if($p['bonuses'] > 0): ?>
                <div class="salary-bonuses">+ Bonos: S/ <?= number_format($p['bonuses'], 2) ?></div>
              <?php endif; ?>
              <?php if($p['deductions'] > 0): ?>
                <div class="salary-deductions">- Deducciones: S/ <?= number_format($p['deductions'], 2) ?></div>
              <?php endif; ?>
              <div class="salary-net">Neto: S/ <?= number_format($p['net_salary'], 2) ?></div>
            </div>
          </td>
          <td>
            <span class="status-badge status-<?= strtolower($p['status']) ?>">
              <?= htmlspecialchars($p['status']) ?>
            </span>
          </td>
          <td>
            <span style="font-weight: 500;">
              <?= $p['payment_date'] ? htmlspecialchars($p['payment_date']) : 'Pendiente' ?>
            </span>
          </td>
          <td>
            <div class="action-buttons">
              <button class="btn-action btn-view" onclick="openViewModal(<?= (int)$p['id'] ?>)" title="Ver detalles">
                <i class="fa fa-eye"></i>
              </button>
              <button class="btn-action btn-edit" onclick="openEditModal(<?= (int)$p['id'] ?>)" title="Editar">
                <i class="fa fa-pen"></i>
              </button>
              <button class="btn-action btn-delete" onclick="confirmDelete(<?= (int)$p['id'] ?>, '<?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?> - ' + '<?= $months[$p['period_month']] ?? $p['period_month'] ?> <?= $p['period_year'] ?>')" title="Eliminar">
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
    <a href="/Recursos/index.php?route=pagos.index&page=<?= ($page-1) . $qparam ?>">
      <i class="fa fa-chevron-left"></i> Anterior
    </a>
  <?php endif; ?>
  
  <span style="color: #6b7280; font-weight: 500;">
    Página <?= (int)$page ?> de <?= (int)$pageCount ?>
  </span>
  
  <?php if (($page ?? 1) < ($pageCount ?? 1)): ?>
    <a href="/Recursos/index.php?route=pagos.index&page=<?= ($page+1) . $qparam ?>">
      Siguiente <i class="fa fa-chevron-right"></i>
    </a>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Modal dinámico -->
<div class="modal" id="modal-dynamic" role="dialog" aria-modal="true" aria-labelledby="modalDynTitle" style="display:none">
  <header style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; border-radius: 12px 12px 0 0;">
    <h3 id="modalDynTitle" style="margin:0; display: flex; align-items: center; gap: 0.5rem;">
      <i class="fa fa-money-bill-wave"></i> <span id="modal-title">Pago</span>
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

<!-- Modal crear pago -->
<div class="modal" id="modal-create" role="dialog" aria-modal="true" aria-labelledby="modalTitle" style="display:none">
  <header style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; border-radius: 12px 12px 0 0;">
    <h3 id="modalTitle" style="margin:0; display: flex; align-items: center; gap: 0.5rem;">
      <i class="fa fa-plus"></i> Crear Pago
    </h3>
    <button class="icon-btn" id="btn-close-modal" title="Cerrar" style="color: white;">
      <i class="fa fa-times"></i>
    </button>
  </header>
  <form id="create-form" action="/Recursos/index.php?route=pagos.create" method="post">
    <div class="modal-body">
      <div class="grid-2">
        <div class="form-row">
          <label for="personal_id" class="required">Empleado *</label>
          <select class="input" id="personal_id" name="personal_id" required>
            <option value="">Seleccionar empleado</option>
            <?php foreach($personalList as $p): ?>
              <option value="<?= $p['id'] ?>" data-salary="<?= $p['salary'] ?>">
                <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?> (<?= htmlspecialchars($p['employee_code']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <label for="period_month" class="required">Mes *</label>
          <select class="input" id="period_month" name="period_month" required>
            <option value="">Seleccionar mes</option>
            <?php foreach($months as $num => $name): ?>
              <option value="<?= $num ?>"><?= $name ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <label for="period_year" class="required">Año *</label>
          <select class="input" id="period_year" name="period_year" required>
            <option value="">Seleccionar año</option>
            <?php foreach($years as $year): ?>
              <option value="<?= $year ?>"><?= $year ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <label for="base_salary" class="required">Salario Base *</label>
          <input class="input" type="number" id="base_salary" name="base_salary" step="0.01" min="0" required>
        </div>
        <div class="form-row">
          <label for="bonuses">Bonos</label>
          <input class="input" type="number" id="bonuses" name="bonuses" step="0.01" min="0" value="0">
        </div>
        <div class="form-row">
          <label for="deductions">Deducciones</label>
          <input class="input" type="number" id="deductions" name="deductions" step="0.01" min="0" value="0">
        </div>
        <div class="form-row">
          <label for="payment_date">Fecha de Pago</label>
          <input class="input" type="date" id="payment_date" name="payment_date">
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
      <button type="submit" class="button" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
        <i class="fa fa-save"></i> Guardar
      </button>
    </div>
  </form>
</div>

<!-- Modal generar pago -->
<div class="modal" id="modal-generate" role="dialog" aria-modal="true" aria-labelledby="modalGenerateTitle" style="display:none">
  <header style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-radius: 12px 12px 0 0;">
    <h3 id="modalGenerateTitle" style="margin:0; display: flex; align-items: center; gap: 0.5rem;">
      <i class="fa fa-magic"></i> Generar Pago Automático
    </h3>
    <button class="icon-btn" id="btn-close-generate" title="Cerrar" style="color: white;">
      <i class="fa fa-times"></i>
    </button>
  </header>
  <form id="generate-form" action="/Recursos/index.php?route=pagos.generatePayment" method="post">
    <div class="modal-body">
      <div class="grid-2">
        <div class="form-row">
          <label for="generate_personal_id" class="required">Empleado *</label>
          <select class="input" id="generate_personal_id" name="personal_id" required>
            <option value="">Seleccionar empleado</option>
            <?php foreach($personalList as $p): ?>
              <option value="<?= $p['id'] ?>">
                <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?> (S/ <?= number_format($p['salary'], 2) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <label for="generate_period_month" class="required">Mes *</label>
          <select class="input" id="generate_period_month" name="period_month" required>
            <option value="">Seleccionar mes</option>
            <?php foreach($months as $num => $name): ?>
              <option value="<?= $num ?>"><?= $name ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <label for="generate_period_year" class="required">Año *</label>
          <select class="input" id="generate_period_year" name="period_year" required>
            <option value="">Seleccionar año</option>
            <?php foreach($years as $year): ?>
              <option value="<?= $year ?>"><?= $year ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div style="background: #f0fdf4; padding: 1rem; border-radius: 8px; margin-top: 1rem;">
        <p style="margin: 0; color: #166534; font-size: 0.875rem;">
          <i class="fa fa-info-circle"></i> 
          Se generará un pago automático basado en el salario configurado del empleado, sin bonos ni deducciones.
        </p>
      </div>
    </div>
    <div class="modal-actions">
      <button type="button" class="button" id="btn-cancel-generate" style="background: #6b7280;">Cancelar</button>
      <button type="submit" class="button" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
        <i class="fa fa-magic"></i> Generar
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
    <p id="delete-message" style="font-size: 1.1rem; margin-bottom: 1rem;">¿Estás seguro de que quieres eliminar este pago?</p>
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
    '¿Estás seguro de que quieres eliminar el pago de <strong>' + name + '</strong>?';
  
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
    form.action = '/Recursos/index.php?route=pagos.delete';
    
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
  // Modal de crear pago
  var openBtn = document.getElementById('btn-open-modal');
  var closeBtn = document.getElementById('btn-close-modal');
  var cancelBtn = document.getElementById('btn-cancel');
  var modal = document.getElementById('modal-create');
  
  // Modal de generar pago
  var generateBtn = document.getElementById('btn-generate-modal');
  var closeGenerateBtn = document.getElementById('btn-close-generate');
  var cancelGenerateBtn = document.getElementById('btn-cancel-generate');
  var generateModal = document.getElementById('modal-generate');
  
  var overlay = document.getElementById('modal-overlay');

  function showModal(){ 
    modal.style.display = 'block'; 
    overlay.classList.add('show'); 
  }
  
  function showGenerateModal(){ 
    generateModal.style.display = 'block'; 
    overlay.classList.add('show'); 
  }
  
  function hideModal(){ 
    modal.style.display = 'none'; 
    overlay.classList.remove('show'); 
  }
  
  function hideGenerateModal(){ 
    generateModal.style.display = 'none'; 
    overlay.classList.remove('show'); 
  }
  
  openBtn && openBtn.addEventListener('click', showModal);
  closeBtn && closeBtn.addEventListener('click', hideModal);
  cancelBtn && cancelBtn.addEventListener('click', hideModal);
  
  generateBtn && generateBtn.addEventListener('click', showGenerateModal);
  closeGenerateBtn && closeGenerateBtn.addEventListener('click', hideGenerateModal);
  cancelGenerateBtn && cancelGenerateBtn.addEventListener('click', hideGenerateModal);
  
  overlay && overlay.addEventListener('click', function(e) {
    if (e.target === overlay) {
      hideModal();
      hideGenerateModal();
      // Cerrar modal dinámico si está abierto
      var dynamicModal = document.getElementById('modal-dynamic');
      if (dynamicModal && dynamicModal.style.display === 'block') {
        dynamicModal.style.display = 'none';
        overlay.classList.remove('show');
      }
    }
  });

  // Auto-completar salario base cuando se selecciona empleado
  var personalSelect = document.getElementById('personal_id');
  var baseSalaryInput = document.getElementById('base_salary');
  
  if (personalSelect && baseSalaryInput) {
    personalSelect.addEventListener('change', function() {
      var selectedOption = this.options[this.selectedIndex];
      var salary = selectedOption.getAttribute('data-salary');
      if (salary) {
        baseSalaryInput.value = salary;
      }
    });
  }

  // Manejar envío del formulario de crear via AJAX
  var createForm = document.getElementById('create-form');
  if (createForm) {
    createForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      var formData = new FormData(this);
      
      fetch('/Recursos/index.php?route=pagos.create', {
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
          alert('Error al crear pago: ' + data.message);
        }
      })
      .catch(error => {
        alert('Error: ' + error);
      });
    });
  }

  // Manejar envío del formulario de generar via AJAX
  var generateForm = document.getElementById('generate-form');
  if (generateForm) {
    generateForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      var formData = new FormData(this);
      
      fetch('/Recursos/index.php?route=pagos.generatePayment', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          hideGenerateModal();
          window.location.reload();
        } else {
          alert('Error al generar pago: ' + data.message);
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
  
  overlay && overlay.addEventListener('click', function(e) {
    if (e.target === overlay) {
      if (deleteModal.style.display === 'block') {
        hideDeleteModal();
      }
    }
  });

  // Cerrar modal dinámico
  var closeDynBtn = document.getElementById('btn-close-dyn');
  if (closeDynBtn) {
    closeDynBtn.addEventListener('click', function() {
      document.getElementById('modal-dynamic').style.display = 'none';
      document.getElementById('modal-overlay').classList.remove('show');
    });
  }

  // Evitar que los clics dentro de los modales cierren el overlay
  var dynamicModal = document.getElementById('modal-dynamic');
  var createModal = document.getElementById('modal-create');
  var generateModal = document.getElementById('modal-generate');
  var deleteModal = document.getElementById('modal-delete');
  if (dynamicModal) dynamicModal.addEventListener('click', function(e){ e.stopPropagation(); });
  if (createModal) createModal.addEventListener('click', function(e){ e.stopPropagation(); });
  if (generateModal) generateModal.addEventListener('click', function(e){ e.stopPropagation(); });
  if (deleteModal) deleteModal.addEventListener('click', function(e){ e.stopPropagation(); });
})();

// Rehidratar scripts embebidos en contenido HTML inyectado
function rehydrateModalScripts(container) {
  if (!container) return;
  const scripts = container.querySelectorAll('script');
  scripts.forEach(oldScript => {
    const newScript = document.createElement('script');
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
  fetch('/Recursos/index.php?route=pagos.view&id=' + id)
    .then(response => response.text())
    .then(data => {
      const container = document.getElementById('modal-content');
      container.innerHTML = data;
      rehydrateModalScripts(container);
      document.getElementById('modal-title').textContent = 'Ver Pago';
      document.getElementById('modal-dynamic').style.display = 'block';
      document.getElementById('modal-overlay').classList.add('show');
    })
    .catch(error => {
      alert('Error al cargar datos: ' + error);
    });
}

// Función para abrir modal de edición
function openEditModal(id) {
  fetch('/Recursos/index.php?route=pagos.edit&id=' + id)
    .then(response => response.text())
    .then(data => {
      const container = document.getElementById('modal-content');
      container.innerHTML = data;
      rehydrateModalScripts(container);
      document.getElementById('modal-title').textContent = 'Editar Pago';
      document.getElementById('modal-dynamic').style.display = 'block';
      document.getElementById('modal-overlay').classList.add('show');
    })
    .catch(error => {
      alert('Error al cargar datos: ' + error);
    });
}

// Función global para cerrar modales (usada por contenido inyectado)
function closeModal() {
  var dynamicModal = document.getElementById('modal-dynamic');
  var overlay = document.getElementById('modal-overlay');
  if (dynamicModal) dynamicModal.style.display = 'none';
  if (overlay) overlay.classList.remove('show');
}
</script>
<script src="/Recursos/assets/js/notifications.js"></script>
<script src="/Recursos/assets/js/main.js"></script>
