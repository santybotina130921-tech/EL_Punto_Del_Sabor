<?php
class Producto {
    private $conn;
    private $table_name = "productos";

    // Propiedades del Producto
    public $id;
    public $nombre;
    public $descripcion;
    public $precio_compra;
    public $precio_venta;
    public $stock;
    public $tipo_negocio;
    public $imagen_url;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 🛠️ MÉTODO AUXILIAR PARA PROCESAR LA SUBIDA DEL ARCHIVO LOCAL
    private function subirImagen($archivo) {
        // Verificar si se seleccionó un archivo sin errores
        if (isset($archivo) && $archivo['error'] === UPLOAD_ERR_OK) {
            
            // Definimos la carpeta de destino dentro del proyecto
            $directorio_destino = __DIR__ . '/../public/uploads/';
            
            // Si la carpeta de subidas no existe, la creamos automáticamente con permisos
            if (!file_exists($directorio_destino)) {
                mkdir($directorio_destino, 0777, true);
            }

            // Obtener la extensión del archivo (jpg, png, etc.)
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            
            // Validar extensiones permitidas
            $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($extension, $extensiones_permitidas)) {
                return false; 
            }

            // Creamos un nombre único para el archivo para que no se sobrescriban si se llaman igual
            $nombre_archivo_unico = time() . "_" . uniqid() . "." . $extension;
            $ruta_completa_servidor = $directorio_destino . $nombre_archivo_unico;

            // Movemos el archivo temporal de la computadora a nuestra carpeta uploads
            if (move_uploaded_file($archivo['tmp_name'], $ruta_completa_servidor)) {
                // Retornamos la ruta relativa que guardará Supabase y usará la etiqueta <img>
                return 'uploads/' . $nombre_archivo_unico;
            }
        }
        return false;
    }

    // 🎨 GENERAR 3 OPCIONES DE IMAGEN CON GOOGLE GEMINI (gemini-2.5-flash-image / "Nano Banana")
    // Requiere tener facturación (tarjeta) vinculada al proyecto de Google Cloud/AI Studio
    // para que la cuota deje de estar en 0. El uso normal se mantiene en $0 dentro de la franja gratis.
    // Devuelve un array de rutas relativas (uploads/tmp/xxx.png) o ['error' => '...'] si falla.
    public function generarImagenesIA($prompt) {
        $api_key = getenv('GEMINI_API_KEY');
        if (empty($api_key)) {
            return ['error' => 'Falta configurar la clave de Gemini (GEMINI_API_KEY) en el servidor.'];
        }

        $directorio_tmp = __DIR__ . '/../public/uploads/tmp/';
        if (!file_exists($directorio_tmp)) {
            mkdir($directorio_tmp, 0777, true);
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image:generateContent";
        $rutas = [];

        // Generamos 1 sola imagen (para ahorrar cuota/costo)
        for ($i = 0; $i < 1; $i++) {
            $data = [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'responseModalities' => ['IMAGE']
                ]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $api_key
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 45);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                return ['error' => 'No se pudo conectar con Google Gemini: ' . $curl_error];
            }

            $resultado = json_decode($response, true);

            if ($http_code !== 200) {
                $mensaje = $resultado['error']['message'] ?? 'Error desconocido de la API de Gemini.';
                if ($http_code === 400) {
                    $mensaje = 'Petición inválida o clave GEMINI_API_KEY incorrecta. Verifica que la clave venga de aistudio.google.com/apikey.';
                } elseif ($http_code === 403) {
                    $mensaje = 'Acceso denegado por Google. Revisa que la clave tenga permisos para Gemini API.';
                } elseif ($http_code === 429) {
                    $mensaje = 'Sigue sin cuota disponible. Confirma que la facturación quedó vinculada correctamente en Google Cloud.';
                }
                return ['error' => $mensaje];
            }

            // Buscamos la parte de la respuesta que trae la imagen en base64
            $partes = $resultado['candidates'][0]['content']['parts'] ?? [];
            $bytes = null;
            foreach ($partes as $parte) {
                if (isset($parte['inlineData']['data'])) {
                    $bytes = base64_decode($parte['inlineData']['data']);
                    break;
                }
            }

            if ($bytes) {
                $nombre_archivo = time() . "_" . uniqid() . ".png";
                $ruta_completa = $directorio_tmp . $nombre_archivo;

                if (file_put_contents($ruta_completa, $bytes) !== false) {
                    $rutas[] = 'uploads/tmp/' . $nombre_archivo;
                }
            }
        }

        if (empty($rutas)) {
            return ['error' => 'Google Gemini no devolvió ninguna imagen procesable.'];
        }

        return $rutas;
    }

    // 🎨 CONFIRMA LA IMAGEN TEMPORAL ELEGIDA: la mueve de uploads/tmp/ a uploads/ definitivo
    public function confirmarImagenIA($ruta_temporal) {
        // Regex de seguridad modificada: Acepta tanto .jpg, .jpeg como .png por compatibilidad con tus archivos anteriores
        if (!preg_match('/^uploads\/tmp\/[a-zA-Z0-9_\.]+\.(jpg|jpeg|png)$/i', $ruta_temporal)) {
            return false;
        }

        $origen = __DIR__ . '/../public/' . $ruta_temporal;
        if (!file_exists($origen)) {
            return false;
        }

        $extension = strtolower(pathinfo($origen, PATHINFO_EXTENSION));
        $directorio_destino = __DIR__ . '/../public/uploads/';
        $nombre_final = time() . "_ia_" . uniqid() . "." . $extension;
        $destino = $directorio_destino . $nombre_final;

        if (rename($origen, $destino)) {
            return 'uploads/' . $nombre_final;
        }
        return false;
    }

    // 🧹 ELIMINA UNA IMAGEN TEMPORAL QUE NO FUE ELEGIDA (limpieza de espacio en disco)
    public function eliminarImagenTemporal($ruta_temporal) {
        // Regex adaptada para aceptar las nuevas imágenes .jpg temporales creadas por Gemini
        if (!preg_match('/^uploads\/tmp\/[a-zA-Z0-9_\.]+\.(jpg|jpeg|png)$/i', $ruta_temporal)) {
            return false;
        }
        $ruta_completa = __DIR__ . '/../public/' . $ruta_temporal;
        if (file_exists($ruta_completa)) {
            @unlink($ruta_completa);
        }
        return true;
    }

    // 1. LEER TODOS LOS PRODUCTOS o FILTRAR POR NEGOCIO
    public function leer($negocio = null) {
        if ($negocio) {
            $query = "SELECT * FROM " . $this->table_name . " WHERE tipo_negocio = :tipo_negocio ORDER BY nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':tipo_negocio', $negocio);
        } else {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY tipo_negocio ASC, nombre ASC";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->execute();
        return $stmt;
    }

    // 2. LEER UN SOLO PRODUCTO (Para editar)
    public function leerUno() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch();
        if ($row) {
            $this->nombre = $row['nombre'];
            $this->descripcion = $row['descripcion'];
            $this->precio_compra = $row['precio_compra'];
            $this->precio_venta = $row['precio_venta'];
            $this->stock = $row['stock'];
            $this->tipo_negocio = $row['tipo_negocio'];
            $this->imagen_url = $row['imagen_url'];
            return true;
        }
        return false;
    }

    // 3. CREAR NUEVO PRODUCTO (Con protección contra fallos de subida)
    public function crear($archivo_imagen = null, $imagen_predefinida = null) {
        // Establecemos imagen por defecto antes del procesamiento
        $this->imagen_url = 'uploads/default.png'; 

        // Prioridad 1: archivo subido manualmente desde la PC
        if ($archivo_imagen && $archivo_imagen['error'] === UPLOAD_ERR_OK) {
            $ruta_subida = $this->subirImagen($archivo_imagen);
            if ($ruta_subida) {
                $this->imagen_url = $ruta_subida;
            }
        // Prioridad 2: imagen generada con IA y ya guardada en el servidor
        } elseif (!empty($imagen_predefinida)) {
            $this->imagen_url = $imagen_predefinida;
        }

        $query = "INSERT INTO " . $this->table_name . " 
            (nombre, descripcion, precio_compra, precio_venta, stock, tipo_negocio, imagen_url) 
            VALUES (:nombre, :descripcion, :precio_compra, :precio_venta, :stock, :tipo_negocio, :imagen_url)";
        
        $stmt = $this->conn->prepare($query);

        // Sanitizamos de forma segura asegurando que existan cadenas legibles
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->tipo_negocio = htmlspecialchars(strip_tags($this->tipo_negocio));
        $this->imagen_url = htmlspecialchars(strip_tags($this->imagen_url));

        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':precio_compra', $this->precio_compra);
        $stmt->bindParam(':precio_venta', $this->precio_venta);
        $stmt->bindParam(':stock', $this->stock);
        $stmt->bindParam(':tipo_negocio', $this->tipo_negocio);
        $stmt->bindParam(':imagen_url', $this->imagen_url);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // 4. ACTUALIZAR PRODUCTO (Conserva la ruta anterior si no se sube un nuevo archivo)
    public function actualizar($archivo_imagen = null, $imagen_predefinida = null) {
        // Prioridad 1: si se sube una nueva imagen desde la PC, reemplazamos la propiedad actual
        if ($archivo_imagen && $archivo_imagen['error'] === UPLOAD_ERR_OK) {
            $ruta_subida = $this->subirImagen($archivo_imagen);
            if ($ruta_subida) {
                $this->imagen_url = $ruta_subida;
            }
        // Prioridad 2: imagen generada con IA y ya guardada en el servidor
        } elseif (!empty($imagen_predefinida)) {
            $this->imagen_url = $imagen_predefinida;
        }

        $query = "UPDATE " . $this->table_name . " 
            SET nombre = :nombre, descripcion = :descripcion, precio_compra = :precio_compra, 
                precio_venta = :precio_venta, stock = :stock, tipo_negocio = :tipo_negocio, imagen_url = :imagen_url 
            WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->tipo_negocio = htmlspecialchars(strip_tags($this->tipo_negocio));
        $this->imagen_url = htmlspecialchars(strip_tags($this->imagen_url));

        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':precio_compra', $this->precio_compra);
        $stmt->bindParam(':precio_venta', $this->precio_venta);
        $stmt->bindParam(':stock', $this->stock);
        $stmt->bindParam(':tipo_negocio', $this->tipo_negocio);
        $stmt->bindParam(':imagen_url', $this->imagen_url);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // 5. ELIMINAR PRODUCTO
    public function eliminar() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // 6. 📉 DESCONTAR STOCK AUTOMÁTICAMENTE TRAS UNA VENTA
    public function descontarStock($cantidad_vendida) {
        $query_check = "SELECT stock FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(':id', $this->id);
        $stmt_check->execute();
        
        $row = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $stock_actual = $row['stock'];
            
            if ($stock_actual >= $cantidad_vendida) {
                $query_update = "UPDATE " . $this->table_name . " 
                                 SET stock = stock - :cantidad 
                                 WHERE id = :id";
                                 
                $stmt_update = $this->conn->prepare($query_update);
                $stmt_update->bindParam(':cantidad', $cantidad_vendida, PDO::PARAM_INT);
                $stmt_update->bindParam(':id', $this->id);
                
                if ($stmt_update->execute()) {
                    return true;
                }
            }
        }
        return false;
    }
}
?>