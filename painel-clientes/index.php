<?php

include '../backend/database/conexao.php';

// Inicia a sessão apenas se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifique se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../usuarios/login.php?msg=Acesso negado! Faça login.");
    exit();
}
$usuario_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <div class="nome-perfil">

        Bem-vindo, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
    </div>
</body>

</html>