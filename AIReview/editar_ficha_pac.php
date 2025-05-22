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
$mensagem = '';
$caminho_fotos = "imagens/";

// Criar diretório de imagens se não existir
if (!is_dir($caminho_fotos)) {
    mkdir($caminho_fotos, 0777, true);
}

// Buscar dados do utilizador
$query_user = "SELECT * FROM Utilizador WHERE id_utilizador = $id_paciente";
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

// Processar o formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Dados básicos
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $morada = mysqli_real_escape_string($conn, $_POST['morada']);
    $contactos = mysqli_real_escape_string($conn, $_POST['contactos']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $nova_password = !empty($_POST['nova_password']) ? password_hash($_POST['nova_password'], PASSWORD_DEFAULT) : null;

    // Dados médicos
    $data_nascimento = mysqli_real_escape_string($conn, $_POST['data_nascimento']);
    $sexo = mysqli_real_escape_string($conn, $_POST['sexo']);
    $NIF = mysqli_real_escape_string($conn, $_POST['NIF']);
    $localidade = mysqli_real_escape_string($conn, $_POST['localidade']);
    $distrito = mysqli_real_escape_string($conn, $_POST['distrito']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $alergias = mysqli_real_escape_string($conn, $_POST['alergias']);

    // Upload de nova fotografia
    $foto = $paciente['fotografia']; // Mantém a foto atual por padrão
    if (!empty($_FILES['foto']['name'])) {
        $nome_foto = basename($_FILES['foto']['name']);
        $destino = $caminho_fotos . time() . "_" . $nome_foto;

        // Verificar se é uma imagem válida
        $check = getimagesize($_FILES['foto']['tmp_name']);
        if ($check !== false) {
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                $foto = $destino;
                // Remover a foto antiga se não for a padrão
                if ($paciente['fotografia'] !== 'imagens/homem.png' && file_exists($paciente['fotografia'])) {
                    unlink($paciente['fotografia']);
                }
            }
        }
    }

    // Iniciar transação para garantir que todas as atualizações são feitas
    mysqli_begin_transaction($conn);

    try {
        // Atualizar tabela Utilizador
        $sql_user = "UPDATE Utilizador SET 
                    nome = '$nome',
                    morada = '$morada',
                    contactos = '$contactos',
                    username = '$username',
                    fotografia = '$foto'";

        if ($nova_password) {
            $sql_user .= ", password = '$nova_password'";
        }

        $sql_user .= " WHERE id_utilizador = $id_paciente";

        if (!mysqli_query($conn, $sql_user)) {
            throw new Exception("Erro ao atualizar dados do utilizador: " . mysqli_error($conn));
        }

        // Atualizar tabela Paciente
        $sql_paciente = "UPDATE Paciente SET
                        data_nascimento = " . (!empty($data_nascimento) ? "'$data_nascimento'" : "NULL") . ",
                        sexo = " . (!empty($sexo) ? "'$sexo'" : "NULL") . ",
                        NIF = " . (!empty($NIF) ? "'$NIF'" : "NULL") . ",
                        localidade = " . (!empty($localidade) ? "'$localidade'" : "NULL") . ",
                        distrito = " . (!empty($distrito) ? "'$distrito'" : "NULL") . ",
                        email = " . (!empty($email) ? "'$email'" : "NULL") . ",
                        alergias = " . (!empty($alergias) ? "'$alergias'" : "NULL") . "
                        WHERE id_utilizador = $id_paciente";

        if (!mysqli_query($conn, $sql_paciente)) {
            throw new Exception("Erro ao atualizar dados médicos: " . mysqli_error($conn));
        }

        // Commit da transação se tudo correr bem
        mysqli_commit($conn);

        // Atualizar dados na sessão
        $_SESSION['nome'] = $nome;
        $_SESSION['fotografia'] = $foto;

        $mensagem = "Dados atualizados com sucesso!";
        header("Location: minha_ficha_pac.php?sucesso=1");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $mensagem = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Ficha Médica</title>
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

        .profile-picture-container {
            position: relative;
            width: 120px;
            height: 120px;
            margin-right: 30px;
        }

        .profile-picture {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: var(--shadow);
        }

        .profile-picture-edit {
            position: absolute;
            bottom: 0;
            right: 0;
            background-color: var(--secondary-color);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .profile-info h3 {
            font-size: 1.4rem;
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .edit-form {
            padding: 25px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary-color);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(108, 143, 199, 0.2);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .file-input {
            display: none;
        }

        .photo-upload {
            grid-column: 1 / -1;
            text-align: center;
        }

        .photo-upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background-color: var(--light-color);
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .photo-upload-btn:hover {
            background-color: #e9ecef;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: var(--success-color);
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: var(--danger-color);
            border: 1px solid #f5c6cb;
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
            cursor: pointer;
            border: none;
            font-size: 1rem;
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
            background-color: white;
        }

        .btn-outline:hover {
            background-color: #f0f4f8;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        @media (max-width: 768px) {
            .profile-section {
                flex-direction: column;
                text-align: center;
            }

            .profile-picture-container {
                margin-right: 0;
                margin-bottom: 20px;
            }

            .edit-form {
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
        <h2><i class="fas fa-edit"></i> Editar Ficha Médica</h2>
    </div>

    <!-- Mensagens de feedback -->
    <?php if (!empty($mensagem)): ?>
        <div class="message <?php echo strpos($mensagem, 'sucesso') !== false ? 'success' : 'error'; ?>">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <!-- Seção do perfil -->
    <div class="profile-section">
        <div class="profile-picture-container">
            <img src="<?php echo $foto_perfil; ?>" alt="Foto de perfil" class="profile-picture" id="previewFoto">
            <label for="foto" class="profile-picture-edit">
                <i class="fas fa-camera"></i>
            </label>
        </div>
        <div class="profile-info">
            <h3>Editar Informações Pessoais</h3>
            <p>Atualize seus dados abaixo</p>
        </div>
    </div>

    <!-- Formulário de edição -->
    <form action="editar_ficha_pac.php" method="POST" enctype="multipart/form-data" class="edit-form">
        <!-- Upload de foto -->
        <div class="photo-upload full-width">
            <label for="foto" class="photo-upload-btn">
                <i class="fas fa-cloud-upload-alt"></i>
                Alterar Fotografia
            </label>
            <input type="file" id="foto" name="foto" accept="image/*" class="file-input">
        </div>

        <!-- Dados básicos -->
        <div class="form-group">
            <label for="nome">Nome Completo</label>
            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($paciente['nome']); ?>" required>
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($paciente['username']); ?>" required>
        </div>

        <div class="form-group">
            <label for="nova_password">Nova Password (deixe em branco para manter a atual)</label>
            <input type="password" id="nova_password" name="nova_password">
        </div>

        <div class="form-group">
            <label for="morada">Morada</label>
            <input type="text" id="morada" name="morada" value="<?php echo htmlspecialchars($paciente['morada']); ?>">
        </div>

        <div class="form-group">
            <label for="contactos">Contactos</label>
            <input type="text" id="contactos" name="contactos" value="<?php echo htmlspecialchars($paciente['contactos']); ?>">
        </div>

        <!-- Dados médicos -->
        <div class="form-group">
            <label for="data_nascimento">Data de Nascimento</label>
            <input type="date" id="data_nascimento" name="data_nascimento"
                   value="<?php echo !empty($dados_extra['data_nascimento']) ? htmlspecialchars($dados_extra['data_nascimento']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="sexo">Sexo</label>
            <select id="sexo" name="sexo">
                <option value="">Selecione...</option>
                <option value="Masculino" <?php echo (isset($dados_extra['sexo']) && $dados_extra['sexo'] === 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                <option value="Feminino" <?php echo (isset($dados_extra['sexo']) && $dados_extra['sexo'] === 'Feminino') ? 'selected' : ''; ?>>Feminino</option>
                <option value="Outro" <?php echo (isset($dados_extra['sexo']) && $dados_extra['sexo'] === 'Outro') ? 'selected' : ''; ?>>Outro</option>
            </select>
        </div>

        <div class="form-group">
            <label for="NIF">NIF</label>
            <input type="text" id="NIF" name="NIF" value="<?php echo !empty($dados_extra['NIF']) ? htmlspecialchars($dados_extra['NIF']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="localidade">Localidade</label>
            <input type="text" id="localidade" name="localidade" value="<?php echo !empty($dados_extra['localidade']) ? htmlspecialchars($dados_extra['localidade']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="distrito">Distrito</label>
            <input type="text" id="distrito" name="distrito" value="<?php echo !empty($dados_extra['distrito']) ? htmlspecialchars($dados_extra['distrito']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo !empty($dados_extra['email']) ? htmlspecialchars($dados_extra['email']) : ''; ?>">
        </div>

        <div class="form-group full-width">
            <label for="alergias">Alergias</label>
            <textarea id="alergias" name="alergias"><?php echo !empty($dados_extra['alergias']) ? htmlspecialchars($dados_extra['alergias']) : ''; ?></textarea>
        </div>

        <!-- Ações -->
        <div class="actions full-width">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Alterações
            </button>
            <a href="minha_ficha_pac.php" class="btn btn-outline">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<script>
    // Preview da foto selecionada
    document.getElementById('foto').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewFoto').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>