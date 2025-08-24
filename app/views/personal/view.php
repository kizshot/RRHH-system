<?php
// app/views/personal/view.php - Ver Personal
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /Recursos/index.php?route=login'); exit; }
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
?>
      <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:.75rem">
          <h2 style="margin:0">Ver Personal</h2>
          <div style="display:flex; gap:.5rem">
            <a href="/Recursos/index.php?route=personal.edit&id=<?= (int)$personal['id'] ?>" class="button" style="background:#f59e0b"><i class="fa fa-pen"></i> Editar</a>
            <a href="/Recursos/index.php?route=personal.index" class="button" style="background:#6b7280"><i class="fa fa-arrow-left"></i> Volver</a>
          </div>
        </div>
        <?php if($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert success"><?= $success ?></div><?php endif; ?>
        
        <div class="grid-2">
          <div class="form-row">
            <label><strong>ID:</strong></label>
            <div><?= (int)$personal['id'] ?></div>
          </div>
          <div class="form-row">
            <label><strong>Código de Empleado:</strong></label>
            <div><?= htmlspecialchars($personal['employee_code']) ?></div>
          </div>
          <div class="form-row">
            <label><strong>Nombre:</strong></label>
            <div><?= htmlspecialchars($personal['first_name']) ?></div>
          </div>
          <div class="form-row">
            <label><strong>Apellidos:</strong></label>
            <div><?= htmlspecialchars($personal['last_name']) ?></div>
          </div>
          <div class="form-row">
            <label><strong>DNI:</strong></label>
            <div><?= htmlspecialchars($personal['dni'] ?? 'No especificado') ?></div>
          </div>
          <div class="form-row">
            <label><strong>Fecha de Nacimiento:</strong></label>
            <div><?= htmlspecialchars($personal['birth_date'] ?? 'No especificada') ?></div>
          </div>
          <div class="form-row">
            <label><strong>Fecha de Contratación:</strong></label>
            <div><?= htmlspecialchars($personal['hire_date']) ?></div>
          </div>
          <div class="form-row">
            <label><strong>Posición:</strong></label>
            <div><?= htmlspecialchars($personal['position'] ?? 'No especificada') ?></div>
          </div>
          <div class="form-row">
            <label><strong>Departamento:</strong></label>
            <div><?= htmlspecialchars($personal['department'] ?? 'No especificado') ?></div>
          </div>
          <div class="form-row">
            <label><strong>Salario:</strong></label>
            <div><?= $personal['salary'] ? 'S/ ' . number_format($personal['salary'], 2) : 'No especificado' ?></div>
          </div>
          <div class="form-row">
            <label><strong>Estado:</strong></label>
            <div>
              <span class="badge" style="background:<?= $personal['status'] === 'ACTIVO' ? '#10b981' : ($personal['status'] === 'VACACIONES' ? '#f59e0b' : '#ef4444') ?>">
                <?= htmlspecialchars($personal['status']) ?>
              </span>
            </div>
          </div>
          <div class="form-row">
            <label><strong>Teléfono:</strong></label>
            <div><?= htmlspecialchars($personal['phone'] ?? 'No especificado') ?></div>
          </div>
          <div class="form-row">
            <label><strong>Dirección:</strong></label>
            <div><?= htmlspecialchars($personal['address'] ?? 'No especificada') ?></div>
          </div>
          <div class="form-row">
            <label><strong>Contacto de Emergencia:</strong></label>
            <div><?= htmlspecialchars($personal['emergency_contact'] ?? 'No especificado') ?></div>
          </div>
          <div class="form-row">
            <label><strong>Teléfono de Emergencia:</strong></label>
            <div><?= htmlspecialchars($personal['emergency_phone'] ?? 'No especificado') ?></div>
          </div>
          <?php if($personal['user_id']): ?>
          <div class="form-row">
            <label><strong>Usuario Asociado:</strong></label>
            <div>
              <?= htmlspecialchars($personal['username'] ?? 'Usuario #' . $personal['user_id']) ?>
              <br><small class="helper"><?= htmlspecialchars($personal['email'] ?? '') ?></small>
            </div>
          </div>
          <?php endif; ?>
          <div class="form-row">
            <label><strong>Fecha de Creación:</strong></label>
            <div><?= htmlspecialchars($personal['created_at']) ?></div>
          </div>
        </div>
      </div>

    </main>
  </div>
  <script src="/Recursos/assets/js/main.js"></script>
</body>
</html>
