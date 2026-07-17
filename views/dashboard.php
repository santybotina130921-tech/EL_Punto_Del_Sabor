<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - El Punto del Sabor</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 font-sans text-gray-800 flex flex-col min-h-screen">

    <nav class="bg-amber-600 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold tracking-wide">🌶️ El Punto del Sabor</h1>
            <div class="flex items-center space-x-6">
                <span class="text-sm bg-amber-700 px-3 py-1 rounded-full">¡Hola, Desarrollador!</span>
                <a href="index.php?url=logout" class="hover:text-amber-200 transition-colors font-medium">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <main class="flex-grow max-w-7xl mx-auto px-4 py-10 w-full">
        <header class="mb-8">
            <h2 class="text-3xl font-extrabold text-gray-900">Panel de Administración</h2>
            <p class="text-gray-600 mt-2">Gestiona las ventas y el inventario de forma automática.</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="text-amber-500 text-3xl mb-4">🍔</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Caja de Comidas</h3>
                <p class="text-gray-600 text-sm mb-4">Registra pedidos de comidas tradicionales y restaurante.</p>
                <a href="index.php?url=ventas_comidas" class="inline-block bg-amber-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-amber-700 transition-colors">Abrir Caja</a>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="text-blue-500 text-3xl mb-4">📚</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Caja de Papelería</h3>
                <p class="text-gray-600 text-sm mb-4">Módulo de ventas rápido para útiles y fotocopias.</p>
                <a href="index.php?url=ventas_papeleria" class="inline-block bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">Abrir Caja</a>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="text-emerald-500 text-3xl mb-4">📦</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Inventario</h3>
                <p class="text-gray-600 text-sm mb-4">Control inteligente del stock conectado a Supabase.</p>
                <a href="index.php?url=productos" class="inline-block bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-emerald-700 transition-colors">Gestionar Stock</a>
            </div>

        </div>
    </main>

    <footer class="bg-gray-900 text-gray-400 text-center py-4 border-t border-gray-800 text-sm">
        &copy; <?php echo date('Y'); ?> El Punto del Sabor - Gestión de Software Avanzada.
    </footer>

</body>
</html>