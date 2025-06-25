<?php

include('../backend/database/conexao.php'); // Arquivo de conexão com o banco de dados

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redireciona para a página de login, se não estiver logado
    exit();
}

// Obter o comerciante_id da sessão
$comerciante_id = $_SESSION['user_id']; // ID do comerciante logado

// Usar prepared statement para evitar SQL injection
$sql = "SELECT * FROM produtos WHERE comerciante_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $comerciante_id); // "i" é o tipo para integer (ID do comerciante)
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Incluindo o Material Icons -->
    <!-- Incluindo o Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


    <title>Minha Loja Online</title>
    <link rel="stylesheet" href="style.css"> <!-- Adicione o seu CSS -->
    <style>
        .main-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .product-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            
            width: 100%;
            max-width: 1200px;
            margin: 0 15px;
        }

        .product-card {
            width: 200px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            text-align: center;
            padding: 10px;
            transition: transform 0.3s;
        }

        .product-card img {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .product-card h3 {
            font-size: 1em;
            margin: 10px 0;
        }

        .product-card p {
            font-size: 1em;
            color: #555;
        }

        .product-card .price {
            font-size: 1em;
            font-weight: bold;
            color: #1c9c5b;
        }

        .product-card:hover {
            transform: scale(1.05);
        }

      

        /* Media Queries para telas menores */
        @media (max-width: 768px) {
            header h1 {
                font-size: 1.5em;
            }

            .product-list {
                grid-template-columns: 1fr 1fr;
            }

            .add-product-form {
                width: 90%;
            }

            .product-card h3 {
                font-size: 1em;
            }

            .product-card p {
                font-size: 0.9em;
            }
        }

        @media (max-width: 480px) {
            .product-list {
                grid-template-columns: 1fr;
            }

            .product-card h3 {
                font-size: 1em;
            }

            .product-card p {
                font-size: 0.85em;
            }
        }
    </style>
</head>

<body>


    <div class="main-container">
    <h2>Produtos Postados</h2>
        <section class="product-list">
            
            <?php
            if ($result->num_rows > 0) {
                while ($produto = $result->fetch_assoc()) {
                    echo "<div class='product-card'>";

                    // Verifica se há fotos no banco de dados
                    if (!empty($produto['foto'])) :
                        $fotos = explode(',', $produto['foto']); // Divide a string em um array com as fotos
                        foreach ($fotos as $foto) : ?>
                            <img src="../<?php echo htmlspecialchars(trim($foto)); ?>" class="foto-produto" width="50" alt="Foto do Produto"><br>
                        <?php endforeach;
                    else : ?>
                        
                        <i class="fas fa-image" style="font-size: 80px; color: #F39C12;"></i>

            <?php endif;

                    // Exibe o nome, descrição e preço do produto
                    echo "<h3>" . htmlspecialchars($produto['nome']) . "</h3>";
                    echo "<p>" . htmlspecialchars($produto['descricao']) . "</p>";
                    echo "<div class='price'>R$ " . number_format($produto['preco'], 2, ',', '.') . "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p>Nenhum produto encontrado.</p>";
            }
            ?>

        </section>
    </div>

    

</body>

</html>