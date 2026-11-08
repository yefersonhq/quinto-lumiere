# Quinto Lumière — Tienda online

Tienda en PHP con base de datos SQLite (sin instalar nada extra). Funciona en Hostinger con planes **Premium o Business**.

## Qué incluye

| Parte | Dónde |
|---|---|
| Tienda pública (sin iniciar sesión se ven todos los productos) | `index.php`, `tienda.php`, `producto.php` |
| Carrito, checkout y pago con Mercado Pago (tarjeta, Yape, PagoEfectivo) | `carrito.php`, `checkout.php`, `pago.php` |
| Pedido por WhatsApp (Yape/Plin/transferencia) | automático si Mercado Pago no está configurado, o como opción |
| Cuentas: **Administrador** (todo) y **Cliente** (compra y ve sus pedidos) | `login.php`, `registro.php`, `cuenta.php` |
| Panel de administración | `/admin/` |
| Chatbot "Lumi" (búsqueda y recomendaciones) | `api/lumi.php` |
| Términos, Privacidad y Libro de Reclamaciones (obligatorio en Perú) | `terminos.php`, `privacidad.php`, `reclamaciones.php` |
| Google Analytics 4 con eventos de e-commerce | se activa al poner tu ID en `config.php` |

## 1. Configura `config.php`

Ábrelo con el Bloc de notas y completa:

- `email`, `ruc`, `razon_social`: tus datos legales (salen en Términos y Libro de Reclamaciones).
- `site_url`: tu dominio, ej. `https://quintolumiere.com`
- `mercadopago`: tus credenciales (ver paso 3).
- `ga_measurement_id`: tu ID de Google Analytics (ver paso 4).
- `shipping`: precios de envío y monto para envío gratis.

## 2. Súbelo a Hostinger

1. hPanel → **Archivos → Administrador de archivos** → entra a `public_html`.
2. Sube **todo el contenido** de esta carpeta **excepto** la carpeta `_dev` y el archivo `LEEME.md`.
3. Asegúrate de que también se suban los archivos ocultos `.htaccess` (hay uno en la raíz, en `data/`, `inc/` y `uploads/`).
4. Activa el SSL gratis: hPanel → **Seguridad → SSL**.
5. Abre tu dominio **inmediatamente**: verás la pantalla para **crear tu cuenta de administrador**. Hazlo enseguida (solo funciona mientras no exista ningún admin).

> Las carpetas `data/` y `uploads/` deben poder escribirse (permisos 755). Hostinger lo hace por defecto.

### Opción gratis: InfinityFree

1. Crea tu cuenta en <https://www.infinityfree.com> → **Create Account** → elige un subdominio gratis (ej. `quintolumiere.infinityfreeapp.com`).
2. En el panel de la cuenta: **File Manager** → entra a `htdocs` → borra los archivos de ejemplo (`index2.html`, etc.) → sube `quinto-lumiere-tienda.zip` → clic derecho → **Extract**. Si no ves la opción de extraer, usa un programa FTP (FileZilla) con los datos de **FTP Details** y sube la carpeta descomprimida.
3. **SSL Certificates** → solicita el certificado gratis para tu subdominio y espera a que diga *Active*.
4. En `config.php` pon `site_url` con `https://` y tu subdominio.
5. Abre tu dirección, crea el administrador y revisa **Admin → Diagnóstico**.
6. Si el diagnóstico dice que no hay SQLite: en el panel crea una base **MySQL** y copia sus datos en `config.php` (`driver` → `mysql`).

Limitación: InfinityFree bloquea los avisos automáticos de Mercado Pago. La tienda igual confirma los pagos cuando la clienta vuelve a la web, al abrir el panel y con el botón **Verificar pagos pendientes** en Pedidos.

## 3. Mercado Pago

1. Crea tu cuenta en <https://www.mercadopago.com.pe> (con DNI o RUC).
2. Ve a <https://www.mercadopago.com.pe/developers/panel> → **Crear aplicación** → Checkout Pro.
3. Copia las **credenciales de prueba** (empiezan con `TEST-`) en `config.php` → `public_key` y `access_token`.
4. En la misma aplicación → **Webhooks** → URL: `https://TU-DOMINIO/api/mp-webhook.php`, evento **Pagos**. Copia la **clave secreta** en `webhook_secret`.
5. Haz una compra de prueba con las tarjetas de prueba de Mercado Pago.
6. Cuando todo funcione, cambia a las **credenciales de producción** (`APP_USR-`).

Los pagos se verifican siempre consultando a Mercado Pago (no se confía en la URL), y el stock se descuenta solo cuando el pago está aprobado.

## 4. Google Analytics

1. Entra a <https://analytics.google.com> → **Administrar → Crear propiedad** → flujo **Web** con tu dominio.
2. Copia el **ID de medición** (`G-XXXXXXXXXX`) en `config.php`.
3. Se registran automáticamente: visitas, búsquedas, productos vistos, agregar al carrito, inicio de pago, compras (con monto), registros y uso del chatbot.
4. En GA4 → **Informes → Monetización** verás ventas y productos más vendidos.

## 5. Uso diario (panel `/admin/`)

- **Inventario → Nuevo producto**: nombre, categoría, marca, descripción, **palabras clave** (cómo lo buscan tus clientes, incluso con errores: "amopolla", "oxigenta"), fotos y **variantes** (cada tamaño, tono o color con su precio y stock).
- **Editar stock y precios rápido**: cambia muchos precios/stock en una sola pantalla.
- **Pedidos**: cambia el estado. Al marcar **Pagado** se descuenta el stock; si cancelas un pedido pagado, el stock vuelve.
- **Usuarios**: convierte a alguien en administrador o restablece su contraseña.
- **Resumen**: ventas del mes, stock bajo, más vendidos y **lo que buscan tus clientes y no encuentran** (ideas para tu inventario).

- **Categorías y marcas**: sube el **logo** de cada marca (PNG con fondo transparente). Ya vienen los de Avon, Natura y Yanbal; las demás muestran su nombre en letra elegante hasta que subas su logo.

### Carta de colores (tintes)

- Para que un tono aparezca con su color, escribe la variante empezando por su número: `7.3 Rubio medio dorado`, `6.66 Rojo intenso`, `5.5 Caoba`.
- El color se calcula solo con el sistema internacional (nivel 1–10 + reflejo .1 ceniza, .2 irisado, .3 dorado, .4 cobrizo, .5 caoba, .6 rojo, .7 marrón, .8 perla). Si la marca tiene un tono distinto, escribe su color exacto (ej. `#8a5a3c`) en la columna **Color** de la variante.
- La página **Carta de colores** (`carta-de-colores.php`) muestra todos los tonos, marca los que tienes en stock y recomienda el oxidante, la mezcla para canas, las combinaciones para mechas y los productos complementarios.

⚠️ Los productos iniciales (incluidos 37 perfumes de Avon, Ésika, Natura y Yanbal) tienen **precios y stock de ejemplo**. Los precios de catálogo cambian cada campaña: revísalos antes de vender.

## Copias de seguridad

Toda la información está en `data/tienda.sqlite` y las fotos en `uploads/`. Descárgalos de vez en cuando desde el Administrador de archivos (Hostinger además hace copias automáticas).

## Probar en tu PC

Con PHP instalado, desde esta carpeta:

```
php -c _dev/php.ini -S localhost:8765
```

y abre <http://localhost:8765>.
