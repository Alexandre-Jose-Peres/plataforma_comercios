<?php
include('../backend/database/conexao.php');

// Categorias disponíveis
$categorias = [
    '0' => 'Selecione uma Categoria',
    '1' => 'Roupas e Acessórios',
    '2' => 'Tecnologia',
    '3' => 'Casa e Decoração',
    '4' => 'Beleza e Cuidados Pessoais',
    '5' => 'Alimentos e Bebidas',
    '6' => 'Esportes e Lazer',
    '7' => 'Brinquedos e Jogos',
    '8' => 'Livros e Papelaria',
    '9' => 'Saúde',
    '10' => 'Automotivo',
];

// Categoria e paginação
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : 'todos';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

// Consulta principal
if ($categoria === 'todos') {
    $sql = "SELECT p.id, p.nome, p.descricao, p.preco, p.foto, c.telefone, c.nome AS comercio_nome
            FROM produtos p
            JOIN comercios c ON p.comercio_id = c.id
            ORDER BY p.id DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
} else {
    $sql = "SELECT p.id, p.nome, p.descricao, p.preco, p.foto, c.telefone, c.nome AS comercio_nome
            FROM produtos p
            JOIN comercios c ON p.comercio_id = c.id
            WHERE p.categoria = ?
            ORDER BY p.id DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $categoria, $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

// Gerar HTML
$html = '';
while ($produto = $result->fetch_assoc()) {
    @$categoria_produto = strtolower($produto['categoria']);

    if (!empty($produto['telefone'])) {
        $numeroWhatsApp = preg_replace('/\D/', '', $produto['telefone']);
        if (strlen($numeroWhatsApp) >= 10 && strlen($numeroWhatsApp) <= 11) {
            $mensagem = "Olá, tudo bem? Tenho interesse neste produto " . $produto['nome'] . " no site SHOPSPACE e gostaria de saber mais detalhes. Poderia me ajudar?";
            $linkWhatsApp = "https://wa.me/55{$numeroWhatsApp}?text=" . urlencode($mensagem);

            $html .= "
            <div class='item {$categoria_produto}'>
                <img src='http://localhost/plataforma_comercios/{$produto['foto']}' alt='{$produto['nome']}'>
                <h4>{$produto['nome']}</h4>
                <p>R$ {$produto['preco']}</p>
                <a href='pages/descricao.php?id=" . htmlspecialchars($produto['id']) . "' class='btn-detales'>Ver Detalhes</a>
                <a href='" . htmlspecialchars($linkWhatsApp) . "' class='buy-button' target='_blank'>Fazer Pedido</a>
            </div>
            ";
        } else {
            $html .= "
            <div class='item {$categoria_produto}'>
                <img src='http://localhost/plataforma_comercios/{$produto['foto']}' alt='{$produto['nome']}'>
                <h4>{$produto['nome']}</h4>
                <p>R$ {$produto['preco']}</p>
                <a href='pages/descricao.php?id=" . htmlspecialchars($produto['id']) . "' class='btn-detales'>Ver Detalhes</a>
                <p>Telefone do vendedor inválido.</p>
            </div>
            ";
        }
    } else {
        $html .= "
        <div class='item {$categoria_produto}'>
            <img src='http://localhost/plataforma_comercios/{$produto['foto']}' alt='{$produto['nome']}'>
            <h4>{$produto['nome']}</h4>
            <p>R$ {$produto['preco']}</p>
            <a href='pages/descricao.php?id=" . htmlspecialchars($produto['id']) . "' class='btn-detales'>Ver Detalhes</a>
            <p>WhatsApp do vendedor não disponível.</p>
        </div>
        ";
    }
}
$stmt->close();

// Contagem total para paginação
if ($categoria === 'todos') {
    $count_sql = "SELECT COUNT(*) AS total FROM produtos";
    $count_stmt = $conn->prepare($count_sql);
} else {
    $count_sql = "SELECT COUNT(*) AS total FROM produtos WHERE categoria = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $categoria);
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total = $count_result->fetch_assoc()['total'];
$count_stmt->close();

$conn->close();

// Retorno em JSON
echo json_encode([
    'html' => $html,
    'total' => $total,
    'limit' => $limit
]);
?>
