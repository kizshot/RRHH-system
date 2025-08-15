<?php
// app/views/auth/register.php
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HR365 - Registro</title>
  <link rel="stylesheet" href="/Recursos/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
  <div class="auth-wrapper">
    <div class="card auth-card">
      <div class="title">Crear cuenta</div>
      <?php if ($error): ?><div class="alert error"><?php echo $error; ?></div><?php endif; ?>
      <form action="/Recursos/index.php?route=do_register" method="post" autocomplete="off">
        <div class="form-row">
          <label for="username">Usuario</label>
          <input class="input" type="text" id="username" name="username" required maxlength="50" placeholder="tu_usuario">
        </div>
        <div class="form-row">
          <label for="email">Email</label>
          <input class="input" type="email" id="email" name="email" required maxlength="100" placeholder="correo@dominio.com">
        </div>
        <div class="form-row">
          <label for="password">Contraseña</label>
          <input class="input" type="password" id="password" name="password" required>
          <small class="helper">Usa una contraseña segura.</small>
        </div>
        <button class="button" type="submit"><i class="fa-solid fa-user-plus"></i> Registrarme</button>
      </form>
      <div class="auth-links">
        <a href="/Recursos/index.php?route=login"><i class="fa-solid fa-arrow-left"></i> Volver al login</a>
      </div>
    </div>
  </div>
</body>
</html>

