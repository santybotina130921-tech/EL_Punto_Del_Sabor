<?php
require_once '../config/database.php';
require_once '../models/Resena.php';

class ResenaController {

    // Agregar una nota/reseña interna a un producto (Admin o Empleado, ambos logueados)
    public function agregar() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $db = $database->getConnection();
            $resena = new Resena($db);

            $resena->producto_id = $_POST['producto_id'] ?? null;
            $resena->usuario_id = $_SESSION['user_id'];
            $resena->calificacion = (int)($_POST['calificacion'] ?? 5);
            $resena->comentario = trim($_POST['comentario'] ?? '');

            if ($resena->producto_id && $resena->comentario !== '') {
                $resena->crear();
            }
        }

        header("Location: index.php?url=productos");
        exit();
    }
}
?>
