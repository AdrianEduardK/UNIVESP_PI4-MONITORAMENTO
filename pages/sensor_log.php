<?php
require_once '../php/auth.php';
exigirManutencao();
include('../php/conexao.php');

function dataFiltroSensor($valor) {
    if (!is_string($valor) || $valor === '') {
        return '';
    }
    $data = DateTime::createFromFormat('!Y-m-d', $valor);
    $erros = DateTime::getLastErrors();
    $invalida = !$data || (is_array($erros) && ((($erros['warning_count'] ?? 0) > 0) || (($erros['error_count'] ?? 0) > 0)));
    return $invalida ? '' : $data->format('Y-m-d');
}

function filtroSensor($maq, $inicio, $fim, $evento) {
    $where = [];
    $types = '';
    $params = [];
    if ($maq !== '') {
        $where[] = 'LOG_TAG_MAQ = ?';
        $types .= 's';
        $params[] = $maq;
    }
    if ($inicio !== '') {
        $where[] = 'DATE(LOG_TIMESTAMP) >= ?';
        $types .= 's';
        $params[] = $inicio;
    }
    if ($fim !== '') {
        $where[] = 'DATE(LOG_TIMESTAMP) <= ?';
        $types .= 's';
        $params[] = $fim;
    }
    if ($evento !== '') {
        $where[] = 'LOG_EVENTO = ?';
        $types .= 's';
        $params[] = $evento;
    }
    $sql = empty($where) ? '' : ' WHERE ' . implode(' AND ', $where);
    return [$sql, $types, $params];
}

function consultarSensor($conn, $sql, $types, $params) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log($conn->error);
        return null;
    }
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        error_log($stmt->error);
        $stmt->close();
        return null;
    }
    $result = $stmt->get_result();
    return $result;
}

$filtro_maq = trim((string)($_GET['maq'] ?? ''));
$filtro_data_inicio = dataFiltroSensor($_GET['data_inicio'] ?? '');
$filtro_data_fim = dataFiltroSensor($_GET['data_fim'] ?? '');
$eventosValidos = ['CONEXAO', 'DESCONEXAO', 'INPUT'];
$filtro_evento = $_GET['evento'] ?? '';
if (!in_array($filtro_evento, $eventosValidos, true)) {
    $filtro_evento = '';
}

[$whereSql, $types, $params] = filtroSensor($filtro_maq, $filtro_data_inicio, $filtro_data_fim, $filtro_evento);
$result = consultarSensor($conn, 'SELECT * FROM tab_sensor_log' . $whereSql . ' ORDER BY LOG_TIMESTAMP DESC LIMIT 500', $types, $params);

$todas_maquinas = $conn->query("SELECT MAQ_TAG AS tag FROM tab_maq UNION SELECT LOG_TAG_MAQ FROM tab_sensor_log ORDER BY tag");

$chartQuery = consultarSensor($conn, 'SELECT LOG_TIMESTAMP, LOG_TOTAL_INPUTS FROM tab_sensor_log' . $whereSql . ' ORDER BY LOG_TIMESTAMP ASC LIMIT 100', $types, $params);
$chart_labels = [];
$chart_data = [];
if ($chartQuery) {
    while ($row = $chartQuery->fetch_assoc()) {
        $ts = strtotime($row['LOG_TIMESTAMP']);
        $chart_labels[] = $ts ? date('d/m H:i', $ts) : '';
        $chart_data[] = (int)$row['LOG_TOTAL_INPUTS'];
    }
}

$ultimaQuery = consultarSensor($conn, 'SELECT LOG_TOTAL_INPUTS FROM tab_sensor_log' . $whereSql . ' ORDER BY LOG_TIMESTAMP DESC LIMIT 1', $types, $params);
$ultima_qtd = 0;
if ($ultimaQuery && ($ultima = $ultimaQuery->fetch_assoc())) {
    $ultima_qtd = (int)$ultima['LOG_TOTAL_INPUTS'];
}

function totalSensor($conn, $sql, $types, $params) {
    $res = consultarSensor($conn, $sql, $types, $params);
    if (!$res || !($row = $res->fetch_assoc())) {
        return 0;
    }
    return $row['total'] ?? 0;
}

