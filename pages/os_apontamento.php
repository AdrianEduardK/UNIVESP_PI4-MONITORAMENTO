<?php
require_once '../php/auth.php';
exigirManutencao();
include('../php/conexao.php');

if (!isset($_GET['id_os'])) {
    echo "<script>alert('ID da OS não informado!'); window.location.href='os.php';</script>";
    exit;
}

$id_os = intval($_GET['id_os']);

// Inserir apontamento
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $manutentor = $_POST['APONT_MANUTENTOR'];
    $inicio = $_POST['APONT_INICIO'];
    $fim = $_POST['APONT_FIM'];
    
    // Remover APONT_TOTAL do INSERT - é calculado automaticamente pelo banco
    $stmt = $conn->prepare("INSERT INTO tab_apont_horas (ID_OS, APONT_MANUTENTOR, APONT_INICIO, APONT_FIM) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $id_os, $manutentor, $inicio, $fim);
    
    if ($stmt->execute()) {
        echo "<script>alert('Apontamento registrado com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// Buscar apontamentos existentes
$stmt_apont = $conn->prepare("SELECT * FROM tab_apont_horas WHERE ID_OS = ? ORDER BY APONT_INICIO DESC");
$stmt_apont->bind_param("i", $id_os);
$stmt_apont->execute();
$apontamentos = $stmt_apont->get_result();

// Buscar info da OS
$stmt_os_info = $conn->prepare("SELECT OS_ID, OS_TAG_MAQ, OS_LINHA FROM tab_ordem_servico WHERE OS_ID = ?");
$stmt_os_info->bind_param("i", $id_os);
$stmt_os_info->execute();
$os_info = $stmt_os_info->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Apontamento de Horas - OS #<?php echo $id_os; ?></title>
<link rel="stylesheet" href="../css/style.css">

<script src="../js/theme.js"></script>
</head>
<body>
<?php include_once dirname(__DIR__) . '/php/header.php'; ?>




<div class="container">
    <h2>â±ï¸ Apontamento de Horas</h2>
    
    <div class="os-info">
        <strong>OS #<?php echo $os_info['OS_ID']; ?></strong> | 
        Tag: <?php echo $os_info['OS_TAG_MAQ']; ?> | 
        Linha: <?php echo $os_info['OS_LINHA']; ?>
    </div>

    <form method="POST">
        <h3 style="color:#28a745; margin-bottom:15px;">Novo Apontamento</h3>
        
        <label>Manutentor:</label>
        <input type="text" name="APONT_MANUTENTOR" value="<?php echo $_SESSION['nome']; ?>" required>

        <label>Data/Hora Início:</label>
        <input type="datetime-local" name="APONT_INICIO" required>

        <label>Data/Hora Fim:</label>
        <input type="datetime-local" name="APONT_FIM" required>

        <button type="submit" class="btn-save">ðŸ’¾ Registrar Apontamento</button>
    </form>

    <h3 style="color:#00aaff; margin-top:30px;">Histórico de Apontamentos</h3>

    <?php if($apontamentos->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Manutentor</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Total (horas)</th>
            </tr>
            <?php 
            $total_geral = 0;
            while($apont = $apontamentos->fetch_assoc()): 
                $total_geral += $apont['APONT_TOTAL'];
            ?>
                <tr>
                    <td><?php echo $apont['ID_APONT_HORAS']; ?></td>
                    <td><?php echo htmlspecialchars($apont['APONT_MANUTENTOR']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($apont['APONT_INICIO'])); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($apont['APONT_FIM'])); ?></td>
                    <td><?php echo number_format($apont['APONT_TOTAL'], 2, ',', '.'); ?>h</td>
                </tr>
            <?php endwhile; ?>
            <tr class="total-row">
                <td colspan="4">TOTAL GERAL</td>
                <td><?php echo number_format($total_geral, 2, ',', '.'); ?>h</td>
            </tr>
        </table>
    <?php else: ?>
        <p style="color:#999; margin-top:20px;">Nenhum apontamento registrado ainda.</p>
    <?php endif; ?>

    

</div>

</body>
</html>
