<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
    <div class="login-container">


        <div class="form-container">
            <h2>Login</h2>
            <form action="../backend/auth/auth.php" method="POST">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Entrar</button>

                <!-- Mensagem de sucesso ou erro -->
                <?php if (isset($_GET['msg'])): ?>
                    <p style="color: green; text-align: center;"> <?= htmlspecialchars($_GET['msg']); ?> </p>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <p style="color: red; text-align: center;"> <?= htmlspecialchars($_GET['error']); ?> </p>
                <?php endif; ?>

                <!-- Link para recuperação de senha -->
                <div class="forgot-password">
                    <a href="recuperar/solicitar.php">Esqueceu sua senha?</a>
                </div>

            </form>
        </div>
        <div class="image-container">
            <!-- Imagem na lateral -->
            <img src="../uploads/imagem-comercio.jpg" alt="Imagem Lateral" class="image">
        </div>
    </div>
</body>

</html>