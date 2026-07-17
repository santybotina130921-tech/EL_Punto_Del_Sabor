<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conectamos a la base de datos para sacar las métricas reales de Supabase
require_once __DIR__ . '/../../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Ajustamos la zona horaria en la sesión de PostgreSQL para Colombia
$db->exec("SET TIME ZONE 'America/Bogota';");

$mes_actual = date('m');
$anio_actual = date('Y');
$hoy = date('Y-m-d');

// Diccionario de meses en español
$meses = [
    '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
    '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
    '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
];
$nombre_mes_actual = $meses[$mes_actual] ?? 'Este Mes';

// --- 1. CALCULAR INGRESOS (Confirmado usando 'fecha_venta') ---
$query_ingresos = "SELECT COALESCE(SUM(total), 0) as total_ingresos FROM ventas 
                   WHERE EXTRACT(MONTH FROM fecha_venta) = :mes AND EXTRACT(YEAR FROM fecha_venta) = :anio";
$stmt = $db->prepare($query_ingresos);
$stmt->execute(['mes' => $mes_actual, 'anio' => $anio_actual]);
$ingresos = $stmt->fetch(PDO::FETCH_ASSOC)['total_ingresos'] ?? 0;

// --- 2. CALCULAR EGRESOS (Inversión total del inventario actual) ---
$query_egresos = "SELECT COALESCE(SUM(precio_compra * stock), 0) as total_egresos FROM productos";
$stmt_egresos = $db->prepare($query_egresos);
$stmt_egresos->execute();
$egresos = $stmt_egresos->fetch(PDO::FETCH_ASSOC)['total_egresos'] ?? 0;

$balance = $ingresos - $egresos;

// --- 3. PRODUCTO MÁS VENDIDO DEL MES (Confirmado usando 'v.fecha_venta') ---
$query_top = "SELECT p.nombre, p.imagen_url, p.tipo_negocio, SUM(dv.cantidad) as total_vendido, SUM(dv.cantidad * dv.precio_unitario) as total_generado
              FROM detalle_ventas dv
              JOIN productos p ON dv.producto_id = p.id
              JOIN ventas v ON dv.venta_id = v.id
              WHERE EXTRACT(MONTH FROM v.fecha_venta) = :mes AND EXTRACT(YEAR FROM v.fecha_venta) = :anio
              GROUP BY p.id, p.nombre, p.imagen_url, p.tipo_negocio
              ORDER BY total_vendido DESC LIMIT 1";
$stmt_top = $db->prepare($query_top);
$stmt_top->execute(['mes' => $mes_actual, 'anio' => $anio_actual]);
$top_producto = $stmt_top->fetch(PDO::FETCH_ASSOC);

// --- 4. LISTADO DE LO VENDIDO HOY (Confirmado usando 'v.fecha_venta') ---
$query_hoy = "SELECT v.id, v.fecha_venta, v.total, v.metodo_pago, v.tipo_negocio, u.nombre as cajero
              FROM ventas v
              LEFT JOIN usuarios u ON v.usuario_id = u.id
              WHERE DATE(v.fecha_venta) = :hoy
              ORDER BY v.fecha_venta DESC";
$stmt_hoy = $db->prepare($query_hoy);
$stmt_hoy->execute(['hoy' => $hoy]);
$ventas_hoy = $stmt_hoy->fetchAll(PDO::FETCH_ASSOC);

// --- 5. RESUMEN MENSUAL DE INGRESOS Y EGRESOS (últimos 12 meses) ---
require_once __DIR__ . '/../../models/Gasto.php';
$gastoModelo = new Gasto($db);

// Ingresos reales por mes (todas las ventas registradas)
$query_ingresos_mes = "SELECT TO_CHAR(fecha_venta, 'YYYY-MM') as mes_key, SUM(total) as total
                        FROM ventas
                        WHERE fecha_venta >= (CURRENT_DATE - INTERVAL '12 months')
                        GROUP BY mes_key";
$ingresos_por_mes = $db->query($query_ingresos_mes)->fetchAll(PDO::FETCH_KEY_PAIR);

