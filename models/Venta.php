<?php
class Venta {
    private $conn;

    // Propiedades de la venta
    public $id;
    public $usuario_id;
    public $total;
    public $tipo_negocio;
    public $metodo_pago;

    public function __construct($db) {
        $this->conn = $db;
        // Forzamos a PDO a que levante excepciones si Supabase rechaza un query
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Registrar una venta completa con su detalle de forma automática
    public function registrarVenta($items) {
        try {
            // Iniciamos una transacción para asegurar que si algo falla, no se guarde a medias
            $this->conn->beginTransaction();

            // 1. Insertar Cabecera de la Venta
            $query_venta = "INSERT INTO ventas (usuario_id, total, tipo_negocio, metodo_pago) 
                            VALUES (:usuario_id, :total, :tipo_negocio, :metodo_pago)";
            $stmt_venta = $this->conn->prepare($query_venta);

            $stmt_venta->bindParam(':usuario_id', $this->usuario_id);
            $stmt_venta->bindParam(':total', $this->total);
            $stmt_venta->bindParam(':tipo_negocio', $this->tipo_negocio);
            $stmt_venta->bindParam(':metodo_pago', $this->metodo_pago);
            $stmt_venta->execute();

            // Capturamos el ID de la venta recién generada en Supabase
            $this->id = $this->conn->lastInsertId();

            // 2. Insertar los detalles y descontar el inventario de forma automática
            $query_detalle = "INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario) 
                              VALUES (:venta_id, :producto_id, :cantidad, :precio_unitario)";
            $stmt_detalle = $this->conn->prepare($query_detalle);

            $query_stock = "UPDATE productos SET stock = stock - :cantidad WHERE id = :producto_id";
            $stmt_stock = $this->conn->prepare($query_stock);

            foreach ($items as $item) {
                // Registrar el producto en el detalle
                $stmt_detalle->bindParam(':venta_id', $this->id);
                $stmt_detalle->bindParam(':producto_id', $item['producto_id']);
                $stmt_detalle->bindParam(':cantidad', $item['cantidad']);
                $stmt_detalle->bindParam(':precio_unitario', $item['precio_unitario']);
                $stmt_detalle->execute();

                // Actualizar el stock del inventario de forma automática
                $stmt_stock->bindParam(':cantidad', $item['cantidad']);
                $stmt_stock->bindParam(':producto_id', $item['producto_id']);
                $stmt_stock->execute();
            }

            // Si todo salió bien, guardamos definitivamente en la base de datos
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Si hubo algún error, deshacemos todo automáticamente en la base de datos
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            // 👇 DETECTOR DE ERRORES REAL EN PANTALLA
            echo "<div style='background:#fff5f5; color:#c53030; padding:20px; border:2px solid #feb2b2; font-family:sans-serif; margin:30px; padding:20px; border-radius:8px;'>";
            echo "<h2 style='margin-top:0;'>🚨 Error al procesar el pago en Supabase:</h2>";
            echo "<p><strong>Mensaje del sistema:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p>Mira qué campo o tabla está mal nombrado arriba. Corrige ese detalle en tu base de datos o muéstramelo para solucionarlo de una.</p>";
            echo "<a href='index.php?url=dashboard' style='background:#e53e3e; color:#fff; padding:8px 15px; text-decoration:none; border-radius:5px; font-weight:bold; display:inline-block; margin-top:10px;'>Volver al Panel</a>";
            echo "</div>";
            die(); // Detiene la aplicación aquí para que puedas leerlo con calma
        }
    }
}
?>