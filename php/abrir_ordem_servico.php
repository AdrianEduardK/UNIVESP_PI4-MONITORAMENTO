<?php
require_once __DIR__ . '/auth.php';
exigirManutencao();
include __DIR__ . '/conexao.php';
require_once __DIR__ . '/flash_messages.php';
require_once dirname(__DIR__) . '/models/IndicadoresCalculo.php';

$id_op = filter_input(INPUT_GET, 'id_op', FILTER_VALIDATE_INT);
if (!$id_op) {
    echo 'ID da OP não informado.';
    exit;
}

$stmt = $conn->prepare('SELECT * FROM tab_op WHERE OP_ID = ?');
$stmt->bind_param('i', $id_op);
$stmt->execute();
$op = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$op) {
    echo 'OP não encontrada.';
    exit;
}

$tags_maquinas = $conn->query('SELECT MAQ_TAG FROM tab_maq ORDER BY MAQ_TAG');
$linhas = $conn->query('SELECT LINHA_DESC FROM tab_linhas ORDER BY LINHA_DESC');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCSRF();

    $os_solicitante = trim((string)($_POST['os_solicitante'] ?? ''));
    $os_status = $_POST['os_status'] ?? '';
    $os_tag_maq = trim((string)($_POST['os_tag_maq'] ?? ''));
    $os_linha = trim((string)($_POST['os_linha'] ?? ''));
    $os_classificacao = $_POST['os_classificacao'] ?? '';
    $os_situacao = $_POST['os_situacao'] ?? '';
    $os_gravidade = (int)($_POST['os_gravidade'] ?? 0);
    $os_urgencia = (int)($_POST['os_urgencia'] ?? 0);
    $os_tendencia = (int)($_POST['os_tendencia'] ?? 0);
    $os_desc_pedido = trim((string)($_POST['os_desc_pedido'] ?? ''));
    $tipo_manut = $_POST['os_tipo_manut'] ?? '';
    $custo_pecas = (float)($_POST['os_custo_pecas'] ?? 0);
    $custo_mao_obra = (float)($_POST['os_custo_mao_obra'] ?? 0);
    $data_planejada = IndicadoresCalculo::dataValida((string)($_POST['os_data_planejada'] ?? ''));

    $classes = ['MECANICA', 'ELETRICA', 'PREDIAL', 'ELETROMECANICA'];
    $situacoes = ['Linha parada', 'Linha segue em atuação'];
    $tipos = ['CORRETIVA', 'PREVENTIVA', 'PREDITIVA'];
    $gutOk = $os_gravidade >= 1 && $os_gravidade <= 5
        && $os_urgencia >= 1 && $os_urgencia <= 5
        && $os_tendencia >= 1 && $os_tendencia <= 5;
    $valido = $os_solicitante !== ''
        && $os_status === 'Aberto'
        && $os_tag_maq !== ''
        && $os_linha !== ''
        && in_array($os_classificacao, $classes, true)
        && in_array($os_situacao, $situacoes, true)
        && in_array($tipo_manut, $tipos, true)
        && $gutOk
        && $os_desc_pedido !== ''
        && strlen($os_desc_pedido) <= 5000
        && $custo_pecas >= 0
        && $custo_mao_obra >= 0
        && ($tipo_manut !== 'PREVENTIVA' || $data_planejada !== null);

    if (!$valido) {
        setFlashMessage('Não foi possível criar a ordem de serviço. Revise os campos.', 'error');
        header('Location: abrir_ordem_servico.php?id_op=' . $id_op);
        exit;
    }

    $os_soma_gut = $os_gravidade + $os_urgencia + $os_tendencia;
    $os_data_solicitacao = date('Y-m-d H:i:s');
    if ($tipo_manut !== 'PREVENTIVA') {
        $data_planejada = null;
    }

    $sql_os = 'INSERT INTO tab_ordem_servico (
                    OS_SOLICITANTE, OS_STATUS, OS_TAG_MAQ, OS_LINHA, OS_CLASSIFICACAO,
                    OS_SITUACAO, OS_GRAVIDADE, OS_URGENCIA, OS_TENDENCIA, OS_SOMA_GUT,
                    OS_DESC_PEDIDO, OS_DATA_SOLICITACAO, OS_ID_OP, OS_TIPO_MANUT, OS_CUSTO_PECAS, OS_CUSTO_MAO_OBRA, OS_DATA_PLANEJADA
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $stmt_os = $conn->prepare($sql_os);
    if (!$stmt_os) {
        error_log($conn->error);
        setFlashMessage('Não foi possível criar a ordem de serviço.', 'error');
        header('Location: abrir_ordem_servico.php?id_op=' . $id_op);
        exit;
    }
    $stmt_os->bind_param(
        'ssssssiiiissisdds',
        $os_solicitante,
        $os_status,
        $os_tag_maq,
        $os_linha,
        $os_classificacao,
        $os_situacao,
        $os_gravidade,
        $os_urgencia,
        $os_tendencia,
        $os_soma_gut,
        $os_desc_pedido,
        $os_data_solicitacao,
        $id_op,
        $tipo_manut,
        $custo_pecas,
        $custo_mao_obra,
        $data_planejada
    );

    if ($stmt_os->execute()) {
        $stmt_os->close();
        setFlashMessage('Ordem de serviço criada com sucesso.', 'success');
        header('Location: detalhes_op.php?id=' . $id_op);
        exit;
    }

    error_log($stmt_os->error);
    $stmt_os->close();
    setFlashMessage('Não foi possível criar a ordem de serviço.', 'error');
    header('Location: abrir_ordem_servico.php?id_op=' . $id_op);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Abertura de Ordem de Serviço</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/theme.js"></script>
    <script>
        function calcularSomaGUT() {
            const gravidade = parseInt(document.querySelector('[name="os_gravidade"]').value) || 0;
            const urgencia = parseInt(document.querySelector('[name="os_urgencia"]').value) || 0;
            const tendencia = parseInt(document.querySelector('[name="os_tendencia"]').value) || 0;
            document.querySelector('[name="os_soma_gut"]').value = gravidade + urgencia + tendencia;
        }

        function toggleDataPlanejada() {
            var tipo = document.querySelector('[name="os_tipo_manut"]').value;
            var div = document.getElementById('div_data_planejada');
            var input = document.querySelector('[name="os_data_planejada"]');
            if (tipo === 'PREVENTIVA') {
                div.style.display = 'block';
                input.required = true;
            } else {
                div.style.display = 'none';
                input.required = false;
                input.value = '';
            }
        }
    </script>
