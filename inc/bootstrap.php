<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
date_default_timezone_set('America/Lima');
mb_internal_encoding('UTF-8');

// config.php tiene tus datos y claves (no se sube a Git). Si aún no existe, se usa el ejemplo.
$GLOBALS['CONFIG'] = require (is_file(ROOT . '/config.php') ? ROOT . '/config.php' : ROOT . '/config.example.php');

/** ¿La visita llegó por https? (incluye hostings detrás de proxy, como InfinityFree o Cloudflare) */
function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443
        || str_contains($_SERVER['HTTP_CF_VISITOR'] ?? '', 'https');
}

// Si site_url usa https, obligamos https (solo en el hosting, nunca en tu PC)
$__host = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]);
if (PHP_SAPI !== 'cli' && !is_https() && str_starts_with((string) ($GLOBALS['CONFIG']['site_url'] ?? ''), 'https://')
    && !in_array($__host, ['localhost', '127.0.0.1', ''], true) && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
    exit;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    // Sesiones en una carpeta propia y protegida (data/ tiene acceso denegado).
    // Los hostings gratuitos borran seguido su carpeta temporal compartida y eso cerraba sesiones.
    $sessDir = ROOT . '/data/sessions';
    if (!is_dir($sessDir)) @mkdir($sessDir, 0775, true);
    if (is_dir($sessDir) && is_writable($sessDir)) {
        session_save_path($sessDir);
        ini_set('session.gc_probability', '1');
        ini_set('session.gc_divisor', '200');
    }
    ini_set('session.gc_maxlifetime', (string) (60 * 60 * 24 * 7));
    ini_set('session.use_strict_mode', '1');
    session_name('ql_sess');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => is_https(),
    ]);
    session_start();
}

/* ------------------------------------------------------------------ config */

function cfg(string $key, $default = null)
{
    $v = $GLOBALS['CONFIG'];
    foreach (explode('.', $key) as $k) {
        if (!is_array($v) || !array_key_exists($k, $v)) return $default;
        $v = $v[$k];
    }
    return $v;
}

/** Ruta base del sitio (soporta instalarlo en subcarpeta). */
function base_path(): string
{
    static $bp = null;
    if ($bp !== null) return $bp;
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
    $root = realpath(ROOT) ?: ROOT;
    $rel = '';
    if ($docRoot && str_starts_with(strtolower($root), strtolower($docRoot))) {
        $rel = str_replace('\\', '/', substr($root, strlen($docRoot)));
    }
    return $bp = rtrim($rel, '/');
}

function url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

