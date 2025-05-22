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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Médico</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e235f;
            --secondary-color: #1e235f;
            --accent-color: #ff7e5f;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --text-color: #495057;
            --border-color: #dee2e6;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: var(--text-color);
            line-height: 1.6;
            padding: 0;
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }

        .sidebar {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            height: fit-content;
        }

        .main-content {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow);
        }

        .header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .header img {
            border-radius: 50%;
            width: 80px;
            height: 80px;
            object-fit: cover;
            border: 3px solid var(--secondary-color);
        }

        .user-info h2 {
            font-size: 1.5rem;
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .user-info p {
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        .nav-menu {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border-radius: var(--border-radius);
            color: var(--text-color);
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            color: var(--secondary-color);
        }

        .nav-link:hover {
            background-color: #f0f4f8;
            color: var(--primary-color);
            transform: translateX(5px);
        }

        .nav-link:hover i {
            color: var(--primary-color);
        }

        .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .nav-link.active i {
            color: white;
        }

        .logout-link {
            margin-top: 20px;
            color: var(--danger-color);
        }

        .logout-link:hover {
            color: #c82333;
        }

        h3 {
            font-size: 1.3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        h3 i {
            font-size: 1.1rem;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .action-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .action-card h4 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-card p {
            font-size: 0.9rem;
            color: var(--text-color);
            margin-bottom: 15px;
        }

        .action-card .btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 15px;
            background-color: var(--primary-color);
            color: white;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .action-card .btn:hover {
            background-color: #3a5a8f;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: var(--border-radius);
            background-color: #f0f4f8;
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .btn-icon:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 2rem;
            margin-bottom: 15px;
            color: var(--border-color);
        }

        .consultation-summary {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }

            .sidebar {
                margin-bottom: 30px;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }

            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="header">
                <img src="<?php echo $fotografia; ?>" alt="Foto do médico">
                <div class="user-info">
                    <h2>Dr(a). <?php echo htmlspecialchars($nome); ?></h2>
                    <p>Médico</p>
                </div>
            </div>

            <nav class="nav-menu">
                <a href="logout.php" class="nav-link logout-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Terminar Sessão</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <h3><i class="fas fa-tachometer-alt"></i> Dashboard</h3>

            <div class="quick-actions">
                <div class="action-card">
                    <h4><i class="fas fa-user"></i> Minha Ficha</h4>
                    <p>Visualize e atualize seus dados pessoais e profissionais</p>
                    <a href="minha_ficha_med.php" class="btn">
                        <i class="fas fa-arrow-right"></i> Acessar
                    </a>
                </div>

                <div class="action-card">
                    <h4><i class="fas fa-file-medical"></i> Nova Consulta</h4>
                    <p>Registe uma nova consulta para um paciente</p>
                    <a href="registar_consulta.php" class="btn">
                        <i class="fas fa-plus"></i> Registrar
                    </a>
                </div>
            </div>

            <h3><i class="fas fa-clipboard-list"></i> Histórico de Consultas</h3>

            <?php if (count($consultas) === 0): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>Não existem consultas registadas</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                    <tr>
                        <th>Data</th>
                        <th>Paciente</th>
                        <th>Resumo</th>
                        <th>Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($consultas as $c): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($c['data_hora'])); ?></td>
                            <td>
                                <a href="ficha_paciente.php?id=<?php echo $c['id_paciente']; ?>">
                                    <?php echo htmlspecialchars($c['paciente']); ?>
                                </a>
                            </td>
                            <td class="consultation-summary" title="<?php echo htmlspecialchars($c['resumo_consulta']); ?>">
                                <?php echo htmlspecialchars($c['resumo_consulta']); ?>
                            </td>
                            <td>
                                <a href="ver_imagens_consulta_med.php?id_consulta=<?php echo $c['id_consulta']; ?>" class="btn-icon">
                                    <i class="fas fa-images"></i> Imagens
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </main>
    </div>
</div>
</body>
</html>