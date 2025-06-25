<?php
include('backend/database/conexao.php');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q !== '') {
    // Verifica se a pesquisa corresponde a um comércio
    $sqlComercio = "SELECT id, nome FROM comercios WHERE nome LIKE ?";
    $stmtComercio = $conn->prepare($sqlComercio);
    $searchParam = "%$q%";
    $stmtComercio->bind_param("s", $searchParam);
    $stmtComercio->execute();
    $resultComercio = $stmtComercio->get_result();

    if ($resultComercio->num_rows > 0) {
        // Se for um comércio, busca os produtos vinculados a ele
        $comercio = $resultComercio->fetch_assoc();
        $comercio_id = $comercio['id'];

        $sqlProdutos = "SELECT * FROM produtos WHERE comercio_id = ?";
        $stmtProdutos = $conn->prepare($sqlProdutos);
        $stmtProdutos->bind_param("i", $comercio_id);
        $stmtProdutos->execute();
        $resultProdutos = $stmtProdutos->get_result();
    } else {
        // Caso não seja um comércio, buscar apenas os comércios que vendem o produto pesquisado
        $sqlProdutos = "SELECT p.*, c.nome as comercio_nome 
                        FROM produtos p 
                        INNER JOIN comercios c ON p.comercio_id = c.id 
                        WHERE p.nome LIKE ?";
        $stmtProdutos = $conn->prepare($sqlProdutos);
        $stmtProdutos->bind_param("s", $searchParam);
        $stmtProdutos->execute();
        $resultProdutos = $stmtProdutos->get_result();
    }
} else {
    echo "Digite algo para pesquisar.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>ShopSpace</title>
</head>

<body>

    <header>
        <div class="header-nav">
            <div class="logo">
                <img src="uploads/logo1.png" alt="">
            </div>
           
            <form action="pesquisa.php" method="GET" id="searchForm">
                <input type="text" name="q" id="searchInput" placeholder="Buscar produtos ou comércios...">
                <button type="submit">Pesquisar</button>
            </form>

            <script>
                document.getElementById("searchForm").addEventListener("submit", function(event) {
                    setTimeout(() => {
                        document.getElementById("searchInput").value = "";
                    }, 100);
                });
            </script>

        </div>

        <button class="menu-toggle">☰</button>
        <nav class="navbar">
            <ul class="nav-links">
                <li class="link"><a href="index.php">INÍCIO</a></li>
                <li class="link"><a href="#clientes">QUEM SOMOS</a></li>
                <li class="link"><a href="produtos.php">PRODUTOS</a></li>
                <li class="link"><a href="comercios.php">COMÉRCIOS</a></li>
            </ul>
        </nav>

    </header>
    <div class="main-container">
        <h2>Resultados da Pesquisa</h2>
        <section class="product-list">
            <?php
            if (isset($resultProdutos) && $resultProdutos->num_rows > 0) {
                while ($produto = $resultProdutos->fetch_assoc()) {
                    echo "<div class='product-card'>";

                    if (!empty($produto['foto'])) :
                        $fotos = explode(',', $produto['foto']);
                        foreach ($fotos as $foto) :
                            echo "<img src='http://localhost/plataforma_comercios/" . htmlspecialchars(trim($foto)) . "' class='foto-produto' alt='Foto do Produto'>";
                        endforeach;
                    else :
                        echo "<i class='fas fa-image' style='font-size: 80px; color: #F39C12;'></i>";
                    endif;

                    echo "<h3>" . htmlspecialchars($produto['nome']) . "</h3>";
                    echo "<p>" . htmlspecialchars($produto['descricao']) . "</p>";
                    echo "<div class='price'>R$ " . number_format($produto['preco'], 2, ',', '.') . "</div>";

                    if (isset($produto['comercio_nome'])) {
                        echo "<p>Comércio: <strong>" . htmlspecialchars($produto['comercio_nome']) . "</strong></p>";
                    }

                    echo "<a href='descricao.php?id=" . htmlspecialchars($produto['id']) . "' class='btn-detales'>Ver Detalhes</a>";
                    echo "</div>";
                }
            } else {
                echo "<p>Nenhum resultado encontrado.</p>";
            }
            ?>
        </section>
    </div>
    <footer style="background-color: #071a3f; color: #ffd700;padding: 40px 20px; font-family: Arial, sans-serif;">
        <div style="max-width: 1200px; margin: auto; display: flex; flex-wrap: wrap; justify-content: space-between;">

            <!-- Informações de Contato -->
            <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                <h5 style="margin-bottom: 15px;">Contato</h5>

                <p>📞 Telefone: (35) 99702-9569</p>
                <div class="rede-sociais">
                    <a href="#" alt=""><img src="uploads/facebook.png">Facebook</a>
                    <a href="#" alt=""><img src="uploads/instagram.png">Instagram</a>
                    <a href="" alt=""><img src="uploads/whatsapp.png">Whatsapp</a>
                    <a href="#"><a href="https://www.flaticon.com/br/icones-gratis/rede-social" title="rede social ícones"></a>
                        Youtube</a>
                </div>
            </div>

            <!-- Navegação Rápida -->
            <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                <h5 style="margin-bottom: 15px;">Links Úteis</h5>
                <p><a href="#">Sobre Nós</a></p>
                <p><a href="#">Serviços</a></p>
                <p><a href="#">Blog</a></p>
                <p><a href="#">Contato</a></p>
            </div>

            <!-- Informações Legais -->
            <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                <h5 style="margin-bottom: 15px;">Legal</h5>
                <p><a href="#">Política de Privacidade</a></p>
                <p><a href="#">Termos de Uso</a></p>
                <p><a href="#">Política de Cookies</a></p>
            </div>

            <!-- Newsletter -->
            <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                <h5 style="margin-bottom: 15px;">Newsletter</h5>
                <p>Inscreva-se para receber novidades e promoções.</p>
                <form>
                    <input type="email" placeholder="Seu e-mail" required
                        style="width: 100%; padding: 8px; margin-bottom: 10px;">
                    <button type="submit"
                        style="width: 100%; padding: 10px; background-color: #ff6600; color: #fff; border: none; cursor: pointer;">Enviar</button>
                </form>
            </div>
        </div>


    </footer>
    <!-- Direitos Autorais -->
    <div style="text-align: center; padding: 15px 0; background-color: #111;color:#ffd700">
        © 2025 Empresa. Todos os direitos reservados.
    </div>

    <script src="../assets/js/index.js"></script>
</body>

</html>