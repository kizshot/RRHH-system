<?php
// app/views/dashboard/index.php - Vista del Dashboard
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>

<div class="dashboard-container">
    <!-- Tarjetas de resumen -->
    <div class="row g-4 mb-4">
        <!-- Tarjeta Personas -->
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body d-flex align-items-center">
                    <div class="card-icon bg-primary me-3">
                        <i class="fa-solid fa-people-group"></i>
                    </div>
                    <div class="card-content">
                        <h6 class="card-title mb-1">Personas</h6>
                        <h3 class="card-value mb-0">Total: <?php echo $stats['total_personas']; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta Trabajando -->
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body d-flex align-items-center">
                    <div class="card-icon bg-success me-3">
                        <i class="fa-solid fa-gear"></i>
                    </div>
                    <div class="card-content">
                        <h6 class="card-title mb-1">Trabajando</h6>
                        <h3 class="card-value mb-0">Total: <?php echo $stats['trabajando']; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta Adelantos -->
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body d-flex align-items-center">
                    <div class="card-icon bg-warning me-3">
                        <i class="fa-solid fa-money-bill-1"></i>
                    </div>
                    <div class="card-content">
                        <h6 class="card-title mb-1">Adelantos</h6>
                        <h3 class="card-value mb-0">S/. <?php echo number_format($stats['total_adelantos'], 2); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta Horas Extras -->
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body d-flex align-items-center">
                    <div class="card-icon bg-purple me-3">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div class="card-content">
                        <h6 class="card-title mb-1">Hrs. extras</h6>
                        <h3 class="card-value mb-0">S/. <?php echo number_format($stats['total_extras'], 2); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row g-4">
        <!-- Gráfico Horas Extras -->
        <div class="col-md-6">
            <div class="card dashboard-chart-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fa-solid fa-chart-bar me-2"></i>
                        Horas extras Mensuales - 2024
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartHorasExtras" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico Adelantos -->
        <div class="col-md-6">
            <div class="card dashboard-chart-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fa-solid fa-chart-line me-2"></i>
                        Adelantos Mensuales - 2024
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartAdelantos" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas adicionales -->
    <div class="row g-4 mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fa-solid fa-calendar-check me-2"></i>
                        Actividad Reciente
                    </h6>
                </div>
                <div class="card-body">
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon bg-success">
                                <i class="fa-solid fa-user-plus"></i>
                            </div>
                            <div class="activity-content">
                                <p class="activity-text">Nuevo empleado registrado</p>
                                <small class="activity-time">Hace 2 horas</small>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon bg-info">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div class="activity-content">
                                <p class="activity-text">Horas extras aprobadas</p>
                                <small class="activity-time">Hace 4 horas</small>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon bg-warning">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </div>
                            <div class="activity-content">
                                <p class="activity-text">Adelanto solicitado</p>
                                <small class="activity-time">Hace 6 horas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fa-solid fa-tasks me-2"></i>
                        Tareas Pendientes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="task-list">
                        <div class="task-item">
                            <div class="task-checkbox">
                                <input type="checkbox" id="task1">
                                <label for="task1">Revisar solicitudes de vacaciones</label>
                            </div>
                            <span class="task-priority high">Alta</span>
                        </div>
                        <div class="task-item">
                            <div class="task-checkbox">
                                <input type="checkbox" id="task2">
                                <label for="task2">Aprobar horas extras del mes</label>
                            </div>
                            <span class="task-priority medium">Media</span>
                        </div>
                        <div class="task-item">
                            <div class="task-checkbox">
                                <input type="checkbox" id="task3">
                                <label for="task3">Generar reporte de asistencia</label>
                            </div>
                            <span class="task-priority low">Baja</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para los gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos para los gráficos
    const horasExtrasData = <?php echo json_encode($stats['horas_extras_mensual']); ?>;
    const adelantosData = <?php echo json_encode($stats['adelantos_mensual']); ?>;

    // Gráfico de Horas Extras
    const ctxHorasExtras = document.getElementById('chartHorasExtras').getContext('2d');
    new Chart(ctxHorasExtras, {
        type: 'bar',
        data: {
            labels: horasExtrasData.map(item => item.mes),
            datasets: [{
                label: 'Horas Extras',
                data: horasExtrasData.map(item => item.valor),
                backgroundColor: '#20c997',
                borderColor: '#20c997',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 25,
                    ticks: {
                        stepSize: 5
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });

    // Gráfico de Adelantos
    const ctxAdelantos = document.getElementById('chartAdelantos').getContext('2d');
    new Chart(ctxAdelantos, {
        type: 'bar',
        data: {
            labels: adelantosData.map(item => item.mes),
            datasets: [{
                label: 'Adelantos',
                data: adelantosData.map(item => item.valor),
                backgroundColor: '#e83e8c',
                borderColor: '#e83e8c',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 900,
                    ticks: {
                        stepSize: 100
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });
});
</script>

