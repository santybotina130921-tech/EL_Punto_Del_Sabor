<?php
// 1. Iniciar el sistema de sesiones de forma automática si no ha iniciado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. IMPORTACIÓN BLINDADA: Asegura la ruta absoluta a los controladores usando __DIR__
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/ProductoController.php';
require_once __DIR__ . '/../controllers/VentaController.php';
require_once __DIR__ . '/../controllers/GastoController.php';
require_once __DIR__ . '/../controllers/ResenaController.php';
require_once __DIR__ . '/../controllers/ChatbotController.php';

// 3. Capturar la acción de la URL (si no se especifica ninguna, por defecto va a 'login')
$url = $_GET['url'] ?? 'login';

// 4. Definir las rutas públicas del sistema (accesibles sin loguearse)
$rutas_publicas = ['login', 'registrar'];

// 5. GUARDIÁN AUTOMÁTICO DE SEGURIDAD
// Si el usuario NO ha iniciado sesión y quiere entrar a una ruta protegida -> Al Login de una
if (!isset($_SESSION['user_id']) && !in_array($url, $rutas_publicas)) {
    header("Location: index.php?url=login");
    exit();
}

// Si el usuario YA inició sesión e intenta volver al login o registro -> Al Dashboard automáticamente
if (isset($_SESSION['user_id']) && in_array($url, $rutas_publicas)) {
    header("Location: index.php?url=dashboard");
    exit();
}

// 6. INSTANCIAR LOS CONTROLADORES GENERALES
$authCtrl = new AuthController();
$productoCtrl = new ProductoController();
$ventaCtrl = new VentaController();
$gastoCtrl = new GastoController();
$resenaCtrl = new ResenaController();
$chatbotCtrl = new ChatbotController();

// 7. ENRUTADOR DINÁMICO (Switch)
switch ($url) {
    
    // ==========================================
    // RUTAS DE AUTENTICACIÓN
    // ==========================================
    case 'login':
        $authCtrl->login();
        break;
        
    case 'registrar':
        $authCtrl->registrar();
        break;
        
    case 'logout':
        $authCtrl->logout();
        break;
        
    // ==========================================
    // RUTA DEL PANEL PRINCIPAL
    // ==========================================
    case 'dashboard':
        // Corregido también con __DIR__ para que cargue la vista de forma segura
        require_once __DIR__ . '/../views/dashboard.php';
        break;

    // ==========================================
    // RUTAS DEL CRUD DE INVENTARIO (INTELIGENTE)
    // ==========================================
    case 'productos':
        $productoCtrl->index();
        break;
        
    // 🌟 CORREGIDO: Soporta ambas variantes de URL para que el menú cargue el catálogo sin dar 404
    case 'productos_catalogo':
    case 'productos_ver':
        require_once __DIR__ . '/../views/productos/catalogo.php';
        break;
        
    case 'productos_crear':
        $productoCtrl->crear();
        break;
        
    case 'productos_editar':
        $productoCtrl->editar();
        break;
        
    case 'productos_eliminar':
        $productoCtrl->eliminar();
        break;

    // 🎨 GENERADOR DE IMÁGENES CON IA (AJAX)
    case 'generar_imagen_ia':
        $productoCtrl->generarImagenIA();
        break;

    case 'guardar_imagen_ia':
        $productoCtrl->guardarImagenIA();
        break;

    // ==========================================
    // RUTAS AUTOMÁTICAS DE LAS CAJAS DE VENTAS
    // ==========================================
    case 'ventas_comidas':
        // Carga la caja interactiva con los colores de "El Punto del Sabor"
        $ventaCtrl->cajaComidas();
        break;

    // ==========================================
    // RUTAS AUTOMÁTICAS DE LAS CAJAS DE VENTAS
    // ==========================================
    case 'ventas_papeleria':
        // Carga la caja interactiva con la paleta de la "Papelería"
        $ventaCtrl->cajaPapeleria();
        break;

    case 'guardar_venta':
        // Recibe el formulario POST con los productos seleccionados y altera Supabase
        $ventaCtrl->procesarVenta();
        break;

    // ==========================================
    // GASTOS (EGRESOS MANUALES) Y RESEÑAS INTERNAS
    // ==========================================
    case 'gastos_registrar':
        $gastoCtrl->registrar();
        break;

    case 'resenas_agregar':
        $resenaCtrl->agregar();
        break;

    // ==========================================
    // CHATBOT DE AYUDA CON IA (AJAX)
    // ==========================================
    case 'chatbot_responder':
        $chatbotCtrl->responder();
        break;

    // ==========================================
    // ERROR DE RUTA NO ENCONTRADA (404)
    // ==========================================
    default:
        http_response_code(404);
        echo "<div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>";
        echo "<h1 style='color: #e65c00;'>404 - Página No Encontrada</h1>";
        echo "<p>La sección a la que intentas acceder de forma automática no existe.</p>";
        echo "<a href='index.php?url=dashboard' style='color: #3a9ad9; text-decoration: none; font-weight: bold;'>Volver al Panel</a>";
        echo "</div>";
        break;
}
?>