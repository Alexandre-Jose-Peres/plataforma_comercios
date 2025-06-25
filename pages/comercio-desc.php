<?php
@session_start();
ob_start();
include('../backend/database/conexao.php');


// Verifica se o usuário está logado
$usuario_logado = isset($_SESSION['user_id']);
$usuario_nome = $usuario_logado ? $_SESSION['user_name'] : null;
// Verifica se o ID do produto foi passado na URL
$id = $_GET['id'] ?? null;

if (!empty($id)) {
    // Consulta segura usando Prepared Statement com MySQLi, agora com JOIN para pegar o nome do comerciante e nome do usuário
    $query_comercio = "
        SELECT 
            c.id, 
            c.comerciante_id, 
            c.nome AS nome_comercio,  -- Nome do comércio
            c.descricao, 
            c.site, 
            c.foto, 
            c.horario_func, 
            c.categoria, 
            cm.usuario_id, 
            u.nome AS nome_usuario  -- Nome do usuário
        FROM comercios c
        JOIN comerciantes cm ON c.comerciante_id = cm.id
        JOIN usuarios u ON cm.usuario_id = u.id
        WHERE c.id = ?
    ";

    $stmt = $conn->prepare($query_comercio);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verifica se encontrou o comércio
    if ($result->num_rows > 0) {
        $comercio = $result->fetch_assoc();
    } else {
        echo "<p>Comércio não encontrado.</p>";
        exit;
    }
} else {
    echo "<p>ID do comércio não informado.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt_BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="uploads/logo1.png" type="image/x-icon">
    <title>ShopSpace</title> <!-- Corrigido aqui -->

    <!-- Estilos -->
    <link href="../vendor2/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="shortcut icon" href="../img/especialmoveis.ico" type="image/x-icon">

    <style>

    </style>
</head>

<body>
    <header>
        <div class="header-nav">
            <a href="../index.php">
                <div class="logo">
                    <img src="../uploads/logo1.png" alt="Logo">
                </div>
            </a>
            <div class="acesso">
                <a class="btn-cadastro" href="backend/auth/registrar.php">Cadastrar</a>
                <a class="btn-login" href="backend/auth/login.php">Login</a>
            </div>
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

    <div id="container">
        <!-- Imagem do Produto -->
        <div class="image-section">
            <img src="http://localhost/plataforma_comercios/uploads/<?php echo htmlspecialchars($comercio['foto']); ?>" alt="Produto">
        </div>

        <div class="details">
            <!-- Detalhes do Produto -->
            <div class="details-section">
                <h1><?php echo htmlspecialchars($comercio['nome_comercio']); ?></h1> <!-- Corrigido aqui -->
                <p><?php echo nl2br(htmlspecialchars($comercio['descricao'])); ?></p>
            </div>

            <!-- Preço e Botão de Compra -->
            <div class="price-section">
                <label for=""><strong>horário de funcionamento</strong></label>
                <h3><?php echo nl2br(htmlspecialchars($comercio['horario_func'])); ?></h3>
                <p><strong>Comerciante:</strong> <?php echo nl2br(htmlspecialchars($comercio['nome_usuario'])); ?></p> <!-- Corrigido aqui -->
                <?php echo "<a href='comercio-produto.php?id=" . htmlspecialchars($comercio['id']) . "' class='btn-detales'>Ver Produtos</a>"; ?>
            </div>


        </div>
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