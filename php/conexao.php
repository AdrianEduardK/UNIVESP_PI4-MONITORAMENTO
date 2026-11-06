<?php
require_once __DIR__ . '/env.php';
carregarEnv(dirname(__DIR__) . '/.env');

$host = env_valor('DB_HOST', 'localhost');
$usuario = env_valor('DB_USER');
$senha = env_valor('DB_PASS');
$banco = env_valor('DB_NAME');
$porta = (int)env_valor('DB_PORT', '3306');

if ($usuario === '' || $banco === '') {
    error_log('Configuração de banco ausente.');
    die('Falha na conexão com o banco de dados.');
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($host, $usuario, $senha, $banco, $porta);
if ($conn->connect_error) {
    error_log('Falha na conexão com o banco.');
    die('Falha na conexão com o banco de dados.');
}

$conn->set_charset('utf8mb4');
