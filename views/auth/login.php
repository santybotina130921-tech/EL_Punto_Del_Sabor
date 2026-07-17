<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Control de Contabilidad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-login {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .btn-primary-custom {
            background-color: #0d6efd;
            border: none;
        }
        .btn-primary-custom:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center">
    <div class="card card-login p-4">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-dark">Sistema Contable</h3>
            <p class="text-muted small">Comidas Rápidas & Papelería</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center py-2 small" role="alert">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['registro']) && $_GET['registro'] == 'exitoso'): ?>
            <div class="alert alert-success text-center py-2 small" role="alert">
                ¡Registro exitoso! Ya puedes iniciar sesión.
            </div>
        <?php endif; ?>

        <form action="index.php?url=login" method="POST">
            <div class="mb-3">
                <label for="correo" class="form-label small fw-bold">Correo Electrónico</label>
                <input type="email" name="correo" id="correo" class="form-control" placeholder="usuario@correo.com" required autocomplete="email">
            </div>

            <div class="mb-4">
                <label for="password" class="form-label small fw-bold">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary btn-primary-custom w-100 py-2 fw-bold text-white mb-3">
                Ingresar al Sistema
            </button>

            <div class="text-center">
                <a href="index.php?url=registrar" class="text-decoration-none small text-secondary">¿No tienes cuenta? Registrate aquí</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>