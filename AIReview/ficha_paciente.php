
<?php
session_start();
require_once('config.php');
global $conn;

// Verificar se utilizador é paciente
if (!isset($_SESSION['id_utilizador']) || $_SESSION['perfil'] !== 'Paciente') {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha do Paciente: <?php echo $paciente['nome']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c6eaf;
            --secondary-color: #4a8bc9;
            --danger-color: #e74c3c;
            --light-bg: #f8fafc;
            --border-color: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background-color: #7b9ec1;
            color: white;
            padding: 20px;
            border-bottom: 4px solid #7b9ec1;
        }

        .header h2 {
            font-size: 24px;
            font-weight: 600;
        }

        .patient-info {
            padding: 25px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .info-group {
            margin-bottom: 15px;
        }

        .info-group strong {
            display: block;
            color: #7b9ec1;
            margin-bottom: 5px;
            font-size: 15px;
        }

        .info-group p {
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 3px solid #7b9ec1;
        }

        .allergies {
            grid-column: 1 / -1;
        }

        .allergies p {
            border-left-color: var(--danger-color);
            background-color: #fde8e8;
        }

        .link-box {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            text-align: center;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #7b9ec1;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background-color: #7b9ec1;
            text-decoration: none;
            transform: translateY(-2px);
        }

        .btn-back i {
            color: white;
            transition: transform 0.3s ease;
        }

        .btn-back:hover i {
            transform: translateX(-3px);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Ficha do Paciente: <?php echo $paciente['nome']; ?></h2>
    </div>

    <div class="patient-info">
        <div class="info-group">
            <strong>Morada:</strong>
            <p><?php echo $paciente['morada']; ?></p>
        </div>

        <div class="info-group">
            <strong>Contactos:</strong>
            <p><?php echo $paciente['contactos']; ?></p>
        </div>

        <div class="info-group">
            <strong>Localidade:</strong>
            <p><?php echo $dados_extra['localidade']; ?></p>
        </div>

        <div class="info-group">
            <strong>Distrito:</strong>
            <p><?php echo $dados_extra['distrito']; ?></p>
        </div>

        <div class="info-group">
            <strong>Email:</strong>
            <p><?php echo $dados_extra['email']; ?></p>
        </div>

        <div class="info-group">
            <strong>Data de nascimento:</strong>
            <p><?php echo $dados_extra['data_nascimento']; ?></p>
        </div>

        <div class="info-group">
            <strong>Sexo:</strong>
            <p><?php echo $dados_extra['sexo']; ?></p>
        </div>

        <div class="info-group">
            <strong>NIF:</strong>
            <p><?php echo $dados_extra['NIF']; ?></p>
        </div>

        <div class="info-group allergies">
            <strong> Alergias:</strong>
            <p>⚠️ <?php echo nl2br($dados_extra['alergias']); ?></p>
        </div>
    </div>

    <div class="link-box">
        <a href="dashboard_paciente.php" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i>
            Voltar ao Dashboard
        </a>
    </div>
</div>
</body>
</html>
