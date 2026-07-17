<?php
require_once '../config/database.php';
require_once '../models/Usuario.php';

class AuthController {

    // 1. PROCESAR EL INGRESO (LOGIN)
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $db = $database->getConnection();
            $usuario = new Usuario($db);

            $usuario->correo = $_POST['correo'] ?? '';
            $password = $_POST['password'] ?? '';

            // Verificar si el correo existe en Supabase
            if ($usuario->login()) {
                // Verificar si la contraseña coincide (usando password_verify por seguridad)
                if (password_verify($password, $usuario->password)) {
                    // Si es correcto, guardamos de forma automática los datos en la sesión
                    if (session_status() === PHP_SESSION_NONE) { session_start(); }
                    
                    $_SESSION['user_id'] = $usuario->id;
                    $_SESSION['user_name'] = $usuario->nombre;
                    $_SESSION['user_role'] = $usuario->rol;

                    // Redirigir directo al panel principal unificado
                    header("Location: index.php?url=dashboard");
                    exit();
                }
            }
            
            // Si las credenciales fallan
            $error = "Correo o contraseña incorrectos.";
            require_once '../views/auth/login.php';
        } else {
            // Si solo entra por URL, le pinta el formulario visual
            require_once '../views/auth/login.php';
        }
    }

    // 2. PROCESAR EL REGISTRO DE NUEVO PERSONAL
    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $db = $database->getConnection();
            $usuario = new Usuario($db);

            $usuario->nombre = $_POST['nombre'] ?? '';
            $usuario->correo = $_POST['correo'] ?? '';
            // Encriptamos la contraseña de forma automática antes de mandarla a Supabase
            $usuario->password = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT);
            $usuario->rol = $_POST['rol'] ?? 'empleado';

            if ($usuario->registrar()) {
                // Si se crea con éxito, lo manda al login con un aviso
                header("Location: index.php?url=login&registro=exito");
                exit();
            } else {
                $error = "El correo ya está registrado o hubo un error en los datos.";
                require_once '../views/auth/registrar.php';
            }
        } else {
            require_once '../views/auth/registrar.php';
        }
    }

    // 3. CERRAR SESIÓN AUTOMÁTICAMENTE
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        session_unset();
        session_destroy();
        header("Location: index.php?url=login");
        exit();
    }
}
?>