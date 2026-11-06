<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['nome'])) {
    require_once __DIR__ . '/flash_messages.php';
    setFlashMessage('Sua sessão expirou. Faça login novamente.', 'error');
    $dir = basename(dirname($_SERVER['SCRIPT_FILENAME'] ?? __FILE__));
    $login = ($dir === 'pages' || $dir === 'php') ? '../index.php' : 'index.php';
    header('Location: ' . $login);
    exit();
}

foreach (['nome', 'segmento'] as $chaveSessao) {
    $valorSessao = $_SESSION[$chaveSessao] ?? '';
    if (!is_string($valorSessao) || !str_contains($valorSessao, "\u{251C}")) {
        continue;
    }
    $corrigido = mb_convert_encoding($valorSessao, 'CP850', 'UTF-8');
    if ($corrigido !== false && mb_check_encoding($corrigido, 'UTF-8') && !str_contains($corrigido, "\u{251C}")) {
        $_SESSION[$chaveSessao] = $corrigido;
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function verificarCSRF() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $enviado = $_POST['csrf_token'] ?? '';
        $sessao = $_SESSION['csrf_token'] ?? '';
        if (!is_string($enviado) || $enviado === '' || $sessao === '' || !hash_equals($sessao, $enviado)) {
            http_response_code(403);
            die('Erro de validação CSRF. Requisição bloqueada.');
        }
    }
}

function campoCsrf() {
    $token = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function exigirManutencao() {
    $segmento = $_SESSION['segmento'] ?? '';
    if ($segmento === 'PRODUÇÃO') {
        require_once __DIR__ . '/flash_messages.php';
        setFlashMessage('Acesso restrito ao perfil de manutenção (PCP).', 'error');
        $dir = basename(dirname($_SERVER['SCRIPT_FILENAME'] ?? __FILE__));
        $destino = ($dir === 'php') ? '../pages/lobby.php' : 'lobby.php';
        header('Location: ' . $destino);
        exit();
    }
}
