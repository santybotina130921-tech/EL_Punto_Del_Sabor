-- ============================================================
-- MIGRACIÓN: nuevas funciones (gastos, reseñas internas)
-- Ejecuta este script UNA SOLA VEZ en el SQL Editor de Supabase.
-- Es seguro volver a correrlo (usa IF NOT EXISTS).
-- ============================================================

-- Tabla de EGRESOS / GASTOS manuales (arriendo, servicios, insumos, etc.)
CREATE TABLE IF NOT EXISTS gastos (
    id SERIAL PRIMARY KEY,
    descripcion VARCHAR(255) NOT NULL,
    monto NUMERIC(10,2) NOT NULL,
    tipo_negocio VARCHAR(20) DEFAULT 'general', -- comidas | papeleria | general
    usuario_id INTEGER,
    fecha_gasto TIMESTAMP NOT NULL DEFAULT now()
);

-- Tabla de RESEÑAS / NOTAS INTERNAS sobre productos (solo administrador/empleados)
CREATE TABLE IF NOT EXISTS resenas (
    id SERIAL PRIMARY KEY,
    producto_id INTEGER NOT NULL REFERENCES productos(id) ON DELETE CASCADE,
    usuario_id INTEGER,
    calificacion INTEGER CHECK (calificacion BETWEEN 1 AND 5),
    comentario TEXT,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_resenas_producto ON resenas(producto_id);
CREATE INDEX IF NOT EXISTS idx_gastos_fecha ON gastos(fecha_gasto);