</head>
<body>
<?php include_once dirname(__DIR__) . '/php/header.php'; ?>

    <div class="form-os">
        <h2>Abrir Ordem de Serviço para OP <?php echo (int)$id_op; ?></h2>

        <div class="gut-info">
            <strong>Obs:</strong><br>
            Cada campo (Gravidade, Urgência e Tendência) deve receber um valor entre <strong>1</strong> (menor grau) e <strong>5</strong> (maior grau). A <strong>Soma GUT</strong> é calculada automaticamente com base nesses três valores.<br>
        </div>

        <form method="POST">
            <?php echo campoCsrf(); ?>
            <label for="os_solicitante">Solicitante:</label>
            <input type="text" name="os_solicitante" value="<?php echo htmlspecialchars($_SESSION['nome'] ?? ''); ?>" readonly required>

            <label for="os_status">Status:</label>
            <select name="os_status" required>
                <option value="Aberto">Aberto</option>
            </select>

            <label for="os_tag_maq">Tag Máquina:</label>
            <select name="os_tag_maq" required>
                <?php if ($tags_maquinas) { while ($tag = $tags_maquinas->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($tag['MAQ_TAG']); ?>"><?php echo htmlspecialchars($tag['MAQ_TAG']); ?></option>
                <?php endwhile; } ?>
            </select>

            <label for="os_linha">Linha:</label>
            <select name="os_linha" required>
                <?php if ($linhas) { while ($linha = $linhas->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($linha['LINHA_DESC']); ?>"><?php echo htmlspecialchars($linha['LINHA_DESC']); ?></option>
                <?php endwhile; } ?>
            </select>

            <label for="os_classificacao">Classificação:</label>
            <select name="os_classificacao" required>
                <option value="MECANICA">MECÂNICA</option>
                <option value="ELETRICA">ELÉTRICA</option>
                <option value="PREDIAL">PREDIAL</option>
                <option value="ELETROMECANICA">ELETROMECÂNICA</option>
            </select>

            <label for="os_situacao">Situação:</label>
            <select name="os_situacao" required>
                <option value="Linha parada">Linha parada</option>
                <option value="Linha segue em atuação">Linha segue em atuação</option>
            </select>

            <label for="os_gravidade">Gravidade (1 a 5):</label>
            <input type="number" name="os_gravidade" min="1" max="5" required oninput="calcularSomaGUT()">

            <label for="os_urgencia">Urgência (1 a 5):</label>
            <input type="number" name="os_urgencia" min="1" max="5" required oninput="calcularSomaGUT()">

            <label for="os_tendencia">Tendência (1 a 5):</label>
            <input type="number" name="os_tendencia" min="1" max="5" required oninput="calcularSomaGUT()">

            <label for="os_soma_gut">Soma GUT:</label>
            <input type="number" name="os_soma_gut" readonly required>

            <label>Tipo de Manutenção:</label>
            <select name="os_tipo_manut" required onchange="toggleDataPlanejada()">
                <option value="CORRETIVA">Corretiva</option>
                <option value="PREVENTIVA">Preventiva</option>
                <option value="PREDITIVA">Preditiva</option>
            </select>

            <div id="div_data_planejada" style="display: none;">
                <label>Data Planejada (Preventiva):</label>
                <input type="date" name="os_data_planejada">
            </div>

            <label>Custo Estimado Peças (R$):</label>
            <input type="number" step="0.01" min="0" name="os_custo_pecas" value="0.00">

            <label>Custo Mão de Obra (R$):</label>
            <input type="number" step="0.01" min="0" name="os_custo_mao_obra" value="0.00">

            <label for="os_desc_pedido">Descrição do Pedido:</label>
            <textarea name="os_desc_pedido" rows="5" required></textarea>

            <button type="submit">Criar Ordem de Serviço</button>
            <button type="button" onclick="window.location.href='detalhes_op.php?id=<?php echo (int)$id_op; ?>'">
                Voltar para a OP
            </button>
        </form>
    </div>
</body>
</html>
