# Sistema de Inventario de Remates

Aplicación web para publicar y administrar un catálogo de productos de remates: los visitantes navegan el catálogo público y un administrador gestiona los productos (crear, editar, eliminar) con imágenes alojadas en la nube. Sin frameworks ni Composer: PHP puro listo para cualquier hosting compartido.

---

## 1. Tecnologías

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.x nativo (PDO, cURL, sesiones nativas) |
| Base de datos | MySQL / MariaDB (solo guarda texto y URLs) |
| Frontend | HTML5 + Tailwind CSS (CDN) + FontAwesome (CDN) |
| JavaScript | Vanilla (Fetch API, sin librerías) |
| Imágenes | Cloudinary (Upload Preset Unsigned, vía cURL desde PHP) |
| Dependencias | **Ninguna** (cero Composer, cero npm) |

## 2. Arquitectura y patrones

Patrón **MVC ligero por capas**, sin framework:

```
Vista (public/*.php + includes/)  →  Controlador (app/Controllers)  →  Modelo (app/Models)  →  PDO/MySQL
                 │
                 └── (CRUD en panel) ──► Fetch API ──► public/api/*.php ──► JSON
```

- **Modelo** (`app/Models/Producto.php`): clases con métodos estáticos que solo hacen consultas preparadas. No contiene lógica de negocio ni HTML.
- **Controlador** (`app/Controllers/ProductoController.php`): valida entradas (`filter_var`, `trim`), decide el código HTTP y arma la respuesta. **Devuelve** `['status' => ..., 'body' => {success, message}]`; nunca imprime.
- **Endpoints API** (`public/api/*.php`): delgados. Cabeceras JSON, validan método HTTP, protegen sesión con `requiereAutenticacionJson()`, delegan al controlador y hacen `echo json_encode(...)`.
- **Vistas** (`public/*.php` + `includes/`): páginas PHP que se componen con `require` (`header.php` → `navbar.php` → contenido → `footer.php`). No acceden a la BD directamente excepto el catálogo, que pagina con `LIMIT/OFFSET`.
- **Autoload** (`app/init.php`): registrador `spl_autoload_register` con prefijo `App\` + carga de `core/auth.php`. Todo lo que usa BD/auth empieza por ahí.
- **Núcleo** (`core/`): `database.php` (singleton PDO), `config.php` (credenciales), `auth.php` (sesiones), `cloudinary.php` (subida de imágenes).

### Flujo de una operación CRUD desde el panel

1. `admin.js` envía `fetch()` con `FormData` a `public/api/<accion>.php`.
2. El endpoint valida método + sesión (`requiereAutenticacionJson()` → 401 si no).
3. `ProductoController` valida, sube la foto a Cloudinary si corresponde y llama al modelo.
4. El modelo ejecuta la consulta preparada en MySQL.
5. Se responde JSON `{success, message}` → `admin.js` actualiza la tabla y `alertas.js` muestra un toast.

## 3. Estructura del proyecto

```
sistema_inventario/
├── app/
│   ├── init.php                  # Autoload App\ + auth
│   ├── Controllers/
│   │   └── ProductoController.php
│   └── Models/
│       └── Producto.php
├── core/
│   ├── config.php                # Credenciales reales (NO se sube a git)
│   ├── config.example.php        # Plantilla a copiar
│   ├── database.php              # getPDO() singleton
│   ├── auth.php                  # Sesiones, login, timeouts
│   └── cloudinary.php            # subirImagenCloudinary()
├── includes/
│   ├── header.php                # <head>, CDNs, <body>
│   ├── navbar.php                # Menú responsive (hamburguesa en móvil)
│   └── footer.php
├── public/                       # ← raíz web (document root)
│   ├── index.php                 # Catálogo público con paginación
│   ├── detalle.php               # Ficha de un producto
│   ├── login.php / logout.php
│   ├── admin.php                 # Panel CRUD (protegido)
│   ├── api/
│   │   ├── leer_producto.php     # GET
│   │   ├── crear.php             # POST
│   │   ├── actualizar.php        # POST
│   │   └── eliminar.php          # POST
│   └── assets/js/
│       ├── admin.js              # CRUD con Fetch
│       └── alertas.js            # Toasts y modales
├── schema.sql                    # BD + usuario admin por defecto
├── proyecto.md                   # PRD (documento maestro de requerimientos)
└── .gitignore                    # Ignora core/config.php
```

> Las vistas usan rutas relativas (`__DIR__ . '/../core/...'`), por eso `core/`, `app/` e `includes/` deben ser **hermanos** de la carpeta web, nunca metidos dentro de ella.

## 4. Requisitos

- PHP ≥ 8.0 con extensiones `pdo_mysql` y `curl`.
- MySQL / MariaDB.
- Una cuenta de Cloudinary (gratuita) para las fotos.

## 5. Instalación local

1. **Clonar** el repositorio y servir la carpeta `public/` (por ejemplo con `php -S localhost:8000 -t public`).
2. **Crear la BD**: importar `schema.sql` en phpMyAdmin o `mysql -u root -p < schema.sql`.
   - Crea la base `inventario_remates`, las tablas `productos` y `usuarios`,
   - e inserta el admin por defecto: **`admin` / `password123`** (`INSERT IGNORE`: solo se crea si aún no existe).
3. **Configurar**: copiar `core/config.example.php` → `core/config.php` y poner los datos reales:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'inventario_remates');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
4. **Cloudinary**: en `core/cloudinary.php` reemplazar `tu_cloud_name` y `tu_upload_preset` por los tuyos (el preset debe estar en modo *Unsigned*).
5. Entrar en `http://localhost:8000/login.php` con `admin` / `password123` y **cambiar la contraseña** (importar de nuevo el `INSERT` del `schema.sql` con otro hash, o editar el registro con phpMyAdmin usando un hash nuevo de `password_hash()`).

