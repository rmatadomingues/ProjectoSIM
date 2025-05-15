
<?php
session_start();
require_once('config.php');

if (!isset($_SESSION['id_utilizador']) || $_SESSION['perfil'] !== 'Medico') {
    header('Location: index.php');
    exit;
}

$id_medico = $_SESSION['id_utilizador'];
$nome = $_SESSION['nome'];
$fotografia = isset($_SESSION['fotografia']) && file_exists($_SESSION['fotografia']) 
    ? $_SESSION['fotografia'] 
    : 'uploads/perfis/default.png';

// Atualizar resumo se enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_consulta'], $_POST['novo_resumo'])) {
    $id_consulta = intval($_POST['id_consulta']);
    $novo_resumo = mysqli_real_escape_string($conn, $_POST['novo_resumo']);
    $check = mysqli_query($conn, "SELECT id_consulta FROM Consulta WHERE id_consulta = $id_consulta AND id_medico = $id_medico");
    if (mysqli_num_rows($check) === 1) {
        mysqli_query($conn, "UPDATE Consulta SET resumo_consulta = '$novo_resumo' WHERE id_consulta = $id_consulta");
    }
}

// Buscar consultas
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
    <title>Dashboard Médico</title>
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 20%;
            left: 50%;
            transform: translate(-50%, -10%);
            background-color: #fff;
            padding: 20px;
            border: 2px solid #888;
            z-index: 1000;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.3);
            z-index: 500;
        }
    </style>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="<?php echo $fotografia; ?>" width="75" height="75" style="border-radius: 50%;">
        <h2>Olá, Dr(a). <?php echo htmlspecialchars($nome); ?>!</h2>
    </div>

    <h3>⚙️ Ações disponíveis:</h3>
    <ul>
        <li><a href="registar_consulta.php">📄 Registar nova consulta</a></li>
        <li><a href="minha_ficha.php">👤 Ver/editar a minha ficha</a></li>
        <li><a href="logout.php">🚪 Terminar sessão</a></li>
    </ul>

    <h3>📋 Histórico de Consultas Realizadas</h3>
    <?php if (count($consultas) === 0): ?>
        <p>Não existem consultas registadas por si.</p>
    <?php else: ?>
        <table border="1" cellpadding="6" cellspacing="0">
            <tr>
                <th>Data</th>
                <th>Paciente</th>
                <th>Resumo</th>
                <th>Imagens</th>
            </tr>
            <?php foreach ($consultas as $c): ?>
                <tr>
                    <td><?php echo $c['data_hora']; ?></td>
                    <td>
                        <a href="ficha_paciente.php?id=<?php echo $c['id_paciente']; ?>">
                            <?php echo htmlspecialchars($c['paciente']); ?>
                        </a>
                    </td>
                    <td>
                        <div style="white-space: pre-wrap;"><?php echo htmlspecialchars($c['resumo_consulta']); ?></div>
                        <button onclick="abrirModal(<?php echo $c['id_consulta']; ?>, <?php echo json_encode($c['resumo_consulta']); ?>)">✏️ Editar</button>
                    </td>
                    <td><a href="ver_imagens_consulta.php?id_consulta=<?php echo $c['id_consulta']; ?>">🔍 Ver imagens</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <div class="overlay" id="overlay" onclick="fecharModal()"></div>
    <div class="modal" id="modalResumo">
        <form method="POST">
            <input type="hidden" name="id_consulta" id="id_consulta">
            <label>Novo resumo:</label><br>
            <textarea name="novo_resumo" id="novo_resumo" rows="6" cols="50"></textarea><br><br>
            <button type="submit">Guardar</button>
            <button type="button" onclick="fecharModal()">Cancelar</button>
        </form>
    </div>

    <script>
        function abrirModal(id, texto) {
            document.getElementById('id_consulta').value = id;
            document.getElementById('novo_resumo').value = texto;
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('modalResumo').style.display = 'block';
        }

        function fecharModal() {
            document.getElementById('overlay').style.display = 'none';
            document.getElementById('modalResumo').style.display = 'none';
        }
    </script>
</body>
</html>
