-- 1. BASE DE DATOS
-- Crea y selecciona la base de datos del sistema.
IF DB_ID(N'SistemaEsterilizacion') IS NULL
BEGIN
    CREATE DATABASE SistemaEsterilizacion;
END;
GO

USE SistemaEsterilizacion;
GO

-- 2. USUARIOS
-- Catalogo de personas que reciben, entregan u operan procesos.
CREATE TABLE dbo.usuarios (
    id INT IDENTITY(1,1) PRIMARY KEY,
    nombres NVARCHAR(100) NOT NULL,
    apellidos NVARCHAR(100) NOT NULL,
    activo BIT NOT NULL DEFAULT 1,
    creado_en DATETIME2 NOT NULL DEFAULT SYSDATETIME()
);
GO
-- 3. SERVICIOS HOSPITALARIOS
-- Catalogo de servicios desde donde se reciben materiales.
CREATE TABLE dbo.servicios (
    id INT IDENTITY(1,1) PRIMARY KEY,
    nombre NVARCHAR(100) NOT NULL UNIQUE,
    activo BIT NOT NULL DEFAULT 1,
    creado_en DATETIME2 NOT NULL DEFAULT SYSDATETIME()
);
GO

-- 4. MATERIALES E INSTRUMENTALES
-- Cada material pertenece a un servicio.
CREATE TABLE dbo.materiales (
    id INT IDENTITY(1,1) PRIMARY KEY,
    servicio_id INT NOT NULL,
    nombre NVARCHAR(150) NOT NULL,
    activo BIT NOT NULL DEFAULT 1,
    creado_en DATETIME2 NOT NULL DEFAULT SYSDATETIME(),

    CONSTRAINT FK_materiales_servicios
        FOREIGN KEY (servicio_id)
        REFERENCES dbo.servicios(id),

    CONSTRAINT UQ_materiales_servicio_nombre
        UNIQUE (servicio_id, nombre)
);
GO

-- 5. EQUIPOS BIOMEDICOS
-- Catalogo de equipos usados en el proceso de lavado o esterilizacion.
CREATE TABLE dbo.equipos_biomedicos (
    id INT IDENTITY(1,1) PRIMARY KEY,
    nombre NVARCHAR(100) NOT NULL UNIQUE,
    activo BIT NOT NULL DEFAULT 1,
    creado_en DATETIME2 NOT NULL DEFAULT SYSDATETIME()
);
GO

-- 6. PROCESO PRINCIPAL: AREA ROJA
-- Registra la recepcion inicial, lavado y material recibido.
CREATE TABLE dbo.proceso_esterilizacion (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,

    servicio_id INT NOT NULL,
    fecha DATE NOT NULL,
    turno NVARCHAR(10) NOT NULL,
    hora TIME(0) NULL,

    recibe_id INT NOT NULL,
    entrega_id INT NOT NULL,

    material_id INT NOT NULL,
    cantidad_piezas INT NOT NULL,

    tipo_lavado NVARCHAR(30) NOT NULL,
    equipo_biomedico_id INT NOT NULL,
    operador_id INT NOT NULL,

    estado_actual NVARCHAR(10) NOT NULL DEFAULT N'ROJA',

    creado_en DATETIME2 NOT NULL DEFAULT SYSDATETIME(),
    actualizado_en DATETIME2 NOT NULL DEFAULT SYSDATETIME(),

    CONSTRAINT FK_proceso_servicio
        FOREIGN KEY (servicio_id)
        REFERENCES dbo.servicios(id),

    CONSTRAINT FK_proceso_recibe
        FOREIGN KEY (recibe_id)
        REFERENCES dbo.usuarios(id),

    CONSTRAINT FK_proceso_entrega
        FOREIGN KEY (entrega_id)
        REFERENCES dbo.usuarios(id),

    CONSTRAINT FK_proceso_material
        FOREIGN KEY (material_id)
        REFERENCES dbo.materiales(id),

    CONSTRAINT FK_proceso_equipo
        FOREIGN KEY (equipo_biomedico_id)
        REFERENCES dbo.equipos_biomedicos(id),

    CONSTRAINT FK_proceso_operador
        FOREIGN KEY (operador_id)
        REFERENCES dbo.usuarios(id),

    CONSTRAINT CK_proceso_turno
        CHECK (turno IN (N'DIURNO', N'NOCTURNO')),

    CONSTRAINT CK_proceso_tipo_lavado
        CHECK (
            tipo_lavado IN (
                N'ULTRASONICA',
                N'MANUAL',
                N'LAVADORA_DESCONTAMINADORA',
                N'NO_CORRESPONDE'
            )
        ),

    CONSTRAINT CK_proceso_cantidad
        CHECK (cantidad_piezas > 0),

    CONSTRAINT CK_proceso_estado
        CHECK (estado_actual IN (N'ROJA', N'AZUL', N'VERDE'))
);
GO

