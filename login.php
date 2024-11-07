<!DOCTYPE html>
<html lang="es">
<head>
  <!-- Meta etiquetas y enlaces necesarios -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inicio de Sesión</title>
  <!-- Enlace a Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Fuente adicional para "Pacifico" -->
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: #f4faff;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Fuente moderna */
      position: relative;
      overflow: hidden;
      color: #333333; /* Texto oscuro para mejor legibilidad */
      flex-direction: column; /* Permite que el footer esté debajo del formulario */
      padding-bottom: 60px; /* Ajustar según la altura del footer */
    }
    .login-container {
      background-color: #ffffff;
      padding: 40px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
    }
    .login-title {
      font-family: 'Pacifico', cursive;
      color: #00b0ff;
      margin-bottom: 20px;
      text-align: center;
    }
    .login-form {
      max-width: 400px;
      width: 100%;
      padding: 40px;
      background-color: #f9f9f9; /* Fondo del formulario claro */
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Sombra sutil */
      z-index: 1;
    }
    .login-form h2 {
      margin-bottom: 30px;
      color: #007bff; /* Color atractivo */
      text-align: center;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; /* Fuente mejorada */
      animation: fadeInDown 1s ease-out;
    }
    .login-form .form-label {
      color: #555555;
      font-weight: bold;
      font-family: 'Segoe UI', sans-serif;
    }
    .login-form .form-control {
      border-radius: 4px;
      border: 1px solid #cccccc;
      background-color: #ffffff;
      color: #333333;
      font-family: 'Segoe UI', sans-serif;
    }
    .login-form .btn-primary {
      background-color: #007bff;
      border-color: #007bff;
      color: #ffffff;
      font-weight: bold;
      transition: background-color 0.3s ease;
      font-family: 'Segoe UI', sans-serif;
    }
    .login-form .btn-primary:hover {
      background-color: #0056b3;
    }
    /* Eliminar estilos relacionados con el header */
    
    /* Actualizar estilos para el footer */
    footer {
      color: #007bff;
      font-size: 1.5em; /* Reducir tamaño de fuente */
      font-family: 'Pacifico', cursive; /* Fuente elegante */
      animation: bounceIn 2s infinite;
      /* Cambiar la posición del footer a fija */
      position: fixed;
      bottom: 0;
      width: 100%;
      /* Añadir un fondo para evitar que el contenido se sobreponga */
      background-color: #ffffff;
      text-align: center;
      padding: 10px 0;
    }
    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    @keyframes bounceIn {
      0%, 20%, 40%, 60%, 80%, 100% {
        transform: translateY(0);
      }
      10%, 30%, 50%, 70%, 90% {
        transform: translateY(-5px); /* Reducir amplitud del rebote */
      }
    }
    @keyframes fadeIn {
      0% { opacity: 0; }
      50% { opacity: 1; }
      100% { opacity: 0; }
    }
  </style>
</head>
<body>
  <!-- Formulario de inicio de sesión -->
  <div class="login-container">
    <h2 class="login-title">Inicio de Sesión</h2>
    <?php if(isset($_GET['error'])): ?>
      <div class="alert alert-danger">Nombre de usuario o contraseña incorrectos.</div>
    <?php endif; ?>
    <form action="authenticate.php" method="POST">
      <div class="mb-3">
        <label for="username" class="form-label">Nombre de Usuario</label>
        <input type="text" class="form-control" id="username" name="username" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>
  </div>
  <!-- Footer con animación -->
  <footer>
    Gobierno Digital
  </footer>
  <!-- Scripts necesarios -->
  <!-- Enlace a Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
