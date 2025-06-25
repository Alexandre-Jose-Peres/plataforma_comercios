<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
    <div class="login-container">


        <div class="form-container">
            <h2>Cadastro de Usuário</h2>
            <form action="register.php" method="POST" id="registerForm">
                <label for="username">Nome de Usuário:</label>
                <input type="text" id="username" name="username" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>

                <label for="confirm_password">Confirmar Senha:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <button type="submit">Cadastrar</button>

                <!-- Exibição de mensagens de sucesso ou erro -->
                <?php if (isset($_GET['msg'])): ?>
                    <p style="color: green; text-align: center;"> <?= htmlspecialchars($_GET['msg']); ?> </p>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <p style="color: red; text-align: center;"> <?= htmlspecialchars($_GET['error']); ?> </p>
                <?php endif; ?>
            </form>

            <!-- Link para o login -->
            <div class="login-link">
                <p>Já tem uma conta? <a href="login.php">Faça login aqui</a></p>
            </div>
        </div>
        <div class="image-container">
            <!-- Imagem na lateral -->
            <img src="../uploads/imagem-comercio.jpg" alt="Imagem Lateral" class="image">

        </div>
    </div>
   

    <script>
        // Função para verificar se as senhas coincidem
        document.getElementById("registerForm").addEventListener("submit", function(event) {
            var password = document.getElementById("password").value;
            var confirm_password = document.getElementById("confirm_password").value;

            if (password !== confirm_password) {
                alert("As senhas não coincidem. Tente novamente.");
                event.preventDefault(); // Impede o envio do formulário
            }
        });
    </script>
</body>

</html>