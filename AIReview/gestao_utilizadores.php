
<?php
session_start();
require_once('config.php');
global $conn;

// Verificar se é gestor
if (!isset($_SESSION['id_utilizador']) || $_SESSION['perfil'] !== 'Gestor') {
    header('Location: index.php');
    exit;
}

// Buscar todos os utilizadores
$query = "SELECT id_utilizador, nome, username, perfil, data_criacao FROM Utilizador ORDER BY data_criacao DESC";
$resultado = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Utilizadores</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #999; padding: 8px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>Gestão de Utilizadores</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Username</th>
            <th>Perfil</th>
            <th>Data de Criação</th>
            <th>Ações</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($resultado)): ?>
            <tr>
                <td><?php echo $row['id_utilizador']; ?></td>
                <td><?php echo $row['nome']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['perfil']; ?></td>
                <td><?php echo $row['data_criacao']; ?></td>
                <td><a href="editar_utilizador.php?id=<?php echo $row['id_utilizador']; ?>">Editar</a></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <p><a href="dashboard_gestor.php">⬅ Voltar ao dashboard</a></p>
</body>
</html>
