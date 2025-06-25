<?php
require '../../backend/database/conexao.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Acesso negado! Faça login."]);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $categoria = $_POST['categoria'];
    $status = $_POST['status'];  // Certifique-se de que está capturando o valor corretamente

    // Buscar foto atual do produto antes de alterar
    $sql_foto = "SELECT foto FROM produtos WHERE id = ?";
    $stmt_foto = $conn->prepare($sql_foto);
    $stmt_foto->bind_param("i", $id);
    $stmt_foto->execute();
    $result_foto = $stmt_foto->get_result();
    $produto = $result_foto->fetch_assoc();
    $fotoAtual = $produto['foto'];  // Foto atual, se existir

    // Verificar se um novo arquivo foi enviado
    if (isset($_FILES['foto']) && !empty($_FILES['foto']['name'])) {
        $foto = uploadArquivo($_FILES['foto']); // Função para processar o upload

        // Se a foto foi carregada com sucesso
        if ($foto === null) {
            echo json_encode(["error" => "Falha no upload da foto."]);
            exit();
        }

        // Se uma nova foto foi carregada, deletar a foto anterior
        if ($fotoAtual && file_exists($_SERVER['DOCUMENT_ROOT'] . "/plataforma_comercios" . $fotoAtual)) {
            unlink($_SERVER['DOCUMENT_ROOT'] . "/plataforma_comercios" . $fotoAtual); // Deletando a foto antiga
        }
    } else {
        // Se não houver novo arquivo, manter a foto atual
        $foto = $fotoAtual;
    }

    // Atualizar dados no banco
    $sql = "UPDATE produtos SET nome = ?, descricao = ?, preco = ?, categoria = ?, status = ?, foto = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsssi", $nome, $descricao, $preco,  $categoria, $status, $foto, $id);

    if ($stmt->execute()) {
        echo json_encode( "Produto atualizado com sucesso!");
    } else {
        error_log("Erro ao atualizar produto: " . $stmt->error);
        echo json_encode(["error" => "Erro ao atualizar produto: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}





// Função para fazer o upload de arquivos
function uploadArquivo($arquivos)
{
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/plataforma_comercios/uploads/produtos/"; // Caminho absoluto correto

    // Tipos de arquivos válidos (imagens e vídeos)
    $tiposValidos = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/avi'];
    $extensoesValidas = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi'];
    
    $foto = $arquivos['name'];  // Corrigido para acessar diretamente o arquivo
    $fileTmp = $arquivos['tmp_name'];
    $fileType = $arquivos['type'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    // Verificar se o tipo MIME do arquivo é válido
    if (!in_array($fileType, $tiposValidos)) {
        error_log("Tipo de arquivo inválido: " . $fileType);
        return null;  // Arquivo inválido
    }

    // Verificar a extensão do arquivo
    $fileExtension = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $extensoesValidas)) {
        error_log("Extensão de arquivo inválida: " . $fileExtension);
        return null;  // Extensão inválida
    }

    // Verificar o tamanho do arquivo
    if (filesize($fileTmp) > $maxSize) {
        error_log("Arquivo muito grande: " . filesize($fileTmp));
        return null;  // Arquivo muito grande
    }

    // Gerar nome único para o arquivo
    $fileName = uniqid() . basename($foto);
    $filePath = $uploadDir . $fileName;

    // Verificar se a pasta de upload existe
    if (!is_dir($uploadDir)) {
        error_log("O diretório de upload não existe: " . $uploadDir);
        return null;
    }

    // Tentar mover o arquivo para o diretório de upload
    if (move_uploaded_file($fileTmp, $filePath)) {
        // Sucesso no upload, retornar o caminho relativo
        error_log("Arquivo movido com sucesso para o diretório de upload.");
        return "/uploads/produtos/" . $fileName;
    } else {
        // Caso contrário, retornar um erro
        error_log("Falha ao mover o arquivo para o diretório de upload.");
        return null;
    }
}
?>