$stats_total = (int)totalSensor($conn, 'SELECT COUNT(*) AS total FROM tab_sensor_log' . $whereSql, $types, $params);
[$whereAtivo, $typesAtivo, $paramsAtivo] = filtroSensor($filtro_maq, $filtro_data_inicio, $filtro_data_fim, '');
$whereAtivo .= ($whereAtivo === '' ? ' WHERE ' : ' AND ') . "LOG_EVENTO = 'CONEXAO' AND LOG_TIMESTAMP_FIM IS NULL";
$stats_conexoes = (int)totalSensor($conn, 'SELECT COUNT(*) AS total FROM tab_sensor_log' . $whereAtivo, $typesAtivo, $paramsAtivo);
$stats_desc = (int)totalSensor($conn, "SELECT COUNT(*) AS total FROM tab_sensor_log" . $whereSql . ($whereSql === '' ? " WHERE LOG_EVENTO = 'DESCONEXAO'" : " AND LOG_EVENTO = 'DESCONEXAO'"), $types, $params);
$stats_inputs = totalSensor($conn, 'SELECT COALESCE(SUM(LOG_TOTAL_INPUTS), 0) AS total FROM tab_sensor_log' . $whereSql, $types, $params);

$rotulosEvento = [
    'CONEXAO' => 'CONECTADO',
    'DESCONEXAO' => 'DESCONECTADO',
    'INPUT' => 'INPUT',
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Log de Sensores</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/theme.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
    <style>
        .stat-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .stat-card { background: var(--container-bg); border: 1px solid var(--input-border); border-radius: 14px; padding: 20px; text-align: center; box-shadow: var(--shadow-sm); }
        .stat-card .stat-value { font-size: 28px; font-weight: 700; color: var(--primary-color); }
        .stat-card .stat-label { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-top: 4px; }
        .evento-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; letter-spacing: 0.5px; }
        .evento-CONEXAO    { background: #d1fae5; color: #065f46; }
        .evento-DESCONEXAO { background: #fee2e2; color: #991b1b; }
        .evento-INPUT      { background: #dbeafe; color: #1e40af; }
        [data-theme="dark"] .evento-CONEXAO    { background: #064e3b; color: #6ee7b7; }
        [data-theme="dark"] .evento-DESCONEXAO { background: #7f1d1d; color: #fca5a5; }
        [data-theme="dark"] .evento-INPUT      { background: #1e3a5f; color: #93c5fd; }
    </style>
</head>
<body>
<?php include_once dirname(__DIR__) . '/php/header.php'; ?>

<div class="container">
    <h2>Log de Sensores / Monitoramento</h2>

    <div class="stat-cards">
        <div class="stat-card" style="background: var(--primary-color); color: white; border-color: var(--primary-color);">
            <div class="stat-value" style="color: white;"><?php echo number_format($ultima_qtd); ?></div>
            <div class="stat-label" style="color: rgba(255,255,255,0.8);">Inputs (Última Leitura)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats_total; ?></div>
            <div class="stat-label">Total de Eventos</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats_conexoes; ?></div>
            <div class="stat-label">Conexões Ativas</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats_desc; ?></div>
            <div class="stat-label">Desconexões</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format((float)$stats_inputs); ?></div>
            <div class="stat-label">Inputs no Período</div>
        </div>
    </div>

    <div style="background: var(--container-bg); border: 1px solid var(--input-border); border-radius: 14px; padding: 20px; margin-bottom: 28px; box-shadow: var(--shadow-sm);">
        <h3 style="margin-top: 0; color: var(--primary-color); font-size: 16px; margin-bottom: 15px;">Comportamento de Inputs</h3>
        <canvas id="inputsChart" height="60"></canvas>
    </div>

    <div style="background: var(--container-bg); border: 1px solid var(--input-border); border-radius: 14px; padding: 20px; margin-bottom: 28px; box-shadow: var(--shadow-sm);">
        <h3 style="margin-top: 0; color: var(--primary-color); font-size: 16px; margin-bottom: 15px;">Inicializar Coleta na Máquina</h3>
        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 15px;">O script Python usa Pygame para ler um controle USB, contar eventos e enviá-los ao servidor. A tag precisa existir no cadastro de máquinas.</p>
        <form method="POST" action="../php/start_sensor.php" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
            <?php echo campoCsrf(); ?>
            <div style="flex-grow: 1;">
                <label>Selecione a Máquina Alvo:</label>
                <select name="tag_maq" required>
                    <option value="">-- Selecione uma Máquina --</option>
                    <?php
                    $maquinasCadastro = $conn->query('SELECT MAQ_TAG FROM tab_maq ORDER BY MAQ_TAG');
                    if ($maquinasCadastro) { while ($m = $maquinasCadastro->fetch_assoc()) { ?>
                        <option value="<?php echo htmlspecialchars($m['MAQ_TAG']); ?>"><?php echo htmlspecialchars($m['MAQ_TAG']); ?></option>
                    <?php } } ?>
                </select>
            </div>
            <div>
                <button type="submit" class="btn" style="background: #10b981; height: 42px;">Iniciar Monitoramento</button>
            </div>
        </form>
    </div>

    <form method="GET" action="sensor_log.php">
        <div class="filtros" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
            <div>
                <label>Máquina (Tag):</label>
                <select name="maq">
                    <option value="">Todas</option>
                    <?php if ($todas_maquinas) { while ($m = $todas_maquinas->fetch_assoc()) { ?>
                        <option value="<?php echo htmlspecialchars($m['tag']); ?>" <?php echo $filtro_maq === $m['tag'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m['tag']); ?>
                        </option>
                    <?php } } ?>
                </select>
            </div>
            <div>
                <label>Data Início:</label>
                <input type="date" name="data_inicio" value="<?php echo htmlspecialchars($filtro_data_inicio); ?>">
            </div>
            <div>
                <label>Data Fim:</label>
                <input type="date" name="data_fim" value="<?php echo htmlspecialchars($filtro_data_fim); ?>">
            </div>
            <div>
                <label>Tipo de Evento:</label>
                <select name="evento">
                    <option value="">Todos</option>
                    <option value="CONEXAO" <?php echo $filtro_evento === 'CONEXAO' ? 'selected' : ''; ?>>Conexão</option>
                    <option value="DESCONEXAO" <?php echo $filtro_evento === 'DESCONEXAO' ? 'selected' : ''; ?>>Desconexão</option>
                    <option value="INPUT" <?php echo $filtro_evento === 'INPUT' ? 'selected' : ''; ?>>Input</option>
                </select>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn">Filtrar</button>
                <a href="sensor_log.php" class="btn btn-outline" style="border: 2px solid var(--input-border); color: var(--text-color); background: transparent;">Limpar</a>
            </div>
        </div>
    </form>

    <div class="table-responsive" style="margin-top: 20px;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tag Máquina</th>
                    <th>Status / Evento</th>
                    <th>Início da Sessão</th>
                    <th>Fim da Sessão</th>
                    <th>Inputs Acumulados</th>
                    <th>Tempo Conectado</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                        $evento = $row['LOG_EVENTO'];
                        $classe = isset($rotulosEvento[$evento]) ? $evento : 'INPUT';
                        $rotulo = $rotulosEvento[$evento] ?? 'EVENTO';
                        $inicioTs = strtotime($row['LOG_TIMESTAMP']);
                    ?>
                    <tr>
                        <td><?php echo (int)$row['LOG_ID']; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['LOG_TAG_MAQ']); ?></strong></td>
                        <td><span class="evento-badge evento-<?php echo $classe; ?>"><?php echo htmlspecialchars($rotulo); ?></span></td>
                        <td><?php echo $inicioTs ? date('d/m/Y H:i:s', $inicioTs) : htmlspecialchars((string)$row['LOG_TIMESTAMP']); ?></td>
                        <td>
                            <?php if (!empty($row['LOG_TIMESTAMP_FIM'])): ?>
                                <?php $fimTs = strtotime($row['LOG_TIMESTAMP_FIM']); ?>
                                <?php echo $fimTs ? date('d/m/Y H:i:s', $fimTs) : htmlspecialchars((string)$row['LOG_TIMESTAMP_FIM']); ?>
                            <?php else: ?>
                                <span style="color:var(--text-muted)">Em andamento...</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format((int)$row['LOG_TOTAL_INPUTS']); ?></td>
                        <td>
                            <?php
                            $seg = (int)$row['LOG_TEMPO_CONECTADO'];
                            if ($seg > 0) {
                                $h = intdiv($seg, 3600);
                                $m = intdiv($seg % 3600, 60);
                                $s = $seg % 60;
                                echo sprintf('%02d:%02d:%02d', $h, $m, $s);
                            } else {
                                echo '—';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--text-muted); font-style: italic; padding: 30px;">Nenhum log encontrado neste período.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
new Chart(document.getElementById('inputsChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chart_labels, JSON_UNESCAPED_UNICODE); ?>,
        datasets: [{
            label: 'Quantidade de Inputs por Sessão',
            data: <?php echo json_encode($chart_data); ?>,
            borderColor: '#3182ce',
            backgroundColor: 'rgba(49, 130, 206, 0.15)',
            borderWidth: 2,
            pointRadius: 5,
            pointBackgroundColor: '#3182ce',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
</body>
</html>
