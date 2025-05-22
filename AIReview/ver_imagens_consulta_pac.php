<?php
session_start();
require_once('config.php');
global $conn;

// Verificar se o utilizador é paciente
if (!isset($_SESSION['id_utilizador']) || $_SESSION['perfil'] !== 'Paciente') {
    header('Location: index.php');
    exit;
}

$id_paciente = $_SESSION['id_utilizador'];
$nome = $_SESSION['nome'];
$fotografia = isset($_SESSION['fotografia']) && file_exists($_SESSION['fotografia'])
    ? $_SESSION['fotografia']
    : 'imagens/homem.png';

// Verificar se o id_consulta foi enviado e é numérico
if (!isset($_GET['id_consulta']) || !is_numeric($_GET['id_consulta'])) {
    echo "Consulta inválida.";
    exit;
}

$id_consulta = intval($_GET['id_consulta']);

// Verificar se a consulta pertence ao paciente
$query = "SELECT c.*, u.nome AS nome_medico 
          FROM consulta c
          JOIN utilizador u ON c.id_medico = u.id_utilizador
          WHERE c.id_consulta = $id_consulta AND c.id_paciente = $id_paciente";
$result = mysqli_query($conn, $query);
$consulta = mysqli_fetch_assoc($result);

if (!$consulta) {
    echo "Consulta não encontrada ou sem permissão de acesso.";
    exit;
}

// Buscar imagens associadas à consulta
$query_imagens = "SELECT caminho_ficheiro FROM imagem WHERE id_consulta = $id_consulta";
$res_imagens = mysqli_query($conn, $query_imagens);
$imagens = mysqli_fetch_all($res_imagens, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imagens da Consulta</title>
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

        .consultation-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
        }

        .info-item i {
            color: var(--primary-color);
        }

        .gallery-container {
            margin-top: 20px;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .media-card {
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
        }

        .media-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .media-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .file-card {
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            text-align: center;
        }

        .file-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
        }

        .file-name {
            word-break: break-all;
            font-size: 0.9rem;
        }

        .file-link {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s;
        }

        .file-link:hover {
            color: var(--accent-color);
            text-decoration: underline;
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

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            background-color: var(--primary-color);
            color: white;
            border-radius: var(--border-radius);
            text-decoration: none;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background-color: #3a5a8f;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }

            .sidebar {
                margin-bottom: 30px;
            }

            .gallery {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .gallery {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
        <!-- Main Content -->
        <main class="main-content">
            <h3><i class="fas fa-images"></i> Imagens da Consulta</h3>

            <div class="consultation-info">
                <div class="info-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span><?php echo date('d/m/Y H:i', strtotime($consulta['data_hora'])); ?></span>
                </div>

                <div class="info-item">
                    <i class="fas fa-user-md"></i>
                    <span><?php echo htmlspecialchars($consulta['nome_medico']); ?></span>
                </div>

                <?php if (!empty($consulta['resumo_consulta'])): ?>
                    <div class="info-item">
                        <i class="fas fa-file-alt"></i>
                        <span><?php echo htmlspecialchars($consulta['resumo_consulta']); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="gallery-container">
                <?php if (count($imagens) === 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-image"></i>
                        <p>Não existem imagens ou documentos associados a esta consulta</p>
                    </div>
                <?php else: ?>
                    <div class="gallery">
                        <?php foreach ($imagens as $img): ?>
                            <?php
                            $caminho = $img['caminho_ficheiro'];
                            $extensao = strtolower(pathinfo($caminho, PATHINFO_EXTENSION));
                            if (in_array($extensao, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])):
                                ?>
                                <div class="media-card">
                                    <img src="<?php echo htmlspecialchars($caminho); ?>" class="media-image" alt="Imagem da consulta">
                                </div>
                            <?php else: ?>
                                <div class="media-card file-card">
                                    <i class="fas fa-file-alt file-icon"></i>
                                    <div class="file-name">
                                        <a href="<?php echo htmlspecialchars($caminho); ?>" class="file-link" target="_blank" download>
                                            <?php echo basename($caminho); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <a href="dashboard_paciente.php" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Voltar ao Dashboard
            </a>
        </main>
    </div>
</div>
</body>
</html>