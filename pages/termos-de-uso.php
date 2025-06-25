<?php
include('../backend/database/conexao.php'); // Arquivo de conexão com o banco de dados
session_start();

// Verifica se o usuário está logado
$usuario_logado = isset($_SESSION['user_id']);
$usuario_nome = $usuario_logado ? $_SESSION['user_name'] : null;

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Paginação
$limite = 50;
$pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$inicio = ($pagina - 1) * $limite;
$totalProdutos = 0;

if ($q !== '') {
    // Verifica se a pesquisa corresponde a um comércio
    $sqlComercio = "SELECT id, nome, telefone FROM comercios WHERE nome LIKE ?";
    $stmtComercio = $conn->prepare($sqlComercio);
    $searchParam = "%$q%";
    $stmtComercio->bind_param("s", $searchParam);
    $stmtComercio->execute();
    $resultComercio = $stmtComercio->get_result();

    if ($resultComercio->num_rows > 0) {
        // É um comércio
        $comercio = $resultComercio->fetch_assoc();
        $comercio_id = $comercio['id'];

        // Contar total de produtos desse comércio
        $sqlCount = "SELECT COUNT(*) as total FROM produtos WHERE comercio_id = ?";
        $stmtCount = $conn->prepare($sqlCount);
        $stmtCount->bind_param("i", $comercio_id);
        $stmtCount->execute();
        $totalProdutos = $stmtCount->get_result()->fetch_assoc()['total'];

        // Pegar produtos com dados do comércio incluídos
        $sqlProdutos = "SELECT p.*, c.nome as comercio_nome, c.telefone as comercio_telefone 
                        FROM produtos p
                        INNER JOIN comercios c ON p.comercio_id = c.id
                        WHERE p.comercio_id = ? 
                        ORDER BY p.id DESC LIMIT ?, ?";
        $stmtProdutos = $conn->prepare($sqlProdutos);
        $stmtProdutos->bind_param("iii", $comercio_id, $inicio, $limite);
        $stmtProdutos->execute();
        $resultProdutos = $stmtProdutos->get_result();
    } else {
        // Buscar produtos pelo nome
        $sqlCount = "SELECT COUNT(*) as total 
                     FROM produtos p 
                     INNER JOIN comercios c ON p.comercio_id = c.id 
                     WHERE p.nome LIKE ?";
        $stmtCount = $conn->prepare($sqlCount);
        $stmtCount->bind_param("s", $searchParam);
        $stmtCount->execute();
        $totalProdutos = $stmtCount->get_result()->fetch_assoc()['total'];

        $sqlProdutos = "SELECT p.*, c.nome as comercio_nome, c.telefone as comercio_telefone 
                        FROM produtos p 
                        INNER JOIN comercios c ON p.comercio_id = c.id 
                        WHERE p.nome LIKE ? 
                        ORDER BY p.id DESC LIMIT ?, ?";
        $stmtProdutos = $conn->prepare($sqlProdutos);
        $stmtProdutos->bind_param("sii", $searchParam, $inicio, $limite);
        $stmtProdutos->execute();
        $resultProdutos = $stmtProdutos->get_result();
    }
} else {
    // Sem pesquisa: exibir todos os produtos
    $sqlCount = "SELECT COUNT(*) as total FROM produtos";
    $stmtCount = $conn->prepare($sqlCount);
    $stmtCount->execute();
    $totalProdutos = $stmtCount->get_result()->fetch_assoc()['total'];

    $sqlProdutos = "SELECT p.*, c.nome as comercio_nome, c.telefone as comercio_telefone 
                    FROM produtos p 
                    INNER JOIN comercios c ON p.comercio_id = c.id 
                    ORDER BY p.id DESC LIMIT ?, ?";
    $stmtProdutos = $conn->prepare($sqlProdutos);
    $stmtProdutos->bind_param("ii", $inicio, $limite);
    $stmtProdutos->execute();
    $resultProdutos = $stmtProdutos->get_result();
}

// Total de páginas para paginação
$totalPaginas = ceil($totalProdutos / $limite);
?>



