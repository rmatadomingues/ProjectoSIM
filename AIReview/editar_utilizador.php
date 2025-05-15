
<?php
session_start();
require_once('config.php');
global $conn;

// Verificar se é gestor
if (!isset($_SESSION['id_utilizador']) || $_SESSION['perfil'] !== 'Gestor') {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de utilizador inválido.";
    exit;
}

$id = intval($_GET['id']);
$mensagem = '';

// Atualizar utilizador
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $morada = $_POST['morada'];
    $contactos = $_POST['contactos'];
    $username = $_POST['username'];
    $perfil = $_POST['perfil'];

    $query = "UPDATE Utilizador SET nome='$nome', morada='$morada', contactos='$contactos', username='$username', perfil='$perfil'
              WHERE id_utilizador = $id";

    if (mysqli_query($conn, $query)) {
        header("Location: gestao_utilizadores.php");
        exit;
    } else {
        $mensagem = "Erro ao atualizar: " . mysqli_error($conn);
    }
}

// Buscar dados atuais do utilizador
$query = "SELECT * FROM Utilizador WHERE id_utilizador = $id";
$resultado = mysqli_query($conn, $query);
$utilizador = mysqli_fetch_assoc($resultado);

if (!$utilizador) {
    echo "Utilizador não encontrado.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Editar Utilizador</title>
</head>
<body>
    <h2>Editar Utilizador</h2>

    <?php if ($mensagem): ?>
        <p style="color:red;"><strong><?php echo $mensagem; ?></strong></p>
    <?php endif; ?>

    <form method="POST">
        <label>Nome:</label><br>
        <input type="text" name="nome" value="<?php echo $utilizador['nome']; ?>" required><br><br>

        <label>Morada:</label><br>
        <input type="text" name="morada" value="<?php echo $utilizador['morada']; ?>"><br><br>

        <label>Contactos:</label><br>
        <input type="text" name="contactos" value="<?php echo $utilizador['contactos']; ?>"><br><br>

        <label>Username:</label><br>
        <input type="text" name="username" value="<?php echo $utilizador['username']; ?>" required><br><br>

        <label>Perfil:</label><br>
        <select name="perfil" required>
            <option value="Medico" <?php if ($utilizador['perfil'] === 'Medico') echo 'selected'; ?>>Médico</option>
            <option value="Paciente" <?php if ($utilizador['perfil'] === 'Paciente') echo 'selected'; ?>>Paciente</option>
            <option value="Gestor" <?php if ($utilizador['perfil'] === 'Gestor') echo 'selected'; ?>>Gestor</option>
        </select><br><br>

        <input type="submit" value="Guardar alterações">
    </form>

    <p><a href="gestao_utilizadores.php">⬅ Voltar à gestão</a></p>
</body>
</html>
