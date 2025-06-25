<?php
require '../../backend/database/conexao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Acesso negado! Faça login."]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar se o ID do produto foi fornecido e se é válido
    if (!isset($_POST['id']) || empty($_POST['id']) || !is_numeric($_POST['id'])) {
        echo json_encode(["error" => "ID do produto inválido."]);
        exit();
    }

    $produto_id = $_POST['id'];
    $comerciante_id = $_SESSION['user_id'];

    // Verifica se o produto pertence ao comerciante logado
    $sql = "SELECT id, foto FROM produtos WHERE id = ? AND comerciante_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $produto_id, $comerciante_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["error" => "Produto não encontrado ou não autorizado."]);
        exit();
    }

    $produto = $result->fetch_assoc();
    $stmt->close();

    // Excluir as imagens do servidor, se houver
    if (!empty($produto['foto'])) {
        $arquivos = explode(',', $produto['foto']);
        foreach ($arquivos as $arquivo) {
            $caminho = "../" . $arquivo;
            if (file_exists($caminho)) {
                if (!unlink($caminho)) {
                    echo json_encode(["error" => "Erro ao excluir a imagem: $arquivo."]);
                    exit();
                }
            } else {
                // Caso a imagem não exista no servidor, registrar como um aviso
                echo json_encode(["warning" => "Imagem não encontrada para: $arquivo."]);
            }
        }
    }

    // Deletar o produto do banco de dados
    $sql = "DELETE FROM produtos WHERE id = ? AND comerciante_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $produto_id, $comerciante_id);

    if ($stmt->execute()) {
        echo json_encode( "Produto excluído com sucesso!");
    } else {
        echo json_encode(["error" => "Erro ao excluir o produto."]);
    }

    $stmt->close();
    $conn->close();
}
?>

