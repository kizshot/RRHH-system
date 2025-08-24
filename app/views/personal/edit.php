<?php
// app/views/personal/edit.php - Editar Personal
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /Recursos/index.php?route=login'); exit; }
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$statuses = ['ACTIVO','INACTIVO','VACACIONES','LICENCIA'];
?>
      <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:.75rem">
          <h2 style="margin:0">Editar Personal</h2>
          <a href="/Recursos/index.php?route=personal.index" class="button" style="background:#6b7280"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>
        <?php if($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert success"><?= $success ?></div><?php endif; ?>
        
        <form action="/Recursos/index.php?route=personal.update" method="post">
          <input type="hidden" name="id" value="<?= (int)$personal['id'] ?>">
          
          <div class="grid-2">
            <div class="form-row">
              <label for="employee_code">Código de Empleado *</label>
              <input class="input" type="text" id="employee_code" name="employee_code" maxlength="20" required value="<?= htmlspecialchars($personal['employee_code']) ?>">
            </div>
            <div class="form-row">
              <label for="dni">DNI</label>
              <input class="input" type="text" id="dni" name="dni" maxlength="20" value="<?= htmlspecialchars($personal['dni'] ?? '') ?>">
            </div>
            <div class="form-row">
              <label for="first_name">Nombre *</label>
              <input class="input" type="text" id="first_name" name="first_name" maxlength="100" required value="<?= htmlspecialchars($personal['first_name']) ?>">
            </div>
            <div class="form-row">
              <label for="last_name">Apellidos *</label>
              <input class="input" type="text" id="last_name" name="last_name" maxlength="100" required value="<?= htmlspecialchars($personal['last_name']) ?>">
            </div>
            <div class="form-row">
              <label for="birth_date">Fecha de Nacimiento</label>
              <input class="input" type="date" id="birth_date" name="birth_date" value="<?= htmlspecialchars($personal['birth_date'] ?? '') ?>">
            </div>
            <div class="form-row">
              <label for="hire_date">Fecha de Contratación *</label>
              <input class="input" type="date" id="hire_date" name="hire_date" required value="<?= htmlspecialchars($personal['hire_date']) ?>">
            </div>
            <div class="form-row">
              <label for="position">Posición</label>
              <input class="input" type="text" id="position" name="position" maxlength="100" value="<?= htmlspecialchars($personal['position'] ?? '') ?>">
            </div>
            <div class="form-row">
              <label for="department">Departamento</label>
              <input class="input" type="text" id="department" name="department" maxlength="100" value="<?= htmlspecialchars($personal['department'] ?? '') ?>">
            </div>
            <div class="form-row">
              <label for="salary">Salario</label>
              <input class="input" type="number" id="salary" name="salary" step="0.01" min="0" value="<?= htmlspecialchars($personal['salary'] ?? '') ?>">
            </div>
            <div class="form-row">
              <label for="status">Estado</label>
              <select class="input" id="status" name="status">
                <?php foreach($statuses as $s): ?>
                  <option value="<?= $s ?>" <?= ($personal['status'] === $s) ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-row">
              <label for="phone">Teléfono</label>
              <input class="input" type="tel" id="phone" name="phone" maxlength="20" value="<?= htmlspecialchars($personal['phone'] ?? '') ?>">
            </div>
            <div class="form-row">
              <label for="address">Dirección</label>
              <textarea class="input" id="address" name="address" rows="2"><?= htmlspecialchars($personal['address'] ?? '') ?></textarea>
            </div>
            <div class="form-row">
              <label for="emergency_contact">Contacto de Emergencia</label>
              <input class="input" type="text" id="emergency_contact" name="emergency_contact" maxlength="100" value="<?= htmlspecialchars($personal['emergency_contact'] ?? '') ?>">
            </div>
            <div class="form-row">
              <label for="emergency_phone">Teléfono de Emergencia</label>
              <input class="input" type="tel" id="emergency_phone" name="emergency_phone" maxlength="20" value="<?= htmlspecialchars($personal['emergency_phone'] ?? '') ?>">
            </div>
          </div>
          
          <div style="margin-top:1rem; display:flex; gap:.5rem">
            <button type="submit" class="button"><i class="fa fa-save"></i> Actualizar</button>
            <a href="/Recursos/index.php?route=personal.index" class="button" style="background:#6b7280">Cancelar</a>
          </div>
        </form>
      </div>

    </main>
  </div>
  <script src="/Recursos/assets/js/main.js"></script>
</body>
</html>
