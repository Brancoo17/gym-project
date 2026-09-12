CREATE DATABASE IF NOT EXISTS gym_mvc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gym_mvc;

DROP TABLE IF EXISTS avisos_enviados;
DROP TABLE IF EXISTS rutina_ejercicios;
DROP TABLE IF EXISTS rutinas;
DROP TABLE IF EXISTS ejercicios;
DROP TABLE IF EXISTS asistencias;
DROP TABLE IF EXISTS reservas;
DROP TABLE IF EXISTS horarios;
DROP TABLE IF EXISTS entrenador_clientes;
DROP TABLE IF EXISTS pagos;
DROP TABLE IF EXISTS membresias;
DROP TABLE IF EXISTS planes;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL,
    apellido VARCHAR(60) NOT NULL,
    dni VARCHAR(20) NULL DEFAULT NULL,
    email VARCHAR(60) NOT NULL UNIQUE,
    password VARCHAR(60) NOT NULL,
    telefono VARCHAR(15),
    rol ENUM('cliente', 'admin', 'entrenador') NOT NULL DEFAULT 'cliente',
    confirmado TINYINT(1) NOT NULL DEFAULT 0,
    token VARCHAR(15)
) ENGINE=InnoDB;

CREATE TABLE planes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL,
    tipo_disciplina VARCHAR(30) NOT NULL DEFAULT 'musculacion',
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    duracion_dias INT NOT NULL,
    cupo_opcional INT NULL,
    imagen VARCHAR(100),
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE membresias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NOT NULL,
    estado ENUM('pendiente', 'activa', 'vencida', 'cancelada') NOT NULL DEFAULT 'pendiente',
    fecha_inicio DATE NULL,
    fecha_fin DATE NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES planes(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE pagos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    membresia_id INT UNSIGNED NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    mp_preference_id VARCHAR(100),
    mp_payment_id VARCHAR(100),
    mp_status VARCHAR(50),
    FOREIGN KEY (membresia_id) REFERENCES membresias(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE entrenador_clientes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entrenador_id INT UNSIGNED NOT NULL,
    cliente_id INT UNSIGNED NOT NULL,
    UNIQUE (entrenador_id, cliente_id),
    FOREIGN KEY (entrenador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE horarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    entrenador_id INT UNSIGNED NOT NULL,
    dia_semana TINYINT NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    cupo INT NULL,
    descripcion TEXT NULL,
    FOREIGN KEY (plan_id) REFERENCES planes(id) ON DELETE CASCADE,
    FOREIGN KEY (entrenador_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reservas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    horario_id INT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    estado ENUM('reservada', 'cancelada') NOT NULL DEFAULT 'reservada',
    UNIQUE (usuario_id, horario_id, fecha),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (horario_id) REFERENCES horarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE asistencias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT UNSIGNED NOT NULL UNIQUE,
    presente TINYINT(1) NOT NULL DEFAULT 0,
    marcado_por INT UNSIGNED NOT NULL,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (marcado_por) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ejercicios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    grupo_muscular VARCHAR(60),
    descripcion TEXT,
    imagen VARCHAR(100),
    video_url VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE rutinas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    cliente_id INT UNSIGNED NULL,
    entrenador_id INT UNSIGNED NOT NULL,
    fecha DATE NULL,
    tipo_formato VARCHAR(30) NOT NULL DEFAULT 'estandar',
    wod_formato VARCHAR(30) NULL,
    wod_tiempo VARCHAR(50) NULL,
    wod_descripcion TEXT NULL,
    nombre VARCHAR(80),
    FOREIGN KEY (plan_id) REFERENCES planes(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (entrenador_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE rutina_ejercicios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rutina_id INT UNSIGNED NOT NULL,
    ejercicio_id INT UNSIGNED NOT NULL,
    bloque VARCHAR(30) NOT NULL DEFAULT 'general',
    rondas INT NULL,
    series INT NULL,
    reps INT NULL,
    peso_hombres VARCHAR(30) NULL,
    peso_mujeres VARCHAR(30) NULL,
    dia TINYINT NULL,
    orden INT NULL,
    notas VARCHAR(255) NULL,
    FOREIGN KEY (rutina_id) REFERENCES rutinas(id) ON DELETE CASCADE,
    FOREIGN KEY (ejercicio_id) REFERENCES ejercicios(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE avisos_enviados (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    membresia_id INT UNSIGNED NOT NULL,
    tipo ENUM('7dias', 'hoy') NOT NULL,
    enviado_at DATETIME NOT NULL,
    FOREIGN KEY (membresia_id) REFERENCES membresias(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE configuracion (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL DEFAULT 'GYM',
    logo VARCHAR(100) NULL,
    portada VARCHAR(100) NULL,
    email VARCHAR(60) NOT NULL DEFAULT 'admin@gym.com',
    whatsapp VARCHAR(20) NULL,
    instagram VARCHAR(100) NULL,
    facebook VARCHAR(100) NULL,
    tiktok VARCHAR(100) NULL,
    direccion VARCHAR(100) NULL DEFAULT 'Av. del Entrenamiento 123, Buenos Aires',
    mapa_url TEXT NULL,
    hero_titulo VARCHAR(150) NULL DEFAULT 'Entrená con un plan a tu medida',
    hero_descripcion VARCHAR(255) NULL DEFAULT 'Crossfit, musculación y funcional. Elegí tu membresía y reservá tu turno.',
    habilitar_turnos TINYINT(1) NOT NULL DEFAULT 1,
    habilitar_crossfit TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- Seed: admin (password = password)
INSERT INTO usuarios (nombre, apellido, email, password, telefono, rol, confirmado, token) VALUES
('Admin', 'Gym', 'admin@gym.com', '$2y$12$nqup3U1dXfRxrz9b4IuYNOFzSttGaCr6VHeXhvlKVn5lWjl7WS8/K', '1111111111', 'admin', 1, '');

-- Seed: 3 planes
INSERT INTO planes (nombre, tipo_disciplina, descripcion, precio, duracion_dias, cupo_opcional, imagen, activo) VALUES
('Crossfit', 'crossfit', 'Entrenamiento funcional de alta intensidad con clases grupales.', 25000.00, 30, 20, '', 1),
('Musculación', 'musculacion', 'Acceso libre a sala de pesas y máquinas para hipertrofia y fuerza.', 18000.00, 30, NULL, '', 1),
('Funcional', 'musculacion', 'Circuito de movilidad, core y acondicionamiento físico general.', 20000.00, 30, 16, '', 1);

-- Seed: configuracion por defecto
INSERT INTO configuracion (id, nombre, logo, portada, email, whatsapp, instagram, facebook, tiktok, direccion, mapa_url, hero_titulo, hero_descripcion, habilitar_turnos, habilitar_crossfit) VALUES
(1, 'GYM', '', '', 'admin@gym.com', '', '', '', '', 'Av. del Entrenamiento 123, Buenos Aires', '', 'Entrená con un plan a tu medida', 'Crossfit, musculación y funcional. Elegí tu membresía y reservá tu turno.', 1, 1);
