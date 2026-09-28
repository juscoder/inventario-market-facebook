# DOCUMENTO MAESTRO DE REQUERIMIENTOS (PRD)
## Sistema de Inventario de Remates (Ligero, Portable y Modular)

> **INSTRUCCIÓN ESTRICTA PARA EL AGENTE DE IA DE CÓDIGO:**
> Eres un Desarrollador Web Senior. Tu tarea es generar el código completo de este proyecto basándote estrictamente en esta arquitectura y ejecutándolo **Fase por Fase**. No avances a la siguiente fase hasta que el usuario te lo indique expresamente.
> **Regla de oro:** Agrega solo lo estrictamente necesario para que el sistema sea funcional y seguro. Si una librería, archivo o línea de código no es esencial, NO lo agregues. Escribe código limpio (DRY) y devuelve los archivos listos para producción.

---

## 1. Stack Tecnológico Estricto
* **Backend:** PHP 8.x nativo (PDO para consultas seguras, cURL para APIs). Cero dependencias (sin Composer).
* **Base de Datos:** MySQL / MariaDB (Solo almacena texto plano y URLs).
* **Frontend UI:** HTML5, Tailwind CSS (vía CDN).
* **Iconos:** FontAwesome (vía CDN).
* **Interactividad:** JavaScript Vanilla (Fetch API estricto para CRUD asíncrono).
* **Almacenamiento de Archivos:** API de Cloudinary (Upload Preset Unsigned) mediante PHP. Ninguna imagen se guarda en el servidor local.

---

## 2. Estructura Organizada de Directorios

```text
inventario-market-facebook/        # ← raíz web (document root)
├── core/
│   ├── database.php        # Conexión PDO a MySQL 
│   ├── cloudinary.php      # Función cURL para enviar fotos a Cloudinary
│   ├── config.php          # Credenciales de la BD
│   └── auth.php            # Validaciones de sesión PHP
├── includes/               
│   ├── header.php          # <head>, Tailwind CDN, FontAwesome CDN, <body>
│   ├── footer.php          # Cierre de </body>, </html> y scripts
│   └── navbar.php          # Menú de navegación
├── app/
│   ├── init.php            # Autoload App\ + auth
│   ├── Controllers/        # Validación y respuestas HTTP
│   └── Models/             # Consultas preparadas
├── admin/
│   ├── index.php           # Panel CRUD (Tabla, Modales). Protegido.
│   └── api/                # Endpoints AJAX (Responden JSON)
│       ├── leer_producto.php   # GET: Obtiene 1 producto para edición
│       ├── crear.php           # POST: Sube foto a Cloudinary y guarda en MySQL
│       ├── actualizar.php      # POST: Actualiza MySQL y reemplaza foto (opcional)
│       └── eliminar.php        # POST: Borra registro en MySQL
├── assets/                 
│   └── js/
│       ├── alertas.js      # Notificaciones Toast y Modales de confirmación
│       └── admin.js        # Lógica CRUD Fetch API
├── index.php               # Catálogo público (Cards) con Paginación
├── detalle.php             # Ficha pública de un producto
├── login.php               # Pantalla de acceso al panel
├── logout.php              # Destrucción de sesión
└── schema.sql              # BD + usuario admin por defecto
```

---

## 3. Esquema de Base de Datos (schema.sql)

```sql
CREATE DATABASE IF NOT EXISTS inventario_remates CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventario_remates;

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    tags VARCHAR(255) DEFAULT NULL,
    imagen_url VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 4. Plan de Ejecución por Fases (Hoja de Ruta para la IA)

El desarrollo debe realizarse estrictamente en el siguiente orden. **El Agente IA debe esperar confirmación del usuario para pasar a la siguiente fase.**

### Fase 1: Base de Datos, Conexión y UI Base
* Generar el código SQL de la tabla.
* Crear `core/database.php` con manejo de excepciones PDO.
* Crear `includes/header.php`, `includes/footer.php` y `includes/navbar.php` (asegurando la importación de Tailwind y FontAwesome).

### Fase 2: Sistema de Autenticación
* Crear `core/auth.php` para validar sesiones.
* Crear `login.php` (diseño con Tailwind e iconos, validación de credenciales hardcodeadas en PHP o en DB simple).
* Crear `logout.php`.

### Fase 3: Integración de Cloudinary (Almacenamiento)
* Crear `core/cloudinary.php`.
* Implementar función cURL que reciba una imagen temporal (`$_FILES`), la envíe a Cloudinary (Preset Unsigned) y retorne la URL pública `https://...` limpia.

### Fase 4: Endpoints API (Backend CRUD)
* Desarrollar los 4 archivos dentro de la carpeta `admin/api/` (`crear.php`, `leer_producto.php`, `actualizar.php`, `eliminar.php`).
* **Regla estricta:** Todos deben devolver un objeto JSON con estructura `{ success: true/false, message: '...' }` y cabeceras de `application/json`.

### Fase 5: Panel de Administración y Javascript (Frontend CRUD)
* Crear `admin/index.php` (Tabla de datos y Modales HTML ocultos para crear/editar y confirmar eliminación).
* Crear `assets/js/alertas.js` (Lógica para renderizar *Toasts* verdes/rojos y manipular la visibilidad de los modales mediante clases de Tailwind).
* Crear `assets/js/admin.js` (Peticiones `fetch()` hacia la carpeta `admin/api/`, envío de `FormData` e inyección de datos dinámicos sin recargar la página).

### Fase 6: Catálogo Público y Paginación
* Crear `index.php`.
* Implementar cláusulas `LIMIT` y `OFFSET` en PHP para la paginación.
* Diseñar las *Cards* del producto (Imagen cover, Título, Precio formateado, Badge de Tag y Descripción truncada).
* Añadir botones de paginación estilizados ("Anterior" y "Siguiente").

---

## 5. Reglas de Negocio y Lógica Clave
1. **Imágenes vs Texto:** La foto obligatoriamente se procesa hacia Cloudinary. MySQL SOLO recibe la URL generada.
2. **Edición Inteligente:** Al actualizar un producto, si el usuario NO adjunta una foto nueva, el sistema debe conservar la `imagen_url` anterior en la base de datos sin lanzar errores ni sobreescribir con valores nulos.
3. **Seguridad Base:** El archivo `admin/index.php` debe invocar `auth.php` (vía `app/init.php`) en la línea 1. Si no hay sesión válida, se bloquea la carga de la vista y redirige a `/login.php`.
4. **Feedback Visual:** Ninguna acción de CRUD debe dejar al usuario en incertidumbre. Toda creación, edición o eliminación debe disparar un *Toast* notificando el resultado (éxito o fallo) manipulado por `alertas.js`.