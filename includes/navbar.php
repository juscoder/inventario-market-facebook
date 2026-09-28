<?php require_once __DIR__ . '/../core/auth.php'; ?>

<?php
$esAdmin = !empty($_SESSION['admin']);

$enlaces = [
    ['url' => rutaBase() . '/index.php', 'icono' => 'fa-store', 'texto' => 'Catálogo', 'clases' => ''],
];
if ($esAdmin) {
    $enlaces[] = ['url' => rutaBase() . '/admin/', 'icono' => 'fa-screwdriver-wrench', 'texto' => 'Admin', 'clases' => ''];
    $enlaces[] = ['url' => rutaBase() . '/logout.php', 'icono' => 'fa-right-from-bracket', 'texto' => 'Salir', 'clases' => 'bg-red-600 hover:bg-red-700'];
} else {
    $enlaces[] = ['url' => rutaBase() . '/login.php', 'icono' => 'fa-user-lock', 'texto' => 'Ingresar', 'clases' => 'bg-amber-500 text-slate-900 font-semibold hover:bg-amber-400'];
}
?>

<nav class="bg-slate-800 text-white shadow-md relative z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="<?= rutaBase() ?>/index.php" class="flex items-center gap-2 text-lg font-bold hover:text-amber-400 transition min-w-0">
                <i class="fa-solid fa-gavel text-amber-400 shrink-0"></i>
                <span class="truncate">Inventario de Remates</span>
            </a>

            <div class="hidden md:flex items-center gap-4 text-sm">
                <?php foreach ($enlaces as $e): ?>
                    <a href="<?= $e['url'] ?>" class="px-3 py-2 rounded-md transition <?= $e['clases'] ?: 'hover:bg-slate-700' ?>">
                        <i class="fa-solid <?= $e['icono'] ?> mr-1"></i> <?= $e['texto'] ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <button id="btnMenuMovil" type="button" aria-label="Abrir menú" aria-expanded="false" aria-controls="menuMovil"
                    class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-md hover:bg-slate-700 transition">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
        </div>
    </div>

    <div id="menuMovil" class="hidden md:hidden border-t border-slate-700 px-4 pb-4">
        <div class="flex flex-col gap-2 pt-3 text-sm">
            <?php foreach ($enlaces as $e): ?>
                <a href="<?= $e['url'] ?>" class="px-3 py-2.5 rounded-md transition <?= $e['clases'] ?: 'hover:bg-slate-700' ?>">
                    <i class="fa-solid <?= $e['icono'] ?> mr-2 w-5 text-center"></i> <?= $e['texto'] ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</nav>

<script>
    (function () {
        var btn = document.getElementById('btnMenuMovil');
        var menu = document.getElementById('menuMovil');
        if (!btn || !menu) return;

        btn.addEventListener('click', function () {
            var oculto = menu.classList.toggle('hidden');
            btn.setAttribute('aria-expanded', String(!oculto));
            btn.querySelector('i').className = oculto
                ? 'fa-solid fa-bars text-xl'
                : 'fa-solid fa-xmark text-xl';
        });
    })();
</script>
