<?php
// app/views/auth/login.php
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HR365 - Iniciar sesión</title>
  <link rel="stylesheet" href="/Recursos/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
  <div class="auth-wrapper">
    <div class="card auth-card">
      <div class="title">Iniciar sesión</div>
      <?php if ($error): ?><div class="alert error"><?php echo $error; ?></div><?php endif; ?>
      <form action="/Recursos/index.php?route=do_login" method="post" autocomplete="on">
        <div class="form-row">
          <label for="username">Usuario o Email</label>
          <input class="input" type="text" id="username" name="username" required maxlength="100" placeholder="tu_usuario o correo@dominio.com">
        </div>
        <div class="form-row">
          <label for="password">Contraseña</label>
          <input class="input" type="password" id="password" name="password" required>
        </div>
        <button class="button" type="submit"><i class="fa-solid fa-right-to-bracket"></i> Entrar</button>
      </form>
      <div class="auth-links">
        <span class="helper">¿No tienes cuenta?</span>
        <a href="/Recursos/index.php?route=register">Crear cuenta</a>
      </div>
    </div>
  </div>
</body>
</html>

