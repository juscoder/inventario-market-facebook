<?php
require_once __DIR__ . '/core/auth.php';

// Si ya hay sesión activa, ir al panel
if (!empty($_SESSION['admin'])) {
    if (!sesionExpirada()) {
        header('Location: ' . rutaBase() . '/admin/');
        exit;
    }
    destruirSesion();
    iniciarSesion();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $clave   = trim($_POST['clave'] ?? '');

    if (credencialesValidas($usuario, $clave)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        $_SESSION['usuario'] = $usuario;
        $_SESSION['ultimo_acceso'] = time();
        header('Location: ' . rutaBase() . '/admin/');
        exit;
    }
    $error = 'Usuario o contraseña incorrectos.';
}

$pageTitle = 'Iniciar Sesión - Inventario de Remates';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="flex-grow flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-800 text-amber-400 text-2xl mb-4">
                <i class="fa-solid fa-user-lock"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Acceso al Panel</h1>
            <p class="text-gray-500 text-sm mt-1">Ingresa tus credenciales de administrador</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 flex items-center gap-2 bg-red-100 border border-red-300 text-red-700 text-sm rounded-lg px-4 py-3">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-5">
            <div>
                <label for="usuario" class="block text-sm font-medium text-slate-700 mb-1">Usuario</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" id="usuario" name="usuario" required autocomplete="username"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none"
                           placeholder="admin">
                </div>
            </div>
            <div>
                <label for="clave" class="block text-sm font-medium text-slate-700 mb-1">Contraseña</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" id="clave" name="clave" required autocomplete="current-password"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none"
                           placeholder="••••••••">
                </div>
            </div>
            <button type="submit"
                    class="w-full bg-slate-800 text-white font-semibold py-2.5 rounded-lg hover:bg-slate-700 transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-right-to-bracket"></i> Ingresar
            </button>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
