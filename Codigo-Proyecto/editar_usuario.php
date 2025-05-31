<?php
 include('db.php');
 session_start();

 if (!isset($_SESSION['usuario'])) {
     header("Location: login.php");
     exit();
 }

 if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
     $_SESSION['error'] = "ID de usuario no válido";
     header("Location: admin_panel.php");
     exit();
 }

 $id_usuario = $_GET['id'];

 function obtener_persona($conn, $id) {
     $sql = "SELECT * FROM persona WHERE id_persona = ?";
     $stmt = $conn->prepare($sql);
     $stmt->bind_param("i", $id);
     $stmt->execute();
     return $stmt->get_result()->fetch_assoc();
 }

 $usuario = obtener_persona($conn, $id_usuario);

 if (!$usuario) {
     $_SESSION['error'] = "Usuario no encontrado";
     header("Location: admin_panel.php");
     exit();
 }


 function actualizar_persona($conn, $id, $nombre1, $apellido1, $direccion, $telefono, $correo, $tipo_persona, $fecha_nacimiento, $rol, $password) {
     if (!empty($password)) {
         $sql = "UPDATE persona SET nombre1 = ?, apellido1 = ?, direccion = ?, telefono = ?, correo = ?, tipo_persona = ?, fecha_nacimiento = ?, rol = ?, password = ? WHERE id_persona = ?";
         $stmt = $conn->prepare($sql);
         $hashed_password = password_hash($password, PASSWORD_DEFAULT);
         $stmt->bind_param("sssssssssi", $nombre1, $apellido1, $direccion, $telefono, $correo, $tipo_persona, $fecha_nacimiento, $rol, $hashed_password, $id);
     } else {
         $sql = "UPDATE persona SET nombre1 = ?, apellido1 = ?, direccion = ?, telefono = ?, correo = ?, tipo_persona = ?, fecha_nacimiento = ?, rol = ? WHERE id_persona = ?";
         $stmt = $conn->prepare($sql);
         $stmt->bind_param("ssssssssi", $nombre1, $apellido1, $direccion, $telefono, $correo, $tipo_persona, $fecha_nacimiento, $rol, $id);
     }
     return $stmt->execute();
 }

 if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     $nombre1 = $_POST['nombre1'] ?? '';
     $apellido1 = $_POST['apellido1'] ?? '';
     $direccion = $_POST['direccion'] ?? '';
     $telefono = $_POST['telefono'] ?? '';
     $correo = $_POST['correo'] ?? '';
     $tipo_persona = $_POST['tipo_persona'] ?? 'P';
     $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
     $rol = $_POST['rol'] ?? '';
     $password = $_POST['password'] ?? '';

     if (actualizar_persona($conn, $id_usuario, $nombre1, $apellido1, $direccion, $telefono, $correo, $tipo_persona, $fecha_nacimiento, $rol, $password)) {
         $_SESSION['mensaje'] = "Usuario actualizado exitosamente.";
         header("Location: admin_panel.php");
         exit();
     } else {
         $_SESSION['error'] = "Error al actualizar el usuario.";
     }
 }

 $theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
 ?>
 <!DOCTYPE html>
 <html>
 <head>
     <title>Editar Usuario</title>
     <style>
         body {
             margin: 0;
             padding: 0;
             font-family: 'Segoe UI', sans-serif;
             background-color: #f0f0f0; 
             color: #333; 
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
             top: 20px;
             left: 20px;
             width: 100px; 
             height: 100px; 
             background-image: url('imagenes_camaras/vecteezy_esoteric-eye-in-triangle_16927071.jpg');
             background-size: contain;
             background-repeat: no-repeat;
             background-position: top left;
             opacity: 0.05; 
             z-index: 0;
             pointer-events: none;
             transition: opacity 0.3s ease, filter 0.3s ease;
             filter: grayscale(100%) brightness(150%); 
         }

         .container {
             position: relative;
             z-index: 1;
             background-color: rgba(255, 255, 255, 0.9); 
             padding: 30px;
             border-radius: 15px;
             box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
             width: 90%;
             max-width: 900px;
             margin-top: 80px; 
         }

         h2 {
             color: #7d26cd; 
             text-align: center;
             margin-bottom: 20px;
         }

         .form-container {
             margin-top: 20px;
             padding: 20px;
             border: 1px solid #d3d3d3; 
             border-radius: 5px;
             background-color: #f9f9f9; 
         }

         .form-container h3 {
             color: #7d26cd; 
             margin-top: 0;
             text-align: center;
             margin-bottom: 20px;
         }

         .form-group {
             margin-bottom: 15px;
         }

         label {
             display: block;
             margin-bottom: 5px;
             font-weight: bold;
             color: #555; 
         }

         input[type="text"], input[type="email"], input[type="tel"], input[type="date"], input[type="password"], select {
             width: calc(100% - 16px);
             padding: 8px;
             border: 1px solid #ccc; 
             border-radius: 4px;
             box-sizing: border-box;
             background-color: #fff; 
             color: #333; 
         }

         button {
             background-color: #7d26cd; 
             color: white;
             padding: 10px 15px;
             border: none;
             border-radius: 4px;
             cursor: pointer;
             font-size: 16px;
             transition: background-color 0.3s ease;
         }

         button:hover {
             background-color: #6a1b9a; 
         }

         .error {
             color: red;
             margin-top: 10px;
             text-align: center;
         }

         .success {
             color: green;
             margin-top: 10px;
             text-align: center;
         }

         table {
             border-collapse: collapse;
             width: 100%;
             margin-top: 20px;
         }

         th, td {
             padding: 12px;
             border: 1px solid #d3d3d3; 
             text-align: left;
         }

         th {
             background-color: #e0e0e0; 
             color: #555; 
         }

         tr:nth-child(even) {
             background-color: #f9f9f9; 
         }

         td {
             background-color: #fff; 
             color: #333; 
         }

         .actions {
             display: flex;
             gap: 10px;
         }

         .actions a, .actions form button {
             display: inline-block;
             padding: 8px 12px;
             text-decoration: none;
             border-radius: 4px;
             cursor: pointer;
             font-size: 14px;
             transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
         }

         .actions a {
             color: #007bff;
             border: 1px solid #007bff;
             background-color: transparent;
         }

         .actions a:hover {
             color: #0056b3;
             border-color: #0056b3;
             background-color: rgba(0, 123, 255, 0.1);
         }

         .actions form button {
             background-color: #dc3545;
             color: white;
             border: none;
         }

         .actions form button:hover {
             background-color: #c82333;
         }

         .back-link {
             margin-top: 20px;
         }

         .back-link a {
             color: #007bff;
             text-decoration: none;
         }

         .back-link a:hover {
             text-decoration: underline;
             color: #0056b3;
         }

         .button-link {
             background-color: #6c757d; /* Gris para el botón de cancelar */
             color: white;
             padding: 10px 15px;
             border: none;
             border-radius: 4px;
             cursor: pointer;
             font-size: 16px;
             text-decoration: none;
             transition: background-color 0.3s ease;
         }

         .button-link:hover {
             background-color: #5a6268; /* Gris más oscuro al pasar el ratón */
         }
     </style>
     <script>

         function validarFecha(event) {
             const fechaInput = document.getElementById('fecha_nacimiento');
             const fechaSeleccionada = new Date(fechaInput.value);
             const fechaActual = new Date();

             if (fechaSeleccionada > fechaActual) {
                 alert('La fecha de nacimiento no puede ser mayor a la fecha actual');
                 event.preventDefault();
                 return false;
             }
             return true;
         }

         function limitarLongitud(input, maxLength) {
             if (input.value.length > maxLength) {
                 input.value = input.value.substring(0, maxLength);
             }
         }
     </script>
 </head>
 <body>
     <div class="container">
         <h2>Editar Usuario</h2>

         <?php if (isset($_SESSION['error'])): ?>
             <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
         <?php endif; ?>

         <div class="form-container">
             <form method="post" onsubmit="return validarFecha(event)">
                 <div class="form-group">
                     <label for="nombre1">Nombre:</label>
                     <input type="text" id="nombre1" name="nombre1" value="<?php echo htmlspecialchars($usuario['nombre1']); ?>"
                            maxlength="30" oninput="limitarLongitud(this, 30)" required>
                 </div>


                 <div class="form-group">
                     <label for="apellido1">Apellido:</label>
                     <input type="text" id="apellido1" name="apellido1" value="<?php echo htmlspecialchars($usuario['apellido1']); ?>"
                            maxlength="30" oninput="limitarLongitud(this, 30)" required>
                 </div>



                 <div class="form-group">
                     <label for="direccion">Dirección:</label>
                     <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($usuario['direccion']); ?>"
                            maxlength="30" oninput="limitarLongitud(this, 30)">
                 </div>

                 <div class="form-group">
                     <label for="telefono">Teléfono:</label>
                     <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono']); ?>"
                            maxlength="30" oninput="limitarLongitud(this, 30)">
                 </div>

                 <div class="form-group">
                     <label for="correo">Correo:</label>
                     <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>"
                            maxlength="30" oninput="limitarLongitud(this, 30)" required>
                 </div>

                 <div class="form-group">
                     <label for="tipo_persona">Tipo de Persona:</label>
                     <select id="tipo_persona" name="tipo_persona">
                         <option value="P" <?php if ($usuario['tipo_persona'] === 'P') echo 'selected'; ?>>Propietario</option>
                         <option value="A" <?php if ($usuario['tipo_persona'] === 'A') echo 'selected'; ?>>Arrendatario</option>
                         <option value="V" <?php if ($usuario['tipo_persona'] === 'V') echo 'selected'; ?>>Vigilante</option>
                         <option value="X" <?php if ($usuario['tipo_persona'] === 'X') echo 'selected'; ?>>Administrador</option>
                     </select>
                 </div>

                 <div class="form-group">
                     <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                     <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                            value="<?php echo htmlspecialchars($usuario['fecha_nacimiento']); ?>"
                            max="<?php echo date('Y-m-d'); ?>">
                 </div>

                 <div class="form-group">
                     <label for="rol">Rol:</label>
                     <input type="text" id="rol" name="rol" value="<?php echo htmlspecialchars($usuario['rol']); ?>"
                            maxlength="30" oninput="limitarLongitud(this, 30)">
                 </div>

                 <div class="form-group">
                     <label for="password">Contraseña:</label>
                     <input type="password" id="password" name="password" value=""
                            maxlength="30" oninput="limitarLongitud(this, 30)">
                     <small>Dejar en blanco para no cambiar la contraseña.</small>
                 </div>

                 <button type="submit">Guardar Cambios</button>
                 <a href="admin_panel.php" class="button-link">Cancelar</a>
             </form>
         </div>

         <p class="back-link"><a href="admin_panel.php">Volver a la lista de usuarios</a></p>
     </div>

     <script>

         document.getElementById('fecha_nacimiento').addEventListener('change', function() {
             const fechaInput = this;
             const fechaSeleccionada = new Date(fechaInput.value);
             const fechaActual = new Date();

             if (fechaSeleccionada > fechaActual) {
                 alert('La fecha de nacimiento no puede ser mayor a la fecha actual');
                 fechaInput.value = '<?php echo htmlspecialchars($usuario['fecha_nacimiento']); ?>';
             }
         });
     </script>
 </body>
 </html>
