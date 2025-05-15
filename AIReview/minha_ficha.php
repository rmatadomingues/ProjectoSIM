
<?php
session_start();
require_once('config.php');
global $conn;

if (!isset($_SESSION['id_utilizador'])) {
    header('Location: index.php');
    exit;
}

$id = $_SESSION['id_utilizador'];
$mensagem = '';
$caminho_fotos = "uploads/perfis/";
if (!is_dir($caminho_fotos)) {
    mkdir($caminho_fotos, 0777, true);
}

// Atualizar dados pessoais e fotografia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $morada = $_POST['morada'];
    $contactos = $_POST['contactos'];
    $username = $_POST['username'];
    $nova_password = $_POST['nova_password'];
    $foto = '';

    // Upload de fotografia
    if (!empty($_FILES['foto']['name'])) {
        $nome_foto = basename($_FILES['foto']['name']);
        $destino = $caminho_fotos . time() . "_" . $nome_foto;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
            $foto = $destino;
        }
    }

    $sql = "UPDATE Utilizador SET nome='$nome', morada='$morada', contactos='$contactos', username='$username'";
    if (!empty($nova_password)) {
        $sql .= ", password='$nova_password'";
    }
    if (!empty($foto)) {
        $sql .= ", fotografia='$foto'";
    }
    $sql .= " WHERE id_utilizador = $id";

    if (mysqli_query($conn, $sql)) {
        $mensagem = "Dados atualizados com sucesso!";
        $_SESSION['nome'] = $nome;
        if (!empty($foto)) {
            $_SESSION['fotografia'] = $foto;
        }
    } else {
        $mensagem = "Erro ao atualizar: " . mysqli_error($conn);
    }
}

// Buscar dados do utilizador
$query = "SELECT * FROM Utilizador WHERE id_utilizador = $id";
$resultado = mysqli_query($conn, $query);
$utilizador = mysqli_fetch_assoc($resultado);

if (!$utilizador) {
    echo "Utilizador não encontrado.";
    exit;
}

// Definir imagem de perfil (real ou genérica)
$foto_perfil = $utilizador['fotografia'] && file_exists($utilizador['fotografia']) 
    ? $utilizador['fotografia'] 
    : 'uploads/perfis/default.png';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Minha Ficha</title>
</head>
<body>
    <h2>Minha Ficha</h2>

    <?php if ($mensagem): ?>
        <p><strong><?php echo $mensagem; ?></strong></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <img src="<?php echo $foto_perfil; ?>" alt="Foto de perfil" width="150" style="cursor:pointer;" onclick="document.getElementById('foto').click();"><br>
        <input type="file" name="foto" id="foto" style="display:none;"><br><br>

        <label>Nome:</label><br>
        <input type="text" name="nome" value="<?php echo $utilizador['nome']; ?>" required><br><br>

        <label>Morada:</label><br>
        <input type="text" name="morada" value="<?php echo $utilizador['morada']; ?>"><br><br>

        <label>Contactos:</label><br>
        <input type="text" name="contactos" value="<?php echo $utilizador['contactos']; ?>"><br><br>

        <label>Username:</label><br>
        <input type="text" name="username" value="<?php echo $utilizador['username']; ?>" required><br><br>

        <label>Nova password (opcional):</label><br>
        <input type="password" name="nova_password"><br><br>

        <input type="submit" value="Guardar alterações">
    </form>

    <p><a href="index.php">⬅ Voltar</a></p>
</body>
</html>
