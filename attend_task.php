<?php
session_start();

// Verificar si el usuario está autenticado y es de tipo 'usuario'
if (!isset($_SESSION['username']) || $_SESSION['user_type'] != 'usuario') {
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
    $tarea_id = intval($_POST['tarea_id']);
    $observaciones = trim($_POST['observations']);
    $estado = trim($_POST['status']);
    $atendido_por = $_SESSION['username'];
    $fecha_atencion = date('Y-m-d H:i:s');

    // Validar que la tarea pertenece al usuario
    $stmt_validar = $pdo->prepare("SELECT asignado_a FROM tareas WHERE id = :tarea_id");
    $stmt_validar->execute([':tarea_id' => $tarea_id]);
    $tarea = $stmt_validar->fetch(PDO::FETCH_ASSOC);

    if (!$tarea || $tarea['asignado_a'] !== $atendido_por) {
        header("Location: index.php?error=tarea_no_permitida");
        exit;
    }

    // Actualizar la tarea con los datos de atención
    $sql = "UPDATE tareas SET 
                observaciones = :observaciones, 
                estado = :estado, 
                atendido_por = :atendido_por, 
                fecha_atencion = :fecha_atencion
            WHERE id = :tarea_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':observaciones' => $observaciones,
        ':estado' => $estado, // Asegurarse de que 'estado' no sea vacío
        ':atendido_por' => $atendido_por,
        ':fecha_atencion' => $fecha_atencion,
        ':tarea_id' => $tarea_id
    ]);

    // Manejar los adjuntos
    if (isset($_FILES['evidence']) && $_FILES['evidence']['error'][0] != UPLOAD_ERR_NO_FILE) {
        $files = $_FILES['evidence'];
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $nombre_archivo = basename($files['name'][$i]);
                $ruta_directorio = "uploads/atendidos/$tarea_id/";

                // Crear el directorio si no existe
                if (!is_dir($ruta_directorio)) {
                    mkdir($ruta_directorio, 0755, true);
                }

                $ruta_archivo = $ruta_directorio . $nombre_archivo;

                // Mover el archivo
                if (move_uploaded_file($files['tmp_name'][$i], $ruta_archivo)) {
                    // Insertar el registro en adjuntos_atendidos
                    $sql_adj = "INSERT INTO adjuntos_atendidos (tarea_id, nombre_archivo, ruta_archivo)
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
    header("Location: index.php?success=atendido");
    exit;

} catch (PDOException $e) {
    die("Error al actualizar la tarea: " . $e->getMessage());
}
?>
