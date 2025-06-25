<?php
header('Content-Type: application/json');

include('backend/database/conexao.php');

$query = "SELECT * FROM produtos WHERE promocao IS NOT NULL AND promocao != '' LIMIT 10";
$result = $conn->query($query);

$produtos = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
}

echo json_encode($produtos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$conn->close();
?>
