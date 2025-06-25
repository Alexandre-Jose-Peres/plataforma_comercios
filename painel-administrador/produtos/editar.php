<?php
include '../../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Certifique-se de que todas as variáveis estão sendo recebidas corretamente
    $id = isset($_POST["id"]) ? $conn->real_escape_string($_POST["id"]) : null;
    $nome = $conn->real_escape_string($_POST["nome"]);
    $descricao = $conn->real_escape_string($_POST["descricao"]);
    $preco = $conn->real_escape_string($_POST["preco"]);
    $estoque = $conn->real_escape_string($_POST["estoque"]);
    $foto = $conn->real_escape_string($_POST["foto"]);

    // Verifica se o ID foi recebido
    if ($id === null) {
        echo "Erro: ID do produto não informado.";
        exit();
    }

    $sql = "UPDATE produtos SET nome=?, descricao=?, preco=?, estoque=?, foto=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    
    // Corrigindo o número de parâmetros e tipos (adicionando "i" para o ID)
    $stmt->bind_param("sssssi", $nome, $descricao, $preco, $estoque, $foto, $id);

    if ($stmt->execute()) {
        echo "Produto atualizado com sucesso!";
    } else {
        echo "Erro ao atualizar produto: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
