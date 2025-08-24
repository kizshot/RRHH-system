<?php
// app/views/layout/sidebar.php - vista sidebar
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$activeUser = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Invitado';
?>
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="logo"><i class="fa-solid fa-people-group"></i></div>
        <div>
          <div class="product">HR365</div>
          <div class="active-user">Usuario: <?php echo $activeUser; ?></div>
        </div>
      </div>
      <nav class="nav">
        <a href="/Recursos/index.php?route=dashboard" class="nav-link">
          <i class="fa fa-gauge"></i> <span>Dashboard</span>
        </a>
        <a href="/Recursos/index.php?route=users.index" class="nav-link">
          <i class="fa fa-user"></i> <span>Usuarios</span>
        </a>
        <a href="/Recursos/index.php?route=roles.index" class="nav-link">
          <i class="fa fa-user-shield"></i> <span>Roles</span>
        </a>
        <a href="/Recursos/index.php?route=personal.index" class="nav-link">
          <i class="fa fa-id-card"></i> <span>Personal</span>
        </a>
        <a href="/Recursos/index.php?route=jornadas.index" class="nav-link">
          <i class="fa fa-clock"></i> <span>Jornadas</span>
        </a>
        <a href="/Recursos/index.php?route=pagos.index" class="nav-link">
          <i class="fa fa-money-bill-wave"></i> <span>Pagos</span>
        </a>
        <a href="/Recursos/index.php?route=adelantos.index" class="nav-link">
          <i class="fa fa-hand-holding-usd"></i> <span>Adelantos</span>
        </a>
        <a href="/Recursos/index.php?route=extras.index" class="nav-link">
          <i class="fa fa-plus-circle"></i> <span>Extras</span>
        </a>
        <a href="/Recursos/index.php?route=vacaciones.index" class="nav-link">
          <i class="fa fa-suitcase-rolling"></i> <span>Vacaciones</span>
        </a>
        <a href="/Recursos/index.php?route=asistencias.index" class="nav-link">
          <i class="fa fa-user-check"></i> <span>Asistencias</span>
        </a>
        <a href="#" class="nav-link">
          <i class="fa fa-chart-line"></i> <span>Reportes</span>
        </a>
        <a href="#" class="nav-link">
          <i class="fa fa-clock"></i> <span>Turnos</span>
        </a>
        <a href="#" class="nav-link">
          <i class="fa fa-sliders"></i> <span>Panel</span>
        </a>
        <a href="#" class="nav-link">
          <i class="fa fa-chart-pie"></i> <span>Resumen G.</span>
        </a>
        <a href="#" class="nav-link">
          <i class="fa fa-building"></i> <span>Empresa</span>
        </a>
        <a href="#" class="nav-link">
          <i class="fa fa-lock"></i> <span>Permisos</span>
        </a>
      </nav>
    </aside>
    <main class="content">

