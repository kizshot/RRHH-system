c?php
// app/views/users/edit.php - Editar usuario
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /Recursos/index.php?route=login'); exit; }
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';
$roles = $roles ?? ['SUPER USER','TRABAJADOR','VENDEDOR','RECEPCIONISTA','CHOFER','PROGRAMADOR'];
$statuses = $statuses ?? ['ACTIVO','INACTIVO'];
?>
      <div class="card">
        <h2 style="margin-top:0">Editar Usuario</h2>
        <?php if(!$user): ?>
          <div class="alert error">Usuario no encontrado</div>
        <?php else: ?>
        <form action="/Recursos/index.php?route=users.update" method="post" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
          <div class="grid-2">
            <div class="form-row">
              <label for="first_name">Nombre</label>
              <input class="input" type="text" id="first_name" name="first_name" maxlength="100" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>">
            </div>
            <div class="form-row">
              <label for="last_name">Apellidos</label>
              <input class="input" type="text" id="last_name" name="last_name" maxlength="100" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
            </div>
            <div class="form-row">
              <label for="role">Rol Usuario</label>
              <select class="input" id="role" name="role" required>
                <?php foreach($roles as $r): ?>
                  <option value="<?= $r ?>" <?= ($user['role']??'')===$r?'selected':'' ?>><?= $r ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-row">
              <label for="status">Estado</label>
              <select class="input" id="status" name="status">
                <?php foreach($statuses as $s): ?>
                  <option value="<?= $s ?>" <?= ($user['status']??'')===$s?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-row">
              <label for="code">Código</label>
              <input class="input" type="text" id="code" name="code" maxlength="32" value="<?= htmlspecialchars($user['code'] ?? '') ?>">
            </div>
            <div class="form-row">
              <label for="avatar">Imagen</label>
              <input class="input" type="file" id="avatar" name="avatar" accept="image/*">
            </div>
          </div>
          <div class="modal-actions" style="padding-left:0">
            <a class="button" href="/Recursos/index.php?route=users.view&id=<?= (int)$user['id'] ?>" style="background:#6b7280"><i class="fa fa-arrow-left"></i> Cancelar</a>
            <button class="button" type="submit"><i class="fa fa-save"></i> Guardar</button>
          </div>
        </form>
        <?php endif; ?>
      </div>
    </main>
  </div>
  <script src="/Recursos/assets/js/main.js"></script>
</body>
</html>

