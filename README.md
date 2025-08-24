# HR365 - Sistema de Gestión de Recursos Humanos

## Descripción
HR365 es un sistema completo de gestión de recursos humanos que permite administrar personal, jornadas, pagos, adelantos, extras, vacaciones y asistencias.

## Estructura del Proyecto

### 📁 Organización de Archivos

```
Recursos/
├── app/
│   ├── controllers/          # Controladores de la aplicación
│   ├── models/              # Modelos de datos
│   └── views/               # Vistas y templates
│       ├── layout/          # Layouts principales (header, sidebar, footer)
│       ├── personal/        # Vistas del módulo de personal
│       ├── jornadas/        # Vistas del módulo de jornadas
│       ├── pagos/           # Vistas del módulo de pagos
│       └── ...              # Otros módulos
├── assets/
│   ├── css/                 # Hojas de estilo
│   │   ├── style.css        # Estilos principales
│   │   ├── components.css   # Componentes reutilizables
│   │   └── modals.css       # Estilos específicos para modales
│   ├── js/                  # JavaScript
│   │   ├── main.js          # Funcionalidades principales
│   │   └── modals.js        # Sistema de modales
│   └── img/                 # Imágenes y recursos gráficos
├── includes/
│   └── db_config.php        # Configuración de base de datos
└── index.php                # Punto de entrada principal
```

## 🚀 Características Principales

### ✨ Diseño Moderno
- **Bootstrap 5.3.2**: Framework CSS moderno y responsivo
- **Font Awesome 6.5.0**: Iconografía profesional
- **Diseño responsivo**: Adaptable a todos los dispositivos
- **Tema claro/oscuro**: Con persistencia en localStorage

### 🔧 Sistema de Modales
- **ModalManager**: Clase JavaScript para manejo eficiente de modales
- **Modales Bootstrap**: Integración nativa con Bootstrap
- **Carga dinámica**: Contenido de modales cargado via AJAX
- **Gestión de estado**: Stack de modales para navegación compleja

### 📱 Interfaz de Usuario
- **Sidebar colapsable**: Navegación optimizada para escritorio y móvil
- **Topbar inteligente**: Con menú de usuario y controles de tema
- **Tablas interactivas**: Con ordenamiento y búsqueda
- **Formularios validados**: Con feedback visual y manejo de errores

## 🛠️ Tecnologías Utilizadas

### Frontend
- **HTML5**: Estructura semántica
- **CSS3**: Estilos modernos con variables CSS
- **JavaScript ES6+**: Funcionalidades interactivas
- **Bootstrap 5.3.2**: Componentes UI
- **Font Awesome**: Iconografía

### Backend
- **PHP 7.4+**: Lógica de servidor
- **MySQL/MariaDB**: Base de datos
- **Arquitectura MVC**: Separación de responsabilidades

## 📋 Módulos Disponibles

### 👥 Personal
- ✅ Listado con búsqueda y filtros
- ✅ Crear nuevo empleado
- ✅ Editar información
- ✅ Ver detalles
- ✅ Eliminar empleado
- ✅ Estados: Activo, Inactivo, Vacaciones, Licencia

### ⏰ Jornadas
- ✅ Gestión de horarios laborales
- ✅ Control de entrada/salida
- ✅ Reportes de tiempo

### 💰 Pagos
- ✅ Generación de nóminas
- ✅ Cálculo de salarios
- ✅ Historial de pagos

### 💸 Adelantos
- ✅ Solicitud de adelantos
- ✅ Aprobación/Rechazo
- ✅ Control de pagos

### ➕ Extras
- ✅ Horas extras
- ✅ Bonificaciones
- ✅ Control de pagos

### 🏖️ Vacaciones
- ✅ Solicitud de vacaciones
- ✅ Aprobación/Rechazo
- ✅ Control de días disponibles

### ✅ Asistencias
- ✅ Registro de asistencia
- ✅ Control de entradas/salidas
- ✅ Reportes de asistencia

## 🚀 Instalación

### Requisitos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)

### Pasos de Instalación
1. **Clonar el repositorio**
   ```bash
   git clone [URL_DEL_REPOSITORIO]
   cd Recursos
   ```

