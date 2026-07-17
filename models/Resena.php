<?php
class Resena {
    private $conn;
    private $table_name = "resenas";

    public $id;
    public $producto_id;
    public $usuario_id;
    public $calificacion;
    public $comentario;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear una nota/reseña interna sobre un producto
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . "
            (producto_id, usuario_id, calificacion, comentario)
            VALUES (:producto_id, :usuario_id, :calificacion, :comentario)";

        $stmt = $this->conn->prepare($query);

        $this->comentario = htmlspecialchars(strip_tags($this->comentario));

        $stmt->bindParam(':producto_id', $this->producto_id);
        $stmt->bindParam(':usuario_id', $this->usuario_id);
        $stmt->bindParam(':calificacion', $this->calificacion);
        $stmt->bindParam(':comentario', $this->comentario);

        return $stmt->execute();
    }

    // Trae las reseñas/notas internas de un producto puntual (con el nombre de quién la escribió)
    public function leerPorProducto($producto_id) {
        $query = "SELECT r.*, u.nombre as usuario_nombre
                   FROM " . $this->table_name . " r
                   LEFT JOIN usuarios u ON r.usuario_id = u.id
                   WHERE r.producto_id = :producto_id
                   ORDER BY r.fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':producto_id', $producto_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Trae TODAS las reseñas/notas agrupadas por producto_id (para pintar el inventario completo de una vez)
    public function leerTodasAgrupadas() {
        $query = "SELECT r.*, u.nombre as usuario_nombre
                   FROM " . $this->table_name . " r
                   LEFT JOIN usuarios u ON r.usuario_id = u.id
                   ORDER BY r.fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $agrupadas = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $agrupadas[$fila['producto_id']][] = $fila;
        }
        return $agrupadas;
    }

    // Trae el promedio de calificación y el total de notas por producto (para mostrar en la lista de inventario)
    public function resumenPorProducto() {
        $query = "SELECT producto_id, ROUND(AVG(calificacion), 1) as promedio, COUNT(*) as total
                   FROM " . $this->table_name . "
                   GROUP BY producto_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $resumen = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $resumen[$fila['producto_id']] = $fila;
        }
        return $resumen;
    }
}
?>