## 6. Despliegue en hosting compartido

1. Subir todo el proyecto (File Manager / FTP).
2. **Document root**: si tu panel lo permite, apuntar el dominio a `/public`. Si no, súbelo todo a `public_html` dejando `core/`, `app/` e `includes/` como carpetas hermanas de los archivos PHP.
3. Crear la base de datos desde el panel (cPanel → MySQL Databases) e importar `schema.sql` desde phpMyAdmin.
   - Si el importador rechaza `CREATE DATABASE`/`USE` por privilegios, ejecuta solo las sentencias `CREATE TABLE` + `INSERT` dentro de la BD ya creada.
4. Subir `core/config.php` (no está en git) con las credenciales del hosting (`localhost` + usuario/clave de la BD del panel).
5. Completar `core/cloudinary.php`.
6. Probar `login.php` → `admin.php`.

## 7. Endpoints de la API

Todos responden JSON `{ "success": bool, "message": string }` y exigen sesión de administrador (401 en caso contrario).

| Archivo | Método | Uso |
|---|---|---|
| `api/leer_producto.php?id=` | GET | Devuelve 1 producto para editar |
| `api/crear.php` | POST | Sube foto a Cloudinary + inserta en MySQL |
| `api/actualizar.php` | POST | Actualiza; si no se adjunta foto, conserva la anterior |
| `api/eliminar.php` | POST | Elimina el registro |

## 8. Almacenamiento de imágenes: Cloudinary

Las fotos **no se guardan nunca en el servidor local** (clave en hosting compartido, donde el espacio es limitado). Se suben a [Cloudinary](https://cloudinary.com), un CDN de imágenes, y en MySQL solo se persiste la URL pública en `productos.imagen_url`.

**Flujo de subida:**

```
admin.js (FormData con la imagen)
   → public/api/crear.php | actualizar.php
   → ProductoController::crear() / actualizar()
   → subirImagenCloudinary() en core/cloudinary.php   (valida MIME: JPG, PNG o WEBP)
   → cURL POST https://api.cloudinary.com/v1_1/<cloud_name>/image/upload
        body: file + upload_preset (unsigned)
   ← Cloudinary responde { secure_url: "https://res.cloudinary.com/..." }
   → INSERT/UPDATE en MySQL guardando solo esa URL
```

**Configuración (una sola vez):**

1. Crear cuenta gratuita en Cloudinary y copiar el **Cloud Name** desde el dashboard.
2. *Settings → Upload → Upload presets* → *Add preset*: modo **Unsigned** (sin firma), con nombre cualquiera.
3. Completar en `core/cloudinary.php`:
   ```php
   define('CLOUDINARY_CLOUD_NAME', 'tu_cloud_name');
   define('CLOUDINARY_UPLOAD_PRESET', 'tu_upload_preset');
   ```

**Detalles a tener en cuenta:**

- La subida la hace **PHP desde el servidor** (cURL), no el navegador: no se expone ninguna API key en el cliente.
- El preset *unsigned* es el que permite subir sin credenciales; por eso debe restringirse solo a imágenes (el código ya valida `mime_content_type` antes de enviar).
- Las imágenes subidas quedan **públicas** (cualquiera con la URL puede verlas): no usar Cloudinary para datos privados.
- Si falla la subida, el endpoint responde `success: false` con el mensaje de Cloudinary y no se crea el producto.

## 9. Seguridad implementada

- Consultas 100% preparadas (PDO) contra inyección SQL.
- Contraseñas con `password_hash()` / `password_verify()` (bcrypt).
- Cookies de sesión endurecidas: `httponly`, `samesite=Lax`, `secure` si hay HTTPS.
- `session_regenerate_id(true)` al iniciar sesión e **idle timeout** de 30 minutos.
- Protección de vistas (`requiereAutenticacion()`) y de endpoints (`requiereAutenticacionJson()`).
- Escapado de salida con `htmlspecialchars()`.
- `core/config.php` fuera del repositorio (`.gitignore`).
- El formulario de login responde en tiempo similar aunque el usuario no exista.

## 10. Reglas de negocio clave

1. Las imágenes nunca se guardan en el servidor: van a Cloudinary y MySQL solo recibe la URL.
2. Al editar, si no se adjunta una foto nueva, se conserva la `imagen_url` anterior.
3. Todo CRUD responde con feedback visual (toast de éxito/error).
4. El catálogo público pagina de 9 en 9 productos.
