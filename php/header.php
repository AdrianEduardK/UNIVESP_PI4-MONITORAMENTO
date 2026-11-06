<?php
// Certifique-se de que a sessão já está iniciada e auth.php foi chamado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$segmento = $_SESSION['segmento'] ?? '';
?>
<nav class="navbar">
    <div class="nav-brand">
        <span class="logo-icon">⚙️</span>
        <span class="logo-text">Sistema de Produção</span>
    </div>
    
    <div class="nav-links">
        <a href="../pages/lobby.php">Painel (Lobby)</a>
        
        <?php if ($segmento != 'PRODUÇÃO'): ?>
            <a href="../pages/pcp.php">Criar OP (PCP)</a>
            <a href="../pages/producao.php">Acompanhar Produção</a>
            <a href="../pages/cadastro_produto.php">Produtos</a>
            <a href="../pages/os.php">Ordens de Serviço</a>
            <a href="../pages/indicadores.php">Indicadores</a>
            <a href="../pages/sensor_log.php">Log de Sensores</a>
            <a href="../pages/linhas_producao.php">Linhas</a>
            <a href="../pages/maquinas.php">Máquinas</a>
            <a href="../php/cadastro_dde.php">DDE</a>
            <a href="../pages/nr12_acompanhamento.php">NR12</a>
        <?php else: ?>
            <a href="../pages/producao.php">Produção</a>
        <?php endif; ?>
    </div>

    <div class="nav-actions">
        <span class="user-greeting">Olá, <?php echo htmlspecialchars($_SESSION['nome'] ?? 'Usuário'); ?></span>
        <button id="theme-toggle-btn" class="theme-switch-btn nav-theme-btn">☀️ Tema</button>
        <a href="../php/logout.php" class="btn-logout" title="Sair do sistema">Sair 🚪</a>
    </div>
</nav>

<!-- Div de espaçamento para compensar a navbar fixa -->
<div class="navbar-spacer"></div>

<?php
// Inclui e dispara mensagens flash (Toasts) se existirem na sessão
require_once __DIR__ . '/flash_messages.php';
displayFlashMessages();
?>
