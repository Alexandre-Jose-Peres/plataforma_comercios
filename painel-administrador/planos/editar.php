<?php
include '../../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $cidade = $_POST['cidade'];
    $telefone = $_POST['telefone'];

    $sql = "UPDATE comercios SET nome=?, descricao=?, cidade=?, telefone=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $nome, $descricao, $cidade, $telefone, $id);

    if ($stmt->execute()) {
        echo "Comércio atualizado com sucesso!";
    } else {
        echo "Erro ao atualizar comércio: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
