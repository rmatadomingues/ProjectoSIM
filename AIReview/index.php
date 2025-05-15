
<?php
session_start();
require_once('config.php');
global $conn;

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM Utilizador WHERE username = '$username'";
    $resultado = mysqli_query($conn, $query);

    if ($resultado && mysqli_num_rows($resultado) === 1) {
        $utilizador = mysqli_fetch_assoc($resultado);

        // Verificação de password (sem hash neste momento)
        if ($utilizador['password'] === $password) {
            $_SESSION['id_utilizador'] = $utilizador['id_utilizador'];
            $_SESSION['nome'] = $utilizador['nome'];
            $_SESSION['perfil'] = $utilizador['perfil'];
            $_SESSION['fotografia'] = $utilizador['fotografia'];

            // Redirecionar consoante o perfil
            switch ($utilizador['perfil']) {
                case 'Medico':
                    header('Location: dashboard_medico.php');
                    break;
                case 'Paciente':
                    header('Location: dashboard_paciente.php');
                    break;
                case 'Gestor':
                    header('Location: dashboard_gestor.php');
                    break;
            }
            exit;
        } else {
            $erro = "Password incorreta.";
        }
    } else {
        $erro = "Utilizador não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>AIReview - Página Inicial</title>
</head>
<body>
    <h1>🧠 AIReview</h1>
    <p>Plataforma de apoio à decisão médica com análise automatizada de opiniões de pacientes.</p>

    <h2>Iniciar Sessão</h2>

    <?php if ($erro): ?>
        <p style="color: red;"><?php echo $erro; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <input type="submit" value="Entrar">
    </form>
</body>
</html>
