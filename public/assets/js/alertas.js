// alertas.js - Notificaciones Toast y manipulación de modales

/**
 * Muestra un Toast (notificación) verde (éxito) o rojo (error).
 * @param {string} mensaje
 * @param {'success'|'error'} tipo
 */
function mostrarToast(mensaje, tipo = 'success') {
    let contenedor = document.getElementById('contenedorToasts');
    if (!contenedor) {
        contenedor = document.createElement('div');
        contenedor.id = 'contenedorToasts';
        contenedor.className = 'fixed top-4 right-4 z-[60] space-y-2';
        document.body.appendChild(contenedor);
    }

    const esExito = tipo === 'success';
    const toast = document.createElement('div');
    toast.className = [
        'flex items-center gap-2 px-4 py-3 rounded-lg shadow-lg text-white text-sm',
        esExito ? 'bg-green-600' : 'bg-red-600',
        'transition-opacity duration-300'
    ].join(' ');
    toast.innerHTML = `<i class="fa-solid ${esExito ? 'fa-circle-check' : 'fa-circle-exclamation'}"></i><span>${mensaje}</span>`;

    contenedor.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/** Abre un modal quitando la clase 'hidden'. */
function abrirModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

/** Cierra un modal añadiendo la clase 'hidden'. */
function cerrarModal(id) {
    document.getElementById(id).classList.add('hidden');
}
