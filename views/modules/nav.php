<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Capturamos el rol y el nombre de forma segura
$rol_usuario = $_SESSION['user_role'] ?? 'EMPLEADO';
$nombre_usuario = $_SESSION['user_name'] ?? 'INVITADO';
?>
<nav class="bg-slate-950 text-white shadow-md w-full relative z-50">
    <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
        
        <div class="flex items-center gap-3">
            <a href="index.php?url=dashboard" class="text-xl font-black tracking-tight hover:opacity-90 transition-opacity">
                <span class="text-amber-500">El Punto</span> Del Sabor & Papelería
            </a>
        </div>

        <div class="hidden lg:flex items-center gap-2">
            <a href="index.php?url=ventas_comidas" class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs uppercase tracking-wider px-4 py-2 rounded-xl transition-all shadow-sm">
                🍔 Caja Comidas
            </a>
            <a href="index.php?url=ventas_papeleria" class="bg-yellow-400 hover:bg-yellow-500 text-slate-950 font-bold text-xs uppercase tracking-wider px-4 py-2 rounded-xl transition-all shadow-sm">
                ✏️ Caja Papelería
            </a>
            <a href="index.php?url=productos" class="hover:bg-slate-800 text-slate-200 hover:text-white font-semibold text-sm px-4 py-2 rounded-xl transition-colors">
                📦 Inventario
            </a>
            <a href="index.php?url=productos_catalogo" class="hover:bg-slate-800 text-slate-200 hover:text-white font-semibold text-sm px-4 py-2 rounded-xl transition-colors flex items-center gap-1.5">
    📊 Reportes
</a>
        </div>

        <div class="hidden sm:flex items-center gap-4">
            <div class="flex items-center gap-2 bg-slate-900 border border-slate-800 px-3 py-1.5 rounded-xl">
                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                <div class="text-xs font-medium text-slate-300">
                    <span class="font-bold text-white"><?= htmlspecialchars($nombre_usuario); ?></span> 
                    <span class="text-[10px] bg-slate-800 text-slate-400 px-1.5 py-0.5 rounded-md font-mono uppercase tracking-wider ml-1 border border-slate-700">
                        <?= htmlspecialchars($rol_usuario); ?>
                    </span>
                </div>
            </div>
            
            <a href="index.php?url=logout" class="bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs uppercase tracking-wider px-3 py-2 rounded-xl transition-all shadow-sm">
                Salir
            </a>
        </div>

        <div class="flex lg:hidden items-center">
            <button id="btn-menu-movil" class="text-slate-300 hover:text-white focus:outline-none p-2 rounded-xl hover:bg-slate-900 transition-colors">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>

    <div id="menu-movil" class="hidden lg:hidden bg-slate-950 border-t border-slate-900 px-6 py-4 space-y-3 absolute top-16 left-0 w-full shadow-xl">
        <div class="flex flex-col gap-2">
            <a href="index.php?url=ventas_comidas" class="bg-amber-600 hover:bg-amber-700 text-white text-center font-bold text-xs uppercase tracking-wider py-2.5 rounded-xl transition-all block">
                🍔 Caja Comidas
            </a>
            <a href="index.php?url=ventas_papeleria" class="bg-yellow-400 hover:bg-yellow-500 text-slate-950 text-center font-bold text-xs uppercase tracking-wider py-2.5 rounded-xl transition-all block">
                ✏️ Caja Papelería
            </a>
            <a href="index.php?url=productos" class="hover:bg-slate-900 text-slate-200 text-center font-semibold text-sm py-2.5 rounded-xl block transition-colors">
                📦 Inventario
            </a>
           <a href="index.php?url=productos_catalogo" class="hover:bg-slate-900 text-slate-200 text-center font-semibold text-sm py-2.5 rounded-xl block transition-colors">
    📊 Reportes Financieros
</a>
        </div>
        <div class="pt-4 border-t border-slate-900 flex items-center justify-between">
            <div class="text-xs text-slate-300">
                <span class="font-bold text-white"><?= htmlspecialchars($nombre_usuario); ?></span>
                <span class="text-[9px] bg-slate-800 text-slate-400 px-1.5 py-0.5 rounded-md ml-1 font-mono uppercase"><?= htmlspecialchars($rol_usuario); ?></span>
            </div>
            <a href="index.php?url=logout" class="bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs uppercase tracking-wider px-3 py-2 rounded-xl transition-all">
                Salir
            </a>
        </div>
    </div>
</nav>

<script>
    document.getElementById('btn-menu-movil')?.addEventListener('click', function() {
        const menu = document.getElementById('menu-movil');
        menu?.classList.toggle('hidden');
    });
</script>