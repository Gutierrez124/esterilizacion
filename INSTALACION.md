# Instalacion local - Sistema de esterilizacion

## Configuracion elegida

El proyecto quedo igual que tu otro Laravel: usa MySQL/MariaDB de XAMPP, MVC y migraciones Laravel.

No necesitas ejecutar `BaseDeDatos.sql` en SQL Server Management Studio para esta version. Las tablas se crean con migraciones.

## 1. Activar XAMPP

Activa MySQL desde el panel de XAMPP.

Apache es opcional:

- Si usas `php artisan serve`, no necesitas Apache.
- Si quieres servirlo por Apache/XAMPP, entonces si activas Apache.

Para trabajar rapido usa:

```bash
php artisan serve
```

## 2. Base de datos

La base configurada es:

```text
sistema_esterilizacion
```

Ya fue creada localmente con MySQL de XAMPP.

Tu `.env` quedo asi:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_esterilizacion
DB_USERNAME=root
DB_PASSWORD=
```

Si tu MySQL tiene contraseña, ponla en `DB_PASSWORD`.

## 3. Crear tablas y datos

Desde `D:\Esterilizacion` ejecuta:

```bash
php artisan migrate:fresh --seed
```

Esto crea:

- `usuarios`
- `servicios`
- `materiales`
- `equipos_biomedicos`
- `proceso_esterilizacion`
- `area_azul`
- `area_verde`

Y carga datos iniciales para catálogos.

## 4. Ejecutar la app

```bash
php artisan config:clear
php artisan serve --host=127.0.0.1 --port=8000
```

Abre:

```text
http://127.0.0.1:8000
```

## 5. Estado verificado

Se verifico que:

- MySQL/MariaDB de XAMPP responde.
- La base `sistema_esterilizacion` existe.
- Las migraciones corren correctamente.
- Los seeders cargan catalogos.
- La pantalla principal responde con HTTP 200.
- El formulario de Area Roja responde con HTTP 200.
- El endpoint de materiales por servicio devuelve datos.
