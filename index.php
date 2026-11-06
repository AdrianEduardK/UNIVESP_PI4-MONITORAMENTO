<?php
session_start();
require_once 'php/flash_messages.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Produção</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/theme.js"></script>
</head>
<body class="tela-login">
    <?php displayFlashMessages(); ?>
    <div class="theme-switch-wrapper">
        <button id="theme-toggle-btn" class="theme-switch-btn">☀️ Modo Claro</button>
    </div>

    <div class="login-container">
        <h2>Entrar no Sistema</h2>
        <form method="POST" action="php/login.php">
            <div>
                <label for="nome">Usuário</label>
                <input type="text" id="nome" name="nome" placeholder="Digite seu usuário" required>
            </div>
            <div>
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
            </div>
            <button type="submit" style="margin-top: 10px;">Acessar Painel</button>
        </form>
    </div>
</body>
</html>
