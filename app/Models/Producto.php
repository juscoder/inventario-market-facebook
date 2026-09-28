<?php

namespace App\Models;

class Producto
{
    public static function obtenerTodos(): array
    {
        return getPDO()->query('SELECT * FROM productos ORDER BY creado_en DESC')->fetchAll();
    }

    public static function obtener(int $id): ?array
    {
        $stmt = getPDO()->prepare(
            'SELECT id, titulo, descripcion, precio, tags, imagen_url FROM productos WHERE id = ?'
        );
        $stmt->execute([$id]);
        $producto = $stmt->fetch();

        return $producto ?: null;
    }

    public static function crear(array $datos): int
    {
        $stmt = getPDO()->prepare(
            'INSERT INTO productos (titulo, descripcion, precio, tags, imagen_url) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $datos['titulo'],
            $datos['descripcion'],
            $datos['precio'],
            $datos['tags'],
            $datos['imagen_url'],
        ]);

        return (int) getPDO()->lastInsertId();
    }

    public static function actualizar(array $datos): void
    {
        $stmt = getPDO()->prepare(
            'UPDATE productos SET titulo = ?, descripcion = ?, precio = ?, tags = ?, imagen_url = ? WHERE id = ?'
        );
        $stmt->execute([
            $datos['titulo'],
            $datos['descripcion'],
            $datos['precio'],
            $datos['tags'],
            $datos['imagen_url'],
            $datos['id'],
        ]);
    }

    public static function eliminar(int $id): bool
    {
        $stmt = getPDO()->prepare('DELETE FROM productos WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }
}
