<?php

include('../backend/database/conexao.php'); // Arquivo de conexão com o banco de dados

session_start();

// Verifica se o usuário está logado
$usuario_logado = isset($_SESSION['user_id']);
$usuario_nome = $usuario_logado ? $_SESSION['user_name'] : null;

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$comercio = null;
$resultProdutos = null;
$resultComercios = null;

if ($q !== '') {
    // Busca o comércio pelo nome
    $sqlComercio = "SELECT id, nome, telefone, endereco, cidade, estado, descricao, foto, horario_func FROM comercios WHERE nome LIKE ?";
    $stmtComercio = $conn->prepare($sqlComercio);
    $searchParam = "%$q%";
    $stmtComercio->bind_param("s", $searchParam);
    $stmtComercio->execute();
    $resultComercio = $stmtComercio->get_result();

    if ($resultComercio->num_rows > 0) {
        // Se o comércio for encontrado, pega os dados do comércio
        $comercio = $resultComercio->fetch_assoc();
        $comercio_id = $comercio['id'];

        // Busca os produtos relacionados ao comércio, ordenando pelos mais recentes
        $sqlProdutos = "SELECT * FROM produtos WHERE comercio_id = ? ORDER BY id DESC";
        $stmtProdutos = $conn->prepare($sqlProdutos);
        $stmtProdutos->bind_param("i", $comercio_id);
        $stmtProdutos->execute();
        $resultProdutos = $stmtProdutos->get_result();
    } else {
        // Se não encontrar o comércio, buscar produtos relacionados ao nome fornecido, ordenando pelos mais recentes
        $sqlProdutos = "SELECT p.*, c.id as comercio_id, c.nome as comercio_nome, c.foto as comercio_foto, c.telefone,
                        c.endereco, c.cidade, c.estado, c.descricao, c.horario_func
                        FROM produtos p 
                        INNER JOIN comercios c ON p.comercio_id = c.id 
                        WHERE p.nome LIKE ?
                        ORDER BY p.id DESC";
        $stmtProdutos = $conn->prepare($sqlProdutos);
        $stmtProdutos->bind_param("s", $searchParam);
        $stmtProdutos->execute();
        $resultProdutos = $stmtProdutos->get_result();

        if ($resultProdutos->num_rows > 0) {
            // Se produtos forem encontrados, pega o primeiro produto e os dados do comércio associado
            $produto = $resultProdutos->fetch_assoc();
            $comercio = [
                'id' => $produto['comercio_id'],
                'nome' => $produto['comercio_nome'],
                'foto' => $produto['comercio_foto'],
                'endereco' => $produto['endereco'],
                'cidade' => $produto['cidade'],
                'estado' => $produto['estado'],
                'telefone' => $produto['telefone'],
                'descricao' => $produto['descricao'],
                'horario_func' => $produto['horario_func']
            ];

            // Reexecuta a busca para trazer todos os produtos desse comércio, ordenando pelos mais recentes
            $comercio_id = $comercio['id'];
            $sqlProdutos = "SELECT * FROM produtos WHERE comercio_id = ? ORDER BY id DESC";
            $stmtProdutos = $conn->prepare($sqlProdutos);
            $stmtProdutos->bind_param("i", $comercio_id);
            $stmtProdutos->execute();
            $resultProdutos = $stmtProdutos->get_result();
        }
    }
} else {
    // Quando não há pesquisa, exibe todos os comércios
    $sqlComercios = "SELECT * FROM comercios ORDER BY id DESC";
    $stmtComercios = $conn->prepare($sqlComercios);
    $stmtComercios->execute();
    $resultComercios = $stmtComercios->get_result();
}
?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../uploads/logo1.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <title>ShopSpace</title>
</head>

