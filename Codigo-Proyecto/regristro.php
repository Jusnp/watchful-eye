<?php
session_start();
include("db.php");

$errores = [];
$form_data = [];
$mensaje = "";

$torres_query = $conn->query("SELECT DISTINCT id_torre FROM apartamento");
$apartamentos_query = $conn->query("SELECT num_apt, id_torre FROM apartamento");
$all_apartamentos = $apartamentos_query->fetch_all(MYSQLI_ASSOC);

if (isset($_SESSION['errores'])) {
    $errores = $_SESSION['errores'];
    $form_data = $_SESSION['form_data'];
    unset($_SESSION['errores'], $_SESSION['form_data']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form_data = [
        'nombre1' => trim($_POST['nombre1']),
        'apellido1' => trim($_POST['apellido1']),
        'tipo_documento' => $_POST['tipo_documento'],
        'numero_documento' => trim($_POST['numero_documento']),
        'correo' => trim($_POST['correo']),
        'telefono' => trim($_POST['telefono']),
        'direccion' => trim($_POST['direccion']),
        'clave' => $_POST['clave'],
        'confirmar_clave' => $_POST['confirmar_clave'] ?? '',
        'tipo_persona' => $_POST['tipo_persona'],
        'fecha_nacimiento' => $_POST['fecha_nacimiento'],
        'num_apt' => $_POST['num_apt'] ?? null,
        'id_torre' => $_POST['id_torre'] ?? null
    ];

    if (empty($form_data['nombre1'])) {
        $errores['nombre1'] = "El nombre es obligatorio";
    } elseif (strlen($form_data['nombre1']) > 30) {
        $errores['nombre1'] = "El nombre no puede exceder 30 caracteres";
    }

    if (empty($form_data['apellido1'])) {
        $errores['apellido1'] = "El apellido es obligatorio";
    } elseif (strlen($form_data['apellido1']) > 30) {
        $errores['apellido1'] = "El apellido no puede exceder 30 caracteres";
    }

    if (empty($form_data['tipo_documento'])) {
        $errores['tipo_documento'] = "Seleccione un tipo de documento";
    }

    if (empty($form_data['numero_documento'])) {
        $errores['numero_documento'] = "El número de documento es obligatorio";
    } elseif (!preg_match('/^[0-9]{6,20}$/', $form_data['numero_documento'])) {
        $errores['numero_documento'] = "Documento no válido (solo números, 6-20 dígitos)";
    }

    if (empty($form_data['correo'])) {
        $errores['correo'] = "El correo es obligatorio";
    } elseif (!filter_var($form_data['correo'], FILTER_VALIDATE_EMAIL)) {
        $errores['correo'] = "Correo electrónico no válido";
    }

    if (empty($form_data['telefono'])) {
        $errores['telefono'] = "El teléfono es obligatorio";
    } elseif (!preg_match('/^[0-9]{7,15}$/', $form_data['telefono'])) {
        $errores['telefono'] = "Teléfono no válido (solo números, 7-15 dígitos)";
    }

    if (empty($form_data['direccion'])) {
        $errores['direccion'] = "La dirección es obligatoria";
    } elseif (strlen($form_data['direccion']) > 100) {
        $errores['direccion'] = "La dirección no puede exceder 100 caracteres";
    }

    if (empty($form_data['clave'])) {
        $errores['clave'] = "La contraseña es obligatoria";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $form_data['clave'])) {
        $errores['clave'] = "La contraseña debe tener al menos 8 caracteres, una mayúscula y un número";
    } elseif ($form_data['clave'] !== $form_data['confirmar_clave']) {
        $errores['confirmar_clave'] = "Las contraseñas no coinciden";
    }

    if (empty($form_data['tipo_persona'])) {
        $errores['tipo_persona'] = "Seleccione un tipo de persona";
    }

    try {
        $fecha_nacimiento = new DateTime($form_data['fecha_nacimiento']);
        $hoy = new DateTime();
        
        if ($fecha_nacimiento > $hoy) {
            $errores['fecha_nacimiento'] = "La fecha no puede ser futura";
        }
        
        $edad = $hoy->diff($fecha_nacimiento)->y;
        if ($edad < 18) {
            $errores['fecha_nacimiento'] = "Debes tener al menos 18 años";
        }
    } catch (Exception $e) {
        $errores['fecha_nacimiento'] = "Fecha no válida";
    }

    if (in_array($form_data['tipo_persona'], ['P', 'A'])) {
        if (empty($form_data['id_torre'])) {
            $errores['id_torre'] = "Seleccione una torre";
        }
        
        if (empty($form_data['num_apt'])) {
            $errores['num_apt'] = "Seleccione un apartamento";
        }
    }

    if (empty($errores)) {
        // Verificar si el correo ya existe
        $stmt = $conn->prepare("SELECT id_persona FROM persona WHERE correo = ?");
        $stmt->bind_param("s", $form_data['correo']);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errores['correo'] = "Este correo ya está registrado";
            $_SESSION['errores'] = $errores;
            $_SESSION['form_data'] = $form_data;
            header("Location: registro.php");
            exit();
        }
        $stmt->close();

        $conn->begin_transaction();

        try {

            $hashed_password = password_hash($form_data['clave'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO persona (nombre1, apellido1, correo, telefono, direccion, tipo_persona, fecha_nacimiento, password, tipo_documento, numero_documento) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", 
                $form_data['nombre1'],
                $form_data['apellido1'],
                $form_data['correo'],
                $form_data['telefono'],
                $form_data['direccion'],
                $form_data['tipo_persona'],
                $form_data['fecha_nacimiento'],
                $hashed_password,
                $form_data['tipo_documento'],
                $form_data['numero_documento']
            );
            $stmt->execute();
            $id_persona = $conn->insert_id;
            $stmt->close();

            if (in_array($form_data['tipo_persona'], ['P', 'A'])) {
                $stmt = $conn->prepare("INSERT INTO persona_apartamento (id_persona, num_apt, id_torre, tipo_relacion) 
                                       VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", 
                    $id_persona,
                    $form_data['num_apt'],
                    $form_data['id_torre'],
                    $form_data['tipo_persona']
                );
                $stmt->execute();
                $stmt->close();
            }

            $conn->commit();
            $_SESSION['registro_exitoso'] = true;
            header("Location: login.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $errores['general'] = "Error al registrar: " . $e->getMessage();
            $_SESSION['errores'] = $errores;
            $_SESSION['form_data'] = $form_data;
            header("Location: registro.php");
            exit();
        }
    } else {
        $_SESSION['errores'] = $errores;
        $_SESSION['form_data'] = $form_data;
        header("Location: registro.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #2d2d2d; 
            color: #e0e0e0; 
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background-color: rgba(45, 45, 45, 0.9); 
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(123, 31, 162, 0.3); 
            width: 90%;
            max-width: 800px;
            border: 1px solid #7b1fa2; 
        }

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-container img {
            width: 80px;
            height: auto;
            filter: drop-shadow(0 0 5px rgba(123, 31, 162, 0.5)); 
        }

        h2 {
            text-align: center;
            color: #ba68c8; 
            margin-bottom: 30px;
        }

        form {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
            width: calc(50% - 10px);
            box-sizing: border-box;
        }

        .form-group.full-width {
            width: 100%;
        }

        label {
            color: #ba68c8; 
            margin-bottom: 5px;
            font-size: 0.9em;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"],
        select {
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid #7b1fa2; 
            border-radius: 5px;
            color: #e0e0e0; 
            font-size: 14px;
            box-sizing: border-box;
            width: 100%;
            transition: border-color 0.3s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #ba68c8; 
            box-shadow: 0 0 5px rgba(123, 31, 162, 0.5); 
        }

        input::placeholder {
            color: #aaa;
        }

        select option {
            background: #3d3d3d; 
            color: #e0e0e0; 
        }

        #apartamento_fields {
            width: 100%;
            margin-bottom: 15px;
            display: flex;
            gap: 20px;
            align-items: flex-start;
            background-color: rgba(123, 31, 162, 0.1); 
            padding: 15px;
            border-radius: 8px;
            border: 1px dashed #7b1fa2; 
        }

        #apartamento_fields > label {
            width: 100%;
            margin-bottom: 5px;
            text-align: left;
            color: #ba68c8; 
        }

        #apartamento_fields select {
            flex: 1;
        }

        input[type="submit"] {
            width: 100%;
            background-color: #7b1fa2; 
            border: none;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            padding: 12px;
            border-radius: 5px;
            margin-top: 20px;
        }

        input[type="submit"]:hover {
            background-color: #9c27b0; 
        }

        .error {
            color: #ef5350; 
            text-align: center;
            font-weight: bold;
            margin-top: 10px;
            width: 100%;
        }

        .validation-error {
            color: #ff9800; 
            font-size: 0.85em;
            margin-top: 5px;
        }

        .button-container {
            text-align: center;
            margin-top: 20px;
            width: 100%;
        }

        .button-container button {
            background-color: #4a4a4a; 
            color: white;
            padding: 10px 20px;
            border: 1px solid #7b1fa2; 
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 1em;
        }

        .button-container button:hover {
            background-color: #7b1fa2; 
        }

        @media (max-width: 600px) {
            form {
                flex-direction: column;
            }
            .form-group {
                width: 100%;
            }
            #apartamento_fields {
                flex-direction: column;
                gap: 10px;
            }
            #apartamento_fields select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="imagenes_camaras/all-seeing-eye-symbol-of-religion-spirituality-occultism-illustration-isolated-on-a-dark-background-free-vector.jpg" alt="Logo del sistema" width="80">
        </div>
        <h2>Registro de Usuario</h2>
        
        <?php if (isset($errores['general'])): ?>
            <p class="error"><?php echo $errores['general']; ?></p>
        <?php endif; ?>
        
        <form method="POST" id="registroForm">
            <div class="form-group">
                <label for="nombre1">Nombre</label>
                <input type="text" name="nombre1" placeholder="Nombre" maxlength="30" 
                       value="<?php echo htmlspecialchars($form_data['nombre1'] ?? ''); ?>" required>
                <?php if (isset($errores['nombre1'])): ?>
                    <span class="validation-error"><?php echo $errores['nombre1']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="apellido1">Apellido</label>
                <input type="text" name="apellido1" placeholder="Apellido" maxlength="30" 
                       value="<?php echo htmlspecialchars($form_data['apellido1'] ?? ''); ?>" required>
                <?php if (isset($errores['apellido1'])): ?>
                    <span class="validation-error"><?php echo $errores['apellido1']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="numero_documento">Número de identificación</label>
                <input type="text" name="numero_documento" placeholder="Número de identificación" maxlength="20" 
                       value="<?php echo htmlspecialchars($form_data['numero_documento'] ?? ''); ?>" required>
                <?php if (isset($errores['numero_documento'])): ?>
                    <span class="validation-error"><?php echo $errores['numero_documento']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="tipo_documento">Tipo de documento</label>
                <select name="tipo_documento" required>
                    <option value="">Seleccione...</option>
                    <option value="CC" <?= (isset($form_data['tipo_documento']) && $form_data['tipo_documento'] == 'CC' ? 'selected' : '') ?>>Cédula de Ciudadanía</option>
                    <option value="CE" <?= (isset($form_data['tipo_documento']) && $form_data['tipo_documento'] == 'CE' ? 'selected' : '') ?>>Cédula de Extranjería</option>
                    <option value="PA" <?= (isset($form_data['tipo_documento']) && $form_data['tipo_documento'] == 'PA' ? 'selected' : '') ?>>Pasaporte</option>
                </select>
                <?php if (isset($errores['tipo_documento'])): ?>
                    <span class="validation-error"><?php echo $errores['tipo_documento']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group full-width">
                <label for="correo">Correo</label>
                <input type="email" name="correo" placeholder="Correo" maxlength="30" 
                       value="<?php echo htmlspecialchars($form_data['correo'] ?? ''); ?>" onkeyup="validarCorreo()" required>
                <span id="correoError" class="validation-error">
                    <?php echo $errores['correo'] ?? ''; ?>
                </span>
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="text" name="telefono" placeholder="Teléfono" maxlength="30" 
                       value="<?php echo htmlspecialchars($form_data['telefono'] ?? ''); ?>" required>
                <?php if (isset($errores['telefono'])): ?>
                    <span class="validation-error"><?php echo $errores['telefono']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <input type="text" name="direccion" placeholder="Dirección" maxlength="30" 
                       value="<?php echo htmlspecialchars($form_data['direccion'] ?? ''); ?>" required>
                <?php if (isset($errores['direccion'])): ?>
                    <span class="validation-error"><?php echo $errores['direccion']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group full-width">
                <label for="clave">Contraseña</label>
                <input type="password" name="clave" placeholder="Contraseña" maxlength="30" 
                       onkeyup="validarPassword()" required>
                <span id="passwordError" class="validation-error">
                    <?php echo $errores['clave'] ?? ''; ?>
                </span>
            </div>
            
            <div class="form-group full-width">
                <label for="confirmar_clave">Confirmar Contraseña</label>
                <input type="password" name="confirmar_clave" placeholder="Confirmar Contraseña" maxlength="30" 
                       onkeyup="validarConfirmacionPassword()" required>
                <span id="confirmPasswordError" class="validation-error">
                    <?php echo $errores['confirmar_clave'] ?? ''; ?>
                </span>
            </div>
            
            <div class="form-group full-width">
                <label for="tipo_persona">Tipo de persona</label>
                <select name="tipo_persona" id="tipo_persona" onchange="actualizarVisibilidadApartamento()" required>
                    <option value="P" <?php echo (isset($form_data['tipo_persona']) && $form_data['tipo_persona'] == 'P' ? 'selected' : ''); ?>>Propietario</option>
                    <option value="A" <?php echo (isset($form_data['tipo_persona']) && $form_data['tipo_persona'] == 'A' ? 'selected' : ''); ?>>Arrendatario</option>
                    <option value="V" <?php echo (isset($form_data['tipo_persona']) && $form_data['tipo_persona'] == 'V' ? 'selected' : ''); ?>>Vigilante</option>
                </select>
            </div>
            
            <div class="form-group full-width">
                <label for="fecha_nacimiento">Fecha de nacimiento</label>
                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" 
                       value="<?php echo htmlspecialchars($form_data['fecha_nacimiento'] ?? ''); ?>" required>
                <?php if (isset($errores['fecha_nacimiento'])): ?>
                    <span class="validation-error"><?php echo $errores['fecha_nacimiento']; ?></span>
                <?php endif; ?>
            </div>
            
            <?php
            $mostrar_campos = (isset($form_data['tipo_persona']) && in_array($form_data['tipo_persona'], ['P', 'A']));
            ?>
            <div id="apartamento_fields" style="display: <?php echo $mostrar_campos ? 'flex' : 'none'; ?>;">
                <div style="width: 100%; margin-bottom: 10px;">
                    <label>Información del Apartamento (Solo para Propietarios y Arrendatarios)</label>
                </div>
                <div style="flex: 1; margin-right: 10px;">
                    <select name="id_torre" id="id_torre" class="form-control" onchange="filtrarApartamentos()" <?php echo $mostrar_campos ? '' : 'disabled'; ?>>
                        <option value="">Seleccione la torre</option>
                        <?php
                        $torres_query->data_seek(0);
                        while($torre = $torres_query->fetch_assoc()):
                            $selected = (isset($form_data['id_torre']) && $form_data['id_torre'] == $torre['id_torre']) ? 'selected' : '';
                            echo "<option value='{$torre['id_torre']}' $selected>Torre {$torre['id_torre']}</option>";
                        endwhile;
                        ?>
                    </select>
                    <?php if (isset($errores['id_torre'])): ?>
                        <span class="validation-error"><?php echo $errores['id_torre']; ?></span>
                    <?php endif; ?>
                </div>
                <div style="flex: 1;">
                    <select name="num_apt" id="num_apt" class="form-control" <?php echo $mostrar_campos ? '' : 'disabled'; ?> required>
                        <option value="">Seleccione el apartamento</option>
                        <?php
                        foreach ($all_apartamentos as $apt):
                            $selected = (isset($form_data['num_apt']) && $form_data['num_apt'] == $apt['num_apt'] && isset($form_data['id_torre']) && $form_data['id_torre'] == $apt['id_torre']) ? 'selected' : '';
                            echo "<option value='{$apt['num_apt']}' data-torre='{$apt['id_torre']}' $selected>Apto {$apt['num_apt']} - Torre {$apt['id_torre']}</option>";
                        endforeach;
                        ?>
                    </select>
                    <?php if (isset($errores['num_apt'])): ?>
                        <span class="validation-error"><?php echo $errores['num_apt']; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <input type="submit" value="Registrarse">
        </form>

        <div class="button-container">
            <button onclick="window.location.href='index.php'">Regresar al Inicio</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Establecer fecha máxima (hoy)
            const hoy = new Date().toISOString().split('T')[0];
            document.getElementById('fecha_nacimiento').setAttribute('max', hoy);
            
            actualizarVisibilidadApartamento();
            
            if (document.getElementById('id_torre').value) {
                filtrarApartamentos();
            }
            
            document.getElementById('registroForm').addEventListener('submit', function(e) {
                // Validar fecha
                const fechaSeleccionada = new Date(document.getElementById('fecha_nacimiento').value);
                const hoy = new Date();
                hoy.setHours(0, 0, 0, 0);
                
                if (fechaSeleccionada > hoy) {
                    e.preventDefault();
                    alert('La fecha de nacimiento no puede ser mayor a la fecha actual');
                    return false;
                }
                
                // Validar campos específicos para propietarios/arrendatarios
                const tipo = document.getElementById('tipo_persona').value;
                if ((tipo === 'P' || tipo === 'A') && 
                    (!document.getElementById('id_torre').value || !document.getElementById('num_apt').value)) {
                    e.preventDefault();
                    alert('Debe seleccionar un apartamento para propietarios/arrendatarios');
                    return false;
                }
                
                // Validar correo y contraseñas
                const correoValido = document.getElementById('correoError').textContent === '';
                const claveValida = document.getElementById('passwordError').textContent === '';
                const confirmacionValida = document.getElementById('confirmPasswordError').textContent === '';
                
                if (!correoValido || !claveValida || !confirmacionValida) {
                    e.preventDefault();
                    alert('Por favor corrija los errores en el formulario antes de enviar');
                    return false;
                }
            });
        });

        function actualizarVisibilidadApartamento() {
            const tipo = document.getElementById("tipo_persona").value;
            const mostrar = (tipo === 'P' || tipo === 'A');
            const apartamentoFields = document.getElementById("apartamento_fields");
            const torreSelect = document.getElementById("id_torre");
            const aptSelect = document.getElementById("num_apt");
            
            apartamentoFields.style.display = mostrar ? "flex" : "none";
            
            torreSelect.disabled = !mostrar;
            aptSelect.disabled = !mostrar;
            
            if (!mostrar) {
                torreSelect.value = "";
                aptSelect.value = "";
            }
            
            torreSelect.required = mostrar;
            aptSelect.required = mostrar;
        }

        function filtrarApartamentos() {
            const torreSeleccionada = document.getElementById("id_torre").value;
            const aptSelect = document.getElementById("num_apt");
            
            aptSelect.disabled = !torreSeleccionada;
            
            Array.from(aptSelect.options).forEach(option => {
                if (option.value === "") return; 
                
                const torreApt = option.getAttribute('data-torre');
                if (torreSeleccionada && torreApt === torreSeleccionada) {
                    option.style.display = '';
                    option.disabled = false;
                } else {
                    option.style.display = 'none';
                    option.disabled = true;
                }
            });
            
            // Reiniciar selección si no es válida
            if (aptSelect.value && aptSelect.options[aptSelect.selectedIndex].disabled) {
                aptSelect.value = "";
            }
        }

        function validarCorreo() {
            const correo = document.querySelector('input[name="correo"]').value;
            const error = document.getElementById('correoError');
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!regex.test(correo)) {
                error.textContent = 'Correo no válido';
            } else {
                error.textContent = '';
            }
        }

        function validarPassword() {
            const clave = document.querySelector('input[name="clave"]').value;
            const error = document.getElementById('passwordError');
            const regex = /^(?=.*[A-Z])(?=.*\d).{8,}$/;

            if (!regex.test(clave)) {
                error.textContent = 'Mínimo 8 caracteres, una mayúscula y un número';
            } else {
                error.textContent = '';
            }
            
            validarConfirmacionPassword();
        }

        function validarConfirmacionPassword() {
            const clave = document.querySelector('input[name="clave"]').value;
            const confirmacion = document.querySelector('input[name="confirmar_clave"]').value;
            const error = document.getElementById('confirmPasswordError');

            if (clave !== confirmacion) {
                error.textContent = 'Las contraseñas no coinciden';
            } else {
                error.textContent = '';
            }
        }
    </script>
</body>
</html>
