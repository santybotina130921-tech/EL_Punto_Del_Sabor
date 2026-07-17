<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuario - Control de Contabilidad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-register {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
        }
        .btn-success-custom {
            background-color: #198754;
            border: none;
        }
        .btn-success-custom:hover {
            background-color: #157347;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center">
    <div class="card card-register p-4">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-dark">Crear Cuenta</h3>
            <p class="text-muted small">Registrar nuevo personal en el sistema</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center py-2 small" role="alert">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <form action="index.php?url=registrar" method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label small fw-bold">Nombre Completo</label>
                <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ej. Juan Pérez" required>
            </div>

            <div class="mb-3">
                <label for="correo" class="form-label small fw-bold">Correo Electrónico</label>
                <input type="email" name="correo" id="correo" class="form-control" placeholder="usuario@correo.com" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label small fw-bold">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
            </div>

            <div class="mb-4">
                <label for="rol" class="form-label small fw-bold">Rol del Usuario</label>
                <select name="rol" id="rol" class="form-select" required>
                    <option value="empleado" selected>Empleado (Solo Ventas/Caja)</option>
                    <option value="admin">Administrador (Control Total e Inventario)</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success btn-success-custom w-100 py-2 fw-bold text-white mb-3">
                Registrar Usuario
            </button>

            <div class="text-center">
                <a href="index.php?url=login" class="text-decoration-none small text-secondary">¿Ya tienes cuenta? Ingresa aquí</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>