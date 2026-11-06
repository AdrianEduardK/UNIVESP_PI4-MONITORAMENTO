<?php
require_once __DIR__ . '/auth.php';
include __DIR__ . '/conexao.php';

$segmento = $_SESSION['segmento'];
$isPCP = ($segmento == 'PCP');

$id_op = $_GET['id'] ?? null;
if (!$id_op) {
    echo "<script>alert('ID da OP não informado!'); window.location.href='../pages/producao.php';</script>";
    exit;
}

// Buscar dados da OP
$sql = "SELECT * FROM tab_op WHERE OP_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_op);
$stmt->execute();
$op = $stmt->get_result()->fetch_assoc();

if (!$op) {
    echo "<script>alert('OP não encontrada!'); window.location.href='../pages/producao.php';</script>";
    exit;
}

// Buscar descrição do produto final
$id_produto_final = $op['OP_ID_PRODUTO'];
$stmt_prod = $conn->prepare("SELECT PROD_DESC FROM tab_produtos WHERE PROD_ID = ?");
$stmt_prod->bind_param("i", $id_produto_final);
$stmt_prod->execute();
$produto = $stmt_prod->get_result()->fetch_assoc();
$descricao_produto = $produto ? $produto['PROD_DESC'] : "Produto não encontrado";

// Buscar estrutura do produto
$sql_estrutura = "SELECT ep.ESTRUTURA_ID, ep.ESTRUTURA_ID_NECESSARIO, p.PROD_DESC
                 FROM TAB_ESTRUTURA_PROD ep
                 INNER JOIN TAB_PRODUTOS p ON ep.ESTRUTURA_ID_NECESSARIO = p.PROD_ID
                 WHERE ep.ESTRUTURA_ID_FINAL = ?";
$stmt_est = $conn->prepare($sql_estrutura);
$stmt_est->bind_param("i", $id_produto_final);
$stmt_est->execute();
$result_estrutura = $stmt_est->get_result();
$estrutura = [];
if ($result_estrutura) {
    while ($row = $result_estrutura->fetch_assoc()) {
        $estrutura[] = $row;
    }
}

