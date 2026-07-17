<?php
class Usuario {
    private $conn;
    private $table_name = "usuarios";

    // Propiedades del objeto mapeadas con Supabase
    public $id;
    public $nombre;
    public $correo;
    public $password;
    public $rol;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. REGISTRAR UN NUEVO USUARIO (ADMIN O EMPLEADO)
    public function registrar() {
        // SEGURIDAD: Verificar si la conexión a la BD está activa
        if ($this->conn === null) {
            error_log("Error: No hay conexión a la base de datos en Usuario->registrar().");
            return false; 
        }

        // Verificar primero si el correo ya existe
        $check_query = "SELECT id FROM " . $this->table_name . " WHERE correo = :correo LIMIT 1";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(':correo', $this->correo);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            return false; // El correo ya está registrado
        }

        // Query de inserción limpia
        $query = "INSERT INTO " . $this->table_name . " (nombre, correo, password, rol) 
                  VALUES (:nombre, :correo, :password, :rol)";
        
        $stmt = $this->conn->prepare($query);

        // Limpieza de seguridad contra inyecciones XSS básicos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $this->rol = htmlspecialchars(strip_tags($this->rol));

        // Vincular los parámetros reales
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':correo', $this->correo);
        $stmt->bindParam(':password', $this->password); 
        $stmt->bindParam(':rol', $this->rol);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // 2. BUSCAR USUARIO PARA INICIAR SESIÓN (LOGIN)
    public function login() {
        // SEGURIDAD: Verificar si la conexión a la BD está activa
        if ($this->conn === null) {
            error_log("Error: No hay conexión a la base de datos en Usuario->login().");
            return false;
        }

        $query = "SELECT id, nombre, password, rol FROM " . $this->table_name . " WHERE correo = :correo LIMIT 1";
        $stmt = $this->conn->prepare($query);

        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $stmt->bindParam(':correo', $this->correo);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->nombre = $row['nombre'];
            $this->password = $row['password']; 
            $this->rol = $row['rol'];
            return true;
        }
        return false;
    }
}
?>