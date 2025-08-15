<?php
// app/views/dashboard/index.php
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /Recursos/index.php?route=login'); exit; }
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';
?>
      <div class="card">
        <h2>Bienvenido a HR365</h2>
        <p>Hola, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>. Este es tu panel principal.</p>
        <p class="helper">Usa el menú lateral para navegar por los módulos del sistema.</p>
      </div>
    </main>
  </div>
  <script src="/Recursos/assets/js/main.js"></script>
</body>
</html>

