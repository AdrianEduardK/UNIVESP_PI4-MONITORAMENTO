<?php
session_start();
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/flash_messages.php';

$nome = trim($_POST['nome'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($nome === '' || $senha === '') {
    setFlashMessage('Usuário ou senha não fornecidos.', 'error');
    header('Location: ../index.php');
    exit();
}

$sql = "SELECT LOGIN_ID, LOGIN_NOME, LOGIN_SENHA, LOGIN_SEGMENTO FROM tab_login WHERE LOGIN_NOME = ? AND LOGIN_ATIVO = 'Ativo' LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log($conn->error);
    setFlashMessage('Não foi possível entrar. Tente novamente.', 'error');
    header('Location: ../index.php');
    exit();
}
$stmt->bind_param('s', $nome);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result ? $result->fetch_assoc() : null;
$stmt->close();

$autenticado = false;
if ($usuario) {
    $armazenada = (string)$usuario['LOGIN_SENHA'];
    $info = password_get_info($armazenada);
    if (!empty($info['algo'])) {
        $autenticado = password_verify($senha, $armazenada);
    } elseif (hash_equals($armazenada, $senha)) {
        $autenticado = true;
        $novo = password_hash($senha, PASSWORD_DEFAULT);
        $id = (int)$usuario['LOGIN_ID'];
        $up = $conn->prepare('UPDATE tab_login SET LOGIN_SENHA = ? WHERE LOGIN_ID = ?');
        if ($up) {
            $up->bind_param('si', $novo, $id);
            $up->execute();
            $up->close();
        }
    }
}

if ($autenticado) {
    session_regenerate_id(true);
    $_SESSION['nome'] = $usuario['LOGIN_NOME'];
    $_SESSION['segmento'] = $usuario['LOGIN_SEGMENTO'];
    header('Location: ../pages/lobby.php');
    exit();
}

setFlashMessage('Usuário ou senha inválidos.', 'error');
header('Location: ../index.php');
exit();