-- 7. AREA AZUL
-- Registra empaque, tipo de carga, numero de carga, IB y operador.
CREATE TABLE dbo.area_azul (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    proceso_id BIGINT NOT NULL UNIQUE,

    hora TIME(0) NULL,
    empaque NVARCHAR(150) NULL,
    tipo_carga NVARCHAR(20) NOT NULL,
    numero_carga INT NULL,
    ib NVARCHAR(100) NULL,
    operador_id INT NOT NULL,

    creado_en DATETIME2 NOT NULL DEFAULT SYSDATETIME(),
    actualizado_en DATETIME2 NOT NULL DEFAULT SYSDATETIME(),

    CONSTRAINT FK_azul_proceso
        FOREIGN KEY (proceso_id)
        REFERENCES dbo.proceso_esterilizacion(id),

    CONSTRAINT FK_azul_operador
        FOREIGN KEY (operador_id)
        REFERENCES dbo.usuarios(id),

    CONSTRAINT CK_azul_tipo_carga
        CHECK (tipo_carga IN (N'INSTRUMENTAL', N'TEXTIL', N'GOMA')),

    CONSTRAINT CK_azul_numero_carga
        CHECK (numero_carga IS NULL OR numero_carga > 0)
);
GO

-- 8. AREA VERDE
-- Registra descarga, entrega final, recepcion y observacion del proceso.
CREATE TABLE dbo.area_verde (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    proceso_id BIGINT NOT NULL UNIQUE,

    fecha_entrega DATE NULL,
    hora_entrega TIME(0) NULL,
    hora_descarga TIME(0) NULL,

    entrega_id INT NOT NULL,
    recibe_id INT NOT NULL,

    observacion NVARCHAR(1000) NULL,

    creado_en DATETIME2 NOT NULL DEFAULT SYSDATETIME(),
    actualizado_en DATETIME2 NOT NULL DEFAULT SYSDATETIME(),

    CONSTRAINT FK_verde_proceso
        FOREIGN KEY (proceso_id)
        REFERENCES dbo.proceso_esterilizacion(id),

    CONSTRAINT FK_verde_entrega
        FOREIGN KEY (entrega_id)
        REFERENCES dbo.usuarios(id),

    CONSTRAINT FK_verde_recibe
        FOREIGN KEY (recibe_id)
        REFERENCES dbo.usuarios(id)
);
GO

-- 9. INDICES PARA CONSULTAS FRECUENTES
-- Mejoran las consultas por fecha, servicio, estado y material.
CREATE INDEX IX_proceso_fecha
    ON dbo.proceso_esterilizacion(fecha);

CREATE INDEX IX_proceso_servicio_fecha
    ON dbo.proceso_esterilizacion(servicio_id, fecha);

CREATE INDEX IX_proceso_estado_fecha
    ON dbo.proceso_esterilizacion(estado_actual, fecha);

CREATE INDEX IX_proceso_material
    ON dbo.proceso_esterilizacion(material_id);
GO

-- 10. USUARIOS DE EJEMPLO
-- Datos iniciales para probar el sistema.
INSERT INTO dbo.usuarios (nombres, apellidos)
VALUES
    (N'Zoila', N'Fonseca'),
    (N'Elizabeth', N'Aguirre'),
    (N'MarÃ­a', N'RodrÃ­guez'),
    (N'Carlos', N'Mendoza'),
    (N'Ana', N'Torres');
GO

-- 11. SERVICIOS DE EJEMPLO
-- Servicios hospitalarios precargados.
INSERT INTO dbo.servicios (nombre)
VALUES
    (N'SOP'),
    (N'CENTRO OBSTÃ‰TRICO'),
    (N'NEONATOLOGÃA'),
    (N'HOSPITALIZACIÃ“N'),
    (N'GINECO EMERGENCIA');
GO

-- 12. MATERIALES POR SERVICIO
-- Materiales asociados a cada servicio de ejemplo.
INSERT INTO dbo.materiales (servicio_id, nombre)
VALUES
    (1, N'CIRUGÃA MAYOR'),
    (1, N'PAQUETES DE GASA 10X10'),
    (1, N'MANDILONES'),
    (2, N'EQUIPO DE PARTO'),
    (2, N'PINZA ROMPE BOLSA'),
    (2, N'PINZA FOERSTER CURVA'),
    (2, N'TIJERA DE EPISIOTOMÃA CURVA'),
    (3, N'EQUIPO DE CURACIÃ“N'),
    (4, N'FCO DE ASPIRACIÃ“N'),
    (4, N'CONEXIONES'),
    (5, N'RIÃ‘ONERA');
GO

-- 13. EQUIPOS BIOMEDICOS
-- Equipos disponibles para seleccionar en Area Roja.
INSERT INTO dbo.equipos_biomedicos (nombre)
VALUES
    (N'AUTOCLAVE'),
    (N'LAVADORA DESCONTAMINADORA'),
    (N'ULTRASÃ“NICA'),
    (N'OTRO'),
    (N'NO CORRESPONDE');
GO
