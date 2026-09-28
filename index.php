<?php
// Catálogo público (Cards) con paginación
require_once __DIR__ . '/core/database.php';

$porPagina = 9;
$pagina    = max(1, filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?: 1);
$offset    = ($pagina - 1) * $porPagina;

$productos    = [];
$total        = 0;
$totalPaginas = 1;

try {
    $pdo   = getPDO();
    $total = (int) $pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn();
    $totalPaginas = max(1, (int) ceil($total / $porPagina));

    if ($pagina > $totalPaginas) {
        $pagina = $totalPaginas;
        $offset = ($pagina - 1) * $porPagina;
    }

    $stmt = $pdo->prepare('SELECT * FROM productos ORDER BY creado_en DESC LIMIT ? OFFSET ?');
    $stmt->bindValue(1, $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $productos = $stmt->fetchAll();
} catch (PDOException $e) {
    $errorBD = true;
}

$pageTitle = 'Catálogo - Inventario de Remates';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-slate-800 mb-6">
        <i class="fa-solid fa-store text-amber-500 mr-2"></i>Catálogo de Remates
    </h1>

    <?php if (!empty($errorBD)): ?>
        <div class="bg-red-100 border border-red-300 text-red-700 rounded-lg px-4 py-3">
            <i class="fa-solid fa-circle-exclamation mr-1"></i> Error al cargar el catálogo.
        </div>
    <?php elseif (empty($productos)): ?>
        <div class="text-center text-gray-400 py-16">
            <i class="fa-solid fa-inbox text-5xl mb-4 block"></i>
            <p>No hay productos disponibles por el momento.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($productos as $p): ?>
                <div class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden flex flex-col">
                    <img src="<?= htmlspecialchars($p['imagen_url']) ?>" alt="<?= htmlspecialchars($p['titulo']) ?>"
                         class="w-full aspect-[4/3] object-cover" loading="lazy">
                    <div class="p-3 flex flex-col flex-grow">
                        <h2 class="font-bold text-slate-800 leading-snug mb-3"><?= htmlspecialchars($p['titulo']) ?></h2>
                        <a href="detalle.php?id=<?= (int) $p['id'] ?>&pagina=<?= $pagina ?>"
                           class="mt-auto inline-flex items-center justify-center gap-2 bg-slate-800 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-slate-700 transition">
                            <i class="fa-solid fa-eye"></i> Ver detalles
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPaginas > 1): ?>
            <nav class="flex items-center justify-center gap-4 mt-10">
                <?php if ($pagina > 1): ?>
                    <a href="?pagina=<?= $pagina - 1 ?>"
                       class="px-4 py-2 rounded-lg bg-slate-800 text-white hover:bg-slate-700 transition flex items-center gap-2">
                        <i class="fa-solid fa-chevron-left"></i> Anterior
                    </a>
                <?php endif; ?>
                <span class="text-sm text-gray-500">
                    Página <strong><?= $pagina ?></strong> de <strong><?= $totalPaginas ?></strong>
                </span>
                <?php if ($pagina < $totalPaginas): ?>
                    <a href="?pagina=<?= $pagina + 1 ?>"
                       class="px-4 py-2 rounded-lg bg-slate-800 text-white hover:bg-slate-700 transition flex items-center gap-2">
                        Siguiente <i class="fa-solid fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
