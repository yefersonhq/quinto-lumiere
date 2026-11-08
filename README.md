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

## Equipo y roles

| Rol | Persona | Responsabilidad |
|---|---|---|
| **Desarrollador de Software Full Stack** | Yeferson Huamán Quinto ([@yefersonhq](https://github.com/yefersonhq)) | Análisis, diseño, desarrollo, pruebas, despliegue y mantenimiento del sistema |
| **Cliente / Dueña del producto** | Quinto Lumière (titular del negocio) | Definición de requisitos, catálogo, precios y validación de entregables |

### Funciones del desarrollador

1. **Requisitos:** levantamiento con el negocio (catálogo, inventario, pagos, roles de usuario y normas peruanas: Libro de Reclamaciones, términos y privacidad).
2. **Análisis y diseño:** arquitectura de la aplicación, modelo de base de datos (productos, variantes, pedidos, usuarios, reclamos) e interfaz según la identidad de marca.
3. **Desarrollo frontend y backend:** PHP 8, SQL (SQLite/MySQL), HTML, CSS y JavaScript, con diseño adaptable a celulares.
4. **Módulo de administración:** inventario con fotos y variantes, pedidos, usuarios y reclamos.
5. **Seguridad:** autenticación y control de acceso por roles (administrador y cliente), contraseñas cifradas y protección CSRF.
6. **Pasarela de pagos:** integración de Mercado Pago Checkout Pro con verificación por API y control automático de stock.
7. **Analítica:** Google Analytics 4 con eventos de comercio electrónico.
8. **Asistente virtual y carta de colores:** chatbot de búsqueda y recomendación, y simulador de tonos para tintes.
9. **Pruebas:** funcionales y de compatibilidad (escritorio y móvil), corrección de errores y panel de diagnóstico.
10. **Despliegue y control de versiones:** hosting con HTTPS, Git y GitHub.
11. **Documentación:** guía técnica y manual de uso para la administradora ([LEEME.md](LEEME.md)).

### Roles dentro del sistema

| Rol | Permisos |
|---|---|
| **Visitante** | Ver catálogo, buscar, usar la carta de colores y el asistente, armar el carrito |
| **Cliente** | Todo lo anterior + comprar, pagar con Mercado Pago y ver sus pedidos |
| **Administrador** | Todo lo anterior + inventario, pedidos, categorías y marcas, usuarios, reclamos y diagnóstico |

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