function site_url(string $path = ''): string
{
    $base = rtrim((string) cfg('site_url', ''), '/');
    if ($base === '') {
        $base = (is_https() ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . base_path();
    }
    return $base . '/' . ltrim($path, '/');
}

/** URL de un archivo estático con cache-buster automático (evita caché vieja en Hostinger). */
function asset(string $path): string
{
    $file = ROOT . '/' . ltrim($path, '/');
    $v = is_file($file) ? filemtime($file) : '1';
    return url($path) . '?v=' . $v;
}

/* --------------------------------------------------------------------- db */

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;

    $c = cfg('db');
    if (($c['driver'] ?? 'sqlite') === 'mysql') {
        $pdo = new PDO(
            "mysql:host={$c['host']};dbname={$c['name']};charset=utf8mb4",
            $c['user'], $c['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    } else {
        if (!extension_loaded('pdo_sqlite')) {
            http_response_code(500);
            exit('<h1>Falta configurar la base de datos</h1><p>Este hosting no tiene SQLite. Crea una base de datos MySQL en el panel del hosting '
                . 'y en <code>config.php</code> cambia <code>driver</code> a <code>mysql</code> y completa host, nombre, usuario y contraseña.</p>');
        }
        $dir = dirname($c['sqlite']);
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $pdo = new PDO('sqlite:' . $c['sqlite'], null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL;');
    }
    migrate($pdo);
    return $pdo;
}

function is_mysql(): bool
{
    return (cfg('db.driver') === 'mysql');
}

function migrate(PDO $pdo): void
{
    $mysql = is_mysql();
    $exists = $mysql
        ? $pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn()
        : $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetchColumn();
    if ($exists) { upgrade($pdo); return; }

    $pk  = $mysql ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $txt  = $mysql ? 'TEXT' : 'TEXT';
    $vc   = fn($n) => $mysql ? "VARCHAR($n)" : 'TEXT';
    $num  = $mysql ? 'DECIMAL(10,2)' : 'REAL';
    $tail = $mysql ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' : '';

    $sql = [
        "CREATE TABLE users (
            id $pk, name {$vc(120)} NOT NULL, email {$vc(190)} NOT NULL UNIQUE, phone {$vc(30)},
            password_hash {$vc(255)} NOT NULL, role {$vc(20)} NOT NULL DEFAULT 'cliente',
            created_at {$vc(25)} NOT NULL)$tail",
        "CREATE TABLE categories (
            id $pk, name {$vc(120)} NOT NULL, slug {$vc(140)} NOT NULL UNIQUE,
            icon {$vc(40)}, sort INT NOT NULL DEFAULT 0)$tail",
        "CREATE TABLE brands (
            id $pk, name {$vc(120)} NOT NULL, slug {$vc(140)} NOT NULL UNIQUE, logo {$vc(255)})$tail",
        "CREATE TABLE products (
            id $pk, name {$vc(200)} NOT NULL, slug {$vc(220)} NOT NULL UNIQUE,
            brand_id INT NULL, category_id INT NULL, description $txt, tags $txt,
            featured INT NOT NULL DEFAULT 0, active INT NOT NULL DEFAULT 1,
            created_at {$vc(25)} NOT NULL, updated_at {$vc(25)} NOT NULL)$tail",
        "CREATE TABLE product_variants (
            id $pk, product_id INT NOT NULL, label {$vc(120)} NOT NULL, sku {$vc(80)},
            price $num NOT NULL DEFAULT 0, compare_price $num NULL, stock INT NOT NULL DEFAULT 0,
            sort INT NOT NULL DEFAULT 0, color {$vc(7)})$tail",
        "CREATE TABLE product_images (
            id $pk, product_id INT NOT NULL, path {$vc(255)} NOT NULL, sort INT NOT NULL DEFAULT 0)$tail",
        "CREATE TABLE orders (
            id $pk, code {$vc(20)} NOT NULL UNIQUE, user_id INT NOT NULL,
            status {$vc(20)} NOT NULL DEFAULT 'pendiente', payment_method {$vc(20)} NOT NULL,
            mp_preference_id {$vc(120)}, mp_payment_id {$vc(60)}, mp_status {$vc(40)},
            subtotal $num NOT NULL, shipping $num NOT NULL, total $num NOT NULL,
            ship_method {$vc(30)} NOT NULL, ship_name {$vc(120)}, ship_phone {$vc(30)},
            ship_address {$vc(255)}, ship_district {$vc(120)}, ship_ref {$vc(255)}, notes $txt,
            stock_applied INT NOT NULL DEFAULT 0, created_at {$vc(25)} NOT NULL, paid_at {$vc(25)})$tail",
        "CREATE TABLE order_items (
            id $pk, order_id INT NOT NULL, product_id INT NOT NULL, variant_id INT NOT NULL,
            name {$vc(200)} NOT NULL, variant_label {$vc(120)}, price $num NOT NULL, qty INT NOT NULL)$tail",
        "CREATE TABLE complaints (
            id $pk, code {$vc(20)} NOT NULL, type {$vc(10)} NOT NULL, name {$vc(150)} NOT NULL,
            doc {$vc(20)} NOT NULL, email {$vc(190)} NOT NULL, phone {$vc(30)}, address {$vc(255)},
            item_type {$vc(10)} NOT NULL, amount $num NULL, description $txt NOT NULL, request $txt NOT NULL,
            status {$vc(20)} NOT NULL DEFAULT 'abierto', response $txt, created_at {$vc(25)} NOT NULL)$tail",
        "CREATE TABLE searches (
            id $pk, q {$vc(200)} NOT NULL, results INT NOT NULL, source {$vc(10)} NOT NULL,
            created_at {$vc(25)} NOT NULL)$tail",
        "CREATE INDEX idx_var_prod ON product_variants(product_id)",
        "CREATE INDEX idx_img_prod ON product_images(product_id)",
        "CREATE INDEX idx_items_order ON order_items(order_id)",
        "CREATE INDEX idx_orders_user ON orders(user_id)",
    ];
    foreach ($sql as $s) $pdo->exec($s);

    require_once __DIR__ . '/seed.php';
    seed_catalog($pdo);
}

