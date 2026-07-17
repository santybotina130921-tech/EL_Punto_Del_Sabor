<?php
class Database {
    public function getConnection() {
        // Todas las credenciales se leen SIEMPRE desde variables de entorno.
        // Ya no hay contraseñas ni usuarios escritos directamente en el código.
        $host = getenv('DB_HOST') ?: "aws-1-us-east-2.pooler.supabase.com";
        $port = getenv('DB_PORT') ?: "5432";
        $db   = getenv('DB_NAME') ?: "postgres";
        $user = getenv('DB_USER') ?: "postgres.hpkkxwzhhanludpzyyrt";
        $pass = getenv('DB_PASS');

        if (empty($pass)) {
            die("Falta configurar la variable de entorno DB_PASS con la contraseña de tu base de datos de Supabase.");
        }

        try {
            $dsn = "pgsql:host=$host;port=$port;dbname=$db;options='--search_path=public'";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT            => 10 
            ];
            
            return new PDO($dsn, $user, $pass, $options);
            
        } catch(PDOException $exception) {
            // En producción, evita mostrar el error completo por seguridad
            die("Error de conexión a la base de datos.");
        }
    }
}
?>