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
$caminho_fotos = "imagens/";
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
    : 'imagens/medico.png';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Editar Ficha</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f4f4f4;
        }
        form {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
        img {
            width: 150px;
            height: auto;
            border-radius: 10px;
            display: block;
            margin: 0 auto 20px;
        }
        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }
        input[type="text"], input[type="password"], input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-top: 4px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            background: #7b9ec1;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #849cb8;
        }
        .edit-link a {
            text-decoration: none;
            color: white;
            background: #7b9ec1;
            padding: 10px 20px;
            border-radius: 6px;
    </style>
</head>
<body>
<form method="POST" enctype="multipart/form-data">
    <h2 style="text-align:center;">Editar Ficha</h2>
    <img src="<?php echo $foto_perfil; ?>" alt="Foto de perfil">
    <input type="file" name="foto">

    <label>Nome:</label>
    <input type="text" name="nome" value="<?php echo $utilizador['nome']; ?>">

    <label>Morada:</label>
    <input type="text" name="morada" value="<?php echo $utilizador['morada']; ?>">

    <label>Contactos:</label>
    <input type="text" name="contactos" value="<?php echo $utilizador['contactos']; ?>">

    <label>Username:</label>
    <input type="text" name="username" value="<?php echo $utilizador['username']; ?>">

    <label>Nova password (opcional):</label>
    <input type="password" name="nova_password">

    <button type="submit">Guardar alterações</button>
    <div class="edit-link">
        <br>
        <a href="minha_ficha_med.php">Voltar</a>
    </div>
</form>
</body>
</html>