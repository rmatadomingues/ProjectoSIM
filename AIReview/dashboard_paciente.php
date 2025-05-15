
<?php
session_start();
require_once('config.php');

if (!isset($_SESSION['id_utilizador']) || $_SESSION['perfil'] !== 'Paciente') {
    header('Location: index.php');
    exit;
}

$nome = $_SESSION['nome'];
$fotografia = isset($_SESSION['fotografia']) && file_exists($_SESSION['fotografia']) 
    ? $_SESSION['fotografia'] 
    : 'uploads/perfis/default.png';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Paciente</title>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="<?php echo $fotografia; ?>" width="75" height="75" style="border-radius: 50%;">
        <h2>Olá, <?php echo htmlspecialchars($nome); ?>!</h2>
    </div>

    <p><a href="minha_ficha.php">👤 Ver/editar a minha ficha</a></p>
    <p><a href="logout.php">🚪 Terminar sessão</a></p>

    <hr>
    <p><em>Conteúdo específico da dashboard de Paciente aqui...</em></p>
</body>
</html>
