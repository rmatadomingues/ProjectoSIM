
<?php
session_start();
require_once('config.php');
global $conn;

if (!isset($_SESSION['id_utilizador']) || $_SESSION['perfil'] !== 'Medico') {
    header('Location: index.php');
    exit;
}

$id_medico = $_SESSION['id_utilizador'];
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usar_existente = $_POST['tipo_paciente'] === 'existente';
    $id_paciente = null;

    if ($usar_existente) {
        $id_paciente = intval($_POST['id_paciente']);
    } else {
        // Criar novo paciente
        $nome = $_POST['nome'];
        $morada = $_POST['morada'];
        $contactos = $_POST['contactos'];
        $username = $_POST['username'];
        $password = $_POST['password'];

        $localidade = $_POST['localidade'];
        $distrito = $_POST['distrito'];
        $email = $_POST['email'];
        $data_nascimento = $_POST['data_nascimento'];
        $sexo = $_POST['sexo'];
        $nif = $_POST['nif'];
        $alergias = $_POST['alergias'];

        $q1 = "INSERT INTO Utilizador (nome, morada, contactos, username, password, perfil) 
               VALUES ('$nome', '$morada', '$contactos', '$username', '$password', 'Paciente')";
        if (mysqli_query($conn, $q1)) {
            $id_paciente = mysqli_insert_id($conn);
            $q2 = "INSERT INTO Paciente (id_utilizador, localidade, distrito, email, data_nascimento, sexo, NIF, alergias)
                   VALUES ($id_paciente, '$localidade', '$distrito', '$email', '$data_nascimento', '$sexo', '$nif', '$alergias')";
            mysqli_query($conn, $q2);
        }
    }

    if ($id_paciente) {
        $temperatura = $_POST['temperatura'];
        $pressao = $_POST['pressao'];
        $resumo = $_POST['resumo'];

        $query = "INSERT INTO Consulta (id_medico, id_paciente, temperatura, pressao_arterial, resumo_consulta)
                  VALUES ($id_medico, $id_paciente, '$temperatura', '$pressao', '$resumo')";

        if (mysqli_query($conn, $query)) {
            $id_consulta = mysqli_insert_id($conn);

            // Upload de imagens
            if (!empty($_FILES['imagens']['name'][0])) {
                $diretorio = "uploads/";
                if (!is_dir($diretorio)) {
                    mkdir($diretorio, 0777, true);
                }

                foreach ($_FILES['imagens']['tmp_name'] as $i => $tmpName) {
                    $nomeOriginal = basename($_FILES['imagens']['name'][$i]);
                    $caminhoFinal = $diretorio . time() . "_" . $nomeOriginal;

                    if (move_uploaded_file($tmpName, $caminhoFinal)) {
                        $insertImagem = "INSERT INTO Imagem (id_consulta, caminho_ficheiro)
                                         VALUES ($id_consulta, '$caminhoFinal')";
                        mysqli_query($conn, $insertImagem);
                    }
                }
            }

            $mensagem = "<div class='success-message'>Consulta registada com sucesso!</div>";
        } else {
            $mensagem = "<div class='error-message'>Erro ao registar consulta: " . mysqli_error($conn) . "</div>";
        }
    }
}

