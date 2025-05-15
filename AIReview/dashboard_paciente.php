
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
    : 'imagens/homem.png';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Paciente</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background-color: #f9f9f9;
            color: #333;
        }

        .header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .header img {
            border-radius: 60%;
        }

        h2 {
            margin: 0;
        }

        a {
            text-decoration: none;
            color: #333333;
        }

        a:hover {
            text-decoration: underline;
        }

        .link-box {
            margin: 15px 0;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
        }

        .link-box i {
            color: #7b9ec1;
        }

        p {
            margin: 10px 0;
        }

        hr {
            border: none;
            border-top: 1px solid #ccc;
            margin: 20px 0;
        }

        em {
            color: #555;
        }
    </style>
</head>

<body>
<div class="header">
    <img src="<?php echo $fotografia; ?>" width="75" height="75" alt="Foto do paciente">
    <h2>Olá, <?php echo htmlspecialchars($nome); ?>!</h2>
</div>
<hr>

<div class="link-box">
    <i class="fa-solid fa-user"></i>
    <a href="minha_ficha.php"><strong>Ver a minha ficha</strong></a>
</div>

<div class="link-box">
    <i class="fa-solid fa-calendar-check"></i>
    <a href="#"><strong>Ver histórico de consulta</strong></a>
</div>

<div class="link-box">
    <i class="fa-solid fa-comment-medical"></i>
    <a href="#"><strong>Adicionar opinião</strong></a>
</div>

<div class="link-box">
    <i class="fa-solid fa-right-from-bracket"></i>
    <a href="logout.php"><strong>Terminar sessão</strong></a>
</div>

<hr>

<p><em>Conteúdo específico da dashboard de Paciente aqui...</em></p>
</body>
</html>
