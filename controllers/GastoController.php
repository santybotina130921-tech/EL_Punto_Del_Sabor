<?php
require_once '../config/database.php';
require_once '../models/Gasto.php';

class GastoController {

    // Registrar un nuevo gasto/egreso manual (Solo Administrador)
    public function registrar() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header("Location: index.php?url=dashboard&error=no_autorizado");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $db = $database->getConnection();
            $gasto = new Gasto($db);

            $gasto->descripcion = trim($_POST['descripcion'] ?? '');
            $monto_limpio = preg_replace('/[^0-9.]/', '', $_POST['monto'] ?? '0');
            $gasto->monto = floatval($monto_limpio);
            $gasto->tipo_negocio = $_POST['tipo_negocio'] ?? 'general';
            $gasto->usuario_id = $_SESSION['user_id'] ?? null;

            if ($gasto->descripcion !== '' && $gasto->monto > 0) {
                $gasto->crear();
                header("Location: index.php?url=productos_catalogo&gasto=creado");
            } else {
                header("Location: index.php?url=productos_catalogo&gasto=error");
            }
            exit();
        }

        header("Location: index.php?url=productos_catalogo");
        exit();
    }
}
?>
