// admin.js - Lógica CRUD con Fetch API (sin recargar la página)

document.addEventListener('DOMContentLoaded', () => {
    const modalProducto   = 'modalProducto';
    const modalEliminar   = 'modalEliminar';
    const form            = document.getElementById('formProducto');
    const tabla           = document.getElementById('tablaProductos');
    let idAEliminar       = null;

    // ---------- Apertura / cierre de modales ----------

    document.getElementById('btnNuevo').addEventListener('click', () => {
        form.reset();
        document.getElementById('productoId').value = '';
        document.getElementById('modalTitulo').textContent = 'Nuevo producto';
        document.getElementById('fImagen').required = true;
        document.getElementById('imagenOpcional').classList.add('hidden');
        document.getElementById('vistaActual').classList.add('hidden');
        abrirModal(modalProducto);
    });

    document.querySelectorAll('.btn-cerrar').forEach(btn =>
        btn.addEventListener('click', () => { cerrarModal(modalProducto); cerrarModal(modalEliminar); })
    );
    document.getElementById('btnCancelarBorrar').addEventListener('click', () => {
        idAEliminar = null;
        cerrarModal(modalEliminar);
    });

    // ---------- Delegación de eventos en la tabla ----------

    tabla.addEventListener('click', async (e) => {
        const btnEditar   = e.target.closest('.btn-editar');
        const btnEliminar = e.target.closest('.btn-eliminar');

        if (btnEditar) {
            const id = btnEditar.closest('tr').dataset.id;
            try {
                const resp = await fetch(`api/leer_producto.php?id=${id}`);
                const data = await resp.json();
                if (!data.success) { mostrarToast(data.message, 'error'); return; }

                const p = data.producto;
                form.reset();
                document.getElementById('productoId').value    = p.id;
                document.getElementById('fTitulo').value       = p.titulo;
                document.getElementById('fDescripcion').value  = p.descripcion || '';
                document.getElementById('fPrecio').value       = p.precio;
                document.getElementById('fTags').value         = p.tags || '';
                document.getElementById('modalTitulo').textContent = 'Editar producto';
                document.getElementById('fImagen').required = false;
                document.getElementById('imagenOpcional').classList.remove('hidden');
                const imgActual = document.getElementById('imgActual');
                imgActual.src = p.imagen_url;
                document.getElementById('vistaActual').classList.remove('hidden');
                abrirModal(modalProducto);
            } catch (err) {
                mostrarToast('Error de conexión con el servidor.', 'error');
            }
        }

        if (btnEliminar) {
            idAEliminar = btnEliminar.closest('tr').dataset.id;
            abrirModal(modalEliminar);
        }
    });

    // ---------- Crear / Actualizar ----------

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btnGuardar = document.getElementById('btnGuardar');
        btnGuardar.disabled = true;

        const esEdicion = document.getElementById('productoId').value !== '';
        const url = esEdicion ? 'api/actualizar.php' : 'api/crear.php';

        try {
            const resp = await fetch(url, { method: 'POST', body: new FormData(form) });
            const data = await resp.json();

            if (!data.success) { mostrarToast(data.message, 'error'); return; }

            mostrarToast(data.message, 'success');
            cerrarModal(modalProducto);
            // Recargar la tabla para reflejar los cambios (URL de imagen incluida)
            location.reload();
        } catch (err) {
            mostrarToast('Error de conexión con el servidor.', 'error');
        } finally {
            btnGuardar.disabled = false;
        }
    });

    // ---------- Eliminar ----------

    document.getElementById('btnConfirmarBorrar').addEventListener('click', async () => {
        if (!idAEliminar) return;

        const fd = new FormData();
        fd.append('id', idAEliminar);

        try {
            const resp = await fetch('api/eliminar.php', { method: 'POST', body: fd });
            const data = await resp.json();

            if (!data.success) { mostrarToast(data.message, 'error'); return; }

            mostrarToast(data.message, 'success');
            cerrarModal(modalEliminar);
            const fila = tabla.querySelector(`tr[data-id="${idAEliminar}"]`);
            if (fila) fila.remove();
            idAEliminar = null;

            // Si no quedan filas, recargar para mostrar el estado vacío
            if (!tabla.querySelector('tr[data-id]')) location.reload();
        } catch (err) {
            mostrarToast('Error de conexión con el servidor.', 'error');
        }
    });
});
