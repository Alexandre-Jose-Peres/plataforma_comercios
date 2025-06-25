<?php
session_start();
include('../../backend/database/conexao.php');

// Verifica se o usuário está logado
$usuario_logado = isset($_SESSION['user_id']);
$usuario_nome = $usuario_logado ? $_SESSION['user_name'] : null;

$id_usuario = $_SESSION['id_usuario'];

// Aqui você pode, por exemplo, gravar os produtos numa tabela de pedidos antes de limpar

// Por enquanto, apenas limpa o carrinho
$sql = "DELETE FROM carrinho WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();

echo "<h2>Compra finalizada com sucesso!</h2>";
echo "<a href='index.php'>Voltar para loja</a>";
?>
