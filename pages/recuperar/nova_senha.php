<?php
require '../../backend/database/conexao.php'; // Aqui está o novo arquivo com mysqli
session_start();
$token = $_GET['token'] ?? '';

$stmt = $conn->prepare("SELECT id, token_expira FROM usuarios WHERE token_recuperacao = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Token inválido.");
}

$usuario = $result->fetch_assoc();

if (strtotime($usuario['token_expira']) < time()) {
    die("Token expirado.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/login.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">

    <title>Nova senha</title>
    <style>
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
        }

        .alert.sucesso {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.erro {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>

    <header>
        <div class="header-nav">
            <a href="../index.php">
                <div class="logo">
                    <img src="../../uploads/logo1.png" alt="Logo">
                </div>
            </a>



            <div class="nome-perfil">
                <?php if ($usuario_logado): ?>
                    <p>Bem Vindo, <?= htmlspecialchars($usuario_nome) ?>! </p>
                    <button class="btn" id="btnLogout">Sair</button>

                <?php else: ?>
                    <div class="acesso">
                        <a class="btn-cadastro" href="../backend/auth/registrar.php">Cadastrar</a>
                        <a class="btn-login" href="../backend/auth/login.php">Login</a>

                    </div>
                <?php endif; ?>
            </div>


        </div>

        <button class="menu-toggle">☰</button>
        <nav class="navbar">
            <ul class="nav-links">
                <li class="link"><a href="../../index.php">INÍCIO</a></li>
                <li class="link"><a href="#sobre">QUEM SOMOS</a></li>
                <li class="link"><a href="../produtos.php">PRODUTOS</a></li>
                <li class="link"><a href="../comercios.php">COMÉRCIOS</a></li>
            </ul>
        </nav>

    </header>

    <div class="login-container">
        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="alert <?= $_SESSION['tipo_mensagem'] ?>">
                <?= htmlspecialchars($_SESSION['mensagem']) ?>
            </div>
            <?php unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']); ?>
        <?php endif; ?>
        <div class="form-container">
            <form action="../../backend/recuperar/atualizar.php" method="POST">

                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <label>Nova senha:</label><br>
                <input type="password" name="nova_senha" required><br><br>
                <button type="submit">Atualizar senha</button>
            </form>
        </div>
    </div>
</body>

</html>