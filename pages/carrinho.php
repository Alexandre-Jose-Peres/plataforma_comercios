<?php

include('../../backend/database/conexao.php');
session_start();
// Verifica se o usuário está logado
$usuario_logado = isset($_SESSION['user_id']);
$usuario_nome = $usuario_logado ? $_SESSION['user_name'] : null;

$id_usuario = $_SESSION['id_usuario'];

$sql = "SELECT c.*, p.nome, p.preco, p.imagem
        FROM carrinho c
        JOIN produtos p ON c.id_produto = p.id
        WHERE c.id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

$total = 0;
echo "<h2>Meu Carrinho</h2>";
while ($row = $res->fetch_assoc()) {
    $subtotal = $row['preco'] * $row['quantidade'];
    $total += $subtotal;
    echo "<div>
            <img src='{$row['imagem']}' width='100'>
            <strong>{$row['nome']}</strong><br>
            Quantidade: {$row['quantidade']}<br>
            Subtotal: R$ {$subtotal}
            <a href='remover_carrinho.php?id={$row['id_produto']}'>Remover</a>
          </div><hr>";
}
echo "<h3>Total: R$ {$total}</h3>";
echo "<a href='finalizar_pedido.php'>Finalizar Compra</a>";
?>
