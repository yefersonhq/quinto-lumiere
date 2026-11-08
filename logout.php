<?php
require __DIR__ . '/inc/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $cart = $_SESSION['cart'] ?? [];
    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION['cart'] = $cart;
    flash('Cerraste sesión. ¡Vuelve pronto!');
}
redirect('');
