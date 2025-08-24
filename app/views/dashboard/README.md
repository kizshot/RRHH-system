# Dashboard HR365

## Descripción
El dashboard de HR365 es una interfaz moderna y responsiva que proporciona una visión general de las métricas clave del sistema de recursos humanos.

## Características

### 📊 Tarjetas de Resumen
- **Personas**: Total de empleados activos
- **Trabajando**: Empleados actualmente trabajando
- **Adelantos**: Monto total de adelantos pendientes
- **Horas Extras**: Monto total de horas extras pendientes

### 📈 Gráficos Interactivos
- **Horas Extras Mensuales**: Gráfico de barras mostrando horas extras por mes
- **Adelantos Mensuales**: Gráfico de barras mostrando adelantos por mes

### 📋 Actividades Recientes
- Lista de actividades recientes del sistema
- Iconos coloridos para cada tipo de actividad
- Timestamps relativos

### ✅ Tareas Pendientes
- Lista de tareas que requieren atención
- Sistema de prioridades (Alta, Media, Baja)
- Checkboxes interactivos

## Tecnologías Utilizadas

### Frontend
- **Bootstrap 5.3.2**: Framework CSS para el diseño responsivo
- **Chart.js**: Biblioteca para gráficos interactivos
- **Font Awesome 6.5.0**: Iconografía profesional
- **CSS Personalizado**: Estilos específicos para el dashboard

### Backend
- **PHP**: Lógica del servidor
- **MySQL**: Base de datos para estadísticas
- **Arquitectura MVC**: Separación de responsabilidades

## Estructura de Archivos

```
dashboard/
├── index.php          # Vista principal del dashboard
├── layout.php         # Layout específico del dashboard
├── README.md          # Este archivo
└── config/            # Configuración del dashboard
    └── dashboard_config.php
```

## Personalización

### Colores de Tarjetas
Los colores se pueden personalizar en `dashboard_config.php`:

```php
'colors' => [
    'primary' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    'success' => 'linear-gradient(135deg, #20c997 0%, #28a745 100%)',
    'warning' => 'linear-gradient(135deg, #ffc107 0%, #fd7e14 100%)',
    'purple' => 'linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%)'
]
```

### Configuración de Gráficos
Cada gráfico se puede configurar individualmente:

```php
'charts' => [
    'horas_extras' => [
        'title' => 'Horas extras Mensuales - 2024',
        'type' => 'bar',
        'color' => '#20c997',
        'y_axis' => [
            'max' => 25,
            'step' => 5
        ]
    ]
]
```

### Actividades y Tareas
Las actividades recientes y tareas pendientes se pueden personalizar en el archivo de configuración.

## Responsive Design

El dashboard está optimizado para diferentes tamaños de pantalla:

- **Desktop (>1024px)**: Layout completo con sidebar fijo
- **Tablet (768px-1024px)**: Sidebar colapsable
- **Mobile (<768px)**: Sidebar overlay, layout optimizado

## Funcionalidades JavaScript

### Gráficos
- Gráficos responsivos que se adaptan al tamaño de la pantalla
- Interactividad con hover y tooltips
- Configuración automática de escalas

### Interactividad
- Hover effects en tarjetas
- Checkboxes funcionales en tareas
- Transiciones suaves en elementos

## Base de Datos

### Consultas Principales
- Conteo de empleados activos
- Suma de montos de adelantos pendientes
- Suma de montos de horas extras pendientes
- Estadísticas mensuales agrupadas

### Fallback de Datos
En caso de error en la base de datos, se muestran datos de ejemplo para mantener la funcionalidad.

## Mantenimiento

### Actualización de Estadísticas
Las estadísticas se actualizan en tiempo real cada vez que se accede al dashboard.

### Logs y Monitoreo
- Errores de base de datos se capturan y manejan graciosamente
- Datos de ejemplo se proporcionan como fallback

## Próximas Mejoras

- [ ] Filtros de fecha para gráficos
- [ ] Exportación de reportes
- [ ] Notificaciones en tiempo real
- [ ] Widgets personalizables
- [ ] Temas adicionales
- [ ] Integración con APIs externas

## Soporte

Para problemas o sugerencias relacionadas con el dashboard, consultar la documentación principal del proyecto o crear un issue en el repositorio.
