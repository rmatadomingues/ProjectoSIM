
<?php
session_start();
require_once('config.php');

if (!isset($_SESSION['id_utilizador']) || $_SESSION['perfil'] !== 'Medico') {
    header('Location: index.php');
    exit;
}

$host = 'localhost';
$user = 'root';
$password = 'root';
$database = 'ai_review';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Erro na ligação à base de dados: " . mysqli_connect_error());
}

$id_medico = $_SESSION['id_utilizador'];
$nome = $_SESSION['nome'];
$fotografia = isset($_SESSION['fotografia']) && file_exists($_SESSION['fotografia']) 
    ? $_SESSION['fotografia'] 
    : 'imagens/medico.png';

// Buscar histórico de consultas
$query = "SELECT c.id_consulta, c.data_hora, u.id_utilizador AS id_paciente, u.nome AS paciente, c.resumo_consulta
          FROM Consulta c
          JOIN Utilizador u ON c.id_paciente = u.id_utilizador
          WHERE c.id_medico = $id_medico
          ORDER BY c.data_hora DESC";
$resultado = mysqli_query($conn, $query);
$consultas = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Médico</title>
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
            width: 75px;
            height: 75px;
        }

        h2 {
            margin: 0;
            font-size: 24px;
        }

        h3 {
            margin-top: 30px;
            font-size: 20px;
            color: #333;
        }

        hr {
            border: none;
            border-top: 1px solid #ccc;
            margin: 20px 0;
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

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            margin-top: 15px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #f1f1f1;
            font-weight: bold;
        }

        a.btn-icon {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #333333;
        }

        a.btn-icon i {
            color: #7b9ec1;
        }
    </style>
</head>
<body>

<div class="header">
    <img src="<?php echo $fotografia; ?>" alt="Foto de perfil">
    <h2>Olá, Dr(a). <?php echo htmlspecialchars($nome); ?>!</h2>
</div>
<hr>
<h3>Ações disponíveis:</h3>

<div class="link-box">
    <i class="fas fa-user"></i>
    <a href="minha_ficha_med.php"> <strong>Ver/editar a minha ficha</strong></a>
</div>

<div class="link-box">
    <i class="fas fa-file-medical"></i>
    <a href="registar_consulta.php"> <strong>Registar nova consulta/paciente</strong></a>
</div>

<div class="link-box">
        <i class="fa-solid fa-right-from-bracket"></i>
        <a href="logout.php"><strong> Terminar sessão</strong></a>
</div>

<div class="historico">
    <h3><i class="fas fa-clipboard-list"></i> Histórico de Consultas Realizadas</h3>
    <?php if (count($consultas) === 0): ?>
        <p>Não existem consultas registadas por si.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>Data</th>
                <th>Paciente</th>
                <th>Resumo</th>
                <th>Imagens</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($consultas as $c): ?>
                <tr>
                    <td><?php echo $c['data_hora']; ?></td>
                    <td>
                        <a href="ficha_paciente.php?id=<?php echo $c['id_paciente']; ?>">
                            <?php echo htmlspecialchars($c['paciente']); ?>
                        </a>
                    </td>
                    <td style="white-space: pre-wrap;"><?php echo htmlspecialchars($c['resumo_consulta']); ?></td>
                    <td>
                        <a class="btn-icon" href="ver_imagens_consulta_med.php?id_consulta=<?php echo $c['id_consulta']; ?>">
                            <i class="fas fa-magnifying-glass"></i> Ver imagens</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>

