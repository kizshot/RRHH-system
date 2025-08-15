# RRHH-system
Sistema de Gestión de Recursos Humanos
1) Tecnologías y entorno
•  Backend: PHP 8.x (mysqli)
•  DB: MySQL 8.x
•  Servidor: Apache (puerto 8088), MySQL (puerto 3306)
•  Frontend: HTML5, CSS3, JavaScript vanilla
•  Iconos: Font Awesome (CDN)

2) Estructura del proyecto
D:\UniServerZ\www\Recursos\
•  index.php  Front Controller (enruta todo el sistema)
•  includes/
•  db_config.php  Conexión mysqli, charset utf8mb4, bootstrap de sesión
•  app/
•  controllers/
◦  AuthController.php  Login, registro, logout
◦  UserController.php  Listado, creación, edición meta, eliminación, ver; edición de credenciales
•  models/
◦  User.php  Acceso a datos de usuarios (CRUD, meta, búsquedas, credenciales, avatar)
•  views/
◦  layout/
◦  header.php  Topbar (hamburguesa, modo día/noche, campana, menú usuario)
◦  sidebar.php  Menú lateral con módulos (Dashboard, Usuarios, ...)
◦  auth/
◦  login.php
◦  register.php
◦  dashboard/
◦  index.php  Página principal del usuario autenticado
◦  users/
◦  index.php  Listado con búsqueda, acciones (Ver, Editar, Eliminar) y modal para Agregar Usuario
◦  view.php  Vista de detalle
◦  edit.php  Edición de meta (nombre, apellidos, rol, estado, código, avatar)
◦  credentials.php  Edición de usuario/email/contraseña
•  assets/
•  css/style.css  Estilos base, responsive, sidebar colapsable, modales, modo oscuro
•  js/main.js  Sidebar, dropdown usuario, modo oscuro, modales (Agregar y dinámico), interacciones varias
•  img/avatars/  Carpeta de avatares de usuarios (creada)
•  database.sql  Esquema de BD actualizado (tabla users con columnas meta)

3) Base de datos
Esquema (database.sql):
•  Base: hr365_db
•  Tabla users:
•  id INT(11) PK AI
•  username VARCHAR(50) UNIQUE NOT NULL
•  email VARCHAR(100) UNIQUE NOT NULL
•  password VARCHAR(255) NOT NULL (hash password_hash)
•  first_name VARCHAR(100) NULL
•  last_name VARCHAR(100) NULL
•  role VARCHAR(32) NULL
•  status VARCHAR(16) NULL DEFAULT 'ACTIVO'
•  code VARCHAR(32) NULL
•  avatar VARCHAR(255) NULL (ruta pública /Recursos/assets/img/avatars/...)
•  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

Importación:
mysql -u root -p < D:\UniServerZ\www\Recursos\database.sql
(Contraseña MySQL: 1234)

4) Configuración de conexión
includes/db_config.php
•  Host: 127.0.0.1
•  Puerto: 3306
•  Usuario: root
•  Password: 1234
•  DB: hr365_db
•  Incluye: set_charset('utf8mb4') y session_start() si no existía
