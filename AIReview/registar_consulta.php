
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

            $mensagem = "Consulta registada com sucesso!";
        } else {
            $mensagem = "Erro ao registar consulta: " . mysqli_error($conn);
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
    <title>Registar Consulta</title>
    <script>
        function toggleCamposNovoPaciente(show) {
            document.getElementById('novo_paciente_fields').style.display = show ? 'block' : 'none';
            document.getElementById('paciente_existente').style.display = show ? 'none' : 'block';
        }
    </script>
</head>
<body>
    <h2>Registar Nova Consulta</h2>
    <?php if ($mensagem): ?>
        <p><strong><?php echo $mensagem; ?></strong></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label><input type="radio" name="tipo_paciente" value="existente" checked onclick="toggleCamposNovoPaciente(false)"> Paciente existente</label>
        <label><input type="radio" name="tipo_paciente" value="novo" onclick="toggleCamposNovoPaciente(true)"> Novo paciente</label><br><br>

        <div id="paciente_existente">
            <label>Selecionar paciente:</label><br>
            <select name="id_paciente">
                <option value="">-- Escolher --</option>
                <?php while ($p = mysqli_fetch_assoc($pacientes)): ?>
                    <option value="<?php echo $p['id_utilizador']; ?>"><?php echo $p['nome']; ?></option>
                <?php endwhile; ?>
            </select><br><br>
        </div>

        <div id="novo_paciente_fields" style="display:none;">
            <h4>Dados do novo paciente</h4>
            <label>Nome:</label><input type="text" name="nome"><br>
            <label>Morada:</label><input type="text" name="morada"><br>
            <label>Contactos:</label><input type="text" name="contactos"><br>
            <label>Username:</label><input type="text" name="username"><br>
            <label>Password:</label><input type="password" name="password"><br><br>

            <label>Localidade:</label><input type="text" name="localidade"><br>
            <label>Distrito:</label><input type="text" name="distrito"><br>
            <label>Email:</label><input type="email" name="email"><br>
            <label>Data de nascimento:</label><input type="date" name="data_nascimento"><br>
            <label>Sexo:</label>
            <select name="sexo">
                <option value="M">Masculino</option>
                <option value="F">Feminino</option>
                <option value="Outro">Outro</option>
            </select><br>
            <label>NIF:</label><input type="text" name="nif"><br>
            <label>Alergias:</label><textarea name="alergias"></textarea><br><br>
        </div>

        <label>Temperatura (ºC):</label><input type="text" name="temperatura"><br>
        <label>Pressão Arterial:</label><input type="text" name="pressao"><br>
        <label>Resumo da Consulta:</label><br><textarea name="resumo" rows="5" cols="40"></textarea><br><br>

        <label>Imagens (pode selecionar várias):</label><br>
        <input type="file" name="imagens[]" multiple><br><br>

        <input type="submit" value="Registar Consulta">
    </form>

    <p><a href="dashboard_medico.php">⬅ Voltar à dashboard</a></p>
</body>
</html>