2. **Configurar base de datos**
   - Crear base de datos MySQL
   - Importar `database.sql`
   - Configurar `includes/db_config.php`

3. **Configurar servidor web**
   - Apuntar al directorio `Recursos/`
   - Asegurar permisos de escritura en directorios necesarios

4. **Acceder a la aplicación**
   - Navegar a `http://localhost/Recursos/`
   - Crear usuario administrador inicial

## 🔧 Configuración

### Variables CSS Personalizables
```css
:root {
  --bg: #f5f7fb;           /* Color de fondo principal */
  --card: #ffffff;          /* Color de tarjetas */
  --text: #1f2937;          /* Color de texto principal */
  --muted: #6b7280;         /* Color de texto secundario */
  --primary: #2563eb;       /* Color primario */
  --border: #e5e7eb;        /* Color de bordes */
}
```

### Configuración de Modales
```javascript
// Abrir modal con opciones personalizadas
modalManager.openModal('modal-id', {
  size: 'large',           // Tamaño del modal
  type: 'form',            // Tipo de modal
  scrollable: true,        // Permitir scroll
  backdrop: 'static'       // Comportamiento del backdrop
});
```

## 📱 Responsive Design

### Breakpoints
- **Desktop**: > 1024px - Sidebar fijo, layout completo
- **Tablet**: 768px - 1024px - Sidebar colapsable
- **Mobile**: < 768px - Sidebar overlay, layout optimizado

### Características Responsivas
- Sidebar adaptable
- Tablas con scroll horizontal
- Formularios en columnas únicas en móvil
- Modales optimizados para pantallas pequeñas

## 🎨 Personalización

### Temas
- **Tema claro**: Colores neutros con acentos azules
- **Tema oscuro**: Colores oscuros con acentos azules
- **Persistencia**: El tema se guarda en localStorage

### Componentes Personalizables
- Colores de marca
- Tipografías
- Espaciados
- Bordes y sombras

## 🔒 Seguridad

### Características de Seguridad
- **Validación de sesiones**: Control de acceso en todas las páginas
- **Sanitización de datos**: Escape de HTML en salidas
- **Preparación de consultas**: Prevención de SQL injection
- **Control de acceso**: Verificación de permisos por módulo

## 📊 Rendimiento

### Optimizaciones
- **CSS modular**: Separación de estilos por funcionalidad
- **JavaScript eficiente**: Uso de event delegation y lazy loading
- **Imágenes optimizadas**: Formatos modernos y tamaños apropiados
- **Caché del navegador**: Headers apropiados para recursos estáticos

## 🐛 Solución de Problemas

### Problemas Comunes

#### Modales no funcionan
- Verificar que Bootstrap JS esté cargado
- Comprobar que `modals.js` esté incluido
- Revisar consola del navegador para errores

#### Estilos no se aplican
- Verificar rutas de archivos CSS
- Comprobar que `components.css` y `modals.css` estén incluidos
- Limpiar caché del navegador

#### Formularios no envían datos
- Verificar que el controlador esté configurado correctamente
- Comprobar permisos de escritura en directorios
- Revisar logs de errores del servidor

## 🤝 Contribución

### Guías de Contribución
1. **Fork del repositorio**
2. **Crear rama feature**: `git checkout -b feature/nueva-funcionalidad`
3. **Realizar cambios**: Seguir estándares de código
4. **Commit**: `git commit -m 'Agregar nueva funcionalidad'`
5. **Push**: `git push origin feature/nueva-funcionalidad`
6. **Pull Request**: Crear PR con descripción detallada

### Estándares de Código
- **PHP**: PSR-12 coding standards
- **JavaScript**: ES6+ con semicolons
- **CSS**: BEM methodology para clases
- **HTML**: Semántico y accesible

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver archivo `LICENSE` para más detalles.

## 📞 Soporte

### Canales de Soporte
- **Issues**: Reportar bugs y solicitar features
- **Documentación**: Este README y comentarios en código
- **Comunidad**: Foros y grupos de usuarios

### Contacto
- **Email**: soporte@hr365.com
- **Sitio web**: https://hr365.com
- **Documentación**: https://docs.hr365.com

---

**HR365** - Transformando la gestión de recursos humanos 🚀

