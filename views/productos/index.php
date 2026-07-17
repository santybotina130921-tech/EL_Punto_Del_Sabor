<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario General - La Cuchara de Oro</title>
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
        
        <!-- Cabecera -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-950 flex items-center gap-2">
                    📦 Inventario General
                </h2>
                <p class="text-gray-600 mt-1 text-sm">
                    Lista unificada de insumos y artículos comerciales sincronizados en tiempo real con Supabase.
                </p>
            </div>
            
            <!-- Buscador y Botón de Añadir -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
                <!-- Input del Buscador -->
                <div class="relative min-w-[280px] flex-grow sm:flex-grow-0">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400 text-sm">
                        🔍
                    </span>
                    <input type="text" id="buscador" placeholder="Buscar por nombre, descripción o negocio..." 
                           class="w-full pl-9 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 shadow-sm transition-all">
                </div>

                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="index.php?url=productos_crear" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-2.5 rounded-xl shadow-md transition-all text-sm text-center whitespace-nowrap">
                        + Añadir Producto
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($_GET['mensaje'])): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex justify-between items-center shadow-sm">
                <span>El producto ha sido <strong><?= htmlspecialchars($_GET['mensaje']); ?></strong> correctamente.</span>
                <a href="index.php?url=productos" class="font-bold hover:text-emerald-900 ml-2">✕</a>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm shadow-sm">
                Acción denegada o error en el proceso de sincronización con Supabase.
            </div>
        <?php endif; ?>

        <!-- Tabla de Inventario -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-950 text-slate-200 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="p-4 pl-6">IMAGEN</th>
                            <th class="p-4">NOMBRE / DESCRIPCIÓN</th>
                            <th class="p-4">NEGOCIO</th>
                            <th class="p-4">PRECIO COMPRA</th>
                            <th class="p-4">PRECIO VENTA</th>
                            <th class="p-4">STOCK ACTUAL</th>
                            <th class="p-4">NOTAS INTERNAS</th>
                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                <th class="p-4 text-center pr-6">ACCIONES</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="tabla-productos" class="divide-y divide-gray-100 text-sm bg-white">
                        <?php if (empty($productos)): ?>
                            <tr id="sin-resultados-db">
                                <td colspan="8" class="text-center text-gray-500 py-12">
                                    <div class="text-3xl mb-2">📦</div>
                                    No hay productos registrados en el sistema.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($productos as $prod): ?>
                                <?php 
                                    $p_id = $prod['id'] ?? '';
                                    $p_nombre = $prod['nombre'] ?? 'Sin nombre';
                                    $p_descripcion = $prod['descripcion'] ?? 'Sin descripción';
                                    $p_precio_compra = $prod['precio_compra'] ?? 0;
                                    $p_precio_venta = $prod['precio_venta'] ?? 0;
                                    $p_stock = $prod['stock'] ?? 0;
                                    $p_tipo_negocio = $prod['tipo_negocio'] ?? '';
                                    $p_imagen_url = $prod['imagen_url'] ?? 'uploads/default.png';
                                ?>
                                <!-- Fila de Producto con clase 'fila-producto' y atributos de búsqueda -->
                                <tr class="fila-producto hover:bg-slate-50/80 transition-colors" data-id="<?= $p_id; ?>">
                                    <td class="p-4 pl-6">
                                        <div class="w-12 h-12 rounded-xl overflow-hidden border border-gray-200 bg-white flex items-center justify-center shadow-sm">
                                            <img src="<?= htmlspecialchars($p_imagen_url); ?>" class="w-full h-full object-cover" alt="Foto">
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold text-gray-900 buscar-nombre"><?= htmlspecialchars($p_nombre); ?></div>
                                        <div class="text-gray-500 text-xs max-w-xs truncate buscar-descripcion"><?= htmlspecialchars($p_descripcion); ?></div>
                                    </td>
                                    <td class="p-4 whitespace-nowrap">
                                        <?php if ($p_tipo_negocio === 'comidas'): ?>
                                            <span class="text-xs font-bold bg-amber-600 text-white px-2.5 py-1 rounded-md shadow-sm buscar-negocio" data-negocio="comidas el punto">
                                                🍔 EL PUNTO
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs font-bold bg-yellow-400 text-black px-2.5 py-1 rounded-md shadow-sm buscar-negocio" data-negocio="papeleria util escolares">
                                                ✏️ PAPELERÍA
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 font-semibold text-gray-600 whitespace-nowrap">
                                        $<?= number_format($p_precio_compra, 0, ',', '.'); ?>
                                    </td>
                                    <td class="p-4 font-extrabold text-amber-700 whitespace-nowrap">
                                        $<?= number_format($p_precio_venta, 0, ',', '.'); ?>
                                    </td>
                                    <td class="p-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold border <?= ($p_stock <= 5) ? 'bg-red-50 text-red-700 border-red-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'; ?>">
                                            <?= htmlspecialchars($p_stock); ?> unds
                                        </span>
                                    </td>
                                    <?php
                                        $notas_producto = $resenas_por_producto[$p_id] ?? [];
                                        $total_notas = count($notas_producto);
                                    ?>
                                    <td class="p-4 whitespace-nowrap">
                                        <button type="button" onclick="document.getElementById('notas-<?= $p_id; ?>').classList.toggle('hidden')" class="text-xs font-bold text-violet-700 bg-violet-50 border border-violet-200 px-2.5 py-1 rounded-lg hover:bg-violet-100 transition-colors">
                                            📝 <?= $total_notas; ?> nota<?= $total_notas === 1 ? '' : 's'; ?>
                                        </button>
                                    </td>
                                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                        <td class="p-4 text-center pr-6 whitespace-nowrap">
                                            <div class="inline-flex rounded-lg border border-gray-200 shadow-sm bg-white overflow-hidden">
                                                <a href="index.php?url=productos_editar&id=<?= $p_id; ?>" class="px-3 py-1.5 text-xs font-bold text-gray-700 hover:bg-gray-50 border-r border-gray-200 transition-colors">
                                                    ✏️ Editar
                                                </a>
                                                <a href="index.php?url=productos_eliminar&id=<?= $p_id; ?>" class="px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 transition-colors" onclick="return confirm('¿Seguro que deseas eliminar este producto?');">
                                                    ❌ Borrar
                                                </a>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                
                                <!-- Fila de Notas -->
                                <tr id="notas-<?= $p_id; ?>" class="hidden bg-violet-50/40 fila-nota-contenedor">
                                    <td colspan="8" class="p-4 sm:p-6">
                                        <div class="max-w-2xl">
                                            <h4 class="text-xs font-bold uppercase tracking-wider text-violet-700 mb-3">📝 Notas internas de "<?= htmlspecialchars($p_nombre); ?>"</h4>

                                            <?php if (empty($notas_producto)): ?>
                                                <p class="text-xs text-gray-400 mb-4">Aún no hay notas para este producto.</p>
                                            <?php else: ?>
                                                <div class="space-y-2 mb-4 max-h-52 overflow-y-auto pr-1">
                                                    <?php foreach ($notas_producto as $nota): ?>
                                                        <div class="bg-white border border-gray-200 rounded-xl p-3 text-xs shadow-sm">
                                                            <div class="flex justify-between items-center mb-1">
                                                                <span class="font-bold text-gray-800"><?= htmlspecialchars($nota['usuario_nombre'] ?? 'Equipo'); ?></span>
                                                                <span class="text-amber-500 font-bold"><?= str_repeat('⭐', (int)$nota['calificacion']); ?></span>
                                                            </div>
                                                            <p class="text-gray-600"><?= nl2br(htmlspecialchars($nota['comentario'])); ?></p>
                                                            <p class="text-[10px] text-gray-400 mt-1"><?= date('d/m/Y H:i', strtotime($nota['fecha_creacion'])); ?></p>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>

                                            <form action="index.php?url=resenas_agregar" method="POST" class="flex flex-col sm:flex-row gap-2">
                                                <input type="hidden" name="producto_id" value="<?= $p_id; ?>">
                                                <select name="calificacion" class="bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-bold focus:outline-none focus:border-violet-500">
                                                    <option value="5">⭐⭐⭐⭐⭐</option>
                                                    <option value="4">⭐⭐⭐⭐</option>
                                                    <option value="3">⭐⭐⭐</option>
                                                    <option value="2">⭐⭐</option>
                                                    <option value="1">⭐</option>
                                                </select>
                                                <input type="text" name="comentario" placeholder="Escribe una nota interna (calidad, proveedor, quejas, ideas...)" class="flex-grow bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-violet-500" required>
                                                <button type="submit" class="bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs uppercase tracking-wider px-4 py-2 rounded-xl transition-all shadow-sm whitespace-nowrap">
                                                    Guardar Nota
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <!-- Fila dinámica si no hay coincidencias de búsqueda -->
                            <tr id="sin-coincidencias" class="hidden">
                                <td colspan="8" class="text-center text-gray-500 py-12">
                                    <div class="text-3xl mb-2">🔍</div>
                                    No se encontraron productos que coincidan con la búsqueda.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php require_once $base_path . '/modules/footer.php'; ?>

    <!-- Script del Buscador en Tiempo Real -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const buscador = document.getElementById('buscador');
            if (!buscador) return;

            buscador.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const filas = document.querySelectorAll('.fila-producto');
                const filaSinCoincidencias = document.getElementById('sin-coincidencias');
                let encontrados = 0;

                filas.forEach(fila => {
                    const nombre = fila.querySelector('.buscar-nombre')?.textContent.toLowerCase() || '';
                    const descripcion = fila.querySelector('.buscar-descripcion')?.textContent.toLowerCase() || '';
                    const negocioTag = fila.querySelector('.buscar-negocio');
                    const negocioTexto = negocioTag?.textContent.toLowerCase() || '';
                    const negocioAtributo = negocioTag?.getAttribute('data-negocio')?.toLowerCase() || '';
                    
                    // Comprobar coincidencia en nombre, descripción o negocio
                    const coincide = nombre.includes(query) || 
                                     descripcion.includes(query) || 
                                     negocioTexto.includes(query) || 
                                     negocioAtributo.includes(query);

                    const prodId = fila.getAttribute('data-id');
                    const filaNotas = document.getElementById(`notas-${prodId}`);

                    if (coincide) {
                        fila.style.display = '';
                        encontrados++;
                    } else {
                        fila.style.display = 'none';
                        // Si se oculta el producto, también cerramos su bloque de notas abiertas
                        if (filaNotas) {
                            filaNotas.classList.add('hidden');
                        }
                    }
                });

                // Mostrar u ocultar mensaje de "No hay resultados"
                if (filaSinCoincidencias) {
                    if (encontrados === 0 && query !== '') {
                        filaSinCoincidencias.classList.remove('hidden');
                    } else {
                        filaSinCoincidencias.classList.add('hidden');
                    }
                }
            });
        });
    </script>
</body>
</html>