// Buscar pacientes existentes
$pacientes = mysqli_query($conn, "SELECT id_utilizador, nome FROM Utilizador WHERE perfil = 'Paciente'");
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registar Consulta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #7b9ec1;
            --secondary-color: #7b9ec1;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
            --light-gray: #f5f7fa;
            --dark-gray: #333;
            --medium-gray: #95a5a6;
            --white: #fff;
            --border-radius: 6px;
            --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
            color: var(--dark-gray);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
        }

        h2 {
            color: var(--primary-color);
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-gray);
        }

        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: border 0.3s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .radio-group {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .radio-option input {
            margin: 0;
        }

        .file-upload {
            margin: 20px 0;
            padding: 20px;
            border: 2px dashed var(--medium-gray);
            border-radius: var(--border-radius);
            text-align: center;
            background-color: #f9f9f9;
            transition: all 0.3s;
        }

        .file-upload:hover {
            border-color: var(--primary-color);
            background-color: #f0f8ff;
        }

        .btn {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 12px 25px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        .btn:hover {
            background-color: var(--secondary-color);
        }

        .btn-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .btn-link:hover {
            text-decoration: underline;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .grid-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .section-title {
            margin: 20px 0 15px;
            color: var(--primary-color);
            font-size: 18px;
        }

        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Registar Nova Consulta</h2>
    <?php if ($mensagem): ?>
        <?php echo $mensagem; ?>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="radio-group">
            <label class="radio-option">
                <input type="radio" name="tipo_paciente" value="existente" checked onclick="toggleCamposNovoPaciente(false)">
                Paciente existente
            </label>
            <label class="radio-option">
                <input type="radio" name="tipo_paciente" value="novo" onclick="toggleCamposNovoPaciente(true)">
                Novo paciente
            </label>
        </div>

        <div id="paciente_existente">
            <div class="form-group">
                <label for="id_paciente">Selecionar paciente:</label>
                <select name="id_paciente" id="id_paciente" required>
                    <option value="">-- Escolher --</option>
                    <?php while ($p = mysqli_fetch_assoc($pacientes)): ?>
                        <option value="<?php echo $p['id_utilizador']; ?>"><?php echo $p['nome']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div id="novo_paciente_fields" style="display:none;">
            <h4 class="section-title">Dados do novo paciente</h4>

            <div class="grid-container">
                <div class="form-group">
                    <label>Nome:</label>
                    <input type="text" name="nome">
                </div>
                <div class="form-group">
                    <label>Morada:</label>
                    <input type="text" name="morada">
                </div>
                <div class="form-group">
                    <label>Contactos:</label>
                    <input type="text" name="contactos">
                </div>
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username">
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password">
                </div>
                <div class="form-group">
                    <label>Localidade:</label>
                    <input type="text" name="localidade">
                </div>
                <div class="form-group">
                    <label>Distrito:</label>
                    <input type="text" name="distrito">
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email">
                </div>
                <div class="form-group">
                    <label>Data de nascimento:</label>
                    <input type="date" name="data_nascimento">
                </div>
                <div class="form-group">
                    <label>Sexo:</label>
                    <select name="sexo">
                        <option value="M">Masculino</option>
                        <option value="F">Feminino</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>NIF:</label>
                    <input type="text" name="nif">
                </div>
                <div class="form-group">
                    <label>Alergias:</label>
                    <textarea name="alergias"></textarea>
                </div>
            </div>
        </div>

        <h4 class="section-title">Dados da consulta</h4>

        <div class="grid-container">
            <div class="form-group">
                <label>Temperatura (ºC):</label>
                <input type="text" name="temperatura">
            </div>
            <div class="form-group">
                <label>Pressão Arterial:</label>
                <input type="text" name="pressao">
            </div>
        </div>

        <div class="form-group">
            <label>Resumo da Consulta:</label>
            <textarea name="resumo"></textarea>
        </div>

        <div class="form-group">
            <label>Imagens (pode selecionar várias):</label>
            <div class="file-upload">
                <p>Arraste arquivos aqui ou clique para selecionar</p>
                <input type="file" name="imagens[]" multiple style="display: none;" id="fileInput">
                <button type="button" class="btn" onclick="document.getElementById('fileInput').click()">Escolher Arquivos</button>
            </div>
        </div>

        <button type="submit" class="btn">Registar Consulta</button>
    </form>

    <a href="dashboard_medico.php" class="btn-link">
        <i class="fa-solid fa-arrow-left"></i> Voltar à dashboard
    </a>
</div>

<script>
    function toggleCamposNovoPaciente(show) {
        document.getElementById('novo_paciente_fields').style.display = show ? 'block' : 'none';
        document.getElementById('paciente_existente').style.display = show ? 'none' : 'block';

        // Atualizar campos obrigatórios
        const pacienteExistente = document.querySelector('input[name="tipo_paciente"][value="existente"]');
        const novoPaciente = document.querySelector('input[name="tipo_paciente"][value="novo"]');

        if (show) {
            pacienteExistente.removeAttribute('required');
            novoPaciente.setAttribute('required', '');
        } else {
            novoPaciente.removeAttribute('required');
            pacienteExistente.setAttribute('required', '');
        }
    }

    // Mostrar nome dos arquivos selecionados
    document.getElementById('fileInput').addEventListener('change', function(e) {
        const files = e.target.files;
        const fileUploadDiv = document.querySelector('.file-upload p:first-of-type');

        if (files.length > 0) {
            fileUploadDiv.textContent = `${files.length} arquivo(s) selecionado(s)`;
        } else {
            fileUploadDiv.textContent = 'Arraste arquivos aqui ou clique para selecionar';
        }
    });
</script>
</body>
</html>