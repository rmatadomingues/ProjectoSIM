
<?php
session_start();
require_once('config.php');
global $conn;

if (!isset($_SESSION['id_utilizador']) || $_SESSION['perfil'] !== 'Gestor') {
    header('Location: index.php');
    exit;
}

$nome = $_SESSION['nome'];
$fotografia = isset($_SESSION['fotografia']) && file_exists($_SESSION['fotografia']) 
    ? $_SESSION['fotografia'] 
    : 'imagens/homem.png';

// Buscar médicos e estatísticas
$query_medicos = "SELECT id_utilizador, nome FROM Utilizador WHERE perfil = 'Medico'";
$resultado_medicos = mysqli_query($conn, $query_medicos);
$estatisticas = [];

while ($medico = mysqli_fetch_assoc($resultado_medicos)) {
    $id_medico = $medico['id_utilizador'];
    $nome_medico = $medico['nome'];

    $query = "
        SELECT avaliacao_gpt, COUNT(*) as total
        FROM Opiniao o
        JOIN Consulta c ON o.id_consulta = c.id_consulta
        WHERE c.id_medico = $id_medico
        GROUP BY avaliacao_gpt
    ";
    $resultado = mysqli_query($conn, $query);

    $stats = ['Positiva' => 0, 'Negativa' => 0, 'Neutra' => 0];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $stats[$row['avaliacao_gpt']] = $row['total'];
    }

    $estatisticas[] = [
        'nome' => $nome_medico,
        'positivas' => $stats['Positiva'],
        'negativas' => $stats['Negativa'],
        'neutras' => $stats['Neutra']
    ];
}

// Contagem de utilizadores por perfil
$query_perfis = "SELECT perfil, COUNT(*) as total FROM Utilizador GROUP BY perfil";
$resultado_perfis = mysqli_query($conn, $query_perfis);
$perfis = [];
while ($row = mysqli_fetch_assoc($resultado_perfis)) {
    $perfis[$row['perfil']] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Gestor</title>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="<?php echo $fotografia; ?>" width="75" height="75" style="border-radius: 50%;">
        <h2>Olá, <?php echo htmlspecialchars($nome); ?>!</h2>
    </div>

    <h3>📈 Estatísticas Gerais</h3>
    <ul>
        <li>Total de Médicos: <?php echo $perfis['Medico'] ?? 0; ?></li>
        <li>Total de Pacientes: <?php echo $perfis['Paciente'] ?? 0; ?></li>
        <li>Total de Gestores: <?php echo $perfis['Gestor'] ?? 0; ?></li>
    </ul>

    <h3>📊 Opiniões por Médico</h3>
    <?php foreach ($estatisticas as $e): ?>
        <div style="border:1px solid #aaa; padding:10px; margin-bottom:10px;">
            <strong><?php echo $e['nome']; ?></strong><br>
            Positivas: <?php echo $e['positivas']; ?> |
            Negativas: <?php echo $e['negativas']; ?> |
            Neutras: <?php echo $e['neutras']; ?>
        </div>
    <?php endforeach; ?>

    <h3>⚙️ Ações Rápidas</h3>
    <ul>
        <li><a href="gestao_utilizadores.php">👥 Gestão de utilizadores</a></li>
        <li><a href="minha_ficha.php">👤 Ver/editar a minha ficha</a></li>
        <li><a href="logout.php">🚪 Terminar sessão</a></li>
    </ul>
</body>
</html>
