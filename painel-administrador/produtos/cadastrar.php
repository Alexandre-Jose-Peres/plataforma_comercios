<?php
include '../../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pegando o nome do comércio enviado pelo formulário
    $nome_comercio = trim($_POST["nome_comercio"]);

    // Verificando se o comércio existe
    $sql_comercio = "SELECT id FROM comercios WHERE nome = ?";
    $stmt = $conn->prepare($sql_comercio);
    $stmt->bind_param("s", $nome_comercio);
    $stmt->execute();
    $result_comercio = $stmt->get_result();

    if ($result_comercio->num_rows > 0) {
        $row_comercio = $result_comercio->fetch_assoc();
        $comercio_id = $row_comercio['id']; // Pegando o ID correto do comércio
    } else {
        echo json_encode(["status" => "erro", "mensagem" => "Comércio não encontrado!"]);
        exit;
    }

    // Coletando os outros dados do formulário
    $nome_produto = trim($_POST["nome_produto"]);
    $descricao = trim($_POST["descricao"]);
    $preco = floatval($_POST["preco"]);
    $estoque = intval($_POST["estoque"]);
    $foto = !empty($_POST["foto"]) ? trim($_POST["foto"]) : null;
    $data = date('Y-m-d H:i:s'); // Pegando a data atual

    // Verifica se o produto já existe no banco de dados
    $sql_check = "SELECT id FROM produtos WHERE nome = ? AND comercio_id = ?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("si", $nome_produto, $comercio_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "erro", "mensagem" => "Já existe um produto cadastrado com esse nome para esse comércio!"]);
    } else {
        // Inserindo os dados na tabela `produtos`
        $sql = "INSERT INTO produtos (comercio_id, nome, descricao, preco, estoque, foto, criado_em) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issdiss", $comercio_id, $nome_produto, $descricao, $preco, $estoque, $foto, $data);

        if ($stmt->execute()) {
            echo json_encode("Produto cadastrado com sucesso!");
        } else {
            echo json_encode(["Erro ao cadastrar: " . $conn->error]);
        }
    }
}

// Fechar conexão
$conn->close();
?>
