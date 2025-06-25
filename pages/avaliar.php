<?php
session_start();
include('../backend/database/conexao.php');

// Define o retorno como JSON
header('Content-Type: application/json');

// Garante que o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Você precisa estar logado para avaliar!'
    ]);
    exit;
}

// Obtém dados da requisição
$produto_id = intval($_POST['produto_id']);
$nota = intval($_POST['nota']);
$usuario_id = $_SESSION['user_id'];

$response = [];

// Valida nota
if ($nota < 1 || $nota > 5) {
    $response['sucesso'] = false;
    $response['mensagem'] = 'Nota inválida.';
    echo json_encode($response);
    exit;
}

// Verifica se o usuário já avaliou o produto
$sql = "SELECT id FROM avaliacoes WHERE usuario_id = ? AND produto_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $usuario_id, $produto_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Já avaliou
    $response['sucesso'] = false;
    $response['mensagem'] = 'Você já avaliou este produto.';
    echo json_encode($response);
    exit;
}else{
    
}

// Nova avaliação
$sql = "INSERT INTO avaliacoes (usuario_id, produto_id, nota) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $usuario_id, $produto_id, $nota);

if ($stmt->execute()) {
    $response['sucesso'] = true;
    $response['mensagem'] = 'Avaliação registrada com sucesso!';
} else {
    $response['sucesso'] = false;
    $response['mensagem'] = 'Falha ao registrar avaliação.';
}

echo json_encode($response);
?>
