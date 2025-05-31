<?php
include('db.php');
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

function obtener_personas($conn, $tipo = '') {
    $sql = "SELECT * FROM persona";
    if ($tipo) {
        $sql .= " WHERE tipo_persona = '$tipo'";
    }
    return $conn->query($sql);
}

function cambiar_estado_persona($conn, $id, $nuevo_estado) {
    $sql = "UPDATE persona SET estado = ? WHERE id_persona = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $nuevo_estado, $id);
    return $stmt->execute();
}

if (isset($_POST['accion']) && in_array($_POST['accion'], ['desactivar', 'activar'])) {
    $id_persona = $_POST['id_persona'] ?? null;
    $nuevo_estado = $_POST['accion'] === 'desactivar' ? 0 : 1;

    if ($id_persona && cambiar_estado_persona($conn, $id_persona, $nuevo_estado)) {
        $_SESSION['mensaje'] = "Usuario " . ($_POST['accion'] === 'desactivar' ? "desactivado" : "activado") . " exitosamente.";
    } else {
        $_SESSION['error'] = "Error al cambiar el estado del usuario.";
    }
    header("Location: admin_panel.php");
    exit();
}

$usuarios = obtener_personas($conn);

$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

if (isset($_GET['toggle_theme'])) {
    $new_theme = ($theme === 'light') ? 'dark' : 'light';
    setcookie('theme', $new_theme, time() + (86400 * 30), "/"); 
    header("Location: admin_panel.php"); 
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Administrar Usuarios</title>
    <style>
    html, body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: <?php echo ($theme === 'dark') ? '#121212' : '#e0e0e0'; ?>;
      color: <?php echo ($theme === 'dark') ? '#f0f0f0' : '#333'; ?>;
      min-height: 100vh;
      width: 100%;
      overflow: hidden; /* Previene scroll innecesario */
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
      margin: 30px auto;
      position: relative;
      z-index: 1;
      background-color: <?php echo ($theme === 'dark') ? 'rgba(30, 30, 30, 0.8)' : 'rgba(255, 255, 255, 0.8)'; ?>;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 0 25px rgba(0, 0, 0, 0.2);
      width: 98%;
      max-width: 1100px;
      overflow: auto;
    }

    h2 {
      color: #8e44ad;
      text-align: center;
      margin-bottom: 15px;
      font-size: 1.8em;
    }

    .button-link {
      display: inline-block;
      padding: 8px 12px;
      background-color: #5cb85c;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-size: 14px;
      transition: background-color 0.3s ease;
    }

    .button-link:hover {
      background-color: #4cae4c;
    }

    .success {
      color: #28a745;
      margin-top: 10px;
      text-align: center;
      font-size: 0.9em;
    }

    .error {
      color: #dc3545;
      margin-top: 10px;
      text-align: center;
      font-size: 0.9em;
    }

    .table-container {
      width: 100%;
      overflow-x: auto;
    }

    table {
      border-collapse: collapse;
      width: 100%;
      margin-top: 10px;
      table-layout: fixed;
      font-size: 0.9em;
      color: <?php echo ($theme === 'dark') ? '#f0f0f0' : '#333'; ?>;
    }

    th, td {
      padding: 6px 8px;
      border: 1px solid <?php echo ($theme === 'dark') ? '#424242' : '#ccc'; ?>;
      text-align: left;
      word-break: break-word;
      background-color: <?php echo ($theme === 'dark') ? '#212121' : '#f9f9f9'; ?>;
    }

    th {
      background-color: <?php echo ($theme === 'dark') ? '#333' : '#f0f0f0'; ?>;
      color: <?php echo ($theme === 'dark') ? '#f0f0f0' : '#333'; ?>;
      font-weight: bold;
    }

    tr:nth-child(even) {
      background-color: <?php echo ($theme === 'dark') ? '#2a2a2a' : '#f9f9f9'; ?>;
    }

    .actions {
      display: flex;
      gap: 3px;
      flex-wrap: wrap;
      justify-content: center;
    }

    .actions a, .actions form button {
      padding: 4px 6px;
      text-decoration: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.8em;
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
      text-align: center;
      font-size: 0.9em;
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
  </style>
</head>
<body>
    <a href="?toggle_theme=true" class="theme-toggle-button">
        <?php echo ($theme === 'dark') ? 'Claro' : 'Oscuro'; ?>
    </a>
  <div class="container">
    <h2>Administrar Usuarios</h2>

    <?php if (isset($_SESSION['mensaje'])): ?>
      <div class="success"><?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div style="text-align: right; margin-bottom: 15px;">
      <a href="crear_usuario.php" class="button-link">Crear Usuario</a>
    </div>
   <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Tipo Doc.</th>
            <th>Nro. Doc.</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Tipo</th>
            <th>Rol</th>
            <th>Dirección</th>
            <th>Fecha Nac.</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
          </thead>
        <tbody>
          <?php while ($row = $usuarios->fetch_assoc()): ?>
            <tr>
              <td><?php echo $row['id_persona']; ?></td>
              <td><?php echo $row['nombre1'] . ' ' . $row['apellido1']; ?></td>
              <td><?php echo $row['tipo_documento']; ?></td>
              <td><?php echo $row['numero_documento']; ?></td>
              <td><?php echo $row['correo']; ?></td>
              <td><?php echo $row['telefono']; ?></td>
              <td><?php
                switch ($row['tipo_persona']) {
                  case 'P': echo 'Prop.'; break;
                  case 'X': echo 'Admin.'; break;
                  case 'V': echo 'Vigil.'; break;
                  case 'A': echo 'Arrend.'; break;
                  default: echo $row['tipo_persona']; break;
                }
              ?></td>
              <td><?php echo $row['rol']; ?></td>
              <td><?php echo $row['direccion']; ?></td>
              <td><?php echo $row['fecha_nacimiento']; ?></td>
              <td><?php echo ($row['estado'] == 1) ? 'Activo' : 'Inactivo'; ?></td>
              <td class="actions">
                <a href="editar_usuario.php?id=<?php echo $row['id_persona']; ?>">Editar</a>
                <form method="post" onsubmit="return confirm('¿Estás seguro?');" style="display:inline;">
                  <input type="hidden" name="id_persona" value="<?php echo $row['id_persona']; ?>">
                  <input type="hidden" name="accion" value="<?php echo ($row['estado'] == 1) ? 'desactivar' : 'activar'; ?>">
                  <button type="submit"><?php echo ($row['estado'] == 1) ? 'Desact.' : 'Act.'; ?></button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
          <?php if ($usuarios->num_rows === 0): ?>
            <tr><td colspan="12">No hay usuarios registrados.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      <p class="back-link"><a href="dashboard.php">Volver al menú</a></p>
    </div>
  </div>
</body>
</html>
