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
Vista (index.php, detalle.php, login.php, admin/ + includes/)  →  Controlador (app/Controllers)  →  Modelo (app/Models)  →  PDO/MySQL
                 │
                 └── (CRUD en panel) ──► Fetch API ──► admin/api/*.php ──► JSON
```

- **Modelo** (`app/Models/Producto.php`): clases con métodos estáticos que solo hacen consultas preparadas. No contiene lógica de negocio ni HTML.
- **Controlador** (`app/Controllers/ProductoController.php`): valida entradas (`filter_var`, `trim`), decide el código HTTP y arma la respuesta. **Devuelve** `['status' => ..., 'body' => {success, message}]`; nunca imprime.
- **Endpoints API** (`admin/api/*.php`): delgados. Cabeceras JSON, validan método HTTP, protegen sesión con `requiereAutenticacionJson()`, delegan al controlador y hacen `echo json_encode(...)`.
- **Vistas** (archivos `.php` de la raíz + `admin/index.php` + `includes/`): páginas PHP que se componen con `require` (`header.php` → `navbar.php` → contenido → `footer.php`). No acceden a la BD directamente excepto el catálogo, que pagina con `LIMIT/OFFSET`.
- **Autoload** (`app/init.php`): registrador `spl_autoload_register` con prefijo `App\` + carga de `core/auth.php`. Todo lo que usa BD/auth empieza por ahí.
- **Núcleo** (`core/`): `config.php` (credenciales de BD y Cloudinary, con variables de entorno y respaldo local), `database.php` (singleton PDO), `auth.php` (sesiones + `rutaBase()`), `cloudinary.php` (subida de imágenes).

### Flujo de una operación CRUD desde el panel

1. `admin.js` envía `fetch()` con `FormData` a `admin/api/<accion>.php`.
2. El endpoint valida método + sesión (`requiereAutenticacionJson()` → 401 si no).
3. `ProductoController` valida, sube la foto a Cloudinary si corresponde y llama al modelo.
4. El modelo ejecuta la consulta preparada en MySQL.
5. Se responde JSON `{success, message}` → `admin.js` actualiza la tabla y `alertas.js` muestra un toast.

## 3. Estructura del proyecto

```
inventario-market-facebook/         # ← raíz web (document root)
├── index.php                       # Catálogo público con paginación
├── detalle.php                     # Ficha de un producto
├── login.php / logout.php          # Acceso y cierre de sesión
├── admin/
│   ├── index.php                   # Panel CRUD (protegido)
│   └── api/
│       ├── leer_producto.php       # GET
│       ├── crear.php               # POST
│       ├── actualizar.php          # POST
│       └── eliminar.php            # POST
├── assets/
│   └── js/
│       ├── admin.js                # CRUD con Fetch
│       └── alertas.js              # Toasts y modales
├── app/
│   ├── init.php                    # Autoload App\ + auth
│   ├── Controllers/
│   │   └── ProductoController.php
│   └── Models/
│       └── Producto.php
├── core/
│   ├── config.php                  # Credenciales (repo privado)
│   ├── database.php                # getPDO() singleton
│   ├── auth.php                    # Sesiones, login, timeouts
│   └── cloudinary.php              # subirImagenCloudinary()
├── includes/
│   ├── header.php                  # <head>, CDNs, <body>
│   ├── navbar.php                  # Menú responsive (hamburguesa en móvil)
│   └── footer.php
├── schema.sql                      # BD + usuario admin por defecto
├── proyecto.md                     # PRD (documento maestro de requerimientos)
└── .gitignore
```

> **Reglas de rutas:**
>
> - Los `require`/`include` usan rutas relativas a `__DIR__`
>   (`__DIR__ . '/core/database.php'` en la raíz, `__DIR__ . '/../core/...'` dentro de `admin/`).
> - Los enlaces HTML y los `header('Location: ...')` usan `rutaBase()` (definida en
>   `core/auth.php`), que devuelve el prefijo de la subcarpeta desde la que se sirve el
>   proyecto: `''` si está en la raíz del dominio, `/inventario-market-facebook` si vive en
>   una subcarpeta. Por eso el mismo código funciona en la raíz (hosting tradicional, Wasmer)
>   y dentro de una subcarpeta (`http://localhost/jus-proyectos/inventario-market-facebook/`).
>
> **Único acoplamiento:** `rutaBase()` asume que `admin/` es la única subcarpeta pública
> (la raíz del proyecto se calcula como un nivel por encima). Si renombras esa carpeta,
> hay que actualizar el `basename($dir) === 'admin'` de `core/auth.php`.

## 4. Requisitos

- PHP ≥ 8.0 con extensiones `pdo_mysql` y `curl`.
- MySQL / MariaDB.
- Una cuenta de Cloudinary (gratuita) para las fotos.

## 5. Instalación local

1. **Clonar** el repositorio y servirlo desde la raíz del proyecto (por ejemplo con `php -S localhost:8000`).
2. **Crear la BD**: importar `schema.sql` en phpMyAdmin o `mysql -u root -p < schema.sql`.
   - Crea la base `inventario_remates`, las tablas `productos` y `usuarios`,
   - e inserta el admin por defecto: **`admin` / `password123`** (`INSERT IGNORE`: solo se crea si aún no existe).
3. **Configurar**: no hace falta nada por defecto. `core/config.php` trae el respaldo para
   XAMPP (`localhost` / `inventario_remates` / `root` / sin contraseña). Si el entorno exporta
   variables de entorno, **manda la variable**; si no existe, se usa el respaldo:
   ```php
   define('DB_HOST',     entorno('DB_HOST', 'localhost'));
   define('DB_PORT',     entorno('DB_PORT', ''));
   define('DB_NAME',     entorno('DB_NAME', 'inventario_remates'));
   define('DB_USERNAME', entorno('DB_USERNAME', 'root'));
   define('DB_PASSWORD', entorno('DB_PASSWORD', ''));
   define('DB_CHARSET',  'utf8mb4');
   ```
   El puerto solo se añade al DSN si `DB_PORT` tiene valor (en local queda en 3306).
4. **Cloudinary**: declarar las dos variables de entorno en Windows (el preset debe estar en
   modo *Unsigned*). Si no existen, `core/config.php` usa el respaldo `tu_cloud_name` y la
   subida de fotos fallará:
   ```powershell
   setx CLOUDINARY_CLOUD_NAME    "tu_cloud_name_real"
   setx CLOUDINARY_UPLOAD_PRESET "tu_upload_preset_real"
   ```
   `setx` solo afecta a los procesos nuevos: abre de nuevo la terminal o el editor.
5. Entrar en `http://localhost:8000/login.php` con `admin` / `password123` y **cambiar la contraseña** (importar de nuevo el `INSERT` del `schema.sql` con otro hash, o editar el registro con phpMyAdmin usando un hash nuevo de `password_hash()`).

## 6. Despliegue en hosting compartido

1. Subir todo el proyecto a la raíz del dominio (`public_html`), de modo que `index.php` quede en `/index.php`.
2. Crear la base de datos desde el panel (cPanel → MySQL Databases) e importar `schema.sql` desde phpMyAdmin.
   - Si el importador rechaza `CREATE DATABASE`/`USE` por privilegios, ejecuta solo las sentencias `CREATE TABLE` + `INSERT` dentro de la BD ya creada.
3. Conectar la BD: si el panel exporta `DB_HOST` / `DB_NAME` / `DB_USERNAME` / `DB_PASSWORD`,
   `core/config.php` las lee solas; si no, editar `core/config.php` con las credenciales
   del hosting (ajustar también `DB_PORT` si la BD no está en 3306).
4. Cloudinary: si el panel exporta `CLOUDINARY_CLOUD_NAME` / `CLOUDINARY_UPLOAD_PRESET`,
   `core/config.php` las lee solas; si no, poner los valores reales en el respaldo de
   `core/config.php`.
5. Probar `/login.php` → `/admin/`.

## 7. Despliegue en Wasmer (GitHub)

La raíz del repositorio **es** la raíz web, así que Wasmer detecta PHP y sirve `index.php` sin ningún archivo de configuración (`app.yaml` es opcional).

1. Subir los cambios a la rama `main` del repositorio.
2. En [Wasmer](https://wasmer.io) → *Apps* → conectar el repositorio de GitHub y elegir la rama `main`.
3. Wasmer detecta los archivos `.php`, empaqueta el proyecto y publica en `https://<nombre-app>.wasmer.app`.
4. **Base de datos**: Wasmer aprovisiona una BD y la inyecta como variables de entorno
   (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USERNAME`, `DB_PASSWORD`), que `core/config.php`
   lee automáticamente — **no hay que editar ningún archivo**. Solo hay que importar
   `schema.sql` en esa base:
   ```bash
   wasmer app database list --with-password   # opcional, para verificar/entrar a phpMyAdmin
   ```
   > Si al desplegar no aparecen esas variables, Wasmer no ha detectado la BD: crearla desde
   > el dashboard o añadir `app.yaml` con `capabilities.database` y volver a desplegar.
5. **Cloudinary**: Wasmer **no** inyecta estas variables (solo las de la BD), hay que
   declararlas una sola vez, **sin tocar ningún archivo ni crear `app.yaml`**:
   - *Dashboard*: la app → *Settings* → pestaña *Environment Vars* → *Add variable* →
     `CLOUDINARY_CLOUD_NAME` y `CLOUDINARY_UPLOAD_PRESET` → *Save and Redeploy*.
   - *CLI (alternativa)*:
     ```bash
     wasmer app secrets create CLOUDINARY_CLOUD_NAME    "tu_cloud_name_real"
     wasmer app secrets create CLOUDINARY_UPLOAD_PRESET "tu_upload_preset_real"
     ```
6. Entrar en `https://<nombre-app>.wasmer.app/login.php`.

> Cada vez que se haga `git push` a `main`, Wasmer vuelve a desplegar automáticamente.

> **Opcional:** si más adelante añades un `app.yaml`, Wasmer extiende la configuración con sus valores (por ejemplo `scaling.mode: single_concurrency`, recomendado para PHP, o `capabilities.database` para aprovisionar la BD automáticamente).

## 8. Endpoints de la API

Todos responden JSON `{ "success": bool, "message": string }` y exigen sesión de administrador (401 en caso contrario).

| Archivo | Método | Uso |
|---|---|---|
| `admin/api/leer_producto.php?id=` | GET | Devuelve 1 producto para editar |
| `admin/api/crear.php` | POST | Sube foto a Cloudinary + inserta en MySQL |
| `admin/api/actualizar.php` | POST | Actualiza; si no se adjunta foto, conserva la anterior |
| `admin/api/eliminar.php` | POST | Elimina el registro |

## 9. Almacenamiento de imágenes: Cloudinary

Las fotos **no se guardan nunca en el servidor local** (clave en hosting compartido, donde el espacio es limitado). Se suben a [Cloudinary](https://cloudinary.com), un CDN de imágenes, y en MySQL solo se persiste la URL pública en `productos.imagen_url`.

**Flujo de subida:**

```
admin.js (FormData con la imagen)
   → admin/api/crear.php | actualizar.php
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
3. Declarar los valores en las variables de entorno (ver §5.4 para local, §7.5 para Wasmer).
   `core/config.php` los lee y expone las constantes:
   ```php
   define('CLOUDINARY_CLOUD_NAME',    entorno('CLOUDINARY_CLOUD_NAME', 'tu_cloud_name'));
   define('CLOUDINARY_UPLOAD_PRESET', entorno('CLOUDINARY_UPLOAD_PRESET', 'tu_upload_preset'));
   ```
   Si la variable no está definida, se usa el respaldo de la derecha (`tu_cloud_name`).

**Detalles a tener en cuenta:**

- Los valores reales **no van en git**: `core/config.php` solo guarda los respaldos; las
  credenciales viven en las variables de entorno de cada entorno.
- La subida la hace **PHP desde el servidor** (cURL), no el navegador: no se expone ninguna API key en el cliente.
- El preset *unsigned* es el que permite subir sin credenciales; por eso debe restringirse solo a imágenes (el código ya valida `mime_content_type` antes de enviar).
- Las imágenes subidas quedan **públicas** (cualquiera con la URL puede verlas): no usar Cloudinary para datos privados.
- Si falla la subida, el endpoint responde `success: false` con el mensaje de Cloudinary y no se crea el producto.

## 10. Seguridad implementada

- Consultas 100% preparadas (PDO) contra inyección SQL.
- Contraseñas con `password_hash()` / `password_verify()` (bcrypt).
- Cookies de sesión endurecidas: `httponly`, `samesite=Lax`, `secure` si hay HTTPS.
- `session_regenerate_id(true)` al iniciar sesión e **idle timeout** de 30 minutos.
- Protección de vistas (`requiereAutenticacion()`) y de endpoints (`requiereAutenticacionJson()`).
- Escapado de salida con `htmlspecialchars()`.
- `core/config.php` fuera del repositorio (`.gitignore`).
- El formulario de login responde en tiempo similar aunque el usuario no exista.

## 11. Reglas de negocio clave

1. Las imágenes nunca se guardan en el servidor: van a Cloudinary y MySQL solo recibe la URL.
2. Al editar, si no se adjunta una foto nueva, se conserva la `imagen_url` anterior.
3. Todo CRUD responde con feedback visual (toast de éxito/error).
4. El catálogo público pagina de 9 en 9 productos.
