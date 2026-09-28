<?php
// Detalle público de un producto

use App\Models\Producto;

require_once __DIR__ . '/../app/init.php';

$id     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$pagina = max(1, filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?: 1);

$producto = $id ? Producto::obtener($id) : null;

$pageTitle = !empty($producto['titulo'])
    ? $producto['titulo'] . ' - Inventario de Remates'
    : 'Producto - Inventario de Remates';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="flex-grow max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="index.php?pagina=<?= $pagina ?>"
       class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-semibold mb-6 transition">
        <i class="fa-solid fa-arrow-left"></i> Volver al catálogo
    </a>

    <?php if (empty($producto)): ?>
        <div class="bg-white rounded-xl shadow p-10 text-center">
            <i class="fa-solid fa-circle-exclamation text-4xl text-red-400 mb-4 block"></i>
            <h1 class="text-xl font-bold text-slate-800 mb-2">Producto no encontrado</h1>
            <p class="text-gray-500 mb-6">El producto que buscas no existe o fue eliminado.</p>
            <a href="index.php?pagina=<?= $pagina ?>"
               class="inline-flex items-center gap-2 bg-slate-800 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-slate-700 transition">
                <i class="fa-solid fa-store"></i> Volver al catálogo
            </a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-6 md:p-8">
                <div class="bg-gray-50 rounded-lg overflow-hidden">
                    <img src="<?= htmlspecialchars($producto['imagen_url']) ?>"
                         alt="<?= htmlspecialchars($producto['titulo']) ?>"
                         class="w-full aspect-square object-cover">
                </div>

                <div class="flex flex-col">
                    <?php
                    $tags = array_filter(array_map('trim', explode(',', (string) ($producto['tags'] ?? ''))));
                    if (!empty($tags)): ?>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <?php foreach ($tags as $tag): ?>
                                <span class="bg-amber-100 text-amber-800 text-xs font-medium px-3 py-1 rounded-full">
                                    <i class="fa-solid fa-tag mr-1"></i><?= htmlspecialchars($tag) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <h1 class="text-3xl font-bold text-slate-800 leading-snug mb-4">
                        <?= htmlspecialchars($producto['titulo']) ?>
                    </h1>

                    <div class="text-3xl font-extrabold text-green-700 mb-6">
                        $<?= number_format($producto['precio'], 2) ?>
                    </div>

                    <?php if (!empty($producto['descripcion'])): ?>
                        <div class="border-t border-gray-100 pt-4">
                            <h2 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-2">
                                <i class="fa-solid fa-align-left mr-1"></i> Descripción
                            </h2>
                            <p class="text-gray-600 leading-relaxed whitespace-pre-line">
                                <?= nl2br(htmlspecialchars($producto['descripcion'])) ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
