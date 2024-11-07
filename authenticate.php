<?php
session_start();
// Conexión a la base de datos
$host = "192.168.1.102";
$db = "plata_pruebas";
$user = "postgres";
$pass = "0888852339";
$port = "1822";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Obtener y sanitizar los datos del formulario
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Preparar y ejecutar la consulta de usuario
    $stmt = $pdo->prepare("SELECT username, password_hash, tipo_usuario FROM usuarios WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Evitar la regeneración de la sesión para prevenir fijación de sesiones
        session_regenerate_id(true);
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['tipo_usuario']; // Guardar el tipo de usuario en sesión
        header("Location: index.php");
        exit;
    } else {
        header("Location: login.php?error=1");
        exit;
    }
} catch (PDOException $e) {
    // Manejar errores de conexión o consulta
    header("Location: login.php?error=1");
    exit;
}
?>
