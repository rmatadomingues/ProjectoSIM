
<?php
session_start();
require_once('config.php');
global $conn;

// Verificar se é médico
if (!isset($_SESSION['id_utilizador']) || $_SESSION['perfil'] !== 'Medico') {
    header('Location: index.php');
    exit;
}

$id_medico = $_SESSION['id_utilizador'];

if (!isset($_GET['id_consulta']) || !is_numeric($_GET['id_consulta'])) {
    echo "Consulta inválida.";
    exit;
}

$id_consulta = intval($_GET['id_consulta']);

// Verificar se esta consulta pertence ao médico
$query = "SELECT * FROM Consulta WHERE id_consulta = $id_consulta AND id_medico = $id_medico";
$result = mysqli_query($conn, $query);
$consulta = mysqli_fetch_assoc($result);

if (!$consulta) {
    echo "Consulta não encontrada ou sem permissão de acesso.";
    exit;
}

// Buscar imagens associadas
$query_imagens = "SELECT caminho_ficheiro FROM Imagem WHERE id_consulta = $id_consulta";
$res_imagens = mysqli_query($conn, $query_imagens);
$imagens = mysqli_fetch_all($res_imagens, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Imagens da Consulta</title>
</head>
<body>
    <h2>Imagens da Consulta de <?php echo $consulta['data_hora']; ?></h2>

    <?php if (count($imagens) === 0): ?>
        <p>Não existem imagens associadas a esta consulta.</p>
    <?php else: ?>
        <?php foreach ($imagens as $img): ?>
            <div style="margin-bottom: 20px;">
                <?php 
                    $caminho = $img['caminho_ficheiro'];
                    $extensao = strtolower(pathinfo($caminho, PATHINFO_EXTENSION));
                    if (in_array($extensao, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])):
                ?>
                    <img src="<?php echo $caminho; ?>" width="200"><br>
                <?php else: ?>
                    📄 <a href="<?php echo $caminho; ?>" target="_blank"><?php echo basename($caminho); ?></a><br>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p><a href="dashboard_medico.php">⬅ Voltar à dashboard</a></p>
</body>
</html>
