# Sistema de control de esterilizacion

Aplicacion Laravel para registrar y consultar el flujo de esterilizacion hospitalaria por etapas:

- Area Roja
- Area Azul
- Area Verde
- Observacion final

## Ejecucion local

Activar MySQL en XAMPP y ejecutar:

```bash
php artisan serve
```

URL local:

```text
http://127.0.0.1:8000
```

## Base de datos

La conexion local usa MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_esterilizacion
DB_USERNAME=root
DB_PASSWORD=
```

Para recrear tablas y catalogos:

```bash
php artisan migrate:fresh --seed
```
