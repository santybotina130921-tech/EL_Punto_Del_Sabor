<?php
require_once '../config/database.php';

class ChatbotController {

    // Responde un mensaje del chatbot (AJAX). Requiere estar logueado.
    public function responder() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['ok' => false, 'error' => 'Debes iniciar sesión.']);
            exit();
        }

        // Clave de Groq: se lee SIEMPRE desde variable de entorno (nunca escrita en el código)
        $api_key = getenv('GROQ_API_KEY');

        if (empty($api_key)) {
            echo json_encode(['ok' => false, 'error' => 'Falta configurar la clave de Groq (GROQ_API_KEY) en el servidor.']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $mensaje_usuario = trim($input['mensaje'] ?? '');
        $historial = $input['historial'] ?? []; // [{role, content}, ...] enviado desde el navegador

        if ($mensaje_usuario === '') {
            echo json_encode(['ok' => false, 'error' => 'Escribe un mensaje.']);
            exit();
        }

        // Generamos el contexto masivo con el estado de toda la base de datos
        $contexto = $this->construirContexto();

        $mensajes = [
            [
                'role' => 'system',
                'content' => "Eres el asistente virtual de 'El Punto del Sabor & Papelería', un negocio de comidas rápidas y papelería en Colombia que usa este software de inventario y ventas. " .
                             "Tienes acceso total al estado de la base de datos a través de la información en tiempo real que se te proporciona abajo. " .
                             "Usa estos datos para responder CUALQUIER pregunta sobre stock, finanzas, qué se vende más, qué se vende menos o cuentas del día. " .
                             "Responde siempre en español de Colombia, de forma breve, clara, usando pesos colombianos ($). " .
                             "Aquí tienes el estado completo y actual de la base de datos:\n" . $contexto
            ]
        ];

        // Añadimos el historial reciente de la conversación (máximo 10 mensajes anteriores)
        if (is_array($historial)) {
            $historial = array_slice($historial, -10);
            foreach ($historial as $m) {
                if (isset($m['role'], $m['content']) && in_array($m['role'], ['user', 'assistant'])) {
                    $mensajes[] = ['role' => $m['role'], 'content' => substr($m['content'], 0, 2000)];
                }
            }
        }

        $mensajes[] = ['role' => 'user', 'content' => $mensaje_usuario];

        // Usamos Llama 3.3 70B: mejor calidad de respuesta, sigue siendo 100% gratis en Groq
        $body = json_encode([
            'model' => 'llama-3.3-70b-versatile',
            'messages' => $mensajes,
            'temperature' => 0.4, // Temperatura más baja para que sea más preciso con los números
            'max_tokens' => 600
        ]);

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $api_key,
            'Content-Type: application/json'
        ]);
        $respuesta = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($respuesta, true);

        if ($http_code !== 200) {
            $mensaje_error = $data['error']['message'] ?? 'Error de conexión con la base de datos de IA.';
            echo json_encode(['ok' => false, 'error' => $mensaje_error]);
            exit();
        }

        $texto_respuesta = $data['choices'][0]['message']['content'] ?? 'No pude procesar la consulta.';
        echo json_encode(['ok' => true, 'respuesta' => $texto_respuesta]);
        exit();
    }

    // Extrae y estructura la base de datos completa para alimentar a la IA
    private function construirContexto() {
        try {
            $database = new Database();
            $db = $database->getConnection();

            $texto = "=== ESTADO ACTUAL DE LA BASE DE DATOS ===\n\n";

            // 1. INVENTARIO COMPLETO (Trae todos los productos registrados, su precio y su stock real)
            $stmt_inventario = $db->prepare("SELECT nombre, stock, precio_venta, tipo_negocio FROM productos ORDER BY nombre ASC");
            $stmt_inventario->execute();
            $productos = $stmt_inventario->fetchAll(PDO::FETCH_ASSOC);

            $texto .= "--- INVENTARIO GENERAL ---\n";
            if (!empty($productos)) {
                foreach ($productos as $p) {
                    $texto .= "- {$p['nombre']} | Categoria: {$p['tipo_negocio']} | Stock: {$p['stock']} unds | Precio: \${$p['precio_venta']}\n";
                }
            } else {
                $texto .= "No hay productos registrados en el inventario.\n";
            }
            $texto .= "\n";

            // 2. VENTAS DEL DÍA EN CURSO (Totales y cantidad)
            $stmt_hoy = $db->prepare("SELECT COALESCE(SUM(total),0) as total_hoy, COUNT(*) as ventas_hoy FROM ventas WHERE DATE(fecha_venta) = CURRENT_DATE");
            $stmt_hoy->execute();
            $hoy = $stmt_hoy->fetch(PDO::FETCH_ASSOC);
            
            $texto .= "--- VENTAS DE HOY ---\n";
            $texto .= "- Cantidad de transacciones hoy: " . ($hoy['ventas_hoy'] ?? 0) . "\n";
            $texto .= "- Caja total de hoy: $" . number_format($hoy['total_hoy'] ?? 0, 0, ',', '.') . "\n\n";

            // 3. ÚLTIMOS MOVIMIENTOS / HISTORIAL RECIENTE DE VENTAS
            $stmt_ultimas_ventas = $db->prepare("SELECT total, fecha_venta FROM ventas ORDER BY fecha_venta DESC LIMIT 5");
            $stmt_ultimas_ventas->execute();
            $ultimas_ventas = $stmt_ultimas_ventas->fetchAll(PDO::FETCH_ASSOC);

            $texto .= "--- ÚLTIMAS 5 VENTAS REGISTRADAS ---\n";
            if (!empty($ultimas_ventas)) {
                foreach ($ultimas_ventas as $v) {
                    $texto .= "- Valor: \${$v['total']} | Hora/Fecha: {$v['fecha_venta']}\n";
                }
            } else {
                $texto .= "No se han registrado ventas recientemente.\n";
            }
            $texto .= "\n";

            // 4. RESUMEN DE GASTOS DEL MES ACTUAL (Para balance de pérdidas y ganancias)
            $stmt_gastos = $db->prepare("SELECT COALESCE(SUM(monto), 0) as total_gastos FROM gastos WHERE TO_CHAR(fecha_gasto, 'YYYY-MM') = TO_CHAR(CURRENT_DATE, 'YYYY-MM')");
            $stmt_gastos->execute();
            $gastos = $stmt_gastos->fetch(PDO::FETCH_ASSOC);

            $texto .= "--- CONTABILIDAD Y GASTOS ---\n";
            $texto .= "- Egresos/Gastos totales de este mes: $" . number_format($gastos['total_gastos'] ?? 0, 0, ',', '.') . "\n";

            return $texto;

        } catch (Exception $e) {
            return "- (Error al leer los datos del sistema: " . $e->getMessage() . ")\n";
        }
    }
}
?>