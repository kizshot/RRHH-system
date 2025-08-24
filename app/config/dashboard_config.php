<?php
// app/config/dashboard_config.php - Configuración del Dashboard

return [
    // Configuración de tarjetas de resumen
    'summary_cards' => [
        'personas' => [
            'title' => 'Personas',
            'icon' => 'fa-people-group',
            'color' => 'primary',
            'format' => 'number'
        ],
        'trabajando' => [
            'title' => 'Trabajando',
            'icon' => 'fa-gear',
            'color' => 'success',
            'format' => 'number'
        ],
        'adelantos' => [
            'title' => 'Adelantos',
            'icon' => 'fa-money-bill-1',
            'color' => 'warning',
            'format' => 'currency',
            'currency' => 'S/.'
        ],
        'horas_extras' => [
            'title' => 'Hrs. extras',
            'icon' => 'fa-clock',
            'color' => 'purple',
            'format' => 'currency',
            'currency' => 'S/.'
        ]
    ],
    
    // Configuración de gráficos
    'charts' => [
        'horas_extras' => [
            'title' => 'Horas extras Mensuales - 2024',
            'type' => 'bar',
            'color' => '#20c997',
            'y_axis' => [
                'max' => 25,
                'step' => 5
            ]
        ],
        'adelantos' => [
            'title' => 'Adelantos Mensuales - 2024',
            'type' => 'bar',
            'color' => '#e83e8c',
            'y_axis' => [
                'max' => 900,
                'step' => 100
            ]
        ]
    ],
    
    // Configuración de actividades recientes
    'recent_activities' => [
        [
            'icon' => 'fa-user-plus',
            'color' => 'success',
            'text' => 'Nuevo empleado registrado',
            'time' => 'Hace 2 horas'
        ],
        [
            'icon' => 'fa-clock',
            'color' => 'info',
            'text' => 'Horas extras aprobadas',
            'time' => 'Hace 4 horas'
        ],
        [
            'icon' => 'fa-money-bill-wave',
            'color' => 'warning',
            'text' => 'Adelanto solicitado',
            'time' => 'Hace 6 horas'
        ]
    ],
    
    // Configuración de tareas pendientes
    'pending_tasks' => [
        [
            'id' => 'task1',
            'text' => 'Revisar solicitudes de vacaciones',
            'priority' => 'high'
        ],
        [
            'id' => 'task2',
            'text' => 'Aprobar horas extras del mes',
            'priority' => 'medium'
        ],
        [
            'id' => 'task3',
            'text' => 'Generar reporte de asistencia',
            'priority' => 'low'
        ]
    ],
    
    // Colores personalizados
    'colors' => [
        'primary' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'success' => 'linear-gradient(135deg, #20c997 0%, #28a745 100%)',
        'warning' => 'linear-gradient(135deg, #ffc107 0%, #fd7e14 100%)',
        'purple' => 'linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%)',
        'info' => 'linear-gradient(135deg, #17a2b8 0%, #0dcaf0 100%)',
        'danger' => 'linear-gradient(135deg, #dc3545 0%, #fd7e14 100%)'
    ]
];
?>
