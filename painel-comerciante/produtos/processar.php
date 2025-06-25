<?php

require '../../backend/database/conexao.php';

// Inicia a sessão caso não esteja ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Acesso negado! Faça login."]);
    exit();
}

$comerciante_id = $_SESSION['user_id']; // ID do comerciante logado

// Busca todos os produtos do comerciante logado
$sql = "SELECT * FROM produtos WHERE comerciante_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $comerciante_id);
$stmt->execute();
$result = $stmt->get_result();

$produtos = [];
while ($row = $result->fetch_assoc()) {
    $produtos[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($produtos);
?>

