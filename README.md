# Quinto Lumière — Tienda online

Tienda de cosméticos y productos capilares de **Quinto Lumière** (Lurín, Lima, Perú).
En línea: <https://quintolumiere.gt.tc>

## Funcionalidades

- Catálogo público con búsqueda tolerante a errores, filtros por categoría y marca.
- **Carta de colores** para tintes: simulador de tono, recomendaciones de oxidante, cobertura de canas y combinaciones.
- Carrito, checkout y pago con **Mercado Pago Checkout Pro** (verificación del pago por API y webhook firmado), o pedido por WhatsApp.
- Cuentas con dos roles: **administrador** (inventario, pedidos, usuarios, reclamos, diagnóstico) y **cliente**.
- Inventario con variantes (tono, tamaño), fotos convertidas a WebP y stock que se descuenta al confirmar el pago.
- Asistente **Lumi** (chatbot por reglas) para búsquedas y recomendaciones.
- **Google Analytics 4** con eventos de e-commerce.
- Términos y condiciones, política de privacidad y Libro de Reclamaciones.

## Tecnología

PHP 8 sin framework · PDO (SQLite o MySQL) · HTML/CSS/JavaScript sin dependencias · protección CSRF, contraseñas con `password_hash`, sesiones propias.

## Cómo ejecutarlo

```bash
cp config.example.php config.php   # completa tus datos y claves
php -c _dev/php.ini -S localhost:8765
```

La primera vez abre `http://localhost:8765/instalar.php` para crear el administrador.
La guía completa (hosting, Mercado Pago, Analytics y uso diario) está en [LEEME.md](LEEME.md).

> `config.php` (claves reales) y la base de datos no se suben al repositorio.
