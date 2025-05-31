<?php 
include('db.php');
session_start();

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $password = $_POST['password'];
    
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Correo inválido";
    } elseif (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/\d/", $password)) {
        $mensaje = "La contraseña no cumple los requisitos mínimos";
    } else {

        $stmt = $conn->prepare("SELECT * FROM intentos_login WHERE correo=?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $intento = $stmt->get_result()->fetch_assoc();

        if ($intento && $intento['bloqueado_hasta'] > date('Y-m-d H:i:s')) {
            $mensaje = "Tu cuenta está bloqueada. Intenta después de " . $intento['bloqueado_hasta'];
        } else {

            $stmt = $conn->prepare("SELECT * FROM persona WHERE correo=?");
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // Login exitoso
                    $_SESSION['usuario'] = $correo;
                    $_SESSION['id_persona'] = $user['id_persona'];
                    $_SESSION['tipo_usuario'] = $user['tipo_persona'];
                    $_SESSION['last_activity'] = time();
                    session_regenerate_id(true);

                    // Limpiar intentos
                    $stmt = $conn->prepare("DELETE FROM intentos_login WHERE correo=?");
                    $stmt->bind_param("s", $correo);
                    $stmt->execute();

                    header("Location: dashboard.php");
                    exit();
                } else {

                    $mensaje = "Credenciales inválidas";

                    if ($intento) {
                        $intentos = $intento['intentos'] + 3;
                        $bloqueado_hasta = ($intentos >= 3) ? date('Y-m-d H:i:s', strtotime('+10 minutes')) : null;

                        $stmt = $conn->prepare("UPDATE intentos_login SET intentos=?, bloqueado_hasta=? WHERE correo=?");
                        $stmt->bind_param("iss", $intentos, $bloqueado_hasta, $correo);
                        $stmt->execute();
                    } else {
                        $stmt = $conn->prepare("INSERT INTO intentos_login (correo, intentos, bloqueado_hasta) VALUES (?, 1, NULL)");
                        $stmt->bind_param("s", $correo);
                        $stmt->execute();
                    }
                }
            } else {

                $mensaje = "Credenciales inválidas";

                if ($intento) {
                    $intentos = $intento['intentos'] + 1;
                    $bloqueado_hasta = ($intentos >= 3) ? date('Y-m-d H:i:s', strtotime('+10 minutes')) : null;

                    $stmt = $conn->prepare("UPDATE intentos_login SET intentos=?, bloqueado_hasta=? WHERE correo=?");
                    $stmt->bind_param("iss", $intentos, $bloqueado_hasta, $correo);
                    $stmt->execute();
                } else {
                    $stmt = $conn->prepare("INSERT INTO intentos_login (correo, intentos, bloqueado_hasta) VALUES (?, 1, NULL)");
                    $stmt->bind_param("s", $correo);
                    $stmt->execute();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Seguro</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #2d2d2d; /* Gris oscuro */
            color: #e0e0e0; /* Gris claro */
            height: 100vh;
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        body::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 400px;
            height: 400px;
            transform: translate(-50%, -50%);
            background-image: url('imagenes_camaras/all-seeing-eye-symbol-of-religion-spirituality-occultism-illustration-isolated-on-a-dark-background-free-vector.jpg');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.05;
            z-index: 0;
            pointer-events: none;
        }

        .container {
            position: relative;
            z-index: 1;
            background-color: rgba(45, 45, 45, 0.9); 
            max-width: 600px;
            margin: 20px;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(123, 31, 162, 0.3); 
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-around;
            border: 1px solid #7b1fa2; 
        }

        .login-form {
            display: flex;
            flex-direction: column;
            width: 45%;
        }

        .login-form h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #ba68c8; 
        }

       .login-form input[type="email"],
.login-form input[type="password"],
.login-form input[type="submit"] {
    padding: 10px;
    margin-bottom: 15px;
    border: none;
    border-radius: 5px;
    background-color: rgba(255, 255, 255, 0.1);
    color: #e0e0e0; 
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #7b1fa2; 
}

.login-form input[type="submit"] {
    background-color: #7b1fa2; 
    margin-top: 10px;
    color: white;
    font-weight: bold;
    transition: background-color 0.3s;
}

.login-form input[type="submit"]:hover {
    background-color: #9c27b0; 
}

        .login-form .validation-error {
            color: #ff9800;
            font-size: 0.9em;
            margin-top: -10px;
            margin-bottom: 10px;
        }

        .login-form .toggle-password {
            cursor: pointer;
            float: right;
            margin-top: -30px;
            margin-right: 10px;
            color: #ba68c8; 
        }

        .login-form a {
            color: #ba68c8; 
            text-decoration: none;
            text-align: center;
            margin-top: 15px;
            display: block;
            transition: color 0.3s;
        }

        .login-form a:hover {
            color: #e1bee7; 
            text-decoration: underline;
        }

        .error {
            color: #ef5350; 
            text-align: center;
            margin-top: 10px;
        }

        .button-container {
            text-align: center;
            margin-top: 20px;
        }

        .button-container button {
            padding: 10px;
            width: 100%;
            box-sizing: border-box;
            background: #4a4a4a; 
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            border: 1px solid #7b1fa2; 
        }

        .button-container button:hover {
            background: #7b1fa2; 
        }

        .login-image {
            width: 45%;
            text-align: center;
        }

        .login-image img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(123, 31, 162, 0.5); 
            border: 1px solid #7b1fa2; 
        }

        .register-link {
            margin-top: 15px;
            text-align: center;
        }

        .register-link a {
            color: #ba68c8; 
            text-decoration: none;
        }

        .register-link a:hover {
            color: #e1bee7; 
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                align-items: stretch;
            }
            .login-form, .login-image {
                width: 100%;
                margin-bottom: 20px;
            }
            .login-image img {
                max-height: 200px;
            }
        }

        .password-wrapper {
            position: relative;
            width: 100%;
            margin-bottom: 15px;
        }

        .password-wrapper input {
            width: 100%;
            padding-right: 40px;
            box-sizing: border-box;
        }

        .password-wrapper #toggle {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #ba68c8; 
            font-size: 1.1em;
            user-select: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h2>Iniciar Sesión</h2>
            <form method="POST" onsubmit="return validarFormularioFinal();">
                <input type="email" name="correo" id="correo" placeholder="Correo" 
                       onkeyup="validarCorreo()" maxlength="30" required>
                <span id="correoError" class="validation-error"></span>

                <div class="password-wrapper">
                    <input type="password" name="password" id="password" placeholder="Contraseña" 
                           onkeyup="validarPassword()" maxlength="30" required>
                    <span id="toggle" onclick="togglePassword()">👁️</span>
                </div>
                <span id="passwordError" class="validation-error"></span>

                <input type="submit" value="Entrar">
            </form>

            <p style="text-align:center;"><a href="recuperar.php">¿Olvidaste tu contraseña?</a></p>

            <div class="register-link">
                ¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a>
            </div>

            <?php if ($mensaje) echo "<p class='error'>$mensaje</p>"; ?>

            <div class="button-container">
                <button onclick="window.location.href='index.php'">Regresar al Inicio</button>
            </div>
        </div>
        <div class="login-image">
            <img src="imagenes_camaras/all-seeing-eye-symbol-of-religion-spirituality-occultism-illustration-isolated-on-a-dark-background-free-vector.jpg" alt="Imagen de Seguridad">
        </div>
    </div>

    <script>
       function togglePassword() {
            const pass = document.getElementById("password");
            const toggle = document.getElementById("toggle");

            if (pass.type === "password") {
                pass.type = "text";
                toggle.textContent = "🙈";
            } else {
                pass.type = "password";
                toggle.textContent = "👁️";
            }
        }

        function validarCorreo() {
            const correoInput = document.getElementById("correo");
            const correoError = document.getElementById("correoError");
            const correoFormato = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (correoInput.value.length > 30) {
                correoError.textContent = "Máximo 30 caracteres";
                return false;
            }
            
            if (!correoFormato.test(correoInput.value)) {
                correoError.textContent = "Formato inválido";
                return false;
            } else {
                correoError.textContent = "";
                return true;
            }
        }

        function validarPassword() {
            const passInput = document.getElementById("password");
            const passError = document.getElementById("passwordError");
            const passFormato = /^(?=.*[A-Z])(?=.*\d).{8,}$/;
            
            if (passInput.value.length > 30) {
                passError.textContent = "Máximo 30 caracteres";
                return false;
            }
            
            if (!passFormato.test(passInput.value)) {
                passError.textContent = "Debe tener al menos 8 caracteres, una mayúscula y un número";
                return false;
            } else {
                passError.textContent = "";
                return true;
            }
        }

        function validarFormularioFinal() {
            const correoValido = validarCorreo();
            const passwordValido = validarPassword();
            return correoValido && passwordValido;
        }
    </script>
</body>
</html>
