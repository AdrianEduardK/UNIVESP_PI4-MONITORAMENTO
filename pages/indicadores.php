<?php
require_once '../php/auth.php';
exigirManutencao();
include('../php/conexao.php');
require_once '../controllers/IndicadoresController.php';

$controller = new IndicadoresController($conn);
$dados = $controller->handleRequest();
$filtros = $controller->getFiltros();
$resumo = $dados['resumo'];

function valorOuTraco($valor, $sufixo = '') {
    if ($valor === null || $valor === '') {
        return '—';
    }
    return htmlspecialchars((string)$valor) . $sufixo;
}

$porTag = [];
foreach ($dados['paradas'] as $item) {
    $porTag[$item['OS_TAG_MAQ']]['paradas'] = (int)$item['N_PARADAS'];
}
foreach ($dados['mtbf'] as $item) {
    $porTag[$item['OS_TAG_MAQ']]['mtbf'] = $item['MTBF_HORAS'];
}
foreach ($dados['mttr'] as $item) {
    $porTag[$item['OS_TAG_MAQ']]['mttr'] = $item['MTTR_HORAS'];
}
$tagsGrafico = array_keys($porTag);
$mtbfChartData = [];
$mttrChartData = [];
$paradasChartData = [];
foreach ($tagsGrafico as $tag) {
    $mtbfChartData[] = $porTag[$tag]['mtbf'] ?? null;
    $mttrChartData[] = $porTag[$tag]['mttr'] ?? null;
    $paradasChartData[] = $porTag[$tag]['paradas'] ?? 0;
}

$custoLabels = [];
$custoData = [];
foreach ($dados['custo'] as $item) {
    $custoLabels[] = $item['OS_TAG_MAQ'];
    $custoData[] = (float)$item['CUSTO_TOTAL'];
}

$evolLabels = [];
$evolMtbf = [];
$evolMttr = [];
$evolParadas = [];
foreach ($dados['evolucao'] as $item) {
    $evolLabels[] = $item['rotulo'];
    $evolMtbf[] = $item['mtbf'];
    $evolMttr[] = $item['mttr'];
    $evolParadas[] = $item['paradas'];
}

$todas_maquinas = $conn->query('SELECT MAQ_TAG FROM tab_maq ORDER BY MAQ_TAG');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Indicadores Analíticos (PI4)</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/theme.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
    <style>
        .stat-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .stat-card { background: var(--container-bg); border: 1px solid var(--input-border); border-radius: 14px; padding: 20px; text-align: center; box-shadow: var(--shadow-sm); }
        .stat-card .stat-value { font-size: 28px; font-weight: 700; color: var(--primary-color); }
        .stat-card .stat-label { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-top: 4px; }
        .grid-graficos { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 28px; }
        .grafico-largo { grid-column: 1 / -1; }
        @media (max-width: 768px) { .grid-graficos { grid-template-columns: 1fr; } }
        .grafico-card { background: var(--container-bg); border: 1px solid var(--input-border); border-radius: 14px; padding: 20px; box-shadow: var(--shadow-sm); }
        .nota-formula { color: var(--text-muted); font-size: 13px; margin: 0 0 20px; }
    </style>
</head>
<body>
<?php include_once dirname(__DIR__) . '/php/header.php'; ?>

