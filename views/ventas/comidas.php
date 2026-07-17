<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja: El Punto del Sabor</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        body { background-color: #fcf8f2; }
    </style>
</head>
<body class="font-sans text-gray-800 min-h-screen flex flex-col">

    <?php 
        $base_path = dirname(__DIR__); 
        require_once $base_path . '/modules/nav.php'; 
    ?>

    <main class="flex-grow max-w-7xl mx-auto px-6 py-10 w-full">
        
        <!-- Cabecera con Buscador -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-950 flex items-center gap-2">
                    🔥 Caja: El Punto del Sabor
                </h2>
                <p class="text-gray-600 mt-1 text-sm">
                    Registra las ventas de comidas rápidas al instante y descuenta el stock automáticamente.
                </p>
            </div>

            <!-- Input del Buscador -->
            <div class="relative min-w-[280px] w-full md:w-auto">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400 text-sm">
                    🔍
                </span>
                <input type="text" id="buscador" placeholder="Buscar hamburguesa, bebida, perro..." 
                       class="w-full pl-9 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 shadow-sm transition-all">
            </div>
        </div>

        <form action="index.php?url=guardar_venta" method="POST">
            <input type="hidden" name="tipo_negocio" value="comidas">

            <!-- Grid de Productos -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
                <?php if (empty($productos)): ?>
                    <div class="col-span-full text-center text-gray-500 py-12 bg-white rounded-2xl border border-gray-200">
                        No hay productos disponibles en el menú de comidas rápidas.
                    </div>
                <?php else: ?>
                    <?php foreach ($productos as $prod): 
                        $p_id = $prod['id'] ?? '';
                        $p_nombre = $prod['nombre'] ?? 'Sin nombre';
                        $p_precio = $prod['precio_venta'] ?? 0;
                        $p_stock = $prod['stock'] ?? 0;
                        $p_imagen = $prod['imagen_url'] ?? 'uploads/default.png';
                    ?>
                        <!-- Tarjeta de Producto Individual con clase de rastreo -->
                        <div class="tarjeta-producto bg-slate-950 text-white rounded-2xl overflow-hidden shadow-md border border-slate-900 flex flex-col justify-between">
                            <div class="h-44 w-full overflow-hidden bg-slate-900 border-b border-slate-800 flex items-center justify-center">
                                <img src="<?= htmlspecialchars($p_imagen); ?>" class="max-w-full max-h-full w-auto h-auto object-contain hover:scale-105 transition-transform duration-300" alt="<?= htmlspecialchars($p_nombre); ?>">
                            </div>
                            
                            <div class="p-4 flex-grow flex flex-col justify-between">
                                <div>
                                    <h3 class="font-black text-lg tracking-tight text-white line-clamp-1 buscar-nombre"><?= htmlspecialchars($p_nombre); ?></h3>
                                    <div class="text-amber-400 font-extrabold text-xl mt-1">
                                        $<?= number_format($p_precio, 0, ',', '.'); ?>
                                    </div>
                                    <div class="text-xs text-slate-400 mt-1">
                                        Stock disponible: <span class="<?= ($p_stock <= 5) ? 'text-rose-400 font-bold' : 'text-emerald-400'; ?>"><?= $p_stock; ?> unds</span>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <input type="hidden" name="productos_seleccionados[<?= $p_id; ?>][precio]" value="<?= $p_precio; ?>">
                                    <label class="block text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">Cantidad a vender:</label>
                                    <input type="number" 
                                           name="productos_seleccionados[<?= $p_id; ?>][text-box]" 
                                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-center text-white font-bold focus:outline-none focus:border-amber-500 text-sm" 
                                           value="0" min="0" max="<?= $p_stock; ?>">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Fila dinámica si no hay coincidencias de búsqueda -->
                    <div id="sin-coincidencias" class="hidden col-span-full text-center text-gray-500 py-12 bg-white rounded-2xl border border-gray-200">
                        <div class="text-3xl mb-2">🔍</div>
                        No se encontraron productos en el menú que coincidan con la búsqueda.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Footer Fijo de la Cuenta -->
            <div class="bg-slate-950 text-white rounded-2xl p-5 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-4 border border-slate-900">
                <div class="text-xl font-bold flex items-center gap-2">
                    Total Cuenta: <span class="text-yellow-400 font-black text-2xl" id="total-cuenta-display">$0</span>
                    <input type="hidden" name="total_venta" id="total_venta_input" value="0">
                </div>
                
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                    <select name="metodo_pago" class="bg-slate-900 border border-slate-800 text-sm font-semibold rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-amber-500 w-full sm:w-44">
                        <option value="efectivo">💵 Efectivo</option>
                        <option value="nequi">📱 Nequi</option>
                        <option value="daviplata">🔴 Daviplata</option>
                        <option value="transferencia">💳 Tarjeta / Transf.</option>
                    </select>

                    <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-sm uppercase tracking-wider px-6 py-3 rounded-xl transition-all shadow-md w-full sm:w-auto cursor-pointer">
                        ⚡ Registrar Venta
                    </button>
                </div>
            </div>
        </form>
    </main>

    <?php require_once $base_path . '/modules/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inputs = document.querySelectorAll('input[name*="[text-box]"]');
            const totalDisplay = document.getElementById('total-cuenta-display');
            const totalInput = document.getElementById('total_venta_input');
            const buscador = document.getElementById('buscador');

            // 1. Lógica para calcular el valor total de la cuenta
            function calcularTotal() {
                let totalGeneral = 0;
                inputs.forEach(input => {
                    const cantidad = parseInt(input.value) || 0;
                    if (cantidad > 0) {
                        const tarjeta = input.closest('.tarjeta-producto');
                        const precioInput = tarjeta.querySelector('input[name*="[precio]"]');
                        const precio = parseFloat(precioInput.value) || 0;
                        totalGeneral += (cantidad * precio);
                    }
                });
                totalDisplay.textContent = '$' + totalGeneral.toLocaleString('es-CO');
                totalInput.value = totalGeneral;
            }

            inputs.forEach(input => {
                input.addEventListener('input', calcularTotal);
                input.addEventListener('change', calcularTotal);
            });

            // 2. Lógica para filtrar tarjetas en tiempo real sin recargar
            if (buscador) {
                buscador.addEventListener('input', function() {
                    const query = this.value.toLowerCase().trim();
                    const tarjetas = document.querySelectorAll('.tarjeta-producto');
                    const sinCoincidencias = document.getElementById('sin-coincidencias');
                    let encontrados = 0;

                    tarjetas.forEach(tarjeta => {
                        const nombre = tarjeta.querySelector('.buscar-nombre')?.textContent.toLowerCase() || '';
                        
                        if (nombre.includes(query)) {
                            tarjeta.style.display = '';
                            encontrados++;
                        } else {
                            tarjeta.style.display = 'none';
                        }
                    });

                    // Mostrar/Ocultar el aviso de "No se encontraron resultados"
                    if (sinCoincidencias) {
                        if (encontrados === 0 && query !== '') {
                            sinCoincidencias.classList.remove('hidden');
                        } else {
                            sinCoincidencias.classList.add('hidden');
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>