
<?php
session_start();
require_once('config.php');
global $conn;

// Verificar se utilizador é médico
if (!isset($_SESSION['id_utilizador']) || $_SESSION['perfil'] !== 'Medico') {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de paciente inválido.";
    exit;
}

$id_paciente = intval($_GET['id']);

// Buscar dados principais do paciente
$query_user = "SELECT * FROM Utilizador WHERE id_utilizador = $id_paciente AND perfil = 'Paciente'";
$result_user = mysqli_query($conn, $query_user);
$paciente = mysqli_fetch_assoc($result_user);

if (!$paciente) {
    echo "Paciente não encontrado.";
    exit;
}

// Buscar dados da tabela Paciente
$query_p = "SELECT * FROM Paciente WHERE id_utilizador = $id_paciente";
$result_p = mysqli_query($conn, $query_p);
$dados_extra = mysqli_fetch_assoc($result_p);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Ficha do Paciente</title>
</head>
<body>
    <h2>Ficha do Paciente</h2>

    <p><strong>Nome:</strong> <?php echo $paciente['nome']; ?></p>
    <p><strong>Morada:</strong> <?php echo $paciente['morada']; ?></p>
    <p><strong>Contactos:</strong> <?php echo $paciente['contactos']; ?></p>
    <p><strong>Localidade:</strong> <?php echo $dados_extra['localidade']; ?></p>
    <p><strong>Distrito:</strong> <?php echo $dados_extra['distrito']; ?></p>
    <p><strong>Email:</strong> <?php echo $dados_extra['email']; ?></p>
    <p><strong>Data de nascimento:</strong> <?php echo $dados_extra['data_nascimento']; ?></p>
    <p><strong>Sexo:</strong> <?php echo $dados_extra['sexo']; ?></p>
    <p><strong>NIF:</strong> <?php echo $dados_extra['NIF']; ?></p>
    <p><strong>Alergias:</strong> <?php echo nl2br($dados_extra['alergias']); ?></p>

    <p><a href="dashboard_medico.php">⬅ Voltar à dashboard</a></p>
</body>
</html>