<div class="container">
    <h2>Indicadores de Manutenção</h2>
    <p class="nota-formula">Disponibilidade = MTBF / (MTBF + MTTR). O MTBF desconta o tempo de reparo das corretivas fechadas. Datas inválidas são ignoradas. Sem período, os cartões usam o intervalo entre a primeira e a última OS; o gráfico mensal usa o mês civil.</p>

    <form method="GET" action="indicadores.php" style="margin-bottom: 20px;">
        <div class="filtros" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
            <div>
                <label>Máquina (Tag):</label>
                <select name="maq">
                    <option value="">Todas</option>
                    <?php if ($todas_maquinas) { while ($m = $todas_maquinas->fetch_assoc()) { ?>
                        <option value="<?php echo htmlspecialchars($m['MAQ_TAG']); ?>" <?php echo $filtros['maq'] === $m['MAQ_TAG'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m['MAQ_TAG']); ?>
                        </option>
                    <?php } } ?>
                </select>
            </div>
            <div>
                <label>Setor/Linha:</label>
                <select name="setor">
                    <option value="">Todos</option>
                    <?php foreach ($dados['setores'] as $setor) { ?>
                        <option value="<?php echo htmlspecialchars($setor); ?>" <?php echo $filtros['setor'] === $setor ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($setor); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <label>Data Início:</label>
                <input type="date" name="data_inicio" value="<?php echo htmlspecialchars($filtros['data_inicio']); ?>">
            </div>
            <div>
                <label>Data Fim:</label>
                <input type="date" name="data_fim" value="<?php echo htmlspecialchars($filtros['data_fim']); ?>">
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn">Filtrar</button>
                <a href="indicadores.php" class="btn btn-outline" style="border: 2px solid var(--input-border); color: var(--text-color); background: transparent;">Limpar</a>
            </div>
        </div>
    </form>

    <div class="stat-cards">
        <div class="stat-card" style="background: var(--primary-color); color: white; border-color: var(--primary-color);">
            <div class="stat-value" style="color: white;"><?php echo valorOuTraco($resumo['dispMedia'], '%'); ?></div>
            <div class="stat-label" style="color: rgba(255,255,255,0.8);">Disponibilidade Média</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo valorOuTraco($resumo['mtbfMedio'], 'h'); ?></div>
            <div class="stat-label">MTBF Médio</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo valorOuTraco($resumo['mttrMedio'], 'h'); ?></div>
            <div class="stat-label">MTTR Médio</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">R$ <?php echo number_format($resumo['custoTotal'], 2, ',', '.'); ?></div>
            <div class="stat-label">Custo Total de Manutenção</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo valorOuTraco($resumo['prevMedia'], '%'); ?></div>
            <div class="stat-label">Preventivas no Prazo</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo (int)$resumo['paradasTotal']; ?></div>
            <div class="stat-label">Paradas Não Planejadas</div>
        </div>
    </div>

    <div class="grid-graficos">
        <div class="grafico-card">
            <h3 style="margin-top: 0; color: var(--primary-color); font-size: 16px; margin-bottom: 15px;">MTBF vs MTTR por Equipamento (Horas)</h3>
            <canvas id="mtbfChart" height="200"></canvas>
        </div>
        <div class="grafico-card">
            <h3 style="margin-top: 0; color: var(--primary-color); font-size: 16px; margin-bottom: 15px;">Paradas Não Planejadas por Equipamento</h3>
            <canvas id="paradasChart" height="200"></canvas>
        </div>
        <div class="grafico-card grafico-largo">
            <h3 style="margin-top: 0; color: var(--primary-color); font-size: 16px; margin-bottom: 15px;">Custos Totais por Equipamento</h3>
            <canvas id="custosChart" height="200"></canvas>
        </div>
        <div class="grafico-card grafico-largo">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 15px;">
                <h3 style="margin: 0; color: var(--primary-color); font-size: 16px;">Evolução mensal (MTBF, MTTR e paradas)</h3>
                <button type="button" onclick="exportEvolucaoCSV()" class="btn" style="background: #10b981; padding: 6px 12px; font-size: 14px;">Exportar CSV</button>
            </div>
            <canvas id="evolucaoChart" height="90"></canvas>
        </div>
    </div>

    <div style="background: var(--container-bg); border: 1px solid var(--input-border); border-radius: 14px; padding: 20px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin: 0; color: var(--primary-color); font-size: 16px;">Ranking de Criticidade de Equipamentos</h3>
            <button type="button" onclick="exportTableToCSV('ranking_criticidade.csv')" class="btn" style="background: #10b981; padding: 6px 12px; font-size: 14px;">Exportar CSV</button>
        </div>
        <p class="nota-formula">O score usa falhas corretivas (0,3), indisponibilidade (0,4) e impacto em produção cadastrado na máquina (0,3). O tempo de reparo aparece na coluna MTTR. Segurança, qualidade e custo não têm campo próprio no cadastro da máquina.</p>

        <div class="table-responsive">
            <table id="tabelaRanking">
                <thead>
                    <tr>
                        <th>Posição</th>
                        <th>Tag Equipamento</th>
                        <th>Nº Falhas (Corretivas)</th>
                        <th>MTTR (h)</th>
                        <th>Disponibilidade (%)</th>
                        <th>Impacto Produção</th>
                        <th>Score Criticidade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($dados['ranking'])): ?>
                        <?php foreach ($dados['ranking'] as $index => $row): ?>
                        <tr>
                            <td><?php echo $index + 1; ?>º</td>
                            <td><strong><?php echo htmlspecialchars($row['OS_TAG_MAQ']); ?></strong></td>
                            <td><?php echo (int)$row['N_FALHAS']; ?></td>
                            <td><?php echo valorOuTraco($row['MTTR']); ?></td>
                            <td><?php echo valorOuTraco($row['DISPONIBILIDADE'], '%'); ?></td>
                            <td>Nível <?php echo htmlspecialchars((string)$row['IMPACTO']); ?></td>
                            <td style="color: var(--danger-color); font-weight: bold;"><?php echo htmlspecialchars((string)$row['SCORE']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align: center; padding: 20px;">Nenhum dado encontrado para o período.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const mtbfLabels = <?php echo json_encode($tagsGrafico, JSON_UNESCAPED_UNICODE); ?>;
const mtbfData = <?php echo json_encode($mtbfChartData); ?>;
const mttrData = <?php echo json_encode($mttrChartData); ?>;
const paradasData = <?php echo json_encode($paradasChartData); ?>;
const custoLabels = <?php echo json_encode($custoLabels, JSON_UNESCAPED_UNICODE); ?>;
const custoData = <?php echo json_encode($custoData); ?>;
const evolLabels = <?php echo json_encode($evolLabels, JSON_UNESCAPED_UNICODE); ?>;
const evolMtbf = <?php echo json_encode($evolMtbf); ?>;
const evolMttr = <?php echo json_encode($evolMttr); ?>;
const evolParadas = <?php echo json_encode($evolParadas); ?>;
const evolucao = <?php echo json_encode($dados['evolucao'], JSON_UNESCAPED_UNICODE); ?>;

new Chart(document.getElementById('mtbfChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: mtbfLabels,
        datasets: [
            {
                label: 'MTBF (h)',
                data: mtbfData,
                backgroundColor: 'rgba(49, 130, 206, 0.7)',
                borderColor: '#3182ce',
                borderWidth: 1
            },
            {
                label: 'MTTR (h)',
                data: mttrData,
                type: 'line',
                backgroundColor: '#e53e3e',
                borderColor: '#e53e3e',
                borderWidth: 2,
                pointBackgroundColor: '#e53e3e'
            }
        ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

new Chart(document.getElementById('paradasChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: mtbfLabels,
        datasets: [{
            label: 'Paradas não planejadas',
            data: paradasData,
            backgroundColor: 'rgba(229, 62, 62, 0.7)',
            borderColor: '#e53e3e',
            borderWidth: 1
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});

new Chart(document.getElementById('custosChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: custoLabels,
        datasets: [{
            label: 'Custo Total (R$)',
            data: custoData,
            backgroundColor: 'rgba(16, 185, 129, 0.7)',
            borderColor: '#10b981',
            borderWidth: 1
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

new Chart(document.getElementById('evolucaoChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: evolLabels,
        datasets: [
            { label: 'MTBF (h)', data: evolMtbf, borderColor: '#3182ce', backgroundColor: '#3182ce', yAxisID: 'y', tension: 0.2 },
            { label: 'MTTR (h)', data: evolMttr, borderColor: '#e53e3e', backgroundColor: '#e53e3e', yAxisID: 'y', tension: 0.2 },
            { label: 'Paradas', data: evolParadas, borderColor: '#d69e2e', backgroundColor: '#d69e2e', yAxisID: 'y1', tension: 0.2 }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Horas' } },
            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Paradas' } }
        }
    }
});

function baixarCsv(linhas, filename) {
    const csv = linhas.map(cols => cols.map(c => '"' + String(c).replace(/"/g, '""') + '"').join(';')).join('\n');
    const csvFile = new Blob(["\uFEFF" + csv], {type: "text/csv;charset=utf-8;"});
    const downloadLink = document.createElement("a");
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
}

function exportTableToCSV(filename) {
    const linhas = [];
    document.querySelectorAll("#tabelaRanking tr").forEach(tr => {
        linhas.push(Array.from(tr.querySelectorAll("td, th")).map(col => col.innerText));
    });
    baixarCsv(linhas, filename);
}

function exportEvolucaoCSV() {
    const linhas = [["Mes", "Paradas", "MTBF_h", "MTTR_h", "Disponibilidade_%", "Custo"]];
    evolucao.forEach(item => {
        linhas.push([
            item.rotulo,
            item.paradas,
            item.mtbf ?? "",
            item.mttr ?? "",
            item.disponibilidade ?? "",
            item.custo
        ]);
    });
    baixarCsv(linhas, "evolucao_mensal.csv");
}
</script>
</body>
</html>
