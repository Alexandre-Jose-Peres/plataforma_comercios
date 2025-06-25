<?php
include('../../backend/database/conexao.php'); // Arquivo de conexão com o banco de dados

session_start();

// Verifica se o usuário está logado
$usuario_logado = isset($_SESSION['user_id']);
$usuario_nome = $usuario_logado ? $_SESSION['user_name'] : null;

$sql = "DELETE FROM carrinho WHERE id_usuario = ? AND id_produto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_usuario, $id_produto);
$stmt->execute();

header("Location: carrinho.php");
?>