/** Agrega columnas nuevas a bases de datos creadas con versiones anteriores. */
function upgrade(PDO $pdo): void
{
    $add = [['brands', 'logo', 'VARCHAR(255)'], ['product_variants', 'color', 'VARCHAR(7)']];
    foreach ($add as [$table, $col, $type]) {
        if (is_mysql()) {
            $has = $pdo->query("SHOW COLUMNS FROM $table LIKE '$col'")->fetchColumn();
        } else {
            $has = in_array($col, array_column($pdo->query("PRAGMA table_info($table)")->fetchAll(), 'name'), true);
        }
        if (!$has) $pdo->exec("ALTER TABLE $table ADD COLUMN $col " . (is_mysql() ? $type : 'TEXT'));
    }
}

/**
 * Guarda una imagen subida: valida que sea imagen real, la redimensiona y la pasa a WebP si se puede.
 * Devuelve la ruta relativa (uploads/…) o null.
 */
function save_image_upload(array $f, string $prefix, int $max = 1400): ?string
{
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || $f['size'] > 8 * 1024 * 1024) return null;
    $info = @getimagesize($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$info['mime'] ?? ''] ?? null;
    if (!$ext) return null;
    $sub = date('Y/m');
    $dir = ROOT . '/uploads/' . $sub;
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $name = $prefix . '-' . bin2hex(random_bytes(5));

    if (function_exists('imagewebp') && $ext !== 'gif') {
        $src = match ($ext) {
            'jpg' => @imagecreatefromjpeg($f['tmp_name']),
            'png' => @imagecreatefrompng($f['tmp_name']),
            'webp' => @imagecreatefromwebp($f['tmp_name']),
        };
        if ($src) {
            if ($ext === 'jpg' && function_exists('exif_read_data')) {
                $o = @exif_read_data($f['tmp_name'])['Orientation'] ?? 1;
                if ($o == 3) $src = imagerotate($src, 180, 0);
                if ($o == 6) $src = imagerotate($src, -90, 0);
                if ($o == 8) $src = imagerotate($src, 90, 0);
            }
            imagepalettetotruecolor($src);
            imagealphablending($src, true);
            imagesavealpha($src, true);
            $w = imagesx($src); $h = imagesy($src);
            if ($w > $max || $h > $max) {
                $r = min($max / $w, $max / $h);
                $dst = imagecreatetruecolor((int) ($w * $r), (int) ($h * $r));
                imagealphablending($dst, false); imagesavealpha($dst, true);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, (int) ($w * $r), (int) ($h * $r), $w, $h);
                imagedestroy($src);
                $src = $dst;
            }
            if (imagewebp($src, "$dir/$name.webp", 82)) {
                imagedestroy($src);
                return "uploads/$sub/$name.webp";
            }
        }
    }
    return move_uploaded_file($f['tmp_name'], "$dir/$name.$ext") ? "uploads/$sub/$name.$ext" : null;
}

/** Logo de marca (imagen) o, si no tiene, su nombre en tipografía elegante. */
function brand_logo(?string $name, ?string $logo, string $class = 'brandmark'): string
{
    if (!$name) return '';
    if ($logo) return '<img class="' . $class . ' ' . $class . '--img" src="' . e(url($logo)) . '" alt="' . e($name) . '" loading="lazy">';
    return '<span class="' . $class . ' ' . $class . '--text">' . e($name) . '</span>';
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

/* ---------------------------------------------------------------- helpers */

function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function money($n): string
{
    return 'S/ ' . number_format((float) $n, 2, '.', ',');
}

function slugify(string $s): string
{
    $s = mb_strtolower(trim($s));
    $s = strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n','à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u']);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-') ?: 'item';
}

