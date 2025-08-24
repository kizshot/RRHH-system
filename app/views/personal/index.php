<?php
// app/views/personal/index.php - Listado y modal Agregar Personal
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /Recursos/index.php?route=login'); exit; }
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$statuses = ['ACTIVO','INACTIVO','VACACIONES','LICENCIA'];

// Obtener datos del controlador
$personal = $personal ?? [];
$departments = $departments ?? [];
$page = $page ?? 1;
$pageCount = $pageCount ?? 1;
$q = $_GET['q'] ?? '';
$department = $_GET['department'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'id';
$dir = $_GET['dir'] ?? 'DESC';
?>

<!-- Header de la página -->
<div class="card mb-4">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h2 class="card-title mb-1">
          <i class="fa fa-users text-primary"></i> Gestión de Personal
        </h2>
        <p class="text-muted mb-0">Administra la información de empleados, departamentos y estados laborales</p>
      </div>
      <button class="btn btn-primary" onclick="openCreateModal()">
        <i class="fa fa-user-plus"></i> Agregar Personal
      </button>
    </div>
  </div>
</div>

<!-- Alertas -->
<?php if($error): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fa fa-exclamation-triangle"></i> <?= $error ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<?php if($success): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fa fa-check-circle"></i> <?= $success ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Filtros de búsqueda -->
<div class="card mb-4">
  <div class="card-body">
    <form method="get" action="/Recursos/index.php" class="row g-3">
      <input type="hidden" name="route" value="personal.index">
      
      <div class="col-md-4">
        <div class="input-group">
          <span class="input-group-text"><i class="fa fa-search"></i></span>
          <input type="text" class="form-control" name="q" placeholder="Buscar por código, nombre, DNI..." value="<?= htmlspecialchars($q) ?>">
        </div>
      </div>
      
      <div class="col-md-2">
        <select class="form-select" name="department">
          <option value="">Todos los departamentos</option>
          <?php foreach($departments as $d): ?>
            <option value="<?= $d ?>" <?= ($department === $d) ? 'selected' : '' ?>><?= $d ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div class="col-md-2">
        <select class="form-select" name="status">
          <option value="">Todos los estados</option>
          <?php foreach($statuses as $s): ?>
            <option value="<?= $s ?>" <?= ($status === $s) ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div class="col-md-2">
        <select class="form-select" name="sort">
          <?php 
          $sorts = [
            'id' => 'ID',
            'employee_code' => 'Código',
            'first_name' => 'Nombre',
            'department' => 'Departamento',
            'position' => 'Posición',
            'hire_date' => 'Contratación',
            'status' => 'Estado'
          ]; 
          foreach($sorts as $k => $label): 
          ?>
            <option value="<?= $k ?>" <?= ($sort === $k) ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">
          <i class="fa fa-search"></i> Buscar
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Tabla de personal -->
<div class="card">
  <div class="card-header">
    <h5 class="card-title mb-0">Lista de Empleados</h5>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 table-sortable">
        <thead class="table-light">
          <tr>
            <th data-sort="employee_code">Empleado</th>
            <th data-sort="position">Posición</th>
            <th data-sort="department">Departamento</th>
            <th data-sort="status">Estado</th>
            <th data-sort="hire_date">Contratación</th>
            <th width="150">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($personal)): ?>
            <tr>
              <td colspan="6" class="text-center py-4">
                <div class="text-muted">
                  <i class="fa fa-users fa-2x mb-2"></i>
                  <p>No se encontraron empleados</p>
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach($personal as $p): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center">
                      <?= strtoupper(substr($p['first_name'] ?? '', 0, 1) . substr($p['last_name'] ?? '', 0, 1)) ?>
                    </div>
                    <div>
                      <h6 class="mb-0"><?= htmlspecialchars(trim(($p['first_name'] ?? '').' '.($p['last_name'] ?? ''))) ?></h6>
                      <small class="text-muted">
                        <?= htmlspecialchars($p['employee_code']) ?> • <?= htmlspecialchars($p['dni'] ?? 'Sin DNI') ?>
                      </small>
                    </div>
                  </div>
                </td>
                <td>
                  <strong><?= htmlspecialchars($p['position'] ?? 'Sin posición') ?></strong>
                  <?php if($p['phone']): ?>
                    <br><small class="text-muted"><i class="fa fa-phone"></i> <?= htmlspecialchars($p['phone']) ?></small>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge bg-light text-dark"><?= htmlspecialchars($p['department'] ?? 'Sin departamento') ?></span>
                </td>
                <td>
                  <?php
                  $statusClass = 'bg-success';
                  if (strtolower($p['status']) === 'vacaciones') $statusClass = 'bg-warning';
                  elseif (strtolower($p['status']) === 'licencia') $statusClass = 'bg-info';
                  elseif (strtolower($p['status']) === 'inactivo') $statusClass = 'bg-danger';
                  ?>
                  <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($p['status']) ?></span>
                </td>
                <td>
                  <span class="text-muted"><?= htmlspecialchars($p['hire_date'] ?? '') ?></span>
                </td>
                <td>
                  <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-outline-primary" onclick="openViewModal(<?= (int)$p['id'] ?>)" title="Ver detalles">
                      <i class="fa fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-warning" onclick="openEditModal(<?= (int)$p['id'] ?>)" title="Editar">
                      <i class="fa fa-pen"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="openDeleteModal(<?= (int)$p['id'] ?>, '<?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>')" title="Eliminar">
                      <i class="fa fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Paginación -->
