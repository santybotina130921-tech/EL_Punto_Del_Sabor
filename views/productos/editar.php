<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - Control Contable</title>
    <!-- Tailwind CSS v4 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        body { background-color: #fcf8f2; }
    </style>
</head>
<body class="font-sans text-gray-800 min-h-screen flex flex-col">

<!-- NAVBAR INTEGRADO -->
<?php require_once __DIR__ . '/../modules/nav.php'; ?>

<!-- CONTENEDOR PRINCIPAL -->
<div class="flex-grow container mx-auto my-10 px-4 flex justify-center items-center">
    <div class="w-full max-w-2xl bg-white rounded-2xl border border-gray-200 shadow-md p-6 sm:p-8">
        
        <!-- Encabezado con imagen de vista previa actual -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-2xl font-black text-slate-950 flex items-center gap-2">
                    ✏️ Editar Producto
                </h3>
                <p class="text-gray-500 text-xs mt-1">Modifica los precios, stock o imágenes en tiempo real.</p>
            </div>
            <!-- Muestra la imagen actual del producto desde su ruta asignada -->
            <img src="<?= !empty($producto->imagen) ? htmlspecialchars($producto->imagen) : (!empty($producto->imagen_url) ? htmlspecialchars($producto->imagen_url) : 'uploads/default.png'); ?>" 
                 class="w-20 h-20 object-cover rounded-xl border border-gray-200 shadow-sm animate-fade-in" alt="Vista previa actual">
        </div>

        <hr class="border-gray-100 mb-6">

        <!-- Alerta de Error -->
        <?php if (isset($error)): ?>
            <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 text-center py-2 px-4 rounded-xl text-xs font-semibold" role="alert">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de Edición -->
        <form action="index.php?url=productos_editar&id=<?= $producto->id; ?>" method="POST" enctype="multipart/form-data" class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">Nombre del Producto</label>
                    <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($producto->nombre); ?>" class="w-full bg-slate-50 border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-amber-500 focus:bg-white transition-colors" required>
                </div>

                <!-- Tipo de Negocio -->
                <div>
                    <label for="tipo_negocio" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">Tipo de Negocio</label>
                    <select name="tipo_negocio" id="tipo_negocio" class="w-full bg-slate-50 border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-amber-500 focus:bg-white transition-colors" required>
                        <option value="comidas" <?= $producto->tipo_negocio === 'comidas' ? 'selected' : ''; ?>>🍔 El Punto del Sabor (Comidas Rápidas)</option>
                        <option value="papeleria" <?= $producto->tipo_negocio === 'papeleria' ? 'selected' : ''; ?>>✏️ Papelería (Útiles / Servicios)</option>
                    </select>
                </div>

                <!-- Descripción -->
                <div class="sm:col-span-2">
                    <label for="descripcion" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">Descripción / Detalles</label>
                    <textarea name="descripcion" id="descripcion" class="w-full bg-slate-50 border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-amber-500 focus:bg-white transition-colors" rows="2" placeholder="Ingredientes, tamaño o marca del artículo..."><?= htmlspecialchars($producto->descripcion); ?></textarea>
                </div>

                <!-- Precio Compra -->
                <div>
                    <label for="precio_compra" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">Precio de Compra ($)</label>
                    <input type="number" step="0.01" name="precio_compra" id="precio_compra" value="<?= htmlspecialchars($producto->precio_compra); ?>" class="w-full bg-slate-50 border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-amber-500 focus:bg-white transition-colors" required>
                </div>

                <!-- Precio Venta -->
                <div>
                    <label for="precio_venta" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">Precio de Venta ($)</label>
                    <input type="number" step="0.01" name="precio_venta" id="precio_venta" value="<?= htmlspecialchars($producto->precio_venta); ?>" class="w-full bg-slate-50 border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-amber-500 focus:bg-white transition-colors" required>
                </div>

                <!-- Stock Actual -->
                <div class="sm:col-span-2 md:col-span-1">
                    <label for="stock" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">Stock Actual</label>
                    <input type="number" name="stock" id="stock" value="<?= htmlspecialchars($producto->stock); ?>" class="w-full bg-slate-50 border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-amber-500 focus:bg-white transition-colors" required>
                </div>

                <!-- Subida de Imagen desde PC -->
                <div class="sm:col-span-2">
                    <label for="imagen" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">Nueva Imagen del Producto (subir desde tu PC)</label>
                    <input type="file" name="imagen" id="imagen" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 file:cursor-pointer cursor-pointer border border-gray-200 rounded-xl bg-slate-50" accept="image/*">
                    <p class="text-[11px] text-gray-400 mt-1.5">Selecciona un archivo solo si deseas reemplazar la imagen actual, o genera una nueva con IA abajo.</p>
                </div>

                <!-- 🎨 GENERADOR DE IMÁGENES CON IA -->
                <div class="sm:col-span-2">
                    <div class="bg-violet-50 border border-violet-200 rounded-xl p-4">
                        <label class="block text-xs font-bold uppercase tracking-wider text-violet-700 mb-1.5">
                            🎨 O genera una nueva imagen con Inteligencia Artificial
                        </label>
                        <p class="text-[11px] text-violet-500 mb-3">Se generará 1 imagen a partir del nombre y descripción.</p>

                        <button type="button" id="btn-generar-ia" class="bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs uppercase tracking-wider px-4 py-2.5 rounded-xl transition-all shadow-sm cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            ✨ Generar imagen con IA
                        </button>

                        <div id="ia-estado" class="text-xs text-violet-600 mt-2 hidden"></div>

                        <div id="ia-resultados" class="grid grid-cols-3 gap-3 mt-4 hidden"></div>

                        <div id="ia-seleccionada" class="mt-3 hidden">
                            <p class="text-[11px] font-bold text-emerald-600 mb-1.5">✅ Imagen seleccionada:</p>
                            <img id="ia-preview-final" src="" class="w-24 h-24 object-cover rounded-xl border-2 border-emerald-400 shadow-sm" alt="Imagen elegida">
                        </div>

                        <input type="hidden" name="imagen_ia_path" id="imagen_ia_path" value="">
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex gap-3 justify-end pt-4 border-t border-gray-100">
                <a href="index.php?url=productos" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-xl transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-xl transition-all shadow-sm">
                    Actualizar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- FOOTER INTEGRADO -->
<?php require_once __DIR__ . '/../modules/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btnGenerar = document.getElementById('btn-generar-ia');
    const estadoDiv = document.getElementById('ia-estado');
    const resultadosDiv = document.getElementById('ia-resultados');
    const seleccionadaDiv = document.getElementById('ia-seleccionada');
    const previewFinal = document.getElementById('ia-preview-final');
    const inputPathIA = document.getElementById('imagen_ia_path');
    const inputArchivo = document.getElementById('imagen');
    const inputNombre = document.getElementById('nombre');
    const inputDescripcion = document.getElementById('descripcion');
    const selectNegocio = document.getElementById('tipo_negocio');

    function mostrarEstado(texto, esError = false) {
        estadoDiv.textContent = texto;
        estadoDiv.classList.remove('hidden');
        estadoDiv.classList.toggle('text-rose-600', esError);
        estadoDiv.classList.toggle('text-violet-600', !esError);
    }

    let rutasGeneradas = [];

    btnGenerar.addEventListener('click', async () => {
        const nombre = inputNombre.value.trim();
        if (!nombre) {
            mostrarEstado('⚠️ Primero escribe el nombre del producto.', true);
            inputNombre.focus();
            return;
        }

        const prompt = (nombre + ' ' + inputDescripcion.value.trim()).trim();

        btnGenerar.disabled = true;
        btnGenerar.textContent = '⏳ Generando (puede tardar 15-30 seg)...';
        resultadosDiv.classList.add('hidden');
        seleccionadaDiv.classList.add('hidden');
        mostrarEstado('Generando la imagen, espera un momento...');

        try {
            const resp = await fetch('index.php?url=generar_imagen_ia', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ prompt: prompt, tipo_negocio: selectNegocio.value })
            });
            const data = await resp.json();

            if (!data.ok) {
                mostrarEstado('❌ ' + (data.error || 'No se pudieron generar las imágenes.'), true);
            } else {
                rutasGeneradas = data.images;
                resultadosDiv.innerHTML = '';
                data.images.forEach((ruta) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'cursor-pointer group';
                    wrapper.innerHTML = `
                        <img src="${ruta}" class="w-full h-28 object-cover rounded-xl border-2 border-transparent group-hover:border-violet-500 transition-all shadow-sm" loading="lazy">
                        <p class="text-[10px] text-center text-violet-500 mt-1 font-bold uppercase">Usar esta</p>
                    `;
                    wrapper.addEventListener('click', () => elegirImagen(ruta, wrapper));
                    resultadosDiv.appendChild(wrapper);
                });
                resultadosDiv.classList.remove('hidden');
                mostrarEstado('👆 Haz clic en la imagen que quieras usar.');
            }
        } catch (err) {
            mostrarEstado('❌ Error de conexión al generar las imágenes.', true);
        } finally {
            btnGenerar.disabled = false;
            btnGenerar.textContent = '✨ Generar imagen con IA';
        }
    });

    async function elegirImagen(ruta, wrapperElegido) {
        [...resultadosDiv.children].forEach(w => w.querySelector('img').classList.remove('border-violet-500'));
        wrapperElegido.querySelector('img').classList.add('border-violet-500');
        mostrarEstado('Guardando la imagen elegida...');

        try {
            const params = new URLSearchParams();
            params.append('ruta_temporal', ruta);
            params.append('todas_las_rutas', JSON.stringify(rutasGeneradas));

            const resp = await fetch('index.php?url=guardar_imagen_ia', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            });
            const data = await resp.json();

            if (data.ok) {
                inputPathIA.value = data.path;
                // Si el usuario selecciona la imagen de IA, limpiamos el campo de subida manual
                inputArchivo.value = '';
                previewFinal.src = data.path;
                seleccionadaDiv.classList.remove('hidden');
                mostrarEstado('✅ Imagen lista. Ya puedes actualizar el producto.');
            } else {
                mostrarEstado('❌ ' + (data.error || 'No se pudo guardar la imagen.'), true);
            }
        } catch (err) {
            mostrarEstado('❌ Error de conexión al guardar la imagen.', true);
        }
    }
});
</script>

</body>
</html>