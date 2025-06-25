<?php
include '../../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? $_POST['id'] : null; // Verifica se há um ID
    $nome = $conn->real_escape_string($_POST["nome"]);
    $endereco = $conn->real_escape_string($_POST["endereco"]);
    $telefone = $conn->real_escape_string($_POST["telefone"]);
    $email = $conn->real_escape_string($_POST["email"]);

    // Verifica se já existe um comerciante com o mesmo nome (exceto no próprio ID)
    $sql_check = "SELECT id FROM comerciantes WHERE nome = ? AND (id != ? OR ? IS NULL)";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("sis", $nome, $id, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Já existe um comerciante cadastrado com esse nome!";
        exit;
    }

    // Se ID existir -> Edita / Se não existir -> Cadastra
    if (!empty($id)) {
        // Atualiza um comerciante existente
        $sql = "UPDATE comerciantes SET nome=?, endereco=?, telefone=?, email=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $nome, $endereco, $telefone, $email, $id);

        if ($stmt->execute()) {
            echo "Comerciante atualizado com sucesso!";
        } else {
            echo "Erro ao atualizar comércio: " . $conn->error;
        }
    } else {
        // Cadastra um novo comerciante
        $sql = "INSERT INTO comerciantes (nome, endereco, telefone, email) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nome, $endereco, $telefone, $email);

        if ($stmt->execute()) {
            echo "Comerciante cadastrado com sucesso!";
        } else {
            echo "Erro ao cadastrar: " . $conn->error;
        }
    }

    $stmt->close();
    $conn->close();
}
?>
