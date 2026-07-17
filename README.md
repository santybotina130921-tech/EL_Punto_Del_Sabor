# contabilidad
proyecto de contabilidad de papeleria y de comidad rapidas

## Variables de entorno necesarias

Configura estas variables en tu hosting/Docker (NUNCA las escribas directo en el código):

| Variable | Para qué sirve | Obligatoria |
|---|---|---|
| `DB_PASS` | Contraseña de tu base de datos Supabase | ✅ Sí, el sistema no arranca sin ella |
| `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` | Datos de conexión a Supabase | No (ya tienen valores por defecto) |
| `GEMINI_API_KEY` | Generador de imágenes de productos con IA (Google Gemini) | Solo si usarás el generador de imágenes |
| `GROQ_API_KEY` | Chatbot de ayuda (Groq, gratis, sin tarjeta) | Solo si usarás el chatbot |

### Cómo conseguir las claves gratis
- **Gemini**: entra a https://aistudio.google.com/apikey con tu cuenta de Google → "Create API key". La clave debe empezar por `AIzaSy...`. ⚠️ **Necesitas vincular una tarjeta a tu proyecto de Google Cloud** (Billing) para que la cuota de generación de imágenes deje de estar en 0 — el uso normal se mantiene en $0 dentro de la franja gratis, pero configura una alerta de presupuesto baja (ej. $2) en Google Cloud → Billing → Budgets & alerts, por seguridad.
- **Groq**: entra a https://console.groq.com/keys → "Create API Key". La clave empieza por `gsk_...`.

⚠️ Si alguna vez compartes una clave por error (en un chat, en GitHub, etc.), crea una nueva y borra la vieja desde la página del proveedor — una clave expuesta debe darse por comprometida.
