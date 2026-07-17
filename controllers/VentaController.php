<?php
require_once '../config/database.php';
require_once '../models/Producto.php';
require_once '../models/Venta.php';

class VentaController {

    // Pantalla de caja para El Punto del Sabor (Comidas Rápidas)
    public function cajaComidas() {
        $database = new Database();
        $db = $database->getConnection();
        
        // Cargamos solo los productos del menú de comidas rápidas
        $producto = new Producto($db);
        $stmt = $producto->leer('comidas');
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once '../views/ventas/comidas.php';
    }

    // Pantalla de caja para la Papelería
    public function cajaPapeleria() {
        $database = new Database();
        $db = $database->getConnection();
        
        // Cargamos solo los artículos escolares y servicios de papelería
        $producto = new Producto($db);
        $stmt = $producto->leer('papeleria');
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once '../views/ventas/papeleria.php';
    }

    // Procesar el cobro automático desde el cliente
    public function procesarVenta() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) { session_start(); }

            $database = new Database();
            $db = $database->getConnection();
            $venta = new Venta($db);

            $tipo_negocio = $_POST['tipo_negocio'] ?? 'papeleria'; 
            $venta->usuario_id = $_SESSION['user_id'] ?? null;
            
            // 🌟 SANEAMIENTO CONTRA OVERFLOW: Remueve signos de pesos, puntos de miles o comas del total
            $total_recibido = $_POST['total_venta'] ?? 0;
            $total_limpio = preg_replace('/[^0-9.]/', '', $total_recibido);
            $venta->total = floatval($total_limpio);
            
            $venta->tipo_negocio = $tipo_negocio;
            $venta->metodo_pago = $_POST['metodo_pago'] ?? 'efectivo';

            // Estructuramos los productos seleccionados enviados desde la interfaz
            $items = [];
            if (isset($_POST['productos_seleccionados']) && is_array($_POST['productos_seleccionados'])) {
                foreach ($_POST['productos_seleccionados'] as $p_id => $detalles) {
                    
                    // Captura la cantidad si viene indexada como 'text-box' o como 'cantidad'
                    $cantidad = 0;
                    if (isset($detalles['text-box'])) {
                        $cantidad = (int)$detalles['text-box'];
                    } elseif (isset($detalles['cantidad'])) {
                        $cantidad = (int)$detalles['cantidad'];
                    }

                    // Si se marcó una cantidad válida y tiene precio establecido, entra al array
                    if ($cantidad > 0 && isset($detalles['precio'])) {
                        // 🌟 SANEAMIENTO CONTRA OVERFLOW: Limpia el precio de cada producto individual
                        $precio_limpio = preg_replace('/[^0-9.]/', '', $detalles['precio']);
                        
                        $items[] = [
                            'producto_id'    => $p_id,
                            'cantidad'       => $cantidad,
                            'precio_unitario'=> floatval($precio_limpio)
                        ];
                    }
                }
            }

            // Al llamar a registrarVenta($items), tu modelo inserta la factura,
            // llena el detalle_ventas y descuenta el stock en un solo bloque seguro (Commit)
            if (!empty($items) && $venta->registrarVenta($items)) {
                header("Location: index.php?url=ventas_" . $tipo_negocio . "&resultado=exito");
            } else {
                header("Location: index.php?url=ventas_" . $tipo_negocio . "&resultado=error");
            }
            exit();
        }
    }
}
?>