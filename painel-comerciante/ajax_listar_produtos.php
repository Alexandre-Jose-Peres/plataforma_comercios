<?php
require '../backend/database/conexao.php';

$busca = $_GET['search'] ?? '';
$query = "SELECT * FROM produtos WHERE nome LIKE ? ORDER BY id DESC";
$stmt = $conn->prepare($query);
$buscaTermo = "%{$busca}%";
$stmt->bind_param('s', $buscaTermo);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $status = $row['status'] == 1 ? 'Ativo' : 'Inativo';
    echo "<tr>
        <td>{$row['nome']}</td>
        <td>{$row['descricao']}</td>
        <td>R$ " . number_format($row['preco'], 2, ',', '.') . "</td>
        <td>{$row['categoria']}</td>
        <td>{$status}</td>
        <td><img src='../uploads/{$row['imagem']}' width='50'></td>
        <td>
            <button class='editar'>Editar</button>
            <button class='excluir'>Excluir</button>
        </td>
    </tr>";
}
?>