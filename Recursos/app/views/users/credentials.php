c?php
// app/views/users/credentials.php - Editar credenciales
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /Recursos/index.php?route=login'); exit; }
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>
      <div class="card">
        <h2 style="margin-top:0">Editar credenciales</h2>
        <?php if(!$user): ?>
          <div class="alert error">Usuario no encontrado</div>
        <?php else: ?>
        <?php if($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>
        <form action="/Recursos/index.php?route=users.credentialsUpdate" method="post">
          <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
          <div class="grid-2">
            <div class="form-row">
              <label for="username">Usuario</label>
              <input class="input" type="text" id="username" name="username" maxlength="50" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="form-row">
              <label for="email">Email</label>
              <input class="input" type="email" id="email" name="email" maxlength="100" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="form-row">
              <label for="password">Nueva contraseña (opcional)</label>
              <input class="input" type="password" id="password" name="password">
            </div>
            <div class="form-row">
              <label for="password2">Repite la nueva contraseña</label>
              <input class="input" type="password" id="password2" name="password2">
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

