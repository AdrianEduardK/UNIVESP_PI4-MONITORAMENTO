<?php
require_once __DIR__ . '/auth.php';
exigirManutencao();
include __DIR__ . '/conexao.php';

// Inserção do novo DDE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $tema = $_POST['tema'];
    $desenvolvimento = $_POST['desenvolvimento'];
    $data = $_POST['data'];
    $responsavel = $_SESSION['nome']; // pegando da sessão

    $sql = "INSERT INTO TAB_DDE (DDE_TITULO, DDE_TEMA, DDE_DESENVOLVIMENTO, DDE_DATA, DDE_RESPONSAVEL)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $titulo, $tema, $desenvolvimento, $data, $responsavel);

    if ($stmt->execute()) {
        require_once 'flash_messages.php';
        setFlashMessage("DDE cadastrado com sucesso!", "success");
        header("Location: ../pages/lobby.php");
        exit;
    } else {
        require_once 'flash_messages.php';
        setFlashMessage("Erro ao cadastrar DDE.", "error");
    }

    $stmt->close();
}

// Filtragem de DDEs por data
$filterData = '';
if (isset($_GET['data_filter']) && $_GET['data_filter'] != '') {
    $filterData = $_GET['data_filter'];
    $filterData = mysqli_real_escape_string($conn, $filterData);
    $query = "SELECT * FROM TAB_DDE WHERE DDE_DATA = '$filterData' ORDER BY DDE_DATA DESC";
} else {
    $query = "SELECT * FROM TAB_DDE ORDER BY DDE_DATA DESC";
}

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar DDE</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/theme.js"></script>
</head>
<body>
<?php include_once dirname(__DIR__) . '/php/header.php'; ?>

<div class="container">
    <h2>Criar novo DDE</h2>
    
    <div class="form-section">
        <form method="post">
            <label for="titulo">Título:</label>
            <input type="text" id="titulo" name="titulo" placeholder="Digite o título..." required>

            <label for="tema">Tema:</label>
            <input type="text" id="tema" name="tema" placeholder="Digite o tema..." required>

            <label for="desenvolvimento">Desenvolvimento:</label>
            <textarea id="desenvolvimento" name="desenvolvimento" rows="6" placeholder="Descreva o conteúdo do DDE..." required></textarea>

            <label for="data">Data desejada de exibição:</label>
            <input type="date" id="data" name="data" required>

            <div style="margin-top: 15px;">
                <button type="submit" class="btn btn-success">Cadastrar DDE</button>
            </div>
        </form>
    </div>

    <hr>

    <h3 style="margin-top: 40px;">DDEs Cadastrados</h3>
    
    <!-- Filtro -->
    <form method="GET" class="filtros" style="margin-bottom: 20px;">
        <div style="display: flex; align-items: flex-end; gap: 15px;">
            <div>
                <label for="data_filter">Filtrar por Data:</label>
                <input type="date" id="data_filter" name="data_filter" value="<?php echo htmlspecialchars($filterData); ?>">
            </div>
            <div>
                <button type="submit" class="btn">Filtrar</button>
                <?php if ($filterData): ?>
                    <a href="cadastro_dde.php" class="btn btn-outline" style="margin-left: 10px;">Limpar Filtro</a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Tema</th>
                    <th style="width: 40%;">Desenvolvimento</th>
                    <th>Data</th>
                    <th>Responsável</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td><strong>" . htmlspecialchars($row['DDE_TITULO']) . "</strong></td>
                                <td>" . htmlspecialchars($row['DDE_TEMA']) . "</td>
                                <td>" . nl2br(htmlspecialchars($row['DDE_DESENVOLVIMENTO'])) . "</td>
                                <td>" . date('d/m/Y', strtotime($row['DDE_DATA'])) . "</td>
                                <td>" . htmlspecialchars($row['DDE_RESPONSAVEL']) . "</td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align: center; padding: 20px;'>Nenhum DDE encontrado</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
