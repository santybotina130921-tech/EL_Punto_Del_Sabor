</main>

    <footer class="bg-slate-950 text-slate-400 text-sm mt-auto w-full border-t border-slate-900/60 shadow-2xl backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-6 py-8 flex flex-col sm:flex-row justify-between items-center gap-6">
            
            <div class="text-center sm:text-left">
                <p class="font-bold text-slate-200 tracking-wide transition-colors duration-200 hover:text-amber-400">
                    &copy; <?php echo date('Y'); ?> <span class="text-amber-500">El Punto del Sabor</span> & Papelería
                </p>
                <p class="text-xs text-slate-500 mt-1 font-medium max-w-sm">
                    Sistema de Gestión de Inventario y Ventas de Alta Eficiencia
                </p>
            </div>
            
            <div class="flex items-center gap-4 sm:gap-6 bg-slate-900/40 px-4 py-2 rounded-xl border border-slate-800/50 backdrop-blur-sm shadow-inner">
                
                <span class="text-xs font-semibold text-emerald-400/90 flex items-center gap-2 tracking-wide select-none">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span> 
                    Supabase Connected
                </span>
                
                <span class="text-slate-700 font-light" aria-hidden="true">|</span>
                
                <span class="text-xs font-bold text-amber-500 tracking-wider uppercase transition-all duration-300 hover:text-amber-400">
                    ADSO &copy;
                </span>
                
            </div>
        </div>
    </footer>

    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- 🤖 CHATBOT FLOTANTE DE AYUDA (OpenAI) -->
    <div id="chatbot-widget" class="fixed bottom-5 right-5 z-[100]">
        <button id="chatbot-toggle" class="bg-amber-600 hover:bg-amber-700 text-white w-14 h-14 rounded-full shadow-xl flex items-center justify-center text-2xl transition-transform hover:scale-105">
            🤖
        </button>

        <div id="chatbot-panel" class="hidden fixed bottom-24 right-5 w-[92vw] max-w-sm h-[70vh] max-h-[520px] bg-white rounded-2xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden">
            <div class="bg-slate-950 text-white px-4 py-3 flex justify-between items-center">
                <span class="font-bold text-sm flex items-center gap-2">🤖 Asistente del Negocio</span>
                <button id="chatbot-cerrar" class="text-slate-400 hover:text-white text-lg leading-none">✕</button>
            </div>
            <div id="chatbot-mensajes" class="flex-grow overflow-y-auto p-4 space-y-3 text-sm bg-slate-50"></div>
            <form id="chatbot-form" class="border-t border-gray-200 p-3 flex gap-2 bg-white">
                <input id="chatbot-input" type="text" placeholder="Escribe tu pregunta..." class="flex-grow bg-slate-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-amber-500" autocomplete="off">
                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold px-4 py-2 rounded-xl text-sm transition-colors">➤</button>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('chatbot-toggle');
        const panel = document.getElementById('chatbot-panel');
        const cerrar = document.getElementById('chatbot-cerrar');
        const form = document.getElementById('chatbot-form');
        const input = document.getElementById('chatbot-input');
        const mensajesDiv = document.getElementById('chatbot-mensajes');

        let historial = [];

        function agregarMensaje(texto, esUsuario) {
            const burbuja = document.createElement('div');
            burbuja.className = esUsuario
                ? 'ml-auto bg-amber-600 text-white rounded-2xl rounded-br-sm px-3 py-2 max-w-[85%] text-sm'
                : 'mr-auto bg-white border border-gray-200 rounded-2xl rounded-bl-sm px-3 py-2 max-w-[85%] text-sm shadow-sm';
            burbuja.textContent = texto;
            mensajesDiv.appendChild(burbuja);
            mensajesDiv.scrollTop = mensajesDiv.scrollHeight;
        }

        toggle.addEventListener('click', () => {
            panel.classList.toggle('hidden');
            if (!panel.classList.contains('hidden') && mensajesDiv.children.length === 0) {
                agregarMensaje('¡Hola! Soy el asistente del negocio. Puedo ayudarte con dudas sobre el sistema, tu inventario o tus ventas. ¿En qué te ayudo?', false);
            }
        });
        cerrar.addEventListener('click', () => panel.classList.add('hidden'));

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const texto = input.value.trim();
            if (!texto) return;

            agregarMensaje(texto, true);
            historial.push({ role: 'user', content: texto });
            input.value = '';
            input.disabled = true;

            const cargando = document.createElement('div');
            cargando.className = 'mr-auto bg-white border border-gray-200 rounded-2xl rounded-bl-sm px-3 py-2 text-sm text-gray-400 shadow-sm';
            cargando.textContent = 'Escribiendo...';
            mensajesDiv.appendChild(cargando);
            mensajesDiv.scrollTop = mensajesDiv.scrollHeight;

            try {
                const resp = await fetch('index.php?url=chatbot_responder', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ mensaje: texto, historial: historial })
                });
                const data = await resp.json();
                cargando.remove();

                if (data.ok) {
                    agregarMensaje(data.respuesta, false);
                    historial.push({ role: 'assistant', content: data.respuesta });
                } else {
                    agregarMensaje('⚠️ ' + (data.error || 'No pude responder en este momento.'), false);
                }
            } catch (err) {
                cargando.remove();
                agregarMensaje('⚠️ Error de conexión con el asistente.', false);
            } finally {
                input.disabled = false;
                input.focus();
            }
        });
    });
    </script>
    <?php endif; ?>

</body>
</html>