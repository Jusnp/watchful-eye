<?php

session_start();

date_default_timezone_set('America/Bogota');

$servername = "localhost"; 
$username_db = "root";     
$password_db = "";
$dbname = "watchful_eye";

$errores = [];
$datos = [];
$max_length = 30;

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username_db, $password_db);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

if (isset($_SESSION['id_persona'])) {
    $id_usuario = filter_var($_SESSION['id_persona'], FILTER_SANITIZE_NUMBER_INT);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $datos['nombre1'] = trim($_POST['nombre1']);
        $datos['apellido1'] = trim($_POST['apellido1']);
        $datos['direccion'] = trim($_POST['direccion']);
        $datos['telefono'] = trim($_POST['telefono']);
        $datos['correo'] = trim($_POST['correo']);
        $datos['fecha_nacimiento'] = trim($_POST['fecha_nacimiento']);
        $datos['password'] = $_POST['password']; 

        if (empty($datos['nombre1'])) {
            $errores['nombre1'] = "El primer nombre es requerido.";
        } elseif (strlen($datos['nombre1']) > $max_length) {
            $errores['nombre1'] = "El primer nombre no puede tener más de " . $max_length . " caracteres.";
        }
        if (empty($datos['apellido1'])) {
            $errores['apellido1'] = "El primer apellido es requerido.";
        } elseif (strlen($datos['apellido1']) > $max_length) {
            $errores['apellido1'] = "El primer apellido no puede tener más de " . $max_length . " caracteres.";
        }
        if (empty($datos['direccion'])) {
            $errores['direccion'] = "La dirección es requerida.";
        } elseif (strlen($datos['direccion']) > $max_length) {
            $errores['direccion'] = "La dirección no puede tener más de " . $max_length . " caracteres.";
        }
        if (empty($datos['telefono'])) {
            $errores['telefono'] = "El teléfono es requerido.";
        } elseif (strlen($datos['telefono']) > $max_length) {
            $errores['telefono'] = "El teléfono no puede tener más de " . $max_length . " caracteres.";
        }
        if (empty($datos['correo'])) {
            $errores['correo'] = "El correo electrónico es requerido.";
        } elseif (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = "El correo electrónico no es válido.";
        } elseif (strlen($datos['correo']) > $max_length) {
            $errores['correo'] = "El correo electrónico no puede tener más de " . $max_length . " caracteres.";
        }
        if (empty($datos['fecha_nacimiento'])) {
            $errores['fecha_nacimiento'] = "La fecha de nacimiento es requerida.";
        } else {
            $fecha_nacimiento = new DateTime($datos['fecha_nacimiento']);
            $fecha_actual = new DateTime();

            if ($fecha_nacimiento > $fecha_actual) {
                $errores['fecha_nacimiento'] = "La fecha de nacimiento no puede ser mayor a la fecha actual.";
            }

            $edad = $fecha_actual->diff($fecha_nacimiento)->y;
            if ($edad < 18) {
                $errores['fecha_nacimiento'] = "Debe ser mayor de 18 años para registrarse.";
            }
        }

        if (!empty($datos['password'])) {
            if (strlen($datos['password']) < 8 || !preg_match('/[A-Z]/', $datos['password']) || !preg_match('/[0-9]/', $datos['password'])) {
                $errores['password'] = "La contraseña debe tener al menos 8 caracteres, una mayúscula y un número.";
            } elseif (strlen($datos['password']) > $max_length) {
                $errores['password'] = "La contraseña no puede tener más de " . $max_length . " caracteres.";
            }
        }

        if (empty($errores)) {
            try {
                $sql = "UPDATE persona SET
                                nombre1 = :nombre1,
                                apellido1 = :apellido1,
                                direccion = :direccion,
                                telefono = :telefono,
                                correo = :correo,
                                fecha_nacimiento = :fecha_nacimiento,
                                password = :password
                                WHERE id_persona = :id_persona";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':nombre1', $datos['nombre1']);
                $stmt->bindParam(':apellido1', $datos['apellido1']);
                $stmt->bindParam(':direccion', $datos['direccion']);
                $stmt->bindParam(':telefono', $datos['telefono']);
                $stmt->bindParam(':correo', $datos['correo']);
                $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nacimiento']);

                if (!empty($datos['password'])) {
                    $stmt->bindParam(':password', password_hash($datos['password'], PASSWORD_DEFAULT));
                } else {

                    $sql_get_current_password = "SELECT password FROM persona WHERE id_persona = :id_persona_password";
                    $stmt_get_current_password = $conn->prepare($sql_get_current_password);
                    $stmt_get_current_password->bindParam(':id_persona_password', $id_usuario, PDO::PARAM_INT);
                    $stmt_get_current_password->execute();
                    $current_password_data = $stmt_get_current_password->fetch(PDO::FETCH_ASSOC);
                    $stmt->bindParam(':password', $current_password_data['password']);
                }
                $stmt->bindParam(':id_persona', $id_usuario, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    echo "<p style='color: green;'>Datos actualizados exitosamente.</p>";

                } else {
                    echo "<p style='color: red;'>Error al actualizar los datos.</p>";
                }

            } catch (PDOException $e) {
                echo "<p style='color: red;'>Error al actualizar en la base de datos: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: red;'>Error al actualizar. Por favor, revise los campos.</p>";
        }
    }


    try {
        $sql_select = "SELECT id_persona, nombre1, apellido1, direccion, telefono, correo, fecha_nacimiento FROM persona WHERE id_persona = :id_usuario";
        $stmt_select = $conn->prepare($sql_select);
        $stmt_select->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt_select->execute();
        $persona = $stmt_select->fetch(PDO::FETCH_ASSOC);

        if ($persona) {
            $datos = $persona; 
        } else {
            echo "<p style='color: red;'>No se encontraron los datos del usuario.</p>";

        }

    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error al obtener los datos del usuario: " . $e->getMessage() . "</p>";
    }

} else {
    echo "<p style='color: yellow;'>No hay sesión de usuario activa. Por favor, inicie sesión.</p>";

    exit();
}

$conn = null;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Actualizar Mis Datos</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #e0e0e0; 
            color: #333; 
            min-height: 100vh;
            position: relative;
            overflow-y: auto;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background-color 0.3s ease, color 0.3s ease;
            flex-direction: column;
            padding: 20px;
            box-sizing: border-box;
        }

        body::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100px;
            height: 100px;
            transform: translate(-50%, -50%);
            background-image: url('imagenes_camaras/all-seeing-eye-symbol-of-religion-spirituality-occultism-illustration-isolated-on-a-dark-background-free-vector.jpg');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.1;
            z-index: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .container {
            position: relative;
            z-index: 1;
            background-color: rgba(255, 255, 255, 0.8); 
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
            text-align: left;
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        h1 {
            color: #8e44ad; 
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            color: #555; 
            margin-bottom: 5px;
            font-size: 0.9em;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="date"],
        .form-group input[type="password"] {
            width: calc(100% - 12px);
            padding: 8px;
            border: 1px solid #ccc; 
            border-radius: 5px;
            background-color: #f9f9f9; 
            color: #333;
            box-sizing: border-box;
            font-size: 1em;
        }

        .error {
            color: #e74c3c; 
            font-size: 0.8em;
            display: block;
            margin-top: 5px;
        }

        .button-container {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .dashboard-button {
            flex-grow: 1;
            padding: 10px 15px;
            background-color: #7957d6; 
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9em;
            text-align: center;
            box-sizing: border-box;
        }

        .dashboard-button:hover {
            background-color: #6741c9; 
        }

        button[type="submit"] {
            flex-grow: 1;
            padding: 10px 20px;
            background-color: #8e44ad; 
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            box-sizing: border-box;
        }

        button[type="submit"]:hover {
            background-color: #7d3c98; 
        }
    </style>
</head>
<body>

    <h1>Actualizar Mis Datos</h1>

    <div class="container">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="id_persona" value="<?php echo htmlspecialchars($persona['id_persona']); ?>">

            <div class="form-group">
                <label for="nombre1">Primer Nombre:</label>
                <input type="text" id="nombre1" name="nombre1" value="<?php echo isset($datos['nombre1']) ? htmlspecialchars($datos['nombre1']) : ''; ?>" maxlength="<?php echo $max_length; ?>">
                <?php if (isset($errores['nombre1'])): ?>
                    <span class="error"><?php echo $errores['nombre1']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="apellido1">Primer Apellido:</label>
                <input type="text" id="apellido1" name="apellido1" value="<?php echo isset($datos['apellido1']) ? htmlspecialchars($datos['apellido1']) : ''; ?>" maxlength="<?php echo $max_length; ?>">
                <?php if (isset($errores['apellido1'])): ?>
                    <span class="error"><?php echo $errores['apellido1']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="direccion">Dirección:</label>
                <input type="text" id="direccion" name="direccion" value="<?php echo isset($datos['direccion']) ? htmlspecialchars($datos['direccion']) : ''; ?>" maxlength="<?php echo $max_length; ?>">
                <?php if (isset($errores['direccion'])): ?>
                    <span class="error"><?php echo $errores['direccion']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" value="<?php echo isset($datos['telefono']) ? htmlspecialchars($datos['telefono']) : ''; ?>" maxlength="<?php echo $max_length; ?>">
                <?php if (isset($errores['telefono'])): ?>
                    <span class="error"><?php echo $errores['telefono']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="correo">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" value="<?php echo isset($datos['correo']) ? htmlspecialchars($datos['correo']) : ''; ?>" maxlength="<?php echo $max_length; ?>">
                <?php if (isset($errores['correo'])): ?>
                    <span class="error"><?php echo $errores['correo']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo isset($datos['fecha_nacimiento']) ? htmlspecialchars($datos['fecha_nacimiento']) : ''; ?>">
                <?php if (isset($errores['fecha_nacimiento'])): ?>
                    <span class="error"><?php echo $errores['fecha_nacimiento']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" value="" maxlength="<?php echo $max_length; ?>" placeholder="Dejar en blanco para no cambiar">
                <?php if (isset($errores['password'])): ?>
                    <span class="error"><?php echo $errores['password']; ?></span>
                <?php endif; ?>
            </div>

            <div class="button-container">
                <button type="submit">Guardar Cambios</button>
                <?php if (isset($persona)): ?>
                    <a href="dashboard.php" class="dashboard-button"> Volver al Panel</a>
                <?php else: ?>
                    <a href="dashboard.php" class="dashboard-button">V Volver al Panel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const nombre1Input = document.getElementById('nombre1');
            const apellido1Input = document.getElementById('apellido1');
            const direccionInput = document.getElementById('direccion');
            const telefonoInput = document.getElementById('telefono');
            const correoInput = document.getElementById('correo');
            const fechaNacimientoInput = document.getElementById('fecha_nacimiento');
            const passwordInput = document.getElementById('password');
            const maxLength = <?php echo $max_length; ?>; 

            function mostrarError(inputElement, mensaje) {
                let errorSpan = inputElement.nextElementSibling;
                if (!errorSpan || !errorSpan.classList.contains('error')) {
                    errorSpan = document.createElement('span');
                    errorSpan.classList.add('error');
                    inputElement.parentNode.appendChild(errorSpan);
                }
                errorSpan.textContent = mensaje;
            }

            function limpiarError(inputElement) {
                const errorSpan = inputElement.nextElementSibling;
                if (errorSpan && errorSpan.classList.contains('error')) {
                    errorSpan.textContent = '';
                }
            }

            nombre1Input.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    mostrarError(this, 'El primer nombre es requerido.');
                } else if (this.value.length > maxLength) {
                    mostrarError(this, `El primer nombre no puede tener más de ${maxLength} caracteres.`);
                } else {
                    limpiarError(this);
                }
            });

            apellido1Input.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    mostrarError(this, 'El primer apellido es requerido.');
                } else if (this.value.length > maxLength) {
                    mostrarError(this, `El primer apellido no puede tener más de ${maxLength} caracteres.`);
                } else {
                    limpiarError(this);
                }
            });

            direccionInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    mostrarError(this, 'La dirección es requerida.');
                } else if (this.value.length > maxLength) {
                    mostrarError(this, `La dirección no puede tener más de ${maxLength} caracteres.`);
                } else {
                    limpiarError(this);
                }
            });

            telefonoInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    mostrarError(this, 'El teléfono es requerido.');
                } else if (this.value.length > maxLength) {
                    mostrarError(this, `El teléfono no puede tener más de ${maxLength} caracteres.`);
                } else {
                    limpiarError(this);
                }
            });

            correoInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    mostrarError(this, 'El correo electrónico es requerido.');
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value)) {
                    mostrarError(this, 'El correo electrónico no es válido.');
                } else if (this.value.length > maxLength) {
                    mostrarError(this, `El correo electrónico no puede tener más de ${maxLength} caracteres.`);
                } else {
                    limpiarError(this);
                }
            });

            fechaNacimientoInput.addEventListener('change', function() {
                if (this.value === '') {
                    mostrarError(this, 'La fecha de nacimiento es requerida.');
                } else {
                    const fechaNacimiento = new Date(this.value);
                    const fechaActual = new Date();
                    if (fechaNacimiento > fechaActual) {
                        mostrarError(this, 'La fecha de nacimiento no puede ser mayor a la fecha actual.');
                    } else {
                        const edad = fechaActual.getFullYear() - fechaNacimiento.getFullYear();
                        const mes = fechaActual.getMonth() - fechaNacimiento.getMonth();
                        if (mes < 0 || (mes === 0 && fechaActual.getDate() < fechaNacimiento.getDate())) {
                            edad--;
                        }
                        if (edad < 18) {
                            mostrarError(this, 'Debe ser mayor de 18 años para registrarse.');
                        } else {
                            limpiarError(this);
                        }
                    }
                }
            });

            passwordInput.addEventListener('input', function() {
                if (this.value !== '') {
                    if (this.value.length < 8 || !/[A-Z]/.test(this.value) || !/[0-9]/.test(this.value)) {
                        mostrarError(this, 'La contraseña debe tener al menos 8 caracteres, una mayúscula y un número.');
                    } else if (this.value.length > maxLength) {
                        mostrarError(this, `La contraseña no puede tener más de ${maxLength} caracteres.`);
                    } else {
                        limpiarError(this);
                    }
                } else {
                    limpiarError(this); 
                }
            });

            form.addEventListener('submit', function(event) {
                let erroresFormulario = false;
                if (nombre1Input.value.trim() === '' || nombre1Input.value.length > maxLength) erroresFormulario = true;
                if (apellido1Input.value.trim() === '' || apellido1Input.value.length > maxLength) erroresFormulario = true;
                if (direccionInput.value.trim() === '' || direccionInput.value.length > maxLength) erroresFormulario = true;
                if (telefonoInput.value.trim() === '' || telefonoInput.value.length > maxLength) erroresFormulario = true;
                if (correoInput.value.trim() === '' || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correoInput.value) || correoInput.value.length > maxLength) erroresFormulario = true;
                if (fechaNacimientoInput.value === '') erroresFormulario = true;
                const fechaNacimiento = new Date(fechaNacimientoInput.value);
                const fechaActual = new Date();
                let edad = fechaActual.getFullYear() - fechaNacimiento.getFullYear();
                const mes = fechaActual.getMonth() - fechaNacimiento.getMonth();
                if (mes < 0 || (mes === 0 && fechaActual.getDate() < fechaNacimiento.getDate())) {
                    edad--;
                }
                if (fechaNacimiento > fechaActual || edad < 18) erroresFormulario = true;
                if (passwordInput.value !== '' && (passwordInput.value.length < 8 || !/[A-Z]/.test(passwordInput.value) || !/[0-9]/.test(passwordInput.value) || passwordInput.value.length > maxLength)) erroresFormulario = true;

                if (erroresFormulario) {
                    alert('Por favor, corrija los errores en el formulario.');
                    event.preventDefault(); 
                }
            });
        });
    </script>

</body>
</html>
