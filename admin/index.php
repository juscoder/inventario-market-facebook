<?php
// Panel CRUD - Protegido

use App\Models\Producto;

require_once __DIR__ . '/../app/init.php';

requiereAutenticacion();

$productos = [];
try {
    $productos = Producto::obtenerTodos();
} catch (PDOException $e) {
    $errorBD = true;
}

$pageTitle = 'Panel de Administración - Inventario de Remates';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-800">
            <i class="fa-solid fa-boxes-stacked text-amber-500 mr-2"></i>Productos
        </h1>
        <button id="btnNuevo"
                class="bg-amber-500 text-slate-900 font-semibold px-4 py-2 rounded-lg hover:bg-amber-400 transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Nuevo producto
        </button>
    </div>

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-800 text-white">
                <tr>
                    <th class="px-4 py-3 text-left">Imagen</th>
                    <th class="px-4 py-3 text-left">Título</th>
                    <th class="px-4 py-3 text-left">Precio</th>
                    <th class="px-4 py-3 text-left">Tags</th>
                    <th class="px-4 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaProductos" class="divide-y divide-gray-200">
                <?php if (empty($productos)): ?>
                    <tr id="filaVacia">
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                            <i class="fa-solid fa-inbox text-3xl mb-2 block"></i>No hay productos registrados.
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($productos as $p): ?>
                    <tr data-id="<?= $p['id'] ?>" class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <img src="<?= htmlspecialchars($p['imagen_url']) ?>" alt="" class="w-14 h-14 object-cover rounded-lg border">
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-800 celda-titulo"><?= htmlspecialchars($p['titulo']) ?></td>
                        <td class="px-4 py-3 text-green-700 font-semibold celda-precio">$<?= number_format($p['precio'], 2) ?></td>
                        <td class="px-4 py-3 text-gray-500 celda-tags"><?= htmlspecialchars($p['tags'] ?? '') ?></td>
                        <td class="px-4 py-3 text-center space-x-2">
                            <button class="btn-editar text-blue-600 hover:text-blue-800" title="Editar">
                                <i class="fa-solid fa-pen-to-square text-lg"></i>
                            </button>
                            <button class="btn-eliminar text-red-600 hover:text-red-800" title="Eliminar">
                                <i class="fa-solid fa-trash-can text-lg"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Modal Crear/Editar Producto -->
<div id="modalProducto" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 id="modalTitulo" class="text-lg font-bold text-slate-800">Nuevo producto</h2>
            <button class="btn-cerrar text-gray-400 hover:text-gray-600 text-xl">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="formProducto" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" id="productoId" name="id">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Título *</label>
                <input type="text" id="fTitulo" name="titulo" required maxlength="150"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                <textarea id="fDescripcion" name="descripcion" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Precio *</label>
                    <input type="number" id="fPrecio" name="precio" required min="0" step="0.01"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tags (separados por coma)</label>
                    <input type="text" id="fTags" name="tags" maxlength="255"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Imagen <span id="imagenOpcional" class="hidden text-gray-400 font-normal">(opcional: se conserva la actual si no eliges otra)</span>
                </label>
                <input type="file" id="fImagen" name="imagen" accept="image/jpeg,image/png,image/webp"
                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-slate-800 file:text-white hover:file:bg-slate-700">
                <div id="vistaActual" class="hidden mt-2 flex items-center gap-2 text-xs text-gray-500">
                    <img id="imgActual" src="" class="w-10 h-10 object-cover rounded border"> Imagen actual
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="btn-cerrar px-4 py-2 rounded-lg bg-gray-200 text-slate-700 hover:bg-gray-300 transition">Cancelar</button>
                <button type="submit" id="btnGuardar"
                        class="px-4 py-2 rounded-lg bg-amber-500 text-slate-900 font-semibold hover:bg-amber-400 transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> <span>Guardar</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Confirmar Eliminación -->
<div id="modalEliminar" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-red-100 text-red-600 text-2xl mb-4">
            <i class="fa-solid fa-trash-can"></i>
        </div>
        <h2 class="text-lg font-bold text-slate-800 mb-1">¿Eliminar producto?</h2>
        <p class="text-gray-500 text-sm mb-6">Esta acción no se puede deshacer.</p>
        <div class="flex justify-center gap-3">
            <button id="btnCancelarBorrar" class="px-4 py-2 rounded-lg bg-gray-200 text-slate-700 hover:bg-gray-300 transition">Cancelar</button>
            <button id="btnConfirmarBorrar"
                    class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition flex items-center gap-2">
                <i class="fa-solid fa-trash-can"></i> Eliminar
            </button>
        </div>
    </div>
</div>

<script src="<?= rutaBase() ?>/assets/js/alertas.js"></script>
<script src="<?= rutaBase() ?>/assets/js/admin.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