// Egresos por costo de lo vendido (precio de compra x cantidad vendida) por mes
$query_costo_mes = "SELECT TO_CHAR(v.fecha_venta, 'YYYY-MM') as mes_key, SUM(dv.cantidad * p.precio_compra) as total
                     FROM detalle_ventas dv
                     JOIN ventas v ON dv.venta_id = v.id
                     JOIN productos p ON dv.producto_id = p.id
                     WHERE v.fecha_venta >= (CURRENT_DATE - INTERVAL '12 months')
                     GROUP BY mes_key";
$costo_ventas_por_mes = $db->query($query_costo_mes)->fetchAll(PDO::FETCH_KEY_PAIR);

// Egresos manuales (arriendo, servicios, insumos, etc.) registrados por el administrador
$gastos_manuales_por_mes = $gastoModelo->totalesPorMes(12);
$ultimos_gastos = $gastoModelo->ultimos(6);

// Unimos las 3 fuentes en una sola tabla mensual
$todos_los_meses = array_unique(array_merge(
    array_keys($ingresos_por_mes),
    array_keys($costo_ventas_por_mes),
    array_keys($gastos_manuales_por_mes)
));
rsort($todos_los_meses); // más reciente primero

$resumen_mensual = [];
foreach ($todos_los_meses as $mes_key) {
    $ingreso = (float)($ingresos_por_mes[$mes_key] ?? 0);
    $egreso = (float)($costo_ventas_por_mes[$mes_key] ?? 0) + (float)($gastos_manuales_por_mes[$mes_key] ?? 0);

    list($anio_m, $num_mes) = explode('-', $mes_key);
    $resumen_mensual[] = [
        'label' => ($meses[$num_mes] ?? $num_mes) . ' ' . $anio_m,
        'ingreso' => $ingreso,
        'egreso' => $egreso,
        'balance' => $ingreso - $egreso
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Financieros - Gestión Comercial</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        body { background-color: #fcf8f2; }
    </style>
</head>
<body class="font-sans text-gray-800 min-h-screen flex flex-col">

    <!-- NAVBAR INTEGRADO COMPATIBLE Y UNIFICADO -->
    <?php 
        $base_path = dirname(__DIR__); 
        require_once $base_path . '/modules/nav.php'; 
    ?>

    <!-- CONTENEDOR PRINCIPAL -->
    <main class="flex-grow max-w-7xl mx-auto px-6 py-10 w-full">
        
        <!-- Encabezado de la Sección -->
        <div class="mb-8">
            <h2 class="text-3xl font-extrabold text-slate-950 flex items-center gap-2">
                📊 Panel de Control y Reportes
            </h2>
            <p class="text-gray-600 mt-1 text-sm">
                Monitoreo financiero del mes de <span class="font-bold text-slate-950"><?= $nombre_mes_actual . ' ' . $anio_actual; ?></span> en tiempo real con Supabase.
            </p>
        </div>

        <!-- GRID DE TARJETAS MÉTRICAS (INGRESOS, EGRESOS, BALANCE) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            
            <!-- Tarjeta Ingresos -->
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-md flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-bold tracking-wider text-gray-500">Ingresos del Mes</p>
                    <h3 class="text-3xl font-black text-emerald-600 mt-2">$<?= number_format($ingresos, 0, ',', '.'); ?></h3>
                    <p class="text-xs text-emerald-600 mt-1 font-medium">▲ Flujo de caja positivo</p>
                </div>
                <div class="text-4xl bg-emerald-50 p-4 rounded-xl text-emerald-600">💵</div>
            </div>

            <!-- Tarjeta Egresos -->
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-md flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-bold tracking-wider text-gray-500">Valor en Inventario (Egresos)</p>
                    <h3 class="text-3xl font-black text-rose-600 mt-2">$<?= number_format($egresos, 0, ',', '.'); ?></h3>
                    <p class="text-xs text-rose-500 mt-1 font-medium">▼ Costo acumulado en stock</p>
                </div>
                <div class="text-4xl bg-rose-50 p-4 rounded-xl text-rose-600">📉</div>
            </div>

            <!-- Tarjeta Balance -->
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-md flex items-center justify-between <?= ($balance >= 0) ? 'bg-gradient-to-br from-white to-emerald-50/30' : 'bg-gradient-to-br from-white to-rose-50/30'; ?>">
                <div>
                    <p class="text-xs uppercase font-bold tracking-wider text-gray-500">Balance Estimado</p>
                    <h3 class="text-3xl font-black <?= ($balance >= 0) ? 'text-slate-950' : 'text-rose-700'; ?> mt-2">
                        $<?= number_format($balance, 0, ',', '.'); ?>
                    </h3>
                    <p class="text-xs mt-1 font-bold <?= ($balance >= 0) ? 'text-emerald-600' : 'text-rose-600'; ?>">
                        <?= ($balance >= 0) ? '✔ Utilidad neta' : '❌ Diferencia en cuenta'; ?>
                    </p>
                </div>
                <div class="text-4xl bg-slate-100 p-4 rounded-xl">⚖</div>
            </div>
        </div>

        <!-- SECCIÓN INFERIOR: PRODUCTO MÁS VENDIDO Y VENTAS DEL DÍA -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- COLUMNA PRODUCTO MÁS VENDIDO -->
            <div class="bg-slate-950 text-white rounded-2xl p-6 shadow-md border border-slate-900 flex flex-col justify-between">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-amber-500 mb-4">🏆 Producto Más Vendido</h4>
                    <?php if ($top_producto): ?>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-16 h-16 rounded-xl overflow-hidden border border-slate-800 bg-white shrink-0">
                                <img src="<?= htmlspecialchars($top_producto['imagen_url'] ?: 'uploads/default.png'); ?>" class="w-full h-full object-cover" alt="Top">
                            </div>
                            <div>
                                <h5 class="text-lg font-black text-white leading-tight line-clamp-2"><?= htmlspecialchars($top_producto['nombre']); ?></h5>
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-slate-800 text-amber-400 mt-1 inline-block">
                                    <?= ($top_producto['tipo_negocio'] === 'comidas') ? '🍔 El Punto' : '✏ Papelería'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="space-y-2 border-t border-slate-900 pt-4 text-sm">
                            <div class="flex justify-between text-slate-400">
                                <span>Unidades vendidas:</span>
                                <span class="text-white font-bold"><?= $top_producto['total_vendido']; ?> unds</span>
                            </div>
                            <div class="flex justify-between text-slate-400">
                                <span>Total recaudado:</span>
                                <span class="text-yellow-400 font-extrabold">$<?= number_format($top_producto['total_generado'], 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-slate-400 text-sm text-center py-12">No hay registros de ventas este mes.</p>
                    <?php endif; ?>
                </div>
                <div class="mt-6 text-[10px] text-slate-500 font-mono text-center border-t border-slate-900 pt-3">
                    Métricas calculadas dinámicamente
                </div>
            </div>

            <!-- TABLA DE LO VENDIDO HOY -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-md overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="p-5 border-b border-gray-100 bg-slate-50/50 flex justify-between items-center">
                        <h4 class="font-bold text-slate-900 text-sm uppercase tracking-wider flex items-center gap-2">
                            ⏱ Registro de Ventas de Hoy
                        </h4>
                        <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-2.5 py-1 rounded-full">
                            <?= count($ventas_hoy); ?> Hechas hoy
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-slate-950 text-slate-200 font-bold uppercase">
                                <tr>
                                    <th class="p-3 pl-5">Hora</th>
                                    <th class="p-3">Negocio</th>
                                    <th class="p-3">Método</th>
                                    <th class="p-3">Cajero</th>
                                    <th class="p-3 text-right pr-5">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($ventas_hoy)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-gray-400 py-12">
                                            Aún no se han registrado ventas el día de hoy.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($ventas_hoy as $v_hoy): ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="p-3 pl-5 font-mono text-gray-500">
                                                <?= date('H:i A', strtotime($v_hoy['fecha_venta'])); ?>
                                            </td>
                                            <td class="p-3">
                                                <span class="px-2 py-0.5 rounded font-bold text-[10px] <?= ($v_hoy['tipo_negocio'] === 'comidas') ? 'bg-amber-100 text-amber-800' : 'bg-yellow-100 text-yellow-900'; ?>">
                                                    <?= ($v_hoy['tipo_negocio'] === 'comidas') ? '🍔 El Punto' : '✏ Papelería'; ?>
                                                </span>
                                            </td>
                                            <td class="p-3 uppercase font-medium text-gray-600">
                                                <?= htmlspecialchars($v_hoy['metodo_pago'] ?? 'efectivo'); ?>
                                            </td>
                                            <td class="p-3 text-gray-700">
                                                <?= htmlspecialchars($v_hoy['cajero'] ?? 'MARIA DEL ROSARIO'); ?>
                                            </td>
                                            <td class="p-3 text-right pr-5 font-bold text-slate-950 text-sm">
                                                $<?= number_format($v_hoy['total'], 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- 📊 RESUMEN MENSUAL DE INGRESOS Y EGRESOS -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Tabla resumen por mes -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-md overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-slate-50/50">
                    <h4 class="font-bold text-slate-900 text-sm uppercase tracking-wider flex items-center gap-2">
                        📅 Resumen Mensual (Ingresos vs Egresos)
                    </h4>
                    <p class="text-xs text-gray-500 mt-1">Egresos = costo de lo vendido + gastos manuales registrados.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-950 text-slate-200 font-bold uppercase">
                            <tr>
                                <th class="p-3 pl-5">Mes</th>
                                <th class="p-3 text-right">Ingresos</th>
                                <th class="p-3 text-right">Egresos</th>
                                <th class="p-3 text-right pr-5">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (empty($resumen_mensual)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-gray-400 py-12">
                                        Aún no hay suficiente historial para mostrar un resumen mensual.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($resumen_mensual as $fila): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="p-3 pl-5 font-bold text-gray-800 capitalize"><?= htmlspecialchars($fila['label']); ?></td>
                                        <td class="p-3 text-right font-bold text-emerald-600">$<?= number_format($fila['ingreso'], 0, ',', '.'); ?></td>
                                        <td class="p-3 text-right font-bold text-rose-600">$<?= number_format($fila['egreso'], 0, ',', '.'); ?></td>
                                        <td class="p-3 text-right pr-5 font-black <?= $fila['balance'] >= 0 ? 'text-slate-950' : 'text-rose-700'; ?>">
                                            $<?= number_format($fila['balance'], 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Registrar gasto + últimos gastos -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-md p-5">
                <h4 class="font-bold text-slate-900 text-sm uppercase tracking-wider mb-4 flex items-center gap-2">
                    ➕ Registrar Egreso Manual
                </h4>

                <?php if (isset($_GET['gasto']) && $_GET['gasto'] === 'creado'): ?>
                    <div class="mb-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold px-3 py-2 rounded-xl">
                        ✅ Gasto registrado correctamente.
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <form action="index.php?url=gastos_registrar" method="POST" class="space-y-3">
                        <input type="text" name="descripcion" placeholder="Ej. Arriendo, servicios, insumos..." class="w-full bg-slate-50 border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-amber-500" required>
                        <input type="number" step="0.01" name="monto" placeholder="Monto ($)" class="w-full bg-slate-50 border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-amber-500" required>
                        <select name="tipo_negocio" class="w-full bg-slate-50 border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-amber-500">
                            <option value="general">General</option>
                            <option value="comidas">🍔 El Punto del Sabor</option>
                            <option value="papeleria">✏️ Papelería</option>
                        </select>
                        <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs uppercase tracking-wider px-4 py-2.5 rounded-xl transition-all shadow-sm">
                            Guardar Gasto
                        </button>
                    </form>
                <?php else: ?>
                    <p class="text-xs text-gray-400">Solo el administrador puede registrar gastos.</p>
                <?php endif; ?>

                <?php if (!empty($ultimos_gastos)): ?>
                    <div class="mt-5 pt-4 border-t border-gray-100">
                        <p class="text-[10px] font-bold uppercase text-gray-400 mb-2">Últimos gastos registrados</p>
                        <div class="space-y-2 max-h-40 overflow-y-auto">
                            <?php foreach ($ultimos_gastos as $g): ?>
                                <div class="flex justify-between text-xs text-gray-600 border-b border-gray-50 pb-1">
                                    <span class="truncate max-w-[140px]"><?= htmlspecialchars($g['descripcion']); ?></span>
                                    <span class="font-bold text-rose-600 whitespace-nowrap">$<?= number_format($g['monto'], 0, ',', '.'); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- FOOTER INTEGRADO Y REUTILIZABLE -->
    <?php require_once $base_path . '/modules/footer.php'; ?>

</body>
</html>