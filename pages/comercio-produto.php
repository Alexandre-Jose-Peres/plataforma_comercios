<?php
include('../backend/database/conexao.php'); // Arquivo de conexão com o banco de dados

session_start();
// Verifica se o usuário está logado
$usuario_logado = isset($_SESSION['user_id']);
$usuario_nome = $usuario_logado ? $_SESSION['user_name'] : null;
$id = $_GET['id'] ?? null;

if (!empty($id)) {
    // Consulta para obter os produtos de um comércio específico
    $query_produtos = "SELECT p.id, p.nome, p.descricao, p.preco, p.foto, 
                              c.nome AS comercio_nome, c.telefone, c.id AS comercio_id
                       FROM produtos p
                       JOIN comercios c ON p.comercio_id = c.id
                       WHERE c.id = ?";

    $stmt = $conn->prepare($query_produtos);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultProdutos = $stmt->get_result();
} else {
    echo "<p>ID do comércio não informado.</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="shortcut icon" href="../uploads/logo1.png" type="image/x-icon">
    <title>ShopSpace</title>
</head>

<body>
    <!-- Cabeçalho -->
    <header>
        <div class="header-nav">
            <a href="../index.php">
                <div class="logo">
                    <img src="../uploads/logo1.png" alt="Logo">
                </div>
            </a>



            <div class="nome-perfil">
                <?php if ($usuario_logado): ?>
                    <p>Bem Vindo, <?= htmlspecialchars($usuario_nome) ?>! </p>
                    <button class="btn" id="btnLogout">Sair</button>

                <?php else: ?>
                    <div class="acesso">
                        <a class="btn-cadastro" href="../backend/auth/registrar.php">Cadastrar</a>
                        <a class="btn-login" href="backend/auth/login.php">Login</a>

                    </div>
                <?php endif; ?>
            </div>
        </div>
        <form action="pages/produtos.php" method="GET" id="searchForm">
            <input type="text" name="q" id="searchInput" placeholder="Buscar produtos ou comércios...">
            <button type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="50" height="50">
                    <path d="M11 2C5.48 2 1 6.48 1 12s4.48 10 10 10 10-4.48 10-10S16.52 2 11 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm4.24-7.24l3.7 3.7-1.42 1.42-3.7-3.7c-1.12.9-2.54 1.44-4.02 1.44-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6c0 1.48-.55 2.9-1.46 4.02z" />
                </svg>
            </button>
        </form>

        <script>
            document.getElementById("searchForm").addEventListener("submit", function(event) {
                let searchInput = document.getElementById("searchInput").value.trim().toLowerCase();
                let form = document.getElementById("searchForm");

                // Lista de palavras-chave para diferenciar produtos e comércios
                let comerciosKeywords = ["restaurante", "bar", "lanchonete", "padaria", "mercado", "loja"];

                // Verifica se a pesquisa contém palavras relacionadas a comércios
                if (comerciosKeywords.some(keyword => searchInput.includes(keyword))) {
                    form.action = "pages/comercios.php"; // Direciona para comércios.php
                } else {
                    form.action = "pages/produtos.php"; // Direciona para produtos.php
                }

                // Limpa o campo de busca após o envio
                setTimeout(() => {
                    document.getElementById("searchInput").value = "";
                }, 100);
            });
        </script>
        <button class="menu-toggle">☰</button>
        <nav class="navbar">
            <ul class="nav-links">
                <li class="link"><a href="../index.php">INÍCIO</a></li>
                
                <li class="link"><a href="#sobre">QUEM SOMOS</a></li>
                <li class="link"><a href="produtos.php">PRODUTOS</a></li>
                <li class="link"><a href="comercios.php">COMÉRCIOS</a></li>

            </ul>
        </nav>
    </header>
    <div class="main-container">
        <?php
        if ($resultProdutos->num_rows > 0) {
            $comercio = null;

            // Capturar nome e telefone do comércio apenas uma vez
            $primeiroProduto = $resultProdutos->fetch_assoc();
            $comercio = [
                'nome' => $primeiroProduto['comercio_nome'],
                'telefone' => preg_replace('/\D/', '', $primeiroProduto['telefone'])
            ];

            // Exibir nome do comércio
            echo "<div class='comercio-nome'>";
            echo "<h2>Produtos do Comércio: " . htmlspecialchars($comercio['nome']) . "</h2>";
            echo "</div>";

            // Abrir a section uma única vez
            echo "<section class='product-list'>";

            // Resetar resultado para percorrer os produtos novamente
            $resultProdutos->data_seek(0);
            while ($produto = $resultProdutos->fetch_assoc()) {
                echo "<div class='product'>";

                // Exibir imagens
                if (!empty($produto['foto'])) {
                    $fotos = explode(',', $produto['foto']);
                    foreach ($fotos as $foto) {
                        echo "<img src='http://localhost/plataforma_comercios/" . htmlspecialchars(trim($foto)) . "' class='foto-produto' alt='Foto do Produto'>";
                    }
                } else {
                    echo "<i class='fas fa-image' style='font-size: 80px; color: #F39C12;'></i>";
                }

                echo "<h3>" . htmlspecialchars($produto['nome']) . "</h3>";
                echo "<p>" . htmlspecialchars($produto['descricao']) . "</p>";
                echo "<div class='price'>R$ " . number_format($produto['preco'], 2, ',', '.') . "</div>";
                echo "<a href='descricao.php?id=" . htmlspecialchars($produto['id']) . "' class='btn-detales'>Ver Detalhes</a>";
                // Botão do WhatsApp para o comércio
                if ($comercio['telefone'] && strlen($comercio['telefone']) >= 10) {
                    $mensagem = "Olá, tudo bem? Tenho interesse no produto " . $produto['nome'] . " no site SHOPSPACE e gostaria de saber mais detalhes. Poderia me ajudar? ";
                    $linkWhatsApp = "https://wa.me/55{$comercio['telefone']}?text=" . urlencode($mensagem);
                    echo "<a href='" . htmlspecialchars($linkWhatsApp) . "' class='buy-button' target='_blank'>Fazer Pedido</a>";
                } else {
                    echo "<p>Telefone do comércio não disponível.</p>";
                }
                echo "</div>";
            }

            // Fechar a section
            echo "</section>";
        } else {
            echo "<p>Nenhum produto encontrado.</p>";
        }
        ?>
    </div>

    <footer>
        <p>&copy; 2025 Empresa. Todos os direitos reservados.</p>
    </footer>

    <script src="../assets/js/index.js"></script>
</body>

</html>