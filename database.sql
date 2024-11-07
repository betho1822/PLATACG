-- Crear tabla 'tareas'
CREATE TABLE IF NOT EXISTS tareas (
    id SERIAL PRIMARY KEY,
    nombre_tarea VARCHAR(255) NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    asignado_a VARCHAR(50) NOT NULL,
    fecha TIMESTAMP NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    peticion_de VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL,
    creado_por VARCHAR(100) NOT NULL
);

-- Actualizar registros existentes de 'Caro' a 'Carol'
UPDATE tareas
SET asignado_a = 'Carol'
WHERE asignado_a = 'Caro';

-- Insertar nuevos registros con el nombre 'Carol'
INSERT INTO tareas (nombre_tarea, asunto, asignado_a, fecha, direccion, telefono, peticion_de, descripcion, created_at, creado_por)
VALUES 
('Tarea 1', 'Asunto 1', 'Carol', '2023-10-01 10:00:00', 'Dirección 1', '1234567890', 'Petición 1', 'Descripción de la Tarea 1', '2023-10-01 09:00:00', 'usuario1'),
('Tarea 2', 'Asunto 2', 'Angie', '2023-10-02 11:00:00', 'Dirección 2', '0987654321', 'Petición 2', 'Descripción de la Tarea 2', '2023-10-02 10:30:00', 'usuario2'),
('Tarea 3', 'Asunto 3', 'Elisa', '2023-10-03 12:00:00', 'Dirección 3', '1122334455', 'Petición 3', 'Descripción de la Tarea 3', '2023-10-03 11:45:00', 'usuario3'),
('Tarea 4', 'Asunto 4', 'Roman', '2023-10-04 13:00:00', 'Dirección 4', '5566778899', 'Petición 4', 'Descripción de la Tarea 4', '2023-10-04 12:15:00', 'usuario4'),
('Tarea 5', 'Asunto 5', 'Carol', '2023-10-05 14:00:00', 'Dirección 5', '6677889900', 'Petición 5', 'Descripción de la Tarea 5', '2023-10-05 13:30:00', 'usuario5');

CREATE TABLE adjuntos (
  id SERIAL PRIMARY KEY,
  tarea_id INT NOT NULL,
  nombre_archivo VARCHAR(255) NOT NULL,
  ruta_archivo VARCHAR(255) NOT NULL,
  FOREIGN KEY (tarea_id) REFERENCES tareas(id) ON DELETE CASCADE
);

CREATE TABLE usuarios (
  id SERIAL PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
);

-- Insertar usuario "Claudia" con contraseña 'Segura2023'
INSERT INTO usuarios (username, password_hash) VALUES
('Roman', '$2y$10$NO3BnPyG/LPwpPRqr7zLAu0DobfevmfyDXH4n3bsHhbTxOmuG06ee'); -- La contraseña es 'Segura2023'

Claudia // clau123 
Roman // roman123