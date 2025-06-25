<?php
include '../../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $usuario_id = $_POST["id"];
    $descricao = $_POST['descricao'];
    $endereco = $_POST['endereco']; // Adicionado
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado']; // Adicionado
    $telefone = $_POST['telefone'];
    $redes_sociais = $_POST['redes_sociais']; // Adicionado
    $categoria = $_POST['categoria']; // Adicionado

    // Verifica se já existe outro comércio com o mesmo nome, excluindo o atual
    $sql_check = "SELECT id FROM comercios WHERE nome = ? AND id != ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("si", $nome, $id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        echo "Erro: Já existe um comércio com esse nome!";
        $stmt_check->close();
        $conn->close();
        exit;
    }
    $stmt_check->close();

    // Se não houver nome duplicado, realiza a atualização
    $sql = "UPDATE comercios SET nome=?, id=?, descricao=?, endereco=?, cidade=?, estado=?, telefone=?, redes_sociais=?, categoria=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisssssssi", $nome, $usuario_id, $descricao, $endereco, $cidade, $estado, $telefone, $redes_sociais, $categoria, $id);

    if ($stmt->execute()) {
        echo "Comércio atualizado com sucesso!";
    } else {
        echo "Erro ao atualizar comércio: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
