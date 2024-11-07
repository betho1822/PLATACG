<?php

header('Content-Type: application/json');

// Verificar si se ha proporcionado el ID de la tarea
if (!isset($_GET['tarea_id'])) {
    echo json_encode([]);
    exit;
}

$tarea_id = intval($_GET['tarea_id']);

// Configuración de la conexión a PostgreSQL
$host = "192.168.1.102";
$db = "plata_pruebas";
$user = "postgres";
$pass = "0888852339";
$port = "1822";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Preparar y ejecutar la consulta para obtener adjuntos
    $sql = "
    SELECT nombre_archivo, ruta_archivo FROM adjuntos WHERE tarea_id = :tarea_id
    UNION
    SELECT nombre_archivo, ruta_archivo FROM adjuntos_atendidos WHERE tarea_id = :tarea_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':tarea_id' => $tarea_id]);
    $adjuntos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($adjuntos);
} catch (PDOException $e) {
    echo json_encode([]);
    exit;
}
?>
