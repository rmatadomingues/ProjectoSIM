
<?php
session_start();
require_once('config.php');
global $conn;

// Verificar se é paciente
if (!isset($_SESSION['id_utilizador']) || $_SESSION['perfil'] !== 'Paciente') {
    header('Location: index.php');
    exit;
}

$id_paciente = $_SESSION['id_utilizador'];
$mensagem = '';

// Submeter opinião (simulação de análise GPT)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_consulta'])) {
    $id_consulta = $_POST['id_consulta'];
    $texto = $_POST['texto_opiniao'];

    // Simulação de análise GPT
    $avaliacao = 'Neutra';
    $palavras = 'exemplo, teste';

    if (stripos($texto, 'bom') !== false || stripos($texto, 'excelente') !== false) {
        $avaliacao = 'Positiva';
    } elseif (stripos($texto, 'mau') !== false || stripos($texto, 'horrível') !== false) {
        $avaliacao = 'Negativa';
    }

    // Guardar na BD
    $query = "INSERT INTO Opiniao (id_consulta, id_paciente, texto_opiniao, avaliacao_gpt, palavras_chave_gpt, avaliacao_final_paciente)
              VALUES ($id_consulta, $id_paciente, '$texto', '$avaliacao', '$palavras', '$avaliacao')";

    if (mysqli_query($conn, $query)) {
        $mensagem = "Opinião submetida com sucesso!";
    } else {
        $mensagem = "Erro ao guardar opinião: " . mysqli_error($conn);
    }
}

// Buscar consultas do paciente
$query = "SELECT c.id_consulta, c.data_hora, c.resumo_consulta, u.nome AS medico
          FROM Consulta c
          JOIN Utilizador u ON c.id_medico = u.id_utilizador
          WHERE c.id_paciente = $id_paciente
          ORDER BY c.data_hora DESC";

$resultado = mysqli_query($conn, $query);
$consultas = [];

while ($row = mysqli_fetch_assoc($resultado)) {
    $id_cons = $row['id_consulta'];

    // Verificar se já existe opinião
    $opiniao_query = "SELECT * FROM Opiniao WHERE id_consulta = $id_cons AND id_paciente = $id_paciente";
    $opiniao_result = mysqli_query($conn, $opiniao_query);
    $opiniao = mysqli_fetch_assoc($opiniao_result);

    $row['opiniao'] = $opiniao;
    $consultas[] = $row;
}
?>

<head>
    <title>Minhas Consultas</title>
</head>
<body>
    <h2>Histórico de Consultas</h2>

    <?php if ($mensagem): ?>
        <p><strong><?php echo $mensagem; ?></strong></p>
    <?php endif; ?>

    <?php foreach ($consultas as $consulta): ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
            <strong>Data:</strong> <?php echo $consulta['data_hora']; ?><br>
            <strong>Médico:</strong> <?php echo $consulta['medico']; ?><br>
            <strong>Resumo:</strong> <?php echo $consulta['resumo_consulta']; ?><br><br>

            <?php if ($consulta['opiniao']): ?>
                <strong>Sua opinião:</strong><br>
                <?php echo nl2br($consulta['opiniao']['texto_opiniao']); ?><br>
                <strong>Análise GPT:</strong> <?php echo $consulta['opiniao']['avaliacao_gpt']; ?><br>
                <strong>Palavras-chave:</strong> <?php echo $consulta['opiniao']['palavras_chave_gpt']; ?><br>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="id_consulta" value="<?php echo $consulta['id_consulta']; ?>">
                    <label>Deixe a sua opinião:</label><br>
                    <textarea name="texto_opiniao" rows="4" cols="60" required></textarea><br><br>
                    <input type="submit" value="Submeter opinião">
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</body>
</html>
