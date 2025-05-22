<?php
session_start();
require_once('config.php');
global $conn;

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM Utilizador WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if ($resultado && mysqli_num_rows($resultado) === 1) {
        $utilizador = mysqli_fetch_assoc($resultado);

        // Verificação de password (deveria usar password_verify() com hash)
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
                default:
                    header('Location: index.php');
                    break;
            }
            exit;
        } else {
            $erro = "Credenciais inválidas.";
        }
    } else {
        $erro = "Credenciais inválidas."; // Mensagem genérica por segurança
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIReview - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e235f;
            --secondary-color: #7b9ec1;
            --accent-color: #e76f51;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background-color: #f5f7fa;
            color: var(--dark-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-color: white;
            box-shadow: var(--box-shadow);
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-content {
            max-width: 600px;
            text-align: center;
        }

        .header img {
            height: 80px;
            width: auto;
        }

        .login-container {
            max-width: 500px;
            margin: 40px auto;
            padding: 40px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 90%;
        }

        .login-title {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 30px;
            font-size: 1.8rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(123, 158, 193, 0.2);
        }

        .btn {
            display: inline-block;
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
        }

        .btn:hover {
            background-color: #3a5a80;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            background-color: #f8d7da;
            color: var(--danger-color);
            border: 1px solid #f5c6cb;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }

        .input-icon input {
            padding-left: 40px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            margin-top: auto;
            background-color: white;
            border-top: 1px solid #eee;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }

            .login-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
<header class="header">
    <img src="imagens/logo.png" alt="AIReview Logo">
    <div class="header-content">
        <h4>Plataforma de apoio à decisão médica com análise automatizada de opiniões de pacientes</h4>
    </div>
</header>

<main>
    <div class="login-container">
        <h1 class="login-title"><i class="fas fa-sign-in-alt"></i> Iniciar Sessão</h1>

        <?php if ($erro): ?>
            <div class="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Nome de Utilizador</label>
                <div class="input-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Digite seu username" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Palavra-passe</label>
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Digite sua senha" required>
                </div>
            </div>

            <button type="submit" class="btn">
                <i class="fas fa-sign-in-alt"></i> Entrar
            </button>
        </form>
    </div>
</main>

<footer class="footer">
    <p>&copy; <?php echo date('Y'); ?> AIReview - Todos os direitos reservados</p>
</footer>
</body>
</html>