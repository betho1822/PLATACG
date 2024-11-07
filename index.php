<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Configuración de la conexión a PostgreSQL
$host = "192.168.1.102";
$db = "plata_pruebas";
$user = "postgres";
$pass = "0888852339";
$port = "1822";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Obtener valores únicos para "direccion" y "asunto"
    $stmt_direccion = $pdo->prepare("SELECT DISTINCT direccion FROM tareas ORDER BY direccion ASC");
    $stmt_direccion->execute();
    $direcciones = $stmt_direccion->fetchAll(PDO::FETCH_COLUMN);

    $stmt_asunto = $pdo->prepare("SELECT DISTINCT asunto FROM tareas ORDER BY asunto ASC");
    $stmt_asunto->execute();
    $asuntos = $stmt_asunto->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Lista de nombres
$nombres = ['Todos', 'Angie', 'Carol', 'Elisa', 'Roman'];

$user_type = $_SESSION['user_type']; // Obtener tipo de usuario
$username = $_SESSION['username'];   // Obtener nombre de usuario

// Obtener tareas atendidas y sin atender según el usuario y la pestaña seleccionada
$tareasAtendidas = [];
$tareasSinAtender = [];

foreach ($nombres as $nombre) {
    if ($user_type == 'administrador' && $nombre != 'Todos') {
        // Para administradores, filtrar por nombre en cada pestaña
        // Tareas Atendidas
        $stmt = $pdo->prepare("SELECT * FROM tareas WHERE asignado_a = :nombre AND estado IS NOT NULL ORDER BY id ASC");
        $stmt->execute([':nombre' => $nombre]);
        $tareasAtendidas[$nombre] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tareas Sin Atender
        $stmt = $pdo->prepare("SELECT * FROM tareas WHERE asignado_a = :nombre AND (estado IS NULL OR estado = '') ORDER BY id ASC");
        $stmt->execute([':nombre' => $nombre]);
        $tareasSinAtender[$nombre] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($user_type != 'administrador') {
        // Para usuarios individuales
        if ($nombre == 'Todos') {
            continue; // Omitir la pestaña 'Todos' para usuarios individuales
        }
        // Tareas Atendidas
        $stmt = $pdo->prepare("SELECT * FROM tareas WHERE asignado_a = :username AND estado IS NOT NULL AND estado != '' ORDER BY id ASC");
        $stmt->execute([':username' => $username]);
        $tareasAtendidas[$username] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tareas Sin Atender
        $stmt = $pdo->prepare("SELECT * FROM tareas WHERE asignado_a = :username AND (estado IS NULL OR estado = '') ORDER BY id ASC");
        $stmt->execute([':username' => $username]);
        $tareasSinAtender[$username] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Para la pestaña 'Todos' en administrador
        if ($nombre == 'Todos') {
            // Tareas Atendidas
            $stmt = $pdo->prepare("SELECT * FROM tareas WHERE estado IS NOT NULL ORDER BY id ASC");
            $stmt->execute();
            $tareasAtendidas['Todos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Tareas Sin Atender
            $stmt = $pdo->prepare("SELECT * FROM tareas WHERE (estado IS NULL OR estado = '') ORDER BY id ASC");
            $stmt->execute();
            $tareasSinAtender['Todos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Planificador de Tareas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <style>
    /* Estilos personalizados */
    body {
      background-color: #f4faff;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      padding-bottom: 60px; /* Ajustar según la altura del footer */
    }
    .header-logo {
      width: 500px;
      height: auto;
    }
    .card-container {
      flex: 1; /* Permite que el contenido principal ocupe el espacio disponible */
      background-color: #ffffff;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      padding: 20px;
      margin-top: 20px;
    }
    .nav-tabs .nav-link {
      color: #666;
      font-weight: bold;
      border: none;
    }
    .nav-tabs .nav-link.active {
      color: #f20d84;
      border-bottom: 3px solid #f20d84;
      background-color: transparent;
    }
    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }
    .section-title {
      font-weight: bold;
      font-size: 18px;
      color: white;
      padding: 10px 15px;
      border-radius: 5px;
      flex: 1;
      margin: 0 10px;
    }
    .section-nuevo {
      background-color: #00b0ff;
    }
    .section-en-progreso {
      background-color: #80deea;
    }
    .task-card {
      border-left: 4px solid #42a5f5;
      background-color: #f9f9f9;
      margin-bottom: 10px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .task-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
    }
    .task-card .card-title {
      color: #333333;
    }
    .task-card .card-text {
      color: #999999;
      font-size: 14px;
    }
    #new-task-btn {
      background-color: #c0ff00;
      border: none;
      color: #333;
      font-weight: bold;
      transition: background-color 0.3s ease, transform 0.3s ease;
    }
    #new-task-btn:hover {
      background-color: #b5f500;
      transform: scale(1.05);
    }
    .btn {
      transition: background-color 0.3s ease, transform 0.3s ease;
    }
    .btn:hover {
      transform: scale(1.05);
    }
    .modal-dialog {
      max-width: 800px;
    }
    .modal.fade .modal-dialog {
      transform: translate(0, -50px);
      opacity: 0;
      transition: opacity 0.3s ease-out, transform 0.3s ease-out;
    }
    .modal.show .modal-dialog {
      transform: translate(0, 0);
      opacity: 1;
    }
    .modal-body .form-label {
      font-weight: bold;
    }
    .modal-body .row > div {
      padding-bottom: 15px;
    }
    /* Añadir estilos para reducir el tamaño de las gráficas */
    .chart-container {
      width: 100%;
      max-width: 400px;
      margin: 20px auto;
    }
    .tab-pane {
      animation: fadeIn 0.5s;
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    /* Estilos para la tabla */
    .task-table {
      width: 100%;
      border-collapse: collapse;
    }
    .task-table td, .task-table th {
      padding: 8px;
      text-align: left;
      border: none;
    }
    .task-table tr:hover {
      background-color: #f1f1f1;
      cursor: pointer;
    }
    /* Layout para vista de tabla y gráfico */
    .view-container {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-start;
      gap: 20px;
    }
    .table-view {
      flex: 2;
      min-width: 300px;
    }
    .chart-view {
      flex: 1;
      max-width: 250px; /* Reducir el tamaño máximo del gráfico */
    }
    .chart-view canvas {
      width: 100% !important;
      height: auto !important;
    }
    /* Estilos para los botones de radio elegantes */
    .radio-group {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
    }
    .radio-group input[type="radio"] {
      display: none;
    }
    .radio-group label {
      cursor: pointer;
      padding: 10px 20px;
      border: 2px solid #ccc;
      border-radius: 25px;
      transition: all 0.3s ease;
    }
    .radio-group input[type="radio"]:checked + label {
      background-color: #00b0ff;
      border-color: #00b0ff;
      color: #fff;
      transform: scale(1.05);
    }

    /* Actualizar estilos para el footer */
    footer {
      color: #007bff;
      font-size: 1.5em; /* Reducir tamaño de fuente */
      font-family: 'Pacifico', cursive; /* Fuente elegante */
      animation: bounceIn 2s infinite;
      position: fixed;
      bottom: 0;
      width: 100%;
      background-color: #ffffff;
      text-align: center;
      padding: 10px 0;
    }
    @keyframes bounceIn {
      0%, 20%, 40%, 60%, 80%, 100% {
        transform: translateY(0);
      }
      10%, 30%, 50%, 70%, 90% {
        transform: translateY(-5px); /* Reducir amplitud del rebote */
      }
    }

    /* Nuevo estilo para el botón 'Cerrar Sesión' */
    .btn-cerrar-sesion {
      background-color: #dc3545; /* Rojo */
      border: none;
      color: #fff;
      font-weight: bold;
      transition: background-color 0.3s ease, transform 0.3s ease;
    }
    .btn-cerrar-sesion:hover {
      background-color: #c82333; /* Rojo oscuro */
      transform: scale(1.05);
    }

    /* Estilo para el botón "Atender" */
    .btn-attend {
      position: absolute;
      right: -25px;
      top: 50%;
      transform: translateY(-50%);
      background-color: #007bff;
      color: #fff;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: right 0.3s ease;
    }
    .btn-attend:hover {
      right: -20px;
    }
  </style>
  <!-- Fuente adicional para "Pacifico" -->
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Añadir dependencias de Font Awesome para el icono de flecha -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
  <!-- Incluir animate.css para animaciones -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body>
  <div class="container card-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <img src="img/bannerplatacg.png" alt="Logo" class="header-logo">
      <div>
        <?php if ($user_type == 'administrador'): ?>
          <button id="new-task-btn" class="btn" data-bs-toggle="modal" data-bs-target="#newTaskModal">NUEVA TAREA</button>
        <?php endif; ?>
        <a href="logout.php" class="btn btn-cerrar-sesion">Cerrar Sesión</a>
      </div>
    </div>

    <!-- Pestañas -->
    <ul class="nav nav-tabs justify-content-center" id="taskTabs">
      <?php if ($user_type == 'administrador'): ?>
        <?php foreach ($nombres as $index => $nombre): ?>
          <li class="nav-item">
            <button class="nav-link <?php echo ($index === 0) ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#<?php echo $nombre; ?>Tab"><?php echo $nombre; ?></button>
          </li>
        <?php endforeach; ?>
      <?php else: ?>
        <li class="nav-item">
          <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#<?php echo $username; ?>Tab"><?php echo $username; ?></button>
        </li>
      <?php endif; ?>
    </ul>

    <!-- Contenido de las Pestañas -->
    <div class="tab-content p-4">
      <?php if ($user_type == 'administrador'): ?>
        <?php foreach ($nombres as $nombre): ?>
          <div class="tab-pane fade <?php echo ($nombre === 'Todos') ? 'show active' : ''; ?>" id="<?php echo $nombre; ?>Tab">
            <!-- Grupo de botones de radio para seleccionar la vista -->
            <div class="radio-group">
              <input type="radio" id="atendidos<?php echo $nombre; ?>" name="view<?php echo $nombre; ?>" value="atendidos">
              <label for="atendidos<?php echo $nombre; ?>">Atendidos</label>

              <input type="radio" id="sinAtender<?php echo $nombre; ?>" name="view<?php echo $nombre; ?>" value="sinAtender" checked>
              <label for="sinAtender<?php echo $nombre; ?>">Sin Atender</label>
            </div>

            <!-- Contenedor para la vista seleccionada -->
            <div class="view-container">
              <!-- Vista de Atendidos -->
              <div class="atendidos-view" id="atendidosView<?php echo $nombre; ?>" style="display: none;">
                <table class="task-table">
                  <thead>
                    <tr>
                      <th>Nombre de la Tarea</th>
                      <th>Asignado a</th>
                      <th>Fecha Atención</th>
                      <!-- ...otros encabezados... -->
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                    if(isset($tareasAtendidas[$nombre])) {
                      foreach ($tareasAtendidas[$nombre] as $tarea): ?>
                        <tr data-id="<?php echo $tarea['id']; ?>" data-bs-toggle="modal" data-bs-target="#taskModal" data-tarea='<?php echo json_encode($tarea); ?>'>
                          <td><?php echo $tarea['nombre_tarea']; ?></td>
                          <td><?php echo $tarea['asignado_a']; ?></td>
                          <td><?php echo $tarea['fecha_atencion']; ?></td>
                          <!-- ...otros datos... -->
                        </tr>
                    <?php endforeach; } ?>
                  </tbody>
                </table>
              </div>

              <!-- Vista de Sin Atender -->
              <div class="sinAtender-view" id="sinAtenderView<?php echo $nombre; ?>">
                <table class="task-table">
                  <thead>
                    <tr>
                      <th>Nombre de la Tarea</th>
                      <th>Asignado a</th>
                      <th>Fecha</th>
                      <!-- ...otros encabezados... -->
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                    if(isset($tareasSinAtender[$nombre])) {
                      foreach ($tareasSinAtender[$nombre] as $tarea): ?>
                        <tr data-id="<?php echo $tarea['id']; ?>" data-bs-toggle="modal" data-bs-target="#taskModal" data-tarea='<?php echo json_encode($tarea); ?>'>
                          <td><?php echo $tarea['nombre_tarea']; ?></td>
                          <td><?php echo $tarea['asignado_a']; ?></td>
                          <td><?php echo $tarea['fecha']; ?></td>
                          <!-- ...otros datos... -->
                        </tr>
                    <?php endforeach; } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="tab-pane fade show active" id="<?php echo $username; ?>Tab">
          <!-- Grupo de botones de radio para seleccionar la vista -->
          <div class="radio-group">
            <input type="radio" id="atendidos<?php echo $username; ?>" name="view<?php echo $username; ?>" value="atendidos">
            <label for="atendidos<?php echo $username; ?>">Atendidos</label>

            <input type="radio" id="sinAtender<?php echo $username; ?>" name="view<?php echo $username; ?>" value="sinAtender" checked>
            <label for="sinAtender<?php echo $username; ?>">Sin Atender</label>
          </div>

          <!-- Contenedor para la vista seleccionada -->
          <div class="view-container">
            <!-- Vista de Atendidos -->
            <div class="atendidos-view" id="atendidosView<?php echo $username; ?>" style="display: none;">
              <table class="task-table">
                <thead>
                  <tr>
                    <th>Nombre de la Tarea</th>
                    <th>Asignado a</th>
                    <th>Fecha Atención</th>
                    <!-- ...otros encabezados... -->
                  </tr>
                </thead>
                <tbody>
                  <?php if(isset($tareasAtendidas[$username])): ?>
                    <?php foreach ($tareasAtendidas[$username] as $tarea): ?>
                      <tr data-id="<?php echo $tarea['id']; ?>" data-bs-toggle="modal" data-bs-target="#taskModal" data-tarea='<?php echo json_encode($tarea); ?>'>
                        <td><?php echo $tarea['nombre_tarea']; ?></td>
                        <td><?php echo $tarea['asignado_a']; ?></td>
                        <td><?php echo $tarea['fecha_atencion']; ?></td>
                        <!-- ...otros datos... -->
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <!-- Vista de Sin Atender -->
            <div class="sinAtender-view" id="sinAtenderView<?php echo $username; ?>">
              <table class="task-table">
                <thead>
                  <tr>
                    <th>Nombre de la Tarea</th>
                    <th>Asignado a</th>
                    <th>Fecha</th>
                    <!-- ...otros encabezados... -->
                  </tr>
                </thead>
                <tbody>
                  <?php if(isset($tareasSinAtender[$username])): ?>
                    <?php foreach ($tareasSinAtender[$username] as $tarea): ?>
                      <tr data-id="<?php echo $tarea['id']; ?>" data-bs-toggle="modal" data-bs-target="#taskModal" data-tarea='<?php echo json_encode($tarea); ?>'>
                        <td><?php echo $tarea['nombre_tarea']; ?></td>
                        <td><?php echo $tarea['asignado_a']; ?></td>
                        <td><?php echo $tarea['fecha']; ?></td>
                        <!-- ...otros datos... -->
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Modal Nueva Tarea -->
    <div class="modal fade" id="newTaskModal" tabindex="-1" aria-labelledby="newTaskModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="newTaskModalLabel">Nueva Tarea</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <!-- Actualización del formulario -->
            <form id="newTaskForm" action="add_task.php" method="POST" enctype="multipart/form-data">
              <div class="row">
                <div class="col-md-6">
                  <label for="taskName" class="form-label">Nombre de la Tarea</label>
                  <input type="text" class="form-control" id="taskName" name="taskName" required>
                </div>
                <div class="col-md-6">
                  <label for="taskSubject" class="form-label">Asunto</label>
                  <input type="text" class="form-control" id="taskSubject" name="taskSubject" list="asuntoList" required>
                  <datalist id="asuntoList">
                    <?php foreach ($asuntos as $asunto): ?>
                      <option value="<?php echo htmlspecialchars($asunto); ?>"></option>
                    <?php endforeach; ?>
                  </datalist>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <label for="assignedTo" class="form-label">Asignado a</label>
                  <select class="form-control" id="assignedTo" name="assignedTo">
                  <option>Angie</option>
                  <option>Carol</option>
                  <option>Elisa</option>
                  <option>Roman</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label for="taskDate" class="form-label">Fecha</label>
                  <input type="text" class="form-control" id="taskDate" name="taskDate" required>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <label for="taskAddress" class="form-label">Dirección</label>
                  <input type="text" class="form-control" id="taskAddress" name="taskAddress" list="direccionList" required>
                  <datalist id="direccionList">
                    <?php foreach ($direcciones as $direccion): ?>
                      <option value="<?php echo htmlspecialchars($direccion); ?>"></option>
                    <?php endforeach; ?>
                  </datalist>
                </div>
                <div class="col-md-6">
                  <label for="taskPhone" class="form-label">Teléfono</label>
                  <input type="tel" class="form-control" id="taskPhone" name="taskPhone" required>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <label for="taskRequestedBy" class="form-label">A petición de</label>
                  <input type="text" class="form-control" id="taskRequestedBy" name="taskRequestedBy" required>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <label for="taskDescription" class="form-label">Descripción</label>
                  <textarea class="form-control" id="taskDescription" name="taskDescription" rows="3" required></textarea>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <label for="taskAttachments" class="form-label">Adjuntar Evidencias</label>
                  <input type="file" class="form-control" id="taskAttachments" name="taskAttachments[]" multiple>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Guardar Tarea</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Detalle de Tarea -->
    <div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content position-relative">
          <div class="modal-header">
            <h5 class="modal-title">Detalle de la Tarea</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <form id="taskDetailForm">
              <div class="row">
                <div class="col-md-6">
                  <label for="detailName" class="form-label">Nombre de la Tarea</label>
                  <input type="text" class="form-control" id="detailName" readonly>
                </div>
                <div class="col-md-6">
                  <label for="detailSubject" class="form-label">Asunto</label>
                  <input type="text" class="form-control" id="detailSubject" readonly>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <label for="detailAssignedTo" class="form-label">Asignado a</label>
                  <input type="text" class="form-control" id="detailAssignedTo" readonly>
                </div>
                <div class="col-md-6">
                  <label for="detailDate" class="form-label">Fecha</label>
                  <input type="text" class="form-control" id="detailDate" readonly>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <label for="detailAddress" class="form-label">Dirección</label>
                  <input type="text" class="form-control" id="detailAddress" readonly>
                </div>
                <div class="col-md-6">
                  <label for="detailPhone" class="form-label">Teléfono</label>
                  <input type="tel" class="form-control" id="detailPhone" readonly>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <label for="detailRequestedBy" class="form-label">A petición de</label>
                  <input type="text" class="form-control" id="detailRequestedBy" readonly>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <label for="detailDescription" class="form-label">Descripción</label>
                  <textarea class="form-control" id="detailDescription" rows="3" readonly></textarea>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <label for="detailAttachments" class="form-label">Adjuntar Evidencias</label>
                  <div id="detailAttachments"></div>
                </div>
              </div>
              <input type="hidden" id="detailId" value="">
            </form>
          </div>
          <?php if ($user_type == 'usuario'): ?>
          <button type="button" id="attendButton" class="btn btn-attend">
            <i class="fas fa-arrow-right"></i>
          </button>
          <?php endif; ?>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Footer actualizado -->
  <footer>
    Gobierno Digital
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const taskTabs = document.getElementById('taskTabs');
      const assignedToSelect = document.getElementById('assignedTo');

      // Configurar Flatpickr
      flatpickr("#taskDate", {
        locale: "es",
        dateFormat: "Y-m-d",
      });

      // Manejar el botón "Nueva Tarea"
      document.getElementById('new-task-btn').addEventListener('click', function() {
        const activeTab = taskTabs.querySelector('.nav-link.active').textContent;
        Array.from(assignedToSelect.options).forEach(option => {
          option.selected = option.text === activeTab;
        });
      });

      // Manejar los botones de radio para vista (Atendidos o Sin Atender)
      const radioGroups = document.querySelectorAll('.radio-group');
      radioGroups.forEach(function(group) {
        const radios = group.querySelectorAll('input[type="radio"]');
        radios.forEach(function(radio) {
          radio.addEventListener('change', function() {
            const nombre = this.id.replace(this.value, '');
            var atendidosView = document.getElementById('atendidosView' + nombre);
            var sinAtenderView = document.getElementById('sinAtenderView' + nombre);

            if (this.value === 'atendidos') {
              atendidosView.style.display = 'block';
              sinAtenderView.style.display = 'none';
            } else {
              atendidosView.style.display = 'none';
              sinAtenderView.style.display = 'block';
            }
          });
        });
      });

      // Validar el formulario antes de enviarlo
      const newTaskForm = document.getElementById('newTaskForm');
      newTaskForm.addEventListener('submit', function(event) {
        const taskDate = document.getElementById('taskDate').value;
        if (!taskDate) {
          event.preventDefault();
          alert('Por favor, selecciona una fecha para la tarea.');
        }
      });
    });

    // Evento para abrir el modal con los detalles de la tarea
    var taskModal = document.getElementById('taskModal');
    taskModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var tarea = JSON.parse(button.getAttribute('data-tarea'));

        // Llenar los campos del modal
        document.getElementById('detailName').value = tarea.nombre_tarea;
        document.getElementById('detailSubject').value = tarea.asunto;
        document.getElementById('detailAssignedTo').value = tarea.asignado_a;
        document.getElementById('detailDate').value = tarea.fecha;
        document.getElementById('detailAddress').value = tarea.direccion;
        document.getElementById('detailPhone').value = tarea.telefono;
        document.getElementById('detailRequestedBy').value = tarea.peticion_de;
        document.getElementById('detailDescription').value = tarea.descripcion;
        document.getElementById('detailId').value = tarea.id;

        // Obtener los adjuntos de la tarea usando AJAX
        var adjuntosDiv = document.getElementById('detailAttachments');
        adjuntosDiv.innerHTML = 'Cargando...';

        fetch('get_adjuntos.php?tarea_id=' + tarea.id)
            .then(response => response.json())
            .then(data => {
                adjuntosDiv.innerHTML = '';
                if(data.length > 0){
                    data.forEach(function(adjunto) {
                        var link = document.createElement('a');
                        link.href = adjunto.ruta_archivo;
                        link.textContent = adjunto.nombre_archivo;
                        link.target = '_blank';
                        link.classList.add('btn', 'btn-link');
                        adjuntosDiv.appendChild(link);
                    });
                } else {
                    adjuntosDiv.innerHTML = 'No hay adjuntos.';
                }
            })
            .catch(error => {
                adjuntosDiv.innerHTML = 'Error al cargar adjuntos.';
                console.error('Error:', error);
            });
    });

    // Obtener referencia al botón "Atender"
    var attendButton = document.getElementById('attendButton');
    if (attendButton) {
      attendButton.addEventListener('click', function() {
        // Animación para ocultar el formulario existente
        var modalContent = document.querySelector('#taskModal .modal-content');
        modalContent.classList.add('animate__animated', 'animate__fadeOutLeft');
        
        modalContent.addEventListener('animationend', function() {
          // Limpiar el contenido del modal y mostrar el nuevo formulario
          var modalBody = document.querySelector('#taskModal .modal-body');
          modalBody.innerHTML = `
            <form id="attendForm" action="attend_task.php" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="tarea_id" value="${document.getElementById('detailId').value}">
              <div class="mb-3">
                <label for="observations" class="form-label">Observaciones</label>
                <textarea class="form-control" id="observations" name="observations" rows="3" required></textarea>
              </div>
              <div class="mb-3">
                <label for="evidence" class="form-label">Cargar Evidencia</label>
                <input type="file" class="form-control" id="evidence" name="evidence[]" multiple>
              </div>
              <div class="mb-3">
                <label class="form-label">Estado</label>
                <select class="form-control" name="status" required>
                  <option value="">Selecciona un estado</option>
                  <option value="Completada">Completada</option>
                  <option value="En Progreso">En Progreso</option>
                </select>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
              </div>
            </form>
          `;

          // Remover clases de animación
          modalContent.classList.remove('animate__animated', 'animate__fadeOutLeft');
          modalContent.classList.add('animate__animated', 'animate__fadeInRight');
        }, {once: true});
      });
    }

    // Evento para abrir el modal y llenar el campo hidden 'detailId'
    document.getElementById('taskModal').addEventListener('show.bs.modal', function (event) {
      var button = event.relatedTarget;
      var tareaId = button.getAttribute('data-id');
      document.getElementById('detailId').value = tareaId;
    });
  </script>
</body>
</html>