<?php if ($pageCount > 1): ?>
<div class="d-flex justify-content-center mt-4">
  <nav aria-label="Paginación de empleados">
    <ul class="pagination">
      <?php if ($page > 1): ?>
        <li class="page-item">
          <a class="page-link" href="/Recursos/index.php?route=personal.index&page=<?= ($page-1) ?>&q=<?= urlencode($q) ?>&department=<?= urlencode($department) ?>&status=<?= urlencode($status) ?>&sort=<?= urlencode($sort) ?>&dir=<?= urlencode($dir) ?>">
            <i class="fa fa-chevron-left"></i> Anterior
          </a>
        </li>
      <?php endif; ?>
      
      <li class="page-item active">
        <span class="page-link">Página <?= (int)$page ?> de <?= (int)$pageCount ?></span>
      </li>
      
      <?php if ($page < $pageCount): ?>
        <li class="page-item">
          <a class="page-link" href="/Recursos/index.php?route=personal.index&page=<?= ($page+1) ?>&q=<?= urlencode($q) ?>&department=<?= urlencode($department) ?>&status=<?= urlencode($status) ?>&sort=<?= urlencode($sort) ?>&dir=<?= urlencode($dir) ?>">
            Siguiente <i class="fa fa-chevron-right"></i>
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
</div>
<?php endif; ?>

<!-- Modal dinámico para Ver/Editar -->
<div class="modal fade" id="modal-dynamic" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal-title">
          <i class="fa fa-window-restore"></i> <span id="modal-title-text">Ventana</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modal-content">
        <!-- El contenido se cargará aquí dinámicamente -->
      </div>
    </div>
  </div>
</div>

<!-- Modal crear personal -->
<div class="modal fade" id="modal-create" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa fa-user-plus"></i> Agregar Personal
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="create-form" action="/Recursos/index.php?route=personal.create" method="post">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="employee_code" class="form-label">Código de Empleado *</label>
              <input type="text" class="form-control" id="employee_code" name="employee_code" maxlength="20" required>
            </div>
            <div class="col-md-6">
              <label for="dni" class="form-label">DNI</label>
              <input type="text" class="form-control" id="dni" name="dni" maxlength="20">
            </div>
            <div class="col-md-6">
              <label for="first_name" class="form-label">Nombre *</label>
              <input type="text" class="form-control" id="first_name" name="first_name" maxlength="100" required>
            </div>
            <div class="col-md-6">
              <label for="last_name" class="form-label">Apellidos *</label>
              <input type="text" class="form-control" id="last_name" name="last_name" maxlength="100" required>
            </div>
            <div class="col-md-6">
              <label for="birth_date" class="form-label">Fecha de Nacimiento</label>
              <input type="date" class="form-control" id="birth_date" name="birth_date">
            </div>
            <div class="col-md-6">
              <label for="hire_date" class="form-label">Fecha de Contratación *</label>
              <input type="date" class="form-control" id="hire_date" name="hire_date" required>
            </div>
            <div class="col-md-6">
              <label for="position" class="form-label">Posición</label>
              <input type="text" class="form-control" id="position" name="position" maxlength="100">
            </div>
            <div class="col-md-6">
              <label for="department" class="form-label">Departamento</label>
              <input type="text" class="form-control" id="department" name="department" maxlength="100">
            </div>
            <div class="col-md-6">
              <label for="salary" class="form-label">Salario</label>
              <input type="number" class="form-control" id="salary" name="salary" step="0.01" min="0">
            </div>
            <div class="col-md-6">
              <label for="status" class="form-label">Estado</label>
              <select class="form-select" id="status" name="status">
                <?php foreach($statuses as $s): ?>
                  <option value="<?= $s ?>"><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="phone" class="form-label">Teléfono</label>
              <input type="tel" class="form-control" id="phone" name="phone" maxlength="20">
            </div>
            <div class="col-12">
              <label for="address" class="form-label">Dirección</label>
              <textarea class="form-control" id="address" name="address" rows="2"></textarea>
            </div>
            <div class="col-md-6">
              <label for="emergency_contact" class="form-label">Contacto de Emergencia</label>
              <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" maxlength="100">
            </div>
            <div class="col-md-6">
              <label for="emergency_phone" class="form-label">Teléfono de Emergencia</label>
              <input type="tel" class="form-control" id="emergency_phone" name="emergency_phone" maxlength="20">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="modal-delete" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class="fa fa-exclamation-triangle"></i> Confirmar Eliminación
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa fa-exclamation-triangle fa-3x text-danger"></i>
        </div>
        <p id="delete-message" class="mb-2">¿Estás seguro de que quieres eliminar este personal?</p>
        <p class="text-muted small"><strong>Esta acción no se puede deshacer.</strong></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" onclick="confirmDelete()">
          <i class="fa fa-trash"></i> Eliminar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal dinámico para ver y editar -->
<div class="modal fade" id="modal-dynamic" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal-title-text">
          <i class="fa fa-user"></i> Personal
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modal-content">
        <!-- El contenido se cargará dinámicamente aquí -->
        <div class="text-center">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
          <p class="mt-2">Cargando contenido...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
