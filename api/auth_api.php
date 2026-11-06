<?php
require_once dirname(__DIR__) . '/php/env.php';
carregarEnv(dirname(__DIR__) . '/.env');

function tokenDaRequisicao() {
    $authHeader = '';
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        foreach ($headers as $nome => $valor) {
            if (strcasecmp($nome, 'Authorization') === 0) {
                $authHeader = $valor;
                break;
            }
        }
    }
    if ($authHeader === '' && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }
    if ($authHeader === '' && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        return '';
    }
    return $matches[1];
}

function exigirTokenApi() {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    $validToken = env_valor('API_TOKEN');
    $token = tokenDaRequisicao();
    if ($validToken === '' || $token === '' || !hash_equals($validToken, $token)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
        exit;
    }
}

exigirTokenApi();