<body>
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

    <div class="main-container">

        <section class="comercios-container">
            <?php if ($resultComercios && $resultComercios->num_rows > 0): ?>
                <?php while ($comercio = $resultComercios->fetch_assoc()): ?>
                    <div class="comercio-card">
                        <?php if (!empty($comercio['foto'])): ?>
                            <img src="http://localhost/plataforma_comercios/uploads/<?php echo htmlspecialchars($comercio['foto']); ?>" class="foto-produto" alt="Foto do Produto">
                        <?php else: ?>
                            <i class="fas fa-image" style="font-size: 80px; color: #F39C12;"></i>
                        <?php endif; ?>
                        <div id="status-<?php echo $comercio['id']; ?>" class="status"></div>
                        <h3><?php echo htmlspecialchars($comercio['nome']); ?></h3>
                        <p><strong>Endereço:</strong> <?php echo htmlspecialchars($comercio['endereco']); ?>, <?php echo htmlspecialchars($comercio['cidade']); ?> - <?php echo htmlspecialchars($comercio['estado']); ?></p>
                        <p><strong>Telefone:</strong> <?php echo htmlspecialchars($comercio['telefone']); ?></p>
                        <a href="comercio-produto.php?id=<?php echo $comercio['id']; ?>" class="btn-detales">Ver Produtos</a>
                        <!-- Passando o horário de funcionamento -->
                        <div id="horario-func-<?php echo $comercio['id']; ?>" style="display: none;"><?php echo htmlspecialchars($comercio['horario_func']); ?></div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>

            <?php endif; ?>
        </section>
        <section class="product-comercio">
            <?php if ($comercio): ?>
                <section class="comercio-info">
                    <h2>Comércio: <?php echo htmlspecialchars($comercio['nome']); ?></h2>
                    <img src="http://localhost/plataforma_comercios/uploads/<?php echo htmlspecialchars($comercio['foto']); ?>" alt="Foto do Comércio">
                    <p><strong>Telefone:</strong> <?php echo htmlspecialchars($comercio['telefone']); ?></p>
                    <p><strong>Endereço:</strong> <?php echo htmlspecialchars($comercio['endereco']); ?>, <?php echo htmlspecialchars($comercio['cidade']); ?> - <?php echo htmlspecialchars($comercio['estado']); ?></p>
                    <p><strong>Descrição:</strong> <?php echo htmlspecialchars($comercio['descricao']); ?></p>

                    <!-- Passando o horário de funcionamento -->
                    <div id="horario-func-<?php echo $comercio['id']; ?>" style="display: none;"><?php echo htmlspecialchars($comercio['horario_func']); ?></div>

                    <!-- Status de aberto ou fechado -->
                    <div id="status-<?php echo $comercio['id']; ?>" class="status"></div>
                </section>
            <?php endif; ?>

        </section> <!-- Div para exibir o status de aberto ou fechado -->
    </div>




    <section class="product-list" id="product">
        <?php if ($resultProdutos && $resultProdutos->num_rows > 0): ?>
            <?php while ($produto = $resultProdutos->fetch_assoc()): ?>
                <div class="product-card">


                    <div class="product-img">
                        <?php if (!empty($produto['foto'])): ?>
                            <img src="http://localhost/plataforma_comercios/<?php echo htmlspecialchars($produto['foto']); ?>" class="foto-produto" alt="Foto do Produto">
                        <?php else: ?>
                            <i class="fas fa-image" style="font-size: 80px; color: #F39C12;"></i>
                        <?php endif; ?>

                    </div>

                    <div class="product-desc">
                        <h3 class="product-name"><?php echo htmlspecialchars($produto['nome']); ?></h3>
                        <p class="product-desc"><?php echo htmlspecialchars($produto['descricao']); ?></p>
                        <div class="price">Preço: R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></div>
                        <a href="descricao.php?id=<?php echo htmlspecialchars($produto['id']); ?>" class="btn-detales">Ver Detalhes</a>
                        <?php


                        if (!empty($comercio['telefone'])) {
                            // Remove caracteres não numéricos do telefone
                            $numeroWhatsApp = preg_replace('/\D/', '', $comercio['telefone']);

                            // Verifica se o telefone tem um tamanho adequado (10 ou 11 dígitos)
                            if (strlen($numeroWhatsApp) >= 10) {
                                $mensagem = "Olá, tudo bem? Tenho interesse no produto " . $produto['nome'] . " no site SHOPSPACE e gostaria de saber mais detalhes. Poderia me ajudar?";
                                $linkWhatsApp = "https://wa.me/55{$numeroWhatsApp}?text=" . urlencode($mensagem);
                                echo "<a href='" . htmlspecialchars($linkWhatsApp) . "' class='buy-button' target='_blank'>Fazer Pedido</a>";
                            } else {
                                echo "<p>Telefone do comércio não disponível.</p>";
                            }
                        } else {
                            echo "<p>WhatsApp do vendedor não disponível.</p>";
                        }


                        ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>

        <?php endif; ?>
    </section>


    </div>

    <footer style="background-color: #071a3f; color: #ffd700; padding: 40px 20px; font-family: Arial, sans-serif;">
        <div style="max-width: 1200px; margin: auto; display: flex; flex-wrap: wrap; justify-content: space-between;">
            <!-- Informações de Contato -->
            <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                <h5 style="margin-bottom: 15px;">Contato</h5>
                <p>📞 Telefone: (35) 99702-9569</p>
                <div class="rede-sociais">
                    <a href="#"><img src="uploads/facebook.png">Facebook</a>
                    <a href="#"><img src="uploads/instagram.png">Instagram</a>
                    <a href="#"><img src="uploads/whatsapp.png">Whatsapp</a>
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
                    <input type="email" placeholder="Seu e-mail" required style="width: 100%; padding: 8px; margin-bottom: 10px;">
                    <button type="submit" style="width: 100%; padding: 10px; background-color: #ff6600; color: #fff; border: none; cursor: pointer;">Enviar</button>
                </form>
            </div>
        </div>
    </footer>

    <div style="text-align: center; padding: 15px 0; background-color: #111; color: #ffd700;">
        © 2025 Empresa. Todos os direitos reservados.
    </div>
    <script>
        function verificarStatus() {
            // Pegar todos os elementos de horário de funcionamento de cada comércio
            const horariosFunc = document.querySelectorAll('[id^="horario-func-"]');

            horariosFunc.forEach(function(horarioElem) {
                // Obter o id do comercio atual (contém o id do comércio)
                const comercioId = horarioElem.id.split('-')[2];

                // Pegar o horário de funcionamento do comércio
                const horarioFunc = horarioElem.textContent;

                // Extrair os horários de abertura e fechamento
                const [horaAbertura, horaFechamento] = horarioFunc.match(/\d+/g).map(Number);

                // Obter a hora atual
                const horaAtual = new Date().getHours();

                // Verificar se o comércio está aberto ou fechado
                const comercioAberto = (horaAtual >= horaAbertura && horaAtual < horaFechamento);

                // Encontrar o elemento de status correspondente ao comércio
                const statusElement = document.getElementById('status-' + comercioId);

                // Adicionar o texto e a classe de estilo adequado (aberto ou fechado)
                if (comercioAberto) {
                    statusElement.textContent = "Aberto"; // Exibe "Aberto"
                    statusElement.classList.add("aberto");
                    statusElement.classList.remove("fechado");
                    statusElement.title = "Comércio Aberto";
                } else {
                    statusElement.textContent = "Fechado"; // Exibe "Fechado"
                    statusElement.classList.add("fechado");
                    statusElement.classList.remove("aberto");
                    statusElement.title = "Comércio Fechado";
                }
            });
        }

        // Atualizar o status imediatamente ao carregar a página
        verificarStatus();

        // Atualizar o status a cada minuto
        setInterval(verificarStatus, 60000);
    </script>



    <script src="../assets/js/index.js"></script>
</body>

</html>