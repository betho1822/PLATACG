<?php
session_start();

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['username']) || $_SESSION['user_type'] != 'administrador') {
    header("Location: index.php?error=permisos");
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

    // Obtener y sanitizar datos del formulario
    $nombre_tarea = trim($_POST['taskName']);
    $asunto = trim($_POST['taskSubject']);
    $asignado_a = trim($_POST['assignedTo']);
    $fecha = trim($_POST['taskDate']);
    $direccion = trim($_POST['taskAddress']);
    $telefono = trim($_POST['taskPhone']);
    $peticion_de = trim($_POST['taskRequestedBy']);
    $descripcion = trim($_POST['taskDescription']);
    $created_at = date('Y-m-d H:i:s');
    $creado_por = $_SESSION['username'];

    // Insertar la tarea
    $sql = "INSERT INTO tareas (nombre_tarea, asunto, asignado_a, fecha, direccion, telefono, peticion_de, descripcion, estado, created_at, creado_por)
            VALUES (:nombre_tarea, :asunto, :asignado_a, :fecha, :direccion, :telefono, :peticion_de, :descripcion, '', :created_at, :creado_por)
            RETURNING id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre_tarea' => $nombre_tarea,
        ':asunto' => $asunto,
        ':asignado_a' => $asignado_a,
        ':fecha' => $fecha,
        ':direccion' => $direccion,
        ':telefono' => $telefono,
        ':peticion_de' => $peticion_de,
        ':descripcion' => $descripcion,
        ':created_at' => $created_at,
        ':creado_por' => $creado_por
    ]);
    $tarea_id = $stmt->fetchColumn();

    // Manejar los adjuntos
    if(isset($_FILES['taskAttachments']) && $_FILES['taskAttachments']['error'][0] != UPLOAD_ERR_NO_FILE) {
        $files = $_FILES['taskAttachments'];
        for($i=0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $nombre_archivo = basename($files['name'][$i]);
                $ruta_directorio = "uploads/$tarea_id/";

                // Crear el directorio si no existe
                if (!is_dir($ruta_directorio)) {
                    mkdir($ruta_directorio, 0755, true);
                }

                $ruta_archivo = $ruta_directorio . $nombre_archivo;

                // Mover el archivo
                if(move_uploaded_file($files['tmp_name'][$i], $ruta_archivo)) {
                    // Insertar el registro en adjuntos
                    $sql_adj = "INSERT INTO adjuntos (tarea_id, nombre_archivo, ruta_archivo)
                                VALUES (:tarea_id, :nombre_archivo, :ruta_archivo)";
                    $stmt_adj = $pdo->prepare($sql_adj);
                    $stmt_adj->execute([
                        ':tarea_id' => $tarea_id,
                        ':nombre_archivo' => $nombre_archivo,
                        ':ruta_archivo' => $ruta_archivo
                    ]);
                }
            }
        }
    }

    // Redireccionar de vuelta a index.php con mensaje de éxito
    header("Location: index.php?success=agregada");
    exit;
} catch (PDOException $e) {
    die("Error al agregar tarea: " . $e->getMessage());
}
?>
