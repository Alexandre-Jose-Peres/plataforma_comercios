<?php
header('Content-Type: application/json');
include '../../backend/database/conexao.php'; // Usa $conn

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    // Verifica se o usuário existe e tem imagem
    $stmt = $conn->prepare("SELECT foto FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário não encontrado.']);
        exit;
    }

    $usuario = $resultado->fetch_assoc();

    // Deleta a imagem física se existir
    if (!empty($usuario['foto'])) {
        $caminhoAntigo = str_replace(URL_IMAGENS_USUARIOS, CAMINHO_IMAGENS_FISICO_USUARIOS, $usuario['foto']);
        if (file_exists($caminhoAntigo)) {
            unlink($caminhoAntigo);
        }
    }

    // Deleta o usuário do banco
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'ok', 'mensagem' => 'Usuário excluído com sucesso.']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao excluir usuário.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'rejeitado', 'mensagem' => 'Requisição inválida.']);
}
