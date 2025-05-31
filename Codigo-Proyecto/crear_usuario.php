<?php
include('db.php');
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

function crear_persona($conn, $nombre1, $apellido1, $tipo_documento, $numero_documento, $direccion, $telefono, $correo, $tipo_persona, $fecha_nacimiento, $rol, $password) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO persona (nombre1, apellido1, tipo_documento, numero_documento, direccion, telefono, correo, tipo_persona, fecha_nacimiento, rol, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssss", $nombre1, $apellido1, $tipo_documento, $numero_documento, $direccion, $telefono, $correo, $tipo_persona, $fecha_nacimiento, $rol, $hashed_password);
    return $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre1 = $_POST['nombre1'] ?? '';
    $apellido1 = $_POST['apellido1'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $tipo_persona = $_POST['tipo_persona'] ?? 'P';
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
    $rol = $_POST['rol'] ?? '';
    $password = $_POST['password'] ?? '';
    $fecha_actual = new DateTime();
    $fecha_nac = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
    $edad = $fecha_nac ? $fecha_nac->diff($fecha_actual)->y : 0;

    if ($edad < 18) {
        $_SESSION['error'] = "El usuario debe ser mayor de 18 años.";
        header("Location: crear_usuario.php");
        exit();
    }

    if (crear_persona($conn, $nombre1, $apellido1, $tipo_documento, $numero_documento, $direccion, $telefono, $correo, $tipo_persona, $fecha_nacimiento, $rol, $password)) {
        $_SESSION['mensaje'] = "Usuario creado exitosamente.";
        header("Location: admin_panel.php");
        exit();
    } else {
        $_SESSION['error'] = "Error al crear el usuario.";
    }
}

$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

if (isset($_GET['toggle_theme'])) {
    $new_theme = ($theme === 'light') ? 'dark' : 'light';
    setcookie('theme', $new_theme, time() + (86400 * 30), "/"); 
    header("Location: crear_usuario.php"); 
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Crear Nuevo Usuario</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: <?php echo ($theme === 'dark') ? '#121212' : '#e0e0e0'; ?>;
            color: <?php echo ($theme === 'dark') ? '#f0f0f0' : '#333'; ?>;
            min-height: 100vh;
            position: relative;
            overflow-x: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body::before {
            content: "";
            position: fixed;
            top: 10px;
            left: 10px;
            width: 60px;
            height: 60px;
            background-image: url('imagenes_camaras/<?php echo ($theme === 'dark') ? 'vecteezy_esoteric-eye-in-triangle_16927071.jpg' : 'vecteezy_esoteric-eye-in-triangle_16927071.jpg'; ?>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: top left;
            opacity: <?php echo ($theme === 'dark') ? '0.5' : '0.1'; ?>;
            z-index: 0;
            pointer-events: none;
            filter: <?php echo ($theme === 'dark') ? 'brightness(100%)' : 'brightness(100%)'; ?>;
        }

        .container {
            position: relative;
            z-index: 1;
            background-color: <?php echo ($theme === 'dark') ? 'rgba(30, 30, 30, 0.8)' : 'rgba(255, 255, 255, 0.8)'; ?>;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 700px;
            margin-top: 60px;
        }

        h2 {
            color: #8e44ad;
            text-align: center;
            margin-bottom: 25px;
        }

        .form-container {
            margin-top: 20px;
            padding: 25px;
            border: 1px solid <?php echo ($theme === 'dark') ? '#424242' : '#ccc'; ?>;
            border-radius: 8px;
            background-color: <?php echo ($theme === 'dark') ? 'rgba(40, 40, 40, 0.8)' : 'rgba(240, 240, 240, 0.8)'; ?>;
        }

        .form-container h3 {
            color: #8e44ad;
            margin-top: 0;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: <?php echo ($theme === 'dark') ? '#e0e0e0' : '#555'; ?>;
        }

        input[type="text"], input[type="email"], input[type="tel"], input[type="date"], input[type="password"], select {
            width: calc(100% - 16px);
            padding: 10px;
            border: 1px solid <?php echo ($theme === 'dark') ? '#616161' : '#aaa'; ?>;
            border-radius: 5px;
            box-sizing: border-box;
            background-color: <?php echo ($theme === 'dark') ? '#333' : '#fff'; ?>;
            color: <?php echo ($theme === 'dark') ? '#f0f0f0' : '#333'; ?>;
            font-size: 1em;
        }

        button {
            background-color: #8e44ad;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #7d3c98;
        }

        .button-link {
            display: inline-block;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1em;
            margin-top: 10px;
            background-color: #6c757d;
            color: white;
            transition: background-color 0.3s ease;
        }

        .button-link:hover {
            background-color: #545b62;
        }

        .error {
            color: #dc3545;
            margin-top: 10px;
            text-align: center;
        }

        .success {
            color: #28a745;
            margin-top: 10px;
            text-align: center;
        }

        .back-link {
            margin-top: 25px;
            text-align: center;
        }

        .back-link a {
            color: #8e44ad;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .theme-toggle-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 8px 12px;
            background-color: #8e44ad;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8em;
            z-index: 2;
            transition: background-color 0.3s ease;
        }

        .theme-toggle-button:hover {
            background-color: #7d3c98;
        }

        .error-message {
            color: #ff6f6f;
            font-size: 0.9em;
            margin-top: 5px;
        }
    </style>
</head>
<body class="<?php echo ($theme === 'dark') ? 'dark-mode' : 'light-mode'; ?>">
    <a href="?toggle_theme=true" class="theme-toggle-button">
        <?php echo ($theme === 'dark') ? 'Claro' : 'Oscuro'; ?>
    </a>
    <div class="container">
        <h2>Crear Nuevo Usuario</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="post" id="usuarioForm">
                <div class="form-group">
                    <label for="nombre1">Nombre:</label>
                    <input type="text" id="nombre1" name="nombre1" maxlength="30" required>
                </div>

                <div class="form-group">
                    <label for="apellido1">Apellido:</label>
                    <input type="text" id="apellido1" name="apellido1" maxlength="30" required>
                </div>
                <div class="form-group">
                    <label for="documento">Número de Documento:</label>
                    <input type="text" id="documento" name="numero_documento" maxlength="10" required>
                </div>
                <div class="form-group">
                    <label for="tipo_documento">Tipo de Documento:</label>
                    <select id="tipo_documento" name="tipo_documento" required>
                        <option value="CC">Cédula de Ciudadanía</option>
                        <option value="CE">Cédula de Extranjería</option>
                        <option value="P">Pasaporte</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" maxlength="30">
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" maxlength="30">
                </div>

                <div class="form-group">
                    <label for="correo">Correo:</label>
                    <input type="email" id="correo" name="correo" maxlength="30" required>
                    <div id="correoError" class="error-message"></div>
                </div>

                <div class="form-group">
                    <label for="tipo_persona">Tipo de Persona:</label>
                    <select id="tipo_persona" name="tipo_persona">
                        <option value="P">Propietario</option>
                        <option value="X">Administrador</option>
                        <option value="V">Vigilante</option>
                        <option value="A">Arrendatario</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                    <?php
                        $mayoria_edad = date('Y-m-d', strtotime('-18 years'));
                    ?>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" max="<?php echo $mayoria_edad; ?>">
                </div>

                <div class="form-group">
                    <label for="rol">Rol:</label>
                    <input type="text" id="rol" name="rol" maxlength="30">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" maxlength="30" required>
                    <div id="passwordError" class="error-message"></div>
                </div>

                <button type="submit">Crear Usuario</button>
                <a href="admin_panel.php" class="button-link">Cancelar</a>
            </form>
        </div>

        <p class="back-link"><a href="admin_panel.php">Volver a la lista de usuarios</a></p>
    </div>

    <script>
        const body = document.body;
        const theme = '<?php echo $theme; ?>';
        if (theme === 'dark') {
            body.classList.add('dark-mode');
        } else {
            body.classList.add('light-mode');
        }

        function validarCorreo() {
            const correoInput = document.querySelector('input[name="correo"]');
            const correoError = document.getElementById('correoError');
            const correoFormato = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!correoFormato.test(correoInput.value)) {
                correoError.textContent = "Formato inválido";
                return false;
            } else {
                correoError.textContent = "";
                return true;
            }
        }

        function validarPassword() {
            const passInput = document.querySelector('input[name="password"]');
            const passError = document.getElementById('passwordError');
            const passFormato = /^(?=.*[A-Z])(?=.*\d).{8,}$/;

            if (!passFormato.test(passInput.value)) {
                passError.textContent = "Debe tener al menos 8 caracteres, una mayúscula y un número";
                return false;
            } else {
                passError.textContent = "";
                return true;
            }
        }

        function validarFecha() {
            const fechaInput = document.getElementById('fecha_nacimiento');
            const fechaSeleccionada = new Date(fechaInput.value);
            const fechaActual = new Date();

            if (fechaSeleccionada > fechaActual) {
                alert('La fecha de nacimiento no puede ser mayor a la fecha actual');
                return false;
            }
            return true;
        }

        function limitarLongitud(input, maxLength) {
            if (input.value.length > maxLength) {
                input.value = input.value.substring(0, maxLength);
            }
        }

        document.getElementById('usuarioForm').addEventListener('submit', function(event) {
            if (!validarCorreo() || !validarPassword() || !validarFecha()) {
                event.preventDefault();
            }
        });

        document.querySelector('input[name="correo"]').addEventListener('blur', validarCorreo);
        document.querySelector('input[name="password"]').addEventListener('blur', validarPassword);
        document.getElementById('fecha_nacimiento').addEventListener('change', validarFecha);

        const textInputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"]');
        textInputs.forEach(input => {
            input.addEventListener('input', function() {
                limitarLongitud(this, 30);
            });
        });
    </script>
</body>
</html>