function unique_slug(string $table, string $name, ?int $ignoreId = null): string
{
    $base = slugify($name);
    $slug = $base;
    $i = 2;
    while (true) {
        $st = db()->prepare("SELECT id FROM $table WHERE slug = ?" . ($ignoreId ? ' AND id <> ?' : ''));
        $st->execute($ignoreId ? [$slug, $ignoreId] : [$slug]);
        if (!$st->fetchColumn()) return $slug;
        $slug = $base . '-' . $i++;
    }
}

/** Normaliza texto para búsquedas: minúsculas, sin tildes, sin signos. */
function norm(string $s): string
{
    $s = mb_strtolower($s);
    $s = strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n','à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u']);
    $s = preg_replace('/[^a-z0-9.\s]+/', ' ', $s);
    return trim(preg_replace('/\s+/', ' ', $s));
}

function redirect(string $to): never
{
    header('Location: ' . (preg_match('#^https?://#', $to) ? $to : url($to)));
    exit;
}

function flash(?string $msg = null, string $type = 'ok')
{
    if ($msg !== null) {
        $_SESSION['flash'][] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function json_out($data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function post(string $k, $default = '')
{
    $v = $_POST[$k] ?? $default;
    return is_string($v) ? trim($v) : $v;
}

/* ------------------------------------------------------------------- csrf */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_check(): void
{
    $t = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? '');
    if (!is_string($t) || !hash_equals(csrf_token(), $t)) {
        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            json_out(['error' => 'Tu sesión se renovó. Recarga la página e inténtalo de nuevo.', 'csrf' => csrf_token()], 419);
        }
        // Volvemos al formulario con un token nuevo en lugar de mostrar una página en blanco
        flash('Por seguridad renovamos tu sesión. Vuelve a enviar el formulario, por favor.', 'info');
        $back = $_SERVER['HTTP_REFERER'] ?? '';
        $sameHost = parse_url($back, PHP_URL_HOST) === explode(':', $_SERVER['HTTP_HOST'] ?? '')[0];
        header('Location: ' . ($sameHost ? $back : url('')), true, 303);
        exit;
    }
}

/* ------------------------------------------------------------------- auth */

function current_user(): ?array
{
    static $u = false;
    if ($u !== false) return $u;
    $id = $_SESSION['uid'] ?? null;
    if (!$id) return $u = null;
    $st = db()->prepare('SELECT id, name, email, phone, role FROM users WHERE id = ?');
    $st->execute([$id]);
    return $u = ($st->fetch() ?: null);
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

function login_user(int $id): void
{
    session_regenerate_id(true);
    $_SESSION['uid'] = $id;
}

function require_login(): array
{
    $u = current_user();
    if (!$u) {
        $_SESSION['after_login'] = $_SERVER['REQUEST_URI'] ?? url('');
        flash('Inicia sesión o crea tu cuenta para continuar.', 'info');
        redirect('login.php');
    }
    return $u;
}

function require_admin(): array
{
    $u = require_login();
    if ($u['role'] !== 'admin') {
        http_response_code(403);
        exit('Acceso solo para administradores.');
    }
    return $u;
}

function admin_exists(): bool
{
    return (bool) db()->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
}

/* --------------------------------------------------------------- catálogo */

function product_images(int $pid): array
{
    $st = db()->prepare('SELECT id, path FROM product_images WHERE product_id = ? ORDER BY sort, id');
    $st->execute([$pid]);
    return $st->fetchAll();
}

function product_cover(array $p): string
{
    if (!empty($p['cover'])) return url($p['cover']);
    return url('placeholder.php?c=' . urlencode($p['category_slug'] ?? '') . '&t=' . urlencode($p['name'] ?? ''));
}

function product_variants(int $pid): array
{
    $st = db()->prepare('SELECT * FROM product_variants WHERE product_id = ? ORDER BY sort, id');
    $st->execute([$pid]);
    return $st->fetchAll();
}

/** Consulta base de productos con marca, categoría, precio mínimo, stock total y portada. */
function product_query(string $where = '1=1', string $order = 'p.featured DESC, p.name', string $limit = ''): string
{
    return "SELECT p.*, b.name AS brand, b.slug AS brand_slug, b.logo AS brand_logo, c.name AS category, c.slug AS category_slug,
              (SELECT MIN(price) FROM product_variants v WHERE v.product_id = p.id) AS min_price,
              (SELECT MAX(price) FROM product_variants v WHERE v.product_id = p.id) AS max_price,
              (SELECT MIN(compare_price) FROM product_variants v WHERE v.product_id = p.id AND compare_price > price) AS compare_price,
              (SELECT COALESCE(SUM(stock),0) FROM product_variants v WHERE v.product_id = p.id) AS stock,
              (SELECT COUNT(*) FROM product_variants v WHERE v.product_id = p.id) AS variant_count,
              (SELECT path FROM product_images i WHERE i.product_id = p.id ORDER BY sort, id LIMIT 1) AS cover
            FROM products p
            LEFT JOIN brands b ON b.id = p.brand_id
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE $where ORDER BY $order $limit";
}

function categories(): array
{
    return db()->query('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id=c.id AND p.active=1) AS n
                        FROM categories c ORDER BY sort, name')->fetchAll();
}

function brands(): array
{
    return db()->query('SELECT b.*, (SELECT COUNT(*) FROM products p WHERE p.brand_id=b.id AND p.active=1) AS n
                        FROM brands b ORDER BY name')->fetchAll();
}

/* ------------------------------------------------------------------ cart */

function cart(): array
{
    return $_SESSION['cart'] ?? [];
}

/** Devuelve las líneas del carrito con precios y stock reales de la base de datos. */
function cart_lines(): array
{
    $cart = cart();
    if (!$cart) return [];
    $ids = array_map('intval', array_keys($cart));
    $in = implode(',', array_fill(0, count($ids), '?'));
    $st = db()->prepare("SELECT v.id AS variant_id, v.label, v.price, v.stock, p.id AS product_id, p.name, p.slug, p.active,
                                c.slug AS category_slug,
                                (SELECT path FROM product_images i WHERE i.product_id = p.id ORDER BY sort, id LIMIT 1) AS cover
                         FROM product_variants v JOIN products p ON p.id = v.product_id
                         LEFT JOIN categories c ON c.id = p.category_id WHERE v.id IN ($in)");
    $st->execute($ids);
    $lines = [];
    foreach ($st->fetchAll() as $r) {
        if (!$r['active']) continue;
        $qty = min((int) $cart[$r['variant_id']], max(0, (int) $r['stock']));
        if ($qty <= 0) continue;
        $r['qty'] = $qty;
        $r['line_total'] = $qty * (float) $r['price'];
        $lines[] = $r;
    }
    return $lines;
}

function cart_count(): int
{
    return array_sum(cart());
}

function shipping_cost(string $method, float $subtotal): float
{
    $s = cfg('shipping');
    if ($method === 'recojo') return (float) $s['recojo_tienda'];
    if ((float) $s['gratis_desde'] > 0 && $subtotal >= (float) $s['gratis_desde']) return 0.0;
    return (float) ($method === 'provincia' ? $s['provincia'] : $s['lima']);
}

function order_status_label(string $s): string
{
    return [
        'pendiente'  => 'Pendiente de pago',
        'pagado'     => 'Pagado',
        'preparando' => 'Preparando',
        'enviado'    => 'Enviado',
        'entregado'  => 'Entregado',
        'cancelado'  => 'Cancelado',
    ][$s] ?? ucfirst($s);
}

/** Descuenta el stock de un pedido una sola vez (idempotente). */
function apply_order_stock(int $orderId): void
{
    $pdo = db();
    $pdo->beginTransaction();
    $st = $pdo->prepare('UPDATE orders SET stock_applied = 1 WHERE id = ? AND stock_applied = 0');
    $st->execute([$orderId]);
    if ($st->rowCount() === 1) {
        $items = $pdo->prepare('SELECT variant_id, qty FROM order_items WHERE order_id = ?');
        $items->execute([$orderId]);
        $up = $pdo->prepare('UPDATE product_variants SET stock = CASE WHEN stock - ? < 0 THEN 0 ELSE stock - ? END WHERE id = ?');
        foreach ($items->fetchAll() as $it) $up->execute([$it['qty'], $it['qty'], $it['variant_id']]);
    }
    $pdo->commit();
}

function whatsapp_link(string $text = ''): string
{
    return 'https://wa.me/' . cfg('whatsapp') . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

require __DIR__ . '/colors.php';
require __DIR__ . '/layout.php';
