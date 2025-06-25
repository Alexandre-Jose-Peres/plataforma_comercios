<?php

include '../../backend/database/conexao.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
    // Verifica se o e-mail já está cadastrado
    $check_stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        header("Location: registrar.php?msg=Usuário já cadastrado!");
        exit();
    }
    
    $check_stmt->close();
    
    // Insere o novo usuário
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);
    
    if ($stmt->execute()) {
        header("Location: login.php?msg=Cadastro realizado com sucesso!");
        exit();
    } else {
        header("Location: registrar.php?msg=Erro ao cadastrar: " . urlencode($stmt->error));
        exit();
    }
    
    $stmt->close();
    $conn->close();
}
?>
