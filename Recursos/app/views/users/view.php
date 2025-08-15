c?php
// app/views/users/view.php - Ver usuario
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /Recursos/index.php?route=login'); exit; }
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';
?>
      <div class="card">
        <h2 style="margin-top:0">Detalle de Usuario</h2>
        <?php if(!$user): ?>
          <div class="alert error">Usuario no encontrado</div>
        <?php else: ?>
          <div style="display:flex; gap:1rem; align-items:center">
            <?php if(!empty($user['avatar'])): ?>
              <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="avatar" style="width:64px;height:64px;border-radius:50%">
            <?php else: ?>
              <i class="fa fa-user-circle" style="font-size:48px"></i>
            <?php endif; ?>
            <div>
              <div><strong>ID:</strong> <?= (int)$user['id'] ?></div>
              <div><strong>Usuario:</strong> <?= htmlspecialchars($user['username']) ?></div>
              <div><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
            </div>
          </div>
          <div style="margin-top:1rem" class="grid-2">
            <div><strong>Nombre:</strong> <?= htmlspecialchars($user['first_name'] ?? '') ?></div>
            <div><strong>Apellidos:</strong> <?= htmlspecialchars($user['last_name'] ?? '') ?></div>
            <div><strong>Rol:</strong> <?= htmlspecialchars($user['role'] ?? '') ?></div>
            <div><strong>Estado:</strong> <?= htmlspecialchars($user['status'] ?? '') ?></div>
            <div><strong>Código:</strong> <?= htmlspecialchars($user['code'] ?? '') ?></div>
            <div><strong>Creado:</strong> <?= htmlspecialchars($user['created_at'] ?? '') ?></div>
          </div>
          <div style="margin-top:1rem; display:flex; gap:.5rem; flex-wrap:wrap">
            <a class="button" style="background:#f59e0b" href="/Recursos/index.php?route=users.edit&id=<?= (int)$user['id'] ?>"><i class="fa fa-pen"></i> Editar</a>
            <a class="button" style="background:#3b82f6" href="/Recursos/index.php?route=users.credentials&id=<?= (int)$user['id'] ?>"><i class="fa fa-key"></i> Credenciales</a>
            <a class="button" href="/Recursos/index.php?route=users.index"><i class="fa fa-arrow-left"></i> Volver</a>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
  <script src="/Recursos/assets/js/main.js"></script>
</body>
</html>