// Processar formulários (somente Produção)
$msg_sucesso = '';
$msg_erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isPCP) {

    // Apontar Refugo
    if (isset($_POST['qtd_refugo'], $_POST['tipo_refugo'], $_POST['refugo_unidade'])) {
        $qtd_refugo = $_POST['qtd_refugo'];
        $tipo_refugo = $_POST['tipo_refugo'];
        $refugo_unidade = $_POST['refugo_unidade'];
        $stmt = $conn->prepare("INSERT INTO tab_refugo (REFUGO_ID_OP, REFUGO_QTD, REFUGO_TIPO, REFUGO_UNIDADE) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $id_op, $qtd_refugo, $tipo_refugo, $refugo_unidade);
        if ($stmt->execute()) {
            $msg_sucesso = "✅ Refugo apontado com sucesso!";
        } else {
            error_log("Erro refugo: " . $stmt->error);
            $msg_erro = "❌ Ocorreu um erro interno ao apontar refugo.";
        }
    }

    // Apontar Parada
    if (isset($_POST['inicio'], $_POST['fim'], $_POST['tipo_parada'])) {
        $inicio = $_POST['inicio'];
        $fim = $_POST['fim'];
        $tipo_parada = $_POST['tipo_parada'];
        $stmt = $conn->prepare("INSERT INTO tab_paradas_produtivas (PARADA_ID_OP, PARADA_INICIO, PARADA_FIM, PARADA_TIPO) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $id_op, $inicio, $fim, $tipo_parada);
        if ($stmt->execute()) {
            $msg_sucesso = "✅ Parada apontada com sucesso!";
        } else {
            error_log("Erro parada: " . $stmt->error);
            $msg_erro = "❌ Ocorreu um erro interno ao apontar parada.";
        }
    }

    // Apontar Lote
    if (isset($_POST['lotes']) && is_array($_POST['lotes'])) {
        $algum_lote = false;
        $stmt = $conn->prepare("INSERT INTO tab_lote_op_produtos (LOTE_ID_OP, LOTE_ID_PROD_NECESSARIO, LOTE_APONTADO) VALUES (?, ?, ?)");
        foreach ($_POST['lotes'] as $estrutura_id => $lote_apontado) {
            if (!empty($lote_apontado)) {
                $algum_lote = true;
                $stmt->bind_param("iis", $id_op, $estrutura_id, $lote_apontado);
                if (!$stmt->execute()) {
                    error_log("Erro lote: " . $stmt->error);
                    $msg_erro = "❌ Ocorreu um erro interno ao apontar lote.";
                }
            }
        }
        if ($algum_lote && empty($msg_erro)) {
            $msg_sucesso = "✅ Lotes apontados com sucesso!";
        }
    }

    // Atualizar dados da OP
    if (isset($_POST['op_recebedor'], $_POST['op_status'], $_POST['op_hora_inicio'])) {
        $op_recebedor = $_POST['op_recebedor'];
        $op_status    = $_POST['op_status'];
        $op_hora_inicio = $_POST['op_hora_inicio'];
        $op_hora_fim  = !empty($_POST['op_hora_fim'])  ? $_POST['op_hora_fim'] : null;
        $op_qtd_real  = !empty($_POST['op_qtd_real'])  ? $_POST['op_qtd_real'] : null;
        $op_diario_bordo = !empty($_POST['op_diario_bordo']) ? $_POST['op_diario_bordo'] : null;

        if ($op_status === 'Finalizado' && (empty($_POST['op_hora_fim']) || empty($_POST['op_qtd_real']))) {
            $msg_erro = "⚠️ Para finalizar a OP, preencha a Hora Fim e a Quantidade Real!";
        } else {
            $sql_atualizar = "UPDATE tab_op SET
                OP_RECEBEDOR = ?,
                OP_STATUS = ?,
                OP_HORA_INICIO = ?,
                OP_HORA_FIM = ?,
                OP_QTD_REAL = ?,
                OP_DIARIO_BORDO = ?
                WHERE OP_ID = ?";
            $stmt = $conn->prepare($sql_atualizar);
            $stmt->bind_param("ssssdsi", $op_recebedor, $op_status, $op_hora_inicio, $op_hora_fim, $op_qtd_real, $op_diario_bordo, $id_op);
            
            if ($stmt->execute()) {
                $msg_sucesso = "✅ Dados da OP atualizados com sucesso!";
                $stmt_op = $conn->prepare("SELECT * FROM tab_op WHERE OP_ID = ?");
                $stmt_op->bind_param("i", $id_op);
                $stmt_op->execute();
                $op = $stmt_op->get_result()->fetch_assoc();
            } else {
                error_log("Erro atualizar OP: " . $stmt->error);
                $msg_erro = "❌ Ocorreu um erro interno ao atualizar OP.";
            }
        }
    }
}

// Buscar históricos
$stmt_hist = $conn->prepare("
    SELECT p.PROD_DESC, l.LOTE_APONTADO
    FROM tab_lote_op_produtos l
    LEFT JOIN tab_estrutura_prod e ON l.LOTE_ID_PROD_NECESSARIO = e.ESTRUTURA_ID
    LEFT JOIN tab_produtos p ON e.ESTRUTURA_ID_NECESSARIO = p.PROD_ID
    WHERE l.LOTE_ID_OP = ?
");
$stmt_hist->bind_param("i", $id_op);
$stmt_hist->execute();
$historico_lotes = $stmt_hist->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt_ref = $conn->prepare("SELECT REFUGO_QTD, REFUGO_TIPO, REFUGO_UNIDADE FROM tab_refugo WHERE REFUGO_ID_OP = ?");
$stmt_ref->bind_param("i", $id_op);
$stmt_ref->execute();
$historico_refugos = $stmt_ref->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt_par = $conn->prepare("SELECT PARADA_INICIO, PARADA_FIM, PARADA_TIPO FROM tab_paradas_produtivas WHERE PARADA_ID_OP = ?");
$stmt_par->bind_param("i", $id_op);
$stmt_par->execute();
$historico_paradas = $stmt_par->get_result()->fetch_all(MYSQLI_ASSOC);

// Badge de status
$status_class = $op['OP_STATUS'] === 'Finalizado' ? 'badge-success' : 'badge-warning';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Detalhes OP <?php echo $op['OP_ID']; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/theme.js"></script>
    <style>
        .op-header-card {
            background: linear-gradient(135deg, var(--primary-color), #1a6fcf);
            color: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
        }
        .op-header-card .op-info-item span {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.75;
            margin-bottom: 4px;
        }
        .op-header-card .op-info-item strong {
            font-size: 18px;
            font-weight: 700;
        }
        .section-card {
            background: var(--container-bg);
            border: 1px solid var(--input-border);
            border-radius: 14px;
            padding: 28px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-md);
        }
        .section-card h3 {
            font-size: 17px;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0 0 20px 0;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--input-border);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }
        .form-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 18px;
            align-items: end;
        }
        .form-grid-4 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 18px;
            align-items: end;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-success {
            background: #1a9e5c;
            color: white;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .badge-warning {
            background: #e6a817;
            color: white;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .flash-message {
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .flash-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
        .flash-error   { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
        .dark-mode .flash-success { background: #064e3b; color: #6ee7b7; }
        .dark-mode .flash-error   { background: #7f1d1d; color: #fca5a5; }
        .historico-empty {
            color: var(--text-muted);
            font-style: italic;
            padding: 12px 0;
            text-align: center;
        }
        .btn-submit-primary {
            padding: 14px 32px;
            font-size: 16px;
        }
        @media (max-width: 768px) {
            .form-grid-2, .form-grid-3, .form-grid-4 { grid-template-columns: 1fr; }
            .op-header-card { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
<?php include_once dirname(__DIR__) . '/php/header.php'; ?>

<div class="container">

    <!-- Mensagens de feedback -->
    <?php if ($msg_sucesso): ?>
        <div class="flash-message flash-success"><?php echo $msg_sucesso; ?></div>
    <?php endif; ?>
    <?php if ($msg_erro): ?>
        <div class="flash-message flash-error"><?php echo $msg_erro; ?></div>
    <?php endif; ?>

    <!-- Cabeçalho da OP -->
    <div class="op-header-card">
        <div class="op-info-item">
            <span>Ordem de Produção</span>
            <strong>#<?php echo $op['OP_ID']; ?></strong>
        </div>
        <div class="op-info-item">
            <span>Produto</span>
            <strong><?php echo htmlspecialchars($descricao_produto); ?></strong>
        </div>
        <div class="op-info-item">
            <span>Linha</span>
            <strong><?php echo htmlspecialchars($op['OP_LINHA']); ?></strong>
        </div>
        <div class="op-info-item">
            <span>Qtd. Programada</span>
            <strong><?php echo $op['OP_QTD_PRODUTO']; ?> un.</strong>
        </div>
        <div class="op-info-item">
            <span>Data</span>
            <strong><?php echo date('d/m/Y', strtotime($op['OP_DATA_PROG'])); ?></strong>
        </div>
        <div class="op-info-item">
            <span>Status</span>
            <strong><span class="<?php echo $status_class; ?>"><?php echo $op['OP_STATUS']; ?></span></strong>
        </div>
    </div>

    <!-- SEÇÃO 1: Dados da OP -->
    <div class="section-card">
        <h3>📋 Dados da OP</h3>
        <form method="POST" action="detalhes_op.php?id=<?php echo $op['OP_ID']; ?>" id="form_op">
            <div class="form-grid-2" style="margin-bottom: 18px;">
                <div class="form-group">
                    <label for="op_recebedor">Recebedor / Operador</label>
                    <input type="text" name="op_recebedor" id="op_recebedor"
                           placeholder="Nome do recebedor..."
                           value="<?php echo htmlspecialchars($op['OP_RECEBEDOR'] ?? ''); ?>"
                           <?php if ($isPCP) echo 'readonly class="readonly"'; else echo 'required'; ?>>
                </div>
                <div class="form-group">
                    <label for="op_status">Status da OP</label>
                    <select name="op_status" id="op_status" <?php if ($isPCP) echo 'disabled'; ?>>
                        <option value="Aberto"     <?php echo $op['OP_STATUS'] === 'Aberto'     ? 'selected' : ''; ?>>Aberto</option>
                        <option value="Finalizado" <?php echo $op['OP_STATUS'] === 'Finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                    </select>
                </div>
            </div>
            <div class="form-grid-2" style="margin-bottom: 18px;">
                <div class="form-group">
                    <label for="op_hora_inicio">Hora de Início</label>
                    <input type="datetime-local" name="op_hora_inicio" id="op_hora_inicio"
                           value="<?php echo $op['OP_HORA_INICIO'] ?? ''; ?>"
                           <?php if ($isPCP) echo 'readonly class="readonly"'; else echo 'required'; ?>>
                </div>
                <div class="form-group">
                    <label for="op_hora_fim">Hora de Fim</label>
                    <input type="datetime-local" name="op_hora_fim" id="op_hora_fim"
                           value="<?php echo $op['OP_HORA_FIM'] ?? ''; ?>"
                           <?php if ($isPCP) echo 'readonly class="readonly"'; ?>>
                </div>
            </div>
            <div class="form-grid-2" style="margin-bottom: 18px;">
                <div class="form-group">
                    <label for="op_qtd_real">Quantidade Real Produzida</label>
                    <input type="number" name="op_qtd_real" id="op_qtd_real"
                           placeholder="Ex: 200"
                           value="<?php echo $op['OP_QTD_REAL'] ?? ''; ?>"
                           <?php if ($isPCP) echo 'readonly class="readonly"'; ?>>
                </div>
                <div class="form-group">
                    <label>Programador</label>
                    <input type="text" value="<?php echo htmlspecialchars($op['OP_PROGRAMADOR'] ?? ''); ?>" readonly class="readonly">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 18px;">
                <label for="op_diario_bordo">📓 Diário de Bordo</label>
                <textarea name="op_diario_bordo" id="op_diario_bordo" rows="4"
                          placeholder="Registre aqui as ocorrências, observações e eventos relevantes da OP..."
                          <?php if ($isPCP) echo 'readonly class="readonly-textarea"'; ?>
                ><?php echo htmlspecialchars($op['OP_DIARIO_BORDO'] ?? ''); ?></textarea>
            </div>
            <?php if (!$isPCP): ?>
                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn-submit-primary">💾 Salvar Dados da OP</button>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- SEÇÃO 2: Botão de OS (visível para todos) -->
    <div class="section-card">
        <h3>🔧 Ordem de Serviço</h3>
        <p style="color: var(--text-muted); margin-bottom: 18px;">Abra uma Ordem de Serviço de manutenção vinculada a esta OP.</p>
        <form action="abrir_ordem_servico.php" method="get">
            <input type="hidden" name="id_op" value="<?php echo $op['OP_ID']; ?>" />
            <button type="submit" class="btn btn-warning" style="font-size: 16px;">🛠️ Abrir Ordem de Serviço da Máquina</button>
        </form>
    </div>

    <?php if (!$isPCP): ?>

    <!-- SEÇÃO 3: Apontar Refugo -->
    <div class="section-card">
        <h3>🗑️ Apontar Refugo</h3>
        <form method="POST" action="detalhes_op.php?id=<?php echo $op['OP_ID']; ?>">
            <div class="form-grid-4">
                <div class="form-group">
                    <label for="qtd_refugo">Quantidade</label>
                    <input type="number" name="qtd_refugo" id="qtd_refugo" min="1" placeholder="Ex: 10" required>
                </div>
                <div class="form-group">
                    <label for="refugo_unidade">Unidade</label>
                    <select name="refugo_unidade" id="refugo_unidade" required>
                        <option value="">Selecione...</option>
                        <option value="KG">KG</option>
                        <option value="UN">UN</option>
                        <option value="M2">M²</option>
                        <option value="M">M</option>
                        <option value="L">L</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tipo_refugo">Tipo / Motivo</label>
                    <input type="text" name="tipo_refugo" id="tipo_refugo" placeholder="Ex: Peça amassada" required>
                </div>
                <div class="form-group">
                    <button type="submit" style="height: 50px;">Registrar</button>
                </div>
            </div>
        </form>
        <?php if (!empty($historico_refugos)): ?>
        <div class="table-responsive" style="margin-top: 22px;">
            <table>
                <thead><tr><th>Quantidade</th><th>Unidade</th><th>Tipo / Motivo</th></tr></thead>
                <tbody>
                    <?php foreach ($historico_refugos as $refugo): ?>
                    <tr>
                        <td><?php echo $refugo['REFUGO_QTD']; ?></td>
                        <td><?php echo $refugo['REFUGO_UNIDADE']; ?></td>
                        <td><?php echo htmlspecialchars($refugo['REFUGO_TIPO']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="historico-empty">Nenhum refugo registrado para esta OP.</p>
        <?php endif; ?>
    </div>

    <!-- SEÇÃO 4: Apontar Parada -->
    <div class="section-card">
        <h3>⏸️ Apontar Parada Produtiva</h3>
        <form method="POST" action="detalhes_op.php?id=<?php echo $op['OP_ID']; ?>">
            <div class="form-grid-4">
                <div class="form-group">
                    <label for="inicio">Início da Parada</label>
                    <input type="datetime-local" name="inicio" id="inicio" required>
                </div>
                <div class="form-group">
                    <label for="fim">Fim da Parada</label>
                    <input type="datetime-local" name="fim" id="fim" required>
                </div>
                <div class="form-group">
                    <label for="tipo_parada">Tipo / Motivo da Parada</label>
                    <input type="text" name="tipo_parada" id="tipo_parada" placeholder="Ex: Manutenção corretiva" required>
                </div>
                <div class="form-group">
                    <button type="submit" style="height: 50px;">Registrar</button>
                </div>
            </div>
        </form>
        <?php if (!empty($historico_paradas)): ?>
        <div class="table-responsive" style="margin-top: 22px;">
            <table>
                <thead><tr><th>Início</th><th>Fim</th><th>Tipo / Motivo</th></tr></thead>
                <tbody>
                    <?php foreach ($historico_paradas as $parada): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($parada['PARADA_INICIO'])); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($parada['PARADA_FIM'])); ?></td>
                        <td><?php echo htmlspecialchars($parada['PARADA_TIPO']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="historico-empty">Nenhuma parada registrada para esta OP.</p>
        <?php endif; ?>
    </div>

    <!-- SEÇÃO 5: Apontar Lotes -->
    <?php if (!empty($estrutura)): ?>
    <div class="section-card">
        <h3>📦 Apontar Lotes de Materiais</h3>
        <form method="POST" action="detalhes_op.php?id=<?php echo $op['OP_ID']; ?>">
            <div class="form-grid-2" style="margin-bottom: 20px;">
                <?php foreach ($estrutura as $item): ?>
                <div class="form-group">
                    <label for="lote_<?php echo $item['ESTRUTURA_ID']; ?>"><?php echo htmlspecialchars($item['PROD_DESC']); ?></label>
                    <input type="number" name="lotes[<?php echo $item['ESTRUTURA_ID']; ?>]" id="lote_<?php echo $item['ESTRUTURA_ID']; ?>" placeholder="Nº do lote...">
                </div>
                <?php endforeach; ?>
            </div>
            <div style="display: flex; justify-content: flex-end;">
                <button type="submit">📦 Registrar Lotes</button>
            </div>
        </form>
        <?php if (!empty($historico_lotes)): ?>
        <div class="table-responsive" style="margin-top: 22px;">
            <table>
                <thead><tr><th>Produto / Componente</th><th>Lote Apontado</th></tr></thead>
                <tbody>
                    <?php foreach ($historico_lotes as $lote): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($lote['PROD_DESC']); ?></td>
                        <td><?php echo htmlspecialchars($lote['LOTE_APONTADO']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="historico-empty">Nenhum lote registrado para esta OP.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php endif; // fim do bloco !isPCP ?>

</div>

<?php if (!$isPCP): ?>
<script>
    const statusSelect = document.getElementById('op_status');
    const camposObrigatorios = ['op_hora_fim', 'op_qtd_real'];

    function atualizarObrigatorios() {
        camposObrigatorios.forEach(id => {
            const campo = document.getElementById(id);
            if (statusSelect.value === 'Finalizado') {
                campo.setAttribute('required', 'required');
            } else {
                campo.removeAttribute('required');
            }
        });
    }

    statusSelect.addEventListener('change', atualizarObrigatorios);
    window.addEventListener('DOMContentLoaded', atualizarObrigatorios);
</script>
<?php endif; ?>

</body>
</html>
