# Rifas PHP (sin frameworks)

Proyecto listo para subir a hosting compartido (Apache + PHP 8.2 + MySQL).

## Requisitos
- PHP 8.2 con extensiones `pdo_mysql`, `mbstring`, `openssl`, `gd` (opcional para dompdf).
- MySQL 5.7+ / MariaDB 10+.
- Apache con mod_rewrite habilitado.

## Instalación
1. **Sube el ZIP** al hosting y descomprímelo. Coloca el contenido de la carpeta `public/` como raíz pública o configura el DocumentRoot a `public/`.
2. Crea una base de datos (por ejemplo `rifas_db`).
3. Importa `database.sql`.
4. Copia `config/env.sample` a `config/.env` y ajusta:
   - APP_URL
   - DB_HOST, DB_NAME, DB_USER, DB_PASS
   - SMTP_* (opcional; si agregas `vendor/` con PHPMailer)
   - BCV_SOURCE_URL, BCV_DEFAULT_RATE
5. Asegura permisos de escritura para:
   - `storage/uploads/receipts`
   - `storage/logs`
6. Define `ADMIN_SETUP_TOKEN` en `config/.env` y crea el primer administrador visitando  
   `/admin/seed-admin?token=EL_TOKEN&email=admin@tu-dominio.com&password=TuClaveSegura`.
7. Elimina `ADMIN_SETUP_TOKEN` (y las credenciales de seed si las agregaste) y accede a  
   `/admin` con las credenciales creadas.

## Notas de producción
- Si la conexión MySQL falla, se mostrará una página de mantenimiento y `/health` devolverá 500.
- **PDF**: Incluimos un generador básico de respaldo. Para PDF con HTML/CSS completo y adjuntos por correo, agrega `vendor/` con **dompdf** y (opcional) **PHPMailer**:
  - Descarga `dompdf/dompdf` y `phpmailer/phpmailer` con Composer en tu equipo y sube el directorio `vendor/` resultante junto con `vendor/autoload.php`.
  - El sistema detecta automáticamente Dompdf si está disponible.

## Esquema de BD y seed
Ver `database.sql`. Se crean 2 rifas de ejemplo. Para cada rifa nueva, el sistema genera los boletos secuencialmente.

## Creación de administradores
1. Establece `ADMIN_SETUP_TOKEN` en `config/.env`. Opcionalmente puedes definir `ADMIN_SEED_EMAIL` y `ADMIN_SEED_PASSWORD`.
2. Visita `/admin/seed-admin?token=EL_TOKEN` agregando `email` y `password` como parámetros si no los definiste en el `.env`.
3. Tras el mensaje de éxito, elimina `ADMIN_SETUP_TOKEN` y las credenciales de seed para deshabilitar la ruta.
4. Ingresa a `/admin` con las credenciales creadas.

## Rutas principales
**Público**
- `GET /` — listado de rifas.
- `GET /rifa/{id}` — detalle y selección de boletos.
- `POST /orden` — crea orden y reserva boletos.
- `GET /orden/{code}` — detalle de orden, subir pago.
- `POST /orden/{code}/pago` — carga de comprobante (jpg/png/webp máx 5MB).
- `GET /orden/{code}/comprobante` — HTML del comprobante.
- `GET /orden/{code}/comprobante.pdf` — PDF (Dompdf si está disponible).
- `GET /mis-boletos?email=...&code=...` — listado de boletos.

**Admin**
- `GET /admin/login`, `POST /admin/login`, `POST /admin/logout`
- `GET /admin` — dashboard con KPIs y actividad real.
- `GET /admin/rifas`, `GET /admin/rifas/crear`, `POST /admin/rifas` — CRUD básico (crear).
- `GET /admin/ordenes`, `GET /admin/ordenes/{id}`
- `POST /admin/pagos/{id}/aprobar`, `POST /admin/pagos/{id}/rechazar`
- `GET /admin/reportes?fecha_desde&fecha_hasta&raffle_id[opcional]&format=csv`
- `GET /admin/ajustes`, `POST /admin/ajustes` (tasa BCV), `GET /admin/ajustes/actualizar-bcv`

## Seguridad
- PDO + prepared statements.
- CSRF token en formularios.
- Validación y sanitización básica.
- Contraseñas con `password_hash()`.
- Límite de intentos en login (5 / 15min por IP).
- Subida de archivos validada; se almacenan fuera de la raíz pública (`/storage/uploads/receipts`) y se sirven por PHP.
- `.htaccess` bloquea acceso directo a `/storage` y activa URL amigables.

## Script de salud
- `GET /health` responde 200 si la DB está OK y 500 en caso contrario.

## Estilo y UX
- UI tipo glassmorphism con CSS vanilla (`public/css/app.css`).
- Transiciones y hover suaves.

## Ajuste de tasa BCV
- En `/admin/ajustes` puedes guardar manualmente la tasa.
- El botón "Actualizar desde API" intenta leer `BCV_SOURCE_URL`; si falla, mantiene el valor actual.

---
## Despliegue rápido para compraturifa.com
1) En cPanel, coloca el **contenido de `public/`** dentro de `public_html/` (docroot del dominio).
   Deja `app/`, `config/`, `storage/` **arriba**, fuera de `public_html/`.
2) Ya incluí `config/.env` con las credenciales provistas.
3) Verifica permisos: `storage/uploads/receipts` y `storage/logs` deben ser escribibles (775/755).
4) Prueba `https://compraturifa.com/health`.
