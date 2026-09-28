<?php

namespace App\Controllers;

use App\Models\Producto;
use PDOException;

class ProductoController
{
    public function leer(array $query): array
    {
        $id = filter_var($query['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            return $this->respuesta(422, 'ID de producto inválido.');
        }

        $producto = Producto::obtener($id);
        if (!$producto) {
            return $this->respuesta(404, 'Producto no encontrado.');
        }

        return ['status' => 200, 'body' => ['success' => true, 'producto' => $producto]];
    }

    public function crear(array $post, array $files): array
    {
        $datos = $this->validar($post);
        if ($datos === null) {
            return $this->respuesta(422, 'Título y precio válido son obligatorios.');
        }

        if (!isset($files['imagen']) || $files['imagen']['error'] === UPLOAD_ERR_NO_FILE) {
            return $this->respuesta(422, 'La imagen del producto es obligatoria.');
        }

        $subida = subirImagenCloudinary($files['imagen']);
        if (!$subida['success']) {
            return $this->respuesta(422, $subida['message']);
        }

        try {
            Producto::crear($datos + ['imagen_url' => $subida['url']]);
        } catch (PDOException $e) {
            return $this->respuesta(500, 'Error al guardar en la base de datos.');
        }

        return $this->respuesta(200, 'Producto creado correctamente.');
    }

    public function actualizar(array $post, array $files): array
    {
        $id = filter_var($post['id'] ?? null, FILTER_VALIDATE_INT);
        $datos = $this->validar($post);
        if (!$id || $datos === null) {
            return $this->respuesta(422, 'ID, título y precio válido son obligatorios.');
        }

        try {
            $existente = Producto::obtener($id);
            if (!$existente) {
                return $this->respuesta(404, 'Producto no encontrado.');
            }

            // Edición inteligente: solo se reemplaza la imagen si el usuario adjunta una nueva
            $imagenUrl = $existente['imagen_url'];
            if (isset($files['imagen']) && $files['imagen']['error'] === UPLOAD_ERR_OK) {
                $subida = subirImagenCloudinary($files['imagen']);
                if (!$subida['success']) {
                    return $this->respuesta(422, $subida['message']);
                }
                $imagenUrl = $subida['url'];
            }

            Producto::actualizar($datos + ['imagen_url' => $imagenUrl, 'id' => $id]);
        } catch (PDOException $e) {
            return $this->respuesta(500, 'Error al actualizar en la base de datos.');
        }

        return $this->respuesta(200, 'Producto actualizado correctamente.');
    }

    public function eliminar(array $post): array
    {
        $id = filter_var($post['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            return $this->respuesta(422, 'ID de producto inválido.');
        }

        try {
            $eliminado = Producto::eliminar($id);
        } catch (PDOException $e) {
            return $this->respuesta(500, 'Error al eliminar en la base de datos.');
        }

        if (!$eliminado) {
            return $this->respuesta(404, 'Producto no encontrado.');
        }

        return $this->respuesta(200, 'Producto eliminado correctamente.');
    }

    private function validar(array $post): ?array
    {
        $titulo = trim($post['titulo'] ?? '');
        $precio = filter_var($post['precio'] ?? null, FILTER_VALIDATE_FLOAT);

        if ($titulo === '' || $precio === false || $precio < 0) {
            return null;
        }

        $tags = trim($post['tags'] ?? '');

        return [
            'titulo'      => $titulo,
            'descripcion' => trim($post['descripcion'] ?? ''),
            'precio'      => $precio,
            'tags'        => $tags !== '' ? $tags : null,
        ];
    }

    private function respuesta(int $status, string $mensaje): array
    {
        return ['status' => $status, 'body' => ['success' => $status < 400, 'message' => $mensaje]];
    }
}
