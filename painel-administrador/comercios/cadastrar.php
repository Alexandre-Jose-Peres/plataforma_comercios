<?php
include '../../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? $_POST['id'] : null; // Verifica se o ID foi enviado
    $usuario_id = $conn->real_escape_string($_POST["comerciante_id"]);
    $nome = $conn->real_escape_string($_POST["nome"]);
    $descricao = $conn->real_escape_string($_POST["descricao"]);
    $endereco = $conn->real_escape_string($_POST["endereco"]);
    $cidade = $conn->real_escape_string($_POST["cidade"]);
    $estado = $conn->real_escape_string($_POST["estado"]);
    $telefone = $conn->real_escape_string($_POST["telefone"]);
    $redes_sociais = $conn->real_escape_string($_POST["redes_sociais"]);
    $categoria = $conn->real_escape_string($_POST["categoria"]);

    // Verifica se o comerciante existe
    $sql_comerciante = "SELECT id FROM comerciantes WHERE id = '$usuario_id'";
    $result_comerciante = $conn->query($sql_comerciante);

    if ($result_comerciante->num_rows == 0) {
        echo "Erro: O comerciante especificado não existe.";
        exit();
    }

    // Verifica se já existe outro comércio com o mesmo nome
    if ($id) {
        // Para edição, excluímos o próprio ID da verificação
        $sql_check = "SELECT id FROM comercios WHERE nome = '$nome' AND id != '$id'";
    } else {
        $sql_check = "SELECT id FROM comercios WHERE nome = '$nome'";
    }
    
    $result = $conn->query($sql_check);

    if ($result->num_rows > 0) {
        echo "Erro: Já existe um comércio cadastrado com esse nome!";
        exit();
    }

    if ($id) {
        // Se o ID existe, faz atualização
        $sql = "UPDATE comercios SET nome=?, usuario_id=?, descricao=?, endereco=?, cidade=?, estado=?, telefone=?, redes_sociais=?, categoria=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisssssssi", $nome, $usuario_id, $descricao, $endereco, $cidade, $estado, $telefone, $redes_sociais, $categoria, $id);
        
        if ($stmt->execute()) {
            echo "Comércio atualizado com sucesso!";
        } else {
            echo "Erro ao atualizar comércio: " . $conn->error;
        }
        $stmt->close();
    } else {
        // Se não tem ID, faz o cadastro
        $sql = "INSERT INTO comercios (nome, usuario_id, descricao, endereco, cidade, estado, telefone, redes_sociais, categoria) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisssssss", $nome, $usuario_id, $descricao, $endereco, $cidade, $estado, $telefone, $redes_sociais, $categoria);
        
        if ($stmt->execute()) {
            echo "Comércio cadastrado com sucesso!";
        } else {
            echo "Erro ao cadastrar comércio: " . $conn->error;
        }
        $stmt->close();
    }

    $conn->close();
}
?>
