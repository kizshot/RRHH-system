<?php
// app/views/layout/header.php - vista header
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$userName = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Invitado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HR365</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Custom CSS -->
  <link rel="stylesheet" href="/Recursos/assets/css/style.css">
  <link rel="stylesheet" href="/Recursos/assets/css/components.css">
  <link rel="stylesheet" href="/Recursos/assets/css/modals.css">
  <link rel="stylesheet" href="/Recursos/assets/css/notifications.css">
</head>
<body>
  <div class="topbar">
    <div class="topbar-left">
      <button class="hamburger" aria-label="Alternar menú"><i class="fa fa-bars"></i></button>
      <button id="theme-toggle" class="icon-btn" title="Modo día/noche"><i class="fa-solid fa-moon"></i></button>
      <div class="brand">HR365</div>
    </div>
    <div class="topbar-right">
      <button class="icon-btn" title="Notificaciones"><i class="fa-solid fa-bell"></i></button>
      <div class="user-menu">
        <span class="user-name"><i class="fa-solid fa-user"></i> <?php echo $userName; ?></span>
        <div class="user-dropdown">
          <?php if(isset($_SESSION['user_id'])): ?>
            <a href="/Recursos/index.php?route=logout"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a>
          <?php else: ?>
            <a href="/Recursos/index.php?route=login"><i class="fa-solid fa-right-to-bracket"></i> Iniciar sesión</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <div class="layout">