<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../uploads/logo1.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Montserrat:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">

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
                        <a class="btn-login" href="../backend/auth/login.php">Login</a>

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
                    form.action = "comercios.php"; // Direciona para comércios.php
                } else {
                    form.action = "produtos.php"; // Direciona para produtos.php
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
    <div class="container-politica">
        <h1>Termos de Uso</h1>

        <p>Bem-vindo ao nosso site! Ao utilizar este site, você concorda com os seguintes termos e condições. Por favor, leia atentamente antes de utilizar nossos serviços.</p>

        <h2>1. Objetivo da Plataforma</h2>
        <p>Nosso site tem como finalidade permitir que comerciantes cadastrem e divulguem seus comércios, produtos e serviços, e que clientes possam visualizar essas informações e entrar em contato com os comerciantes diretamente.</p>

        <h2>2. Cadastro de Comerciantes</h2>
        <p>O comerciante é responsável por fornecer informações verdadeiras, atualizadas e completas sobre seu comércio e produtos. É proibido o uso da plataforma para divulgar produtos ilícitos, ofensivos ou que infrinjam direitos de terceiros.</p>

        <h2>3. Responsabilidade sobre os Conteúdos</h2>
        <p>Todo o conteúdo (textos, imagens, preços, etc.) publicado por comerciantes é de responsabilidade exclusiva deles. O site não se responsabiliza por eventuais danos causados por informações incorretas ou negociações feitas fora da plataforma.</p>

        <h2>4. Contato entre Clientes e Comerciantes</h2>
        <p>O site apenas facilita o contato entre clientes e comerciantes. A negociação, compra, venda ou prestação de serviço ocorre entre as partes envolvidas, sem qualquer intermediação ou responsabilidade por parte da plataforma.</p>

        <h2>5. Regras de Uso</h2>
        <ul>
            <li>É proibido publicar conteúdos falsos, enganosos ou ilegais;</li>
            <li>É vedado o uso da plataforma para envio de spam ou práticas abusivas;</li>
            <li>Usuários devem respeitar os outros membros da comunidade e agir com boa-fé.</li>
        </ul>

        <h2>6. Propriedade Intelectual</h2>
        <p>Todo o layout, design, código-fonte e elementos gráficos do site são de propriedade exclusiva da plataforma, sendo vedada sua cópia ou reutilização sem autorização.</p>

        <h2>7. Suspensão e Encerramento de Conta</h2>
        <p>Reservamo-nos o direito de suspender ou excluir contas de comerciantes ou usuários que violem estes termos, sem necessidade de aviso prévio.</p>

        <h2>8. Modificações nos Termos</h2>
        <p>Estes termos podem ser alterados a qualquer momento. Recomendamos que o usuário consulte esta página periodicamente para se manter atualizado.</p>

        <h2>9. Legislação Aplicável</h2>
        <p>Este site é regido pelas leis brasileiras. Em caso de litígio, o foro da comarca do responsável pela plataforma será o competente para resolver quaisquer controvérsias.</p>

        <h2>10. Contato</h2>
        <p>Para dúvidas ou solicitações, entre em contato com nosso suporte:</p>
        <ul>
            <li><strong>Nome da Plataforma:</strong> Seu Site</li>
            <li><strong>E-mail:</strong> seuemail@dominio.com</li>
            <li><strong>Telefone:</strong> (00) 00000-0000</li>
        </ul>



    </div>

    <div class="cta-buttons" style="padding: 30px;">
        <a href="backend/auth/registrar.php" class="cta-btn">Cadastre seu Comércio</a>
        <a href="backend/auth/registrar.php" class="cta-btn">Descubra Produtos</a>
    </div>

    <footer style="background-color: #071a3f; color: #ffd700; padding: 40px 20px; font-family: Arial, sans-serif;">
        <div style="max-width: 1200px; margin: auto; display: flex; flex-wrap: wrap; justify-content: space-between;">
            <!-- Informações de Contato -->
            <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                <h5 style="margin-bottom: 15px;">Contato</h5>
                <p>📞 Telefone: (35) 99702-9569</p>
                <div class="rede-sociais">
                    <a href="https://www.facebook.com/alexandrejose.peres/" target="_blank"><img src="uploads/facebook.png">Facebook</a>
                    <a href="https://www.instagram.com/alexandrejpdp/" target="_blank"><img src="uploads/instagram.png">Instagram</a>
                    <a href="https://wa.me/5535997029569?text=Olá%2C%20tenho%20interesse" target="_blank"><img src="uploads/whatsapp.png">Whatsapp</a>
                </div>
            </div>

            <!-- Navegação Rápida -->
            <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                <h5 style="margin-bottom: 15px;">Links Úteis</h5>
                <p><a href="#sobre">Sobre Nós</a></p>
                <p><a href="pages/produtos.php">Produtos</a></p>
                <p><a href="pages/comercios.php">Comércios</a></p>

            </div>

            <!-- Informações Legais -->
            <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                <h5 style="margin-bottom: 15px;">Legal</h5>
                <p><a href="politica-privacidade.php">Política de Privacidade</a></p>
                <p><a href="#">Termos de Uso</a></p>
                <p><a href="#">Política de Cookies</a></p>
            </div>

            <!-- Newsletter -->
            <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                <h5 style="margin-bottom: 15px;">Newsletter</h5>
                <p>Inscreva-se para receber novidades e promoções.</p>
                <form>
                    <input type="email" placeholder="Seu e-mail" required style="width: 100%; padding: 8px; margin-bottom: 10px;">
                    <button type="submit" style="width: 100%; padding: 10px; background-color: #ff6600; color: #fff; border: none; cursor: pointer;">Enviar</button>
                </form>
            </div>
        </div>
    </footer>

    <!-- Direitos Autorais -->
    <div style="text-align: center; padding: 15px 0; background-color: #111; color: #ffd700;">
        © 2025 Empresa. Todos os direitos reservados.
    </div>

    <script src="assets/js/index.js"></script>
</body>

</html>