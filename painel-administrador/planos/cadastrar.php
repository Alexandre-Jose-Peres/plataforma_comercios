<?php
include '../../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pegando o nome do comerciante enviado pelo formulário
    @$nome_comerciante = $conn->real_escape_string($_POST["nome_comerciante"]);

    // Verificando se o comerciante existe
    $sql_comerciante = "SELECT * FROM comerciantes WHERE nome = '$nome_comerciante'";
    $result_comerciante = $conn->query($sql_comerciante);

    if ($result_comerciante->num_rows > 0) {
        $row_comerciante = $result_comerciante->fetch_assoc();
        $usuario_id = $row_comerciante['id']; // Pegando o ID a partir do nome
    } else {
        echo "Erro: Comerciante não encontrado!";
        exit;
    }

    // Coletando os outros dados do formulário
    $nome = $conn->real_escape_string($_POST["nome"]);
    $descricao = $conn->real_escape_string($_POST["descricao"]);
    $endereco = $conn->real_escape_string($_POST["endereco"]);
    $cidade = $conn->real_escape_string($_POST["cidade"]);
    $estado = $conn->real_escape_string($_POST["estado"]);
    $telefone = $conn->real_escape_string($_POST["telefone"]);
    $redes_sociais = $conn->real_escape_string($_POST["redes_sociais"]);
    $categoria = $conn->real_escape_string($_POST["categoria"]);

    // Verifica se o nome do comércio já existe no banco de dados
    $sql_check = "SELECT id FROM comercios WHERE nome = '$nome'";
    $result = $conn->query($sql_check);

    if ($result->num_rows > 0) {
        echo "Já existe um comércio cadastrado com esse nome!";
    } else {
        // Inserindo os dados na tabela `comercios`
        $sql = "INSERT INTO comercios (nome, usuario_id, descricao, endereco, cidade, estado, telefone, redes_sociais, categoria) 
                VALUES ('$nome', '$usuario_id', '$descricao', '$endereco', '$cidade', '$estado', '$telefone', '$redes_sociais', '$categoria')";

        if ($conn->query($sql) === TRUE) {
            echo "Comércio cadastrado com sucesso!";
        } else {
            echo "Erro ao cadastrar: " . $conn->error;
        }
    }
}
?>
