<?php
require_once '../php/auth.php';
exigirManutencao();
include('../php/conexao.php');

$os = $conn->query("SELECT * FROM tab_ordem_servico ORDER BY OS_DATA_SOLICITACAO DESC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Ordem de Serviço</title>
    <link rel="stylesheet" href="../css/style.css">
    
<script src="../js/theme.js"></script>
</head>
<body>
<?php include_once dirname(__DIR__) . '/php/header.php'; ?>




<div class="container">
    <h2>Ordens de Serviço</h2>

    <div class="top-buttons" style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px;">
        <a href="os_criar.php" class="btn btn-success">+ Nova Ordem de Serviço</a>
        <a href="sensor_log.php" class="btn" style="white-space: nowrap;">📡 Log de Sensores</a>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Solicitante</th>
                    <th>Status</th>
                    <th>Tag Máquina</th>
                    <th>Linha</th>
                    <th>Data Solicitação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $os->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['OS_ID']; ?></td>
                        <td><?php echo htmlspecialchars($row['OS_SOLICITANTE']); ?></td>
                        <td><?php echo htmlspecialchars($row['OS_STATUS']); ?></td>
                        <td><?php echo htmlspecialchars($row['OS_TAG_MAQ']); ?></td>
                        <td><?php echo htmlspecialchars($row['OS_LINHA']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['OS_DATA_SOLICITACAO'])); ?></td>
                        <td>
                            <a href="os_editar.php?id=<?php echo $row['OS_ID']; ?>" class="btn">Editar / Ver</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
