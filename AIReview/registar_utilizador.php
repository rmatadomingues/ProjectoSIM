<?php
session_start();
require_once('config.php');
global $conn;

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $morada = $_POST['morada'];
    $contactos = $_POST['contactos'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $perfil = $_POST['perfil'];
    $foto = ''; // não implementado ainda

    // Inserir na tabela Utilizador
    $query = "INSERT INTO Utilizador (nome, morada, contactos, username, password, fotografia, perfil)
              VALUES ('$nome', '$morada', '$contactos', '$username', '$password', '$foto', '$perfil')";

    if (mysqli_query($conn, $query)) {
        $id = mysqli_insert_id($conn);

        // Se for paciente, inserir também na tabela Paciente
        if ($perfil === 'Paciente') {
            $localidade = $_POST['localidade'];
            $distrito = $_POST['distrito'];
            $email = $_POST['email'];
            $data_nascimento = $_POST['data_nascimento'];
            $sexo = $_POST['sexo'];
            $nif = $_POST['nif'];
            $alergias = $_POST['alergias'];

            $query_paciente = "INSERT INTO Paciente (id_utilizador, localidade, distrito, email, data_nascimento, sexo, NIF, alergias)
                               VALUES ($id, '$localidade', '$distrito', '$email', '$data_nascimento', '$sexo', '$nif', '$alergias')";

            mysqli_query($conn, $query_paciente);
        }

        $mensagem = "Utilizador registado com sucesso!";
    } else {
        $mensagem = "Erro: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Registo de Utilizador</title>
    <script>
        function mostrarCamposPaciente() {
            const perfil = document.getElementById('perfil').value;
            document.getElementById('campos_paciente').style.display = (perfil === 'Paciente') ? 'block' : 'none';
        }
    </script>
</head>
<body>
<h2>Registar novo utilizador</h2>

<?php if ($mensagem): ?>
    <p><strong><?php echo $mensagem; ?></strong></p>
<?php endif; ?>

<form method="POST">
    <label for="nome">Nome:</label>
    <input type="text" name="nome" required><br><br>

    <label for="morada">Morada:</label>
    <input type="text" name="morada" required><br><br>

    <label for="contactos">Contactos:</label>
    <input type="text" name="contactos" required><br><br>

    <label for="username">Username:</label>
    <input type="text" name="username" required><br><br>

    <label for="password">Password:</label>
    <input type="password" name="password" required><br><br>

    <label for="perfil">Perfil:</label>
    <select name="perfil" id="perfil" onchange="mostrarCamposPaciente()" required>
        <option value="">-- Escolher --</option>
        <option value="Medico">Médico</option>
        <option value="Paciente">Paciente</option>
    </select><br><br>

    <div id="campos_paciente" style="display:none;">
        <h4>Dados adicionais do paciente</h4>

        <label for="localidade">Localidade:</label>
        <input type="text" name="localidade"><br><br>

        <label for="distrito">Distrito:</label>
        <input type="text" name="distrito"><br><br>

        <label for="email">Email:</label>
        <input type="email" name="email"><br><br>

        <label for="data_nascimento">Data de nascimento:</label>
        <input type="date" name="data_nascimento"><br><br>

        <label for="sexo">Sexo:</label>
        <select name="sexo">
            <option value="">-- Escolher --</option>
            <option value="Masculino">Masculino</option>
            <option value="Feminino">Feminino</option>
            <option value="Outro">Outro</option>
        </select><br><br>

        <label for="nif">NIF:</label>
        <input type="text" name="nif"><br><br>

        <label for="alergias">Alergias:</label>
        <textarea name="alergias"></textarea><br><br>
    </div>

    <input type="submit" value="Registar">
</form>
</body>
</html>
