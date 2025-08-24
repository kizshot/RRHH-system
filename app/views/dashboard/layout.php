<?php
// app/views/dashboard/layout.php - Layout específico para el dashboard
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: /Recursos/index.php?route=login');
    exit;
}

// Incluir el header
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';
?>

<!-- Contenido del dashboard se insertará aquí -->
<?php if (isset($dashboardContent)) echo $dashboardContent; ?>

<?php
// Incluir el footer
require __DIR__ . '/../layout/footer.php';
?>
