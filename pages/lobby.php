<?php
require_once '../php/auth.php';
include('../php/conexao.php');

// Gráfico: Ordens de Serviço por Linha
$sql_linha = "SELECT OS_LINHA, COUNT(*) AS total_os FROM tab_ordem_servico GROUP BY OS_LINHA";
$result_linha = mysqli_query($conn, $sql_linha);
$linhas = [];
$total_os_linha = [];
while ($row = mysqli_fetch_assoc($result_linha)) {
    $linhas[] = $row['OS_LINHA'];
    $total_os_linha[] = $row['total_os'];
}

// Gráfico: Ordens de Serviço por Status
$sql_status = "SELECT OS_STATUS, COUNT(*) AS total_status FROM tab_ordem_servico GROUP BY OS_STATUS";
$result_status = mysqli_query($conn, $sql_status);
$status = [];
$total_status = [];
while ($row = mysqli_fetch_assoc($result_status)) {
    $status[] = $row['OS_STATUS'];
    $total_status[] = $row['total_status'];
}

// Gráfico: Ordens de Serviço por Classificação
$sql_classificacao = "SELECT OS_CLASSIFICACAO, COUNT(*) AS total_classificacao FROM tab_ordem_servico GROUP BY OS_CLASSIFICACAO";
$result_classificacao = mysqli_query($conn, $sql_classificacao);
$classificacoes = [];
$total_classificacao = [];
while ($row = mysqli_fetch_assoc($result_classificacao)) {
    $classificacoes[] = $row['OS_CLASSIFICACAO'];
    $total_classificacao[] = $row['total_classificacao'];
}

// Gráfico: Soma GUT média por Situação (ou outro indicador)
$sql_gut = "SELECT OS_SITUACAO, AVG(OS_SOMA_GUT) AS media_gut FROM tab_ordem_servico GROUP BY OS_SITUACAO";
$result_gut = mysqli_query($conn, $sql_gut);
$situacoes = [];
$media_gut = [];
while ($row = mysqli_fetch_assoc($result_gut)) {
    $situacoes[] = $row['OS_SITUACAO'];
    $media_gut[] = round($row['media_gut'],2);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lobby - Painel</title>
<link rel="stylesheet" href="../css/style.css">
<script src="../js/theme.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>
<?php include_once dirname(__DIR__) . '/php/header.php'; ?>


<div class="container">
    <h2>Bem-vindo, <?php echo htmlspecialchars($_SESSION['nome']); ?>!</h2>
    

<?php if ($_SESSION['segmento'] != 'PRODUÇÃO') { ?>
    
<?php } else { ?>
    
<?php } ?>

    <div class="grafico-container">
        <div class="grafico"><canvas id="graficoLinha"></canvas></div>
        <div class="grafico"><canvas id="graficoStatus"></canvas></div>
    </div>
    <div class="grafico-container">
        <div class="grafico"><canvas id="graficoClassificacao"></canvas></div>
        <div class="grafico"><canvas id="graficoGUT"></canvas></div>
    </div>
</div>

<script>
// Gráfico OS por Linha
new Chart(document.getElementById('graficoLinha'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($linhas); ?>,
        datasets: [{
            label: 'Ordens de Serviço por Linha',
            data: <?php echo json_encode($total_os_linha); ?>,
            backgroundColor: '#00aaff',
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});

// Gráfico OS por Status
new Chart(document.getElementById('graficoStatus'), {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($status); ?>,
        datasets: [{
            label: 'Ordens de Serviço por Status',
            data: <?php echo json_encode($total_status); ?>,
            backgroundColor: ['#ff5733','#33ff57','#3357ff','#f4e842','#ff33aa'],
        }]
    },
    options: { responsive: true }
});

// Gráfico OS por Classificação
new Chart(document.getElementById('graficoClassificacao'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($classificacoes); ?>,
        datasets: [{
            label: 'Ordens de Serviço por Classificação',
            data: <?php echo json_encode($total_classificacao); ?>,
            backgroundColor: '#ff5733',
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});

// Gráfico Média Soma GUT por Situação
new Chart(document.getElementById('graficoGUT'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($situacoes); ?>,
        datasets: [{
            label: 'Média GUT por Situação',
            data: <?php echo json_encode($media_gut); ?>,
            backgroundColor: ['#ff0000','#66ccff','#ffcc00','#00ffcc','#aa33ff'],
        }]
    },
    options: { responsive: true }
});
</script>
</body>
</html>

