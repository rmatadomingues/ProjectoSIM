<?php
session_start();
require_once('config.php');
global $conn;

// Verifica se o utilizador é um paciente
if (!isset($_SESSION['id_utilizador']) || $_SESSION['perfil'] !== 'Paciente') {
    header('Location: index.php');
    exit;
}

$id_paciente = $_SESSION['id_utilizador'];
$id = $_SESSION['id_utilizador'];
$mensagem = '';
$caminho_fotos = "imagens/";

// Criar diretório de imagens se não existir
if (!is_dir($caminho_fotos)) {
    mkdir($caminho_fotos, 0777, true);
}

// Buscar dados do utilizador
$query_user = "SELECT * FROM Utilizador WHERE id_utilizador = $id_paciente AND perfil = 'Paciente'";
$result_user = mysqli_query($conn, $query_user);
$paciente = mysqli_fetch_assoc($result_user);

if (!$paciente) {
    echo "Paciente não encontrado.";
    exit;
}

// Buscar dados complementares da tabela Paciente
$query_p = "SELECT * FROM Paciente WHERE id_utilizador = $id_paciente";
$result_p = mysqli_query($conn, $query_p);
$dados_extra = mysqli_fetch_assoc($result_p);

// Definir imagem de perfil (real ou genérica)
$foto_perfil = isset($paciente['fotografia']) && file_exists($paciente['fotografia'])
    ? $paciente['fotografia']
    : 'imagens/homem.png';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Ficha Médica</title>
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
            --danger-color: #e74c3c;
            --success-color: #28a745;
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
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 25px;
            text-align: center;
        }

        .header h2 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .profile-section {
            display: flex;
            align-items: center;
            padding: 25px;
            border-bottom: 1px solid var(--border-color);
            background-color: #f8fafc;
        }

        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: var(--shadow);
            margin-right: 30px;
        }

        .profile-info h3 {
            font-size: 1.4rem;
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .profile-info p {
            color: var(--text-color);
            font-size: 0.95rem;
        }

        .patient-data {
            padding: 25px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .data-card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--secondary-color);
        }

        .data-card h4 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .data-card h4 i {
            color: var(--secondary-color);
        }

        .data-item {
            margin-bottom: 12px;
        }

        .data-item strong {
            display: block;
            color: var(--primary-color);
            font-size: 0.85rem;
            margin-bottom: 3px;
        }

        .data-item p {
            padding: 8px 12px;
            background-color: var(--light-color);
            border-radius: 4px;
        }

        .allergies-card {
            grid-column: 1 / -1;
            border-left-color: var(--danger-color);
        }

        .allergies-card h4 {
            color: var(--danger-color);
        }

        .allergies-card .data-item p {
            background-color: #fde8e8;
            border-left: 3px solid var(--danger-color);
        }

        .actions {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            border-top: 1px solid var(--border-color);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #3a5a8f;
            transform: translateY(-2px);
        }

        .btn-outline {
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background-color: #f0f4f8;
        }

        .empty-field {
            color: #6c757d;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .profile-section {
                flex-direction: column;
                text-align: center;
            }

            .profile-picture {
                margin-right: 0;
                margin-bottom: 20px;
            }

            .patient-data {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Cabeçalho -->
    <div class="header">
        <h2><i class="fas fa-file-medical"></i> Minha Ficha Médica</h2>
    </div>

    <!-- Seção do perfil -->
    <div class="profile-section">
        <img src="<?php echo $foto_perfil; ?>" alt="Foto de perfil" class="profile-picture">
        <div class="profile-info">
            <h3><?php echo htmlspecialchars($paciente['nome']); ?></h3>
            <p>Paciente desde <?php echo date('d/m/Y', strtotime($paciente['data_criacao'])); ?></p>
        </div>
    </div>

    <!-- Dados do paciente -->
    <div class="patient-data">
        <!-- Informações Pessoais -->
        <div class="data-card">
            <h4><i class="fas fa-user"></i> Informações Pessoais</h4>

            <div class="data-item">
                <strong>Data de Nascimento</strong>
                <p><?php echo !empty($dados_extra['data_nascimento']) ? date('d/m/Y', strtotime($dados_extra['data_nascimento'])) : '<span class="empty-field">Não informado</span>'; ?></p>
            </div>

            <div class="data-item">
                <strong>Sexo</strong>
                <p><?php echo !empty($dados_extra['sexo']) ? htmlspecialchars($dados_extra['sexo']) : '<span class="empty-field">Não informado</span>'; ?></p>
            </div>

            <div class="data-item">
                <strong>NIF</strong>
                <p><?php echo !empty($dados_extra['NIF']) ? htmlspecialchars($dados_extra['NIF']) : '<span class="empty-field">Não informado</span>'; ?></p>
            </div>
        </div>

        <!-- Contactos -->
        <div class="data-card">
            <h4><i class="fas fa-address-book"></i> Contactos</h4>

            <div class="data-item">
                <strong>Morada</strong>
                <p><?php echo !empty($paciente['morada']) ? htmlspecialchars($paciente['morada']) : '<span class="empty-field">Não informado</span>'; ?></p>
            </div>

            <div class="data-item">
                <strong>Telefone</strong>
                <p><?php echo !empty($paciente['contactos']) ? htmlspecialchars($paciente['contactos']) : '<span class="empty-field">Não informado</span>'; ?></p>
            </div>

            <div class="data-item">
                <strong>Email</strong>
                <p><?php echo !empty($dados_extra['email']) ? htmlspecialchars($dados_extra['email']) : '<span class="empty-field">Não informado</span>'; ?></p>
            </div>
        </div>

        <!-- Localização -->
        <div class="data-card">
            <h4><i class="fas fa-map-marker-alt"></i> Localização</h4>

            <div class="data-item">
                <strong>Localidade</strong>
                <p><?php echo !empty($dados_extra['localidade']) ? htmlspecialchars($dados_extra['localidade']) : '<span class="empty-field">Não informado</span>'; ?></p>
            </div>

            <div class="data-item">
                <strong>Distrito</strong>
                <p><?php echo !empty($dados_extra['distrito']) ? htmlspecialchars($dados_extra['distrito']) : '<span class="empty-field">Não informado</span>'; ?></p>
            </div>
        </div>

        <!-- Alergias -->
        <div class="data-card allergies-card">
            <h4><i class="fas fa-allergies"></i> Alergias</h4>

            <div class="data-item">
                <p><?php echo !empty($dados_extra['alergias']) ? nl2br(htmlspecialchars($dados_extra['alergias'])) : '<span class="empty-field">Nenhuma alergia registada</span>'; ?></p>
            </div>
        </div>
    </div>

    <!-- Ações -->
    <div class="actions">
        <a href="editar_ficha_pac.php" class="btn btn-outline">
            <i class="fas fa-edit"></i> Editar Ficha
        </a>
        <a href="dashboard_paciente.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
        </a>
    </div>
</div>
</body>
</html>
