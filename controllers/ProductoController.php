<?php
require_once '../config/database.php';
require_once '../models/Producto.php';

class ProductoController {

    // Listar todos los productos (Accesible por Admin y Empleado)
    public function index() {
        // Asegurar que la sesión esté activa para renderizar correctamente el nav/footer según el rol
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $database = new Database();
        $db = $database->getConnection();
        $producto = new Producto($db);

        // Trae todos los productos de Supabase ordenados por negocio
        $stmt = $producto->leer();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 📝 Trae las notas/reseñas internas de cada producto
        require_once '../models/Resena.php';
        $resena = new Resena($db);
        $resenas_por_producto = $resena->leerTodasAgrupadas();

        // Carga la vista de la tabla pasándole los datos
        require_once '../views/productos/index.php';
    }

    // Catálogo Automatizado (Accesible por todos los usuarios)
    public function catalogo() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $database = new Database();
        $db = $database->getConnection();
        $producto = new Producto($db);

        // Trae de forma automática los productos reales desde Supabase
        $stmt = $producto->leer();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Corregido: Estandarización de la ruta relativa idéntica al index
        require_once '../views/productos/catalogo.php';
    }

    // Guardar un producto nuevo (Solo Administrador)
    public function crear() {
        $this->verificarAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $db = $database->getConnection();
            $producto = new Producto($db);

            $producto->nombre = $_POST['nombre'] ?? '';
            $producto->descripcion = $_POST['descripcion'] ?? '';
            $producto->precio_compra = $_POST['precio_compra'] ?? 0;
            $producto->precio_venta = $_POST['precio_venta'] ?? 0;
            $producto->stock = $_POST['stock'] ?? 0;
            $producto->tipo_negocio = $_POST['tipo_negocio'] ?? '';
            
            // 👇 CAPTURAMOS EL ARCHIVO LOCAL DESDE LA COMPUTADORA
            $archivo_imagen = $_FILES['imagen'] ?? null;

            // 🎨 CAPTURAMOS LA RUTA DE LA IMAGEN GENERADA CON IA (SI EL USUARIO ELIGIÓ UNA)
            $imagen_ia = $_POST['imagen_ia_path'] ?? null;

            if ($producto->crear($archivo_imagen, $imagen_ia)) {
                header("Location: index.php?url=productos&mensaje=creado");
                exit();
            } else {
                $error = "No se pudo registrar el producto.";
                require_once '../views/productos/crear.php';
            }
        } else {
            require_once '../views/productos/crear.php';
        }
    }

    // Editar un producto existente (Solo Administrador)
    public function editar() {
        $this->verificarAdmin();

        $database = new Database();
        $db = $database->getConnection();
        $producto = new Producto($db);
        $producto->id = $_GET['id'] ?? null;

        if (!$producto->id || !$producto->leerUno()) {
            header("Location: index.php?url=productos&error=no_encontrado");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $producto->nombre = $_POST['nombre'] ?? '';
            $producto->descripcion = $_POST['descripcion'] ?? '';
            $producto->precio_compra = $_POST['precio_compra'] ?? 0;
            $producto->precio_venta = $_POST['precio_venta'] ?? 0;
            $producto->stock = $_POST['stock'] ?? 0;
            $producto->tipo_negocio = $_POST['tipo_negocio'] ?? '';
            
            // 👇 CAPTURAMOS EL NUEVO ARCHIVO SI EL USUARIO SELECCIONÓ UNO NUEVO
            $archivo_imagen = $_FILES['imagen'] ?? null;

            // 🎨 CAPTURAMOS LA RUTA DE LA IMAGEN GENERADA CON IA (SI EL USUARIO ELIGIÓ UNA)
            $imagen_ia = $_POST['imagen_ia_path'] ?? null;

            if ($producto->actualizar($archivo_imagen, $imagen_ia)) {
                header("Location: index.php?url=productos&mensaje=actualizado");
                exit();
            } else {
                $error = "No se pudo actualizar el producto.";
                require_once '../views/productos/editar.php';
            }
        } else {
            // Muestra el formulario de edición con los datos actuales cargados
            require_once '../views/productos/editar.php';
        }
    }

    // Eliminar un producto (Solo Administrador)
    public function eliminar() {
        $this->verificarAdmin();

        if (isset($_GET['id'])) {
            $database = new Database();
            $db = $database->getConnection();
            $producto = new Producto($db);
            $producto->id = $_GET['id'];

            if ($producto->eliminar()) {
                header("Location: index.php?url=productos&mensaje=eliminado");
                exit();
            }
        }
        header("Location: index.php?url=productos&error=no_eliminado");
        exit();
    }

    // 🎨 GENERAR 3 OPCIONES DE IMAGEN CON GOOGLE GEMINI IMAGEN 3 (AJAX - Solo Administrador)
    public function generarImagenIA() {
        $this->verificarAdmin();
        header('Content-Type: application/json');

        // Validar que sea petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $prompt_usuario = trim($input['prompt'] ?? '');
        $tipo_negocio = trim($input['tipo_negocio'] ?? '');

        if ($prompt_usuario === '') {
            echo json_encode(['ok' => false, 'error' => 'Escribe un nombre o descripción para generar la imagen.']);
            exit();
        }

        // Le damos contexto a Gemini según el tipo de negocio para mejores resultados estéticos
        $estilo = 'foto de producto profesional, iluminación de estudio, centrada, fondo limpio, alta calidad, fotorrealista';
        if ($tipo_negocio === 'comidas') {
            $estilo = 'foto apetitosa de comida rápida, iluminación de estudio gastronómica, centrada, estilo menú de restaurante, alta calidad, fotorrealista';
        } elseif ($tipo_negocio === 'papeleria') {
            $estilo = 'foto de producto de papelería detallada, fondo de color plano limpio, iluminación de estudio, centrada, alta calidad, fotorrealista';
        }

        $prompt_final = $prompt_usuario . ', ' . $estilo;

        $database = new Database();
        $db = $database->getConnection();
        $producto = new Producto($db);

        // Llama al método que conecta con el endpoint gratuito de Imagen 3 en Producto.php
        $resultado = $producto->generarImagenesIA($prompt_final);

        if (isset($resultado['error'])) {
            echo json_encode(['ok' => false, 'error' => $resultado['error']]);
        } else {
            echo json_encode(['ok' => true, 'images' => $resultado]);
        }
        exit();
    }

    // 🎨 CONFIRMAR LA IMAGEN QUE EL USUARIO ELIGIÓ (mueve de tmp/ a definitiva y limpia las otras 2)
    public function guardarImagenIA() {
        $this->verificarAdmin();
        header('Content-Type: application/json');

        // Validar que sea petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
            exit();
        }

        $ruta_elegida = $_POST['ruta_temporal'] ?? '';
        $todas_las_rutas_raw = json_decode($_POST['todas_las_rutas'] ?? '[]', true);
        $todas_las_rutas = is_array($todas_las_rutas_raw) ? $todas_las_rutas_raw : [];

        $database = new Database();
        $db = $database->getConnection();
        $producto = new Producto($db);

        $ruta_final = $producto->confirmarImagenIA($ruta_elegida);

        if ($ruta_final) {
            // Limpiamos las otras imágenes temporales que no se usaron para no saturar el servidor local
            foreach ($todas_las_rutas as $ruta) {
                if ($ruta !== $ruta_elegida) {
                    $producto->eliminarImagenTemporal($ruta);
                }
            }
            echo json_encode(['ok' => true, 'path' => $ruta_final]);
        } else {
            echo json_encode(['ok' => false, 'error' => 'No se pudo guardar la imagen generada. Intenta de nuevo.']);
        }
        exit();
    }

    // Guardián estricto para bloquear empleados en funciones críticas
    private function verificarAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header("Location: index.php?url=dashboard&error=no_autorizado");
            exit();
        }
    }
}
?>