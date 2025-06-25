<?php
session_start(); // Inicia a sessão
require '../database/conexao.php';

$token = $_POST['token'];
$nova_senha = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);

// Atualiza a senha e remove o token
$stmt = $conn->prepare("UPDATE usuarios SET senha = ?, token_recuperacao = NULL, token_expira = NULL WHERE token_recuperacao = ?");
$stmt->bind_param("ss", $nova_senha, $token);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $_SESSION['mensagem'] = "Senha atualizada com sucesso.";
    $_SESSION['tipo_mensagem'] = "sucesso";
} else {
    $_SESSION['mensagem'] = "Erro ao atualizar senha. Token inválido ou expirado.";
    $_SESSION['tipo_mensagem'] = "erro";
}

header("Location: ../../pages/login.php?token=" . urlencode($token));
exit;
