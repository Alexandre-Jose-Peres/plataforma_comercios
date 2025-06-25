<?php
session_start();
include('../../backend/database/conexao.php');
// Verifica se o usuário está logado
$usuario_logado = isset($_SESSION['user_id']);
$usuario_nome = $usuario_logado ? $_SESSION['user_name'] : null;

$id_usuario = $_SESSION['user_id'];
$id_produto = (int) $_POST['id_produto'];
$quantidade = max(1, (int) $_POST['quantidade']);

// Verificar se o produto já está no carrinho
$sql = "SELECT * FROM carrinho WHERE id_usuario = ? AND id_produto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_usuario, $id_produto);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $sql = "UPDATE carrinho SET quantidade = quantidade + ? WHERE id_usuario = ? AND id_produto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $quantidade, $id_usuario, $id_produto);
} else {
    $sql = "INSERT INTO carrinho (id_usuario, id_produto, quantidade) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $id_usuario, $id_produto, $quantidade);
}

$stmt->execute();
header("Location: carrinho.php");
?>
