<?php
require '../backend/database/conexao.php';
$pag = 'comercio';


// Inicia a sessão apenas se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifique se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../usuarios/login.php?msg=Acesso negado! Faça login.");
    exit();
}

$usuario_id = $_SESSION['user_id'];

// 1️⃣ Buscar os dados do usuário logado
$sqlUser = "SELECT nome, email, telefone, cadastro_completo, cadastro_comercio
 FROM usuarios WHERE id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $usuario_id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$usuario = $resultUser->fetch_assoc();

// 2️⃣ Verificar se o comerciante existe na tabela 'comerciantes'
$sqlComerciante = "SELECT id FROM comerciantes WHERE usuario_id = ?";
$stmtComerciante = $conn->prepare($sqlComerciante);
$stmtComerciante->bind_param("i", $usuario_id);
$stmtComerciante->execute();
$resultComerciante = $stmtComerciante->get_result();

// Se o comerciante não existir, criamos um novo comerciante
if ($resultComerciante->num_rows == 0) {
    // Criar o comerciante, pois não existe
    $sqlInserirComerciante = "INSERT INTO comerciantes (usuario_id) VALUES (?)";
    $stmtInserirComerciante = $conn->prepare($sqlInserirComerciante);
    $stmtInserirComerciante->bind_param("i", $usuario_id);
    if ($stmtInserirComerciante->execute()) {
        // Pegando o id do comerciante recém-criado
        $comerciante_id = $stmtInserirComerciante->insert_id;
    } else {
        echo "Erro ao criar comerciante.";
        exit();
    }
} else {
    // Comerciante já existe, pegamos o id
    $comerciante = $resultComerciante->fetch_assoc();
    $comerciante_id = $comerciante['id'];
}

// 3️⃣ Buscar dados do comerciante associado ao usuário na tabela comercios
$sqlComercio = "SELECT * FROM comercios WHERE comerciante_id = ?";
$stmtComercio = $conn->prepare($sqlComercio);
$stmtComercio->bind_param("i", $comerciante_id);
$stmtComercio->execute();
$resultComercio = $stmtComercio->get_result();
$comercio = $resultComercio->fetch_assoc();

// Se o formulário for enviado (cadastro ou edição)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta os dados do formulário
    $nome = $_POST['nome'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $endereco = $_POST['endereco_comercio'] ?? '';
    $cep = $_POST['cep'] ?? '';
    $cidade = $_POST['cidade_comercio'] ?? '';
    $estado = $_POST['estado_comercio'] ?? '';
    $telefone = $_POST['telefone_comercio'] ?? '';
    $email = $_POST['email_comercio'] ?? '';
    $site = $_POST['site'] ?? '';
    $horario_func = $_POST['horario_func'] ?? '';
    $categoria = $_POST['categoria'] ?? '';

    // Foto atual (caso já exista)
    $foto = $comercio['foto'] ?? null;

    // Status ativo por padrão
    $status = 1;

    // Data e hora de criação ou atualização
    $criado_em = date('Y-m-d H:i:s');

    // Verifica e faz upload da nova foto se enviada
    if (!empty($_FILES['foto']['name'])) {
        // Verifica a extensão da imagem
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        $pastaDestino = '../uploads/comercio/';  // Diretório correto

        if (in_array($extensao, $extensoesPermitidas)) {
            $nomeFoto = uniqid('comercio_') . '.' . $extensao; // Gera um nome único para a foto
            $caminhoFoto = $pastaDestino . $nomeFoto;

            // Verifica se a pasta existe, caso contrário cria
            if (!is_dir($pastaDestino)) {
                mkdir($pastaDestino, 0777, true);
            }

            // Move a foto para o diretório
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminhoFoto)) {
                // Se já existir uma foto anterior, exclui ela
                if ($foto && file_exists("../" . $foto)) {
                    unlink("../" . $foto);
                }
                $foto = '../uploads/comercio/' . $nomeFoto; // Atualiza o caminho da foto (no banco de dados)
            }
        } else {
            echo "Apenas arquivos de imagem (JPG, JPEG, PNG, GIF) são permitidos.";
        }
    }

    // Verificação se todos os campos obrigatórios foram preenchidos
    if (!empty($nome) && !empty($descricao) && !empty($endereco) && !empty($cep) && !empty($cidade) && !empty($estado) && !empty($telefone)) {
        // Atualizar o campo $cadastro_comercio para 1, já que o cadastro está completo
        $cadastro_comercio = 1;
    } else {
        // Se algum campo obrigatório não foi preenchido, mantenha o $cadastro_comercio como 0
        $cadastro_comercio = 0;
    }

    // Se o comércio não existe, inserimos um novo
    if (!$comercio) {
        $sql = "INSERT INTO comercios (comerciante_id, nome, descricao, cep, endereco, cidade, estado, telefone, email, site, horario_func, categoria, foto, status, criado_em) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        // Bind dos parâmetros
        $stmt->bind_param("issssssssssissi", $comerciante_id, $nome, $descricao, $cep, $endereco, $cidade, $estado, $telefone, $email, $site, $horario_func, $categoria, $foto, $status, $criado_em);
    } else {
        // Se o comércio já existe, fazemos uma atualização
        $sql = "UPDATE comercios SET nome=?, descricao=?, cep=?, endereco=?, cidade=?, estado=?, telefone=?, email=?, site=?, horario_func=?, categoria=?, foto=? WHERE comerciante_id=?";
        $stmt = $conn->prepare($sql);

        // Se a foto não for enviada, passamos NULL para o parâmetro de foto
        $fotoParam = $foto ?: NULL;

        // Bind dos parâmetros para atualização
        $stmt->bind_param("ssssssssssssi", $nome, $descricao, $cep, $endereco, $cidade, $estado, $telefone, $email, $site, $horario_func, $categoria, $fotoParam, $comerciante_id);
    }

    // Verificar a execução da query
    if ($stmt->execute()) {
        // Atualizar o campo $cadastro_comercio na tabela usuarios
        $sqlUpdateCadastro = "UPDATE usuarios SET cadastro_comercio = ? WHERE id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdateCadastro);
        $stmtUpdate->bind_param("ii", $cadastro_comercio, $usuario_id);
        $stmtUpdate->execute();

        // Se a execução for bem-sucedida, redireciona
       header("Location: index.php?pagina=<?php echo $menu3 ?>&msg=Dados atualizados com sucesso!");
 // Redireciona após salvar
        exit();
    } else {
        // Se ocorrer um erro ao salvar, exibe a mensagem
        echo "Erro ao executar a query: " . $stmt->error;
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css">
    <title>Cadastrar Comércio</title>
    <link rel="stylesheet" href="../assets/css/formularios.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    


    <?php if (!empty($erro_msg)): ?>
        <div class="erro"><?php echo htmlspecialchars($erro_msg); ?></div>
    <?php endif; ?>

    <form action="comercio.php" method="POST" enctype="multipart/form-data">
        <h2><?= $comercio ? 'Edite os dados do seu Comércio' : 'Cadastre o Seu Comércio' ?></h2>

        <div class="container_image">

            <?php
            if (!empty($comercio['foto'])) : ?>
                <img id="imagePreview" src="<?= $comercio['foto']; ?>" width="150"><br>

            <?php else : ?>

                <img id="imagePreview" src="../uploads/perfil/usuário.png" width="150px" alt="Imagem padrão">
            <?php endif; ?>


            <div id="uploadArea" class="upload-area" ondrop="handleDrop(event)" ondragover="allowDrop(event)">
                <label for="image" class="custom-file-upload">
                    Selecione uma imagem aqui
                </label>
                <input id="image" type="file" name="foto" accept="image/*" onchange="previewImage(event)" />
                <br>
            </div>
        </div>

        <div class="form-section">
            <div class="grupo">

                <?php // Captura a categoria do banco de dados ou usa um valor padrão se não existir
                // Captura a categoria do banco de dados ou usa um valor padrão se não existir
                $categoria = $comercio['categoria'] ?? ''; // Se não existir categoria, atribui vazio


                // Verifica se a categoria é '0' (string) ou vazia
                if ($categoria === '0' || empty($categoria)) {
                    $categoria = ''; // Ou atribua um valor padrão, como por exemplo 'Categoria padrão'
                }

                ?>
                <!-- Dados do comerciante -->
                <div class="form-group">
                    <label for="nome">Nome do Comércio</label>
                    <input type="text" name="nome" id="nome" value="<?= $comercio['nome'] ?? '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="email_comercio">E-mail</label>
                    <input type="email" name="email_comercio" id="email_comercio" value="<?= $usuario['email'] ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <input type="text" name="descricao" id="descricao" value="<?= $comercio['descricao'] ?? '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="cep">CEP</label>
                    <input type="text" name="cep" id="cep" value="<?= $comercio['cep'] ?? '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="endereco_comercio">Endereço</label>
                    <input type="text" name="endereco_comercio" id="endereco_comercio" value="<?= $comercio['endereco'] ?? '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="cidade_comercio">Cidade</label>
                    <input type="text" name="cidade_comercio" id="cidade_comercio" value="<?= $comercio['cidade'] ?? '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="estado_comercio">Estado</label>
                    <input type="text" name="estado_comercio" id="estado_comercio" value="<?= $comercio['estado'] ?? '' ?>" required>
                </div>
            </div>

            <div class="grupo">
                <div class="form-group">
                    <label for="telefone_comercio">Telefone</label>
                    <input type="text" name="telefone_comercio" id="telefone_comercio" value="<?= $comercio['telefone'] ?? '' ?>" required>
                </div>



                <div class="form-group">
                    <label for="site">Site</label>
                    <input type="text" name="site" id="site" value="<?= $comercio['site'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label for="horario_func">Horário de Funcionamento</label>
                    <input type="text" name="horario_func" id="horario_func" value="<?= $comercio['horario_func'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label for="categoria">Categoria</label>
                    <select name="categoria" id="categoria" required>
                        <?php
                        $categorias = [
                            '0' => 'Selecione uma Categoria',
                            '1' => 'Supermercados',
                            '2' => 'Farmácias',
                            '3' => 'Restaurantes',
                            '4' => 'Lojas de Roupas',
                            '5' => 'Papelarias',
                            '6' => 'Academias',
                            '7' => 'Salões de Beleza',
                            '8' => 'Oficinas Mecânicas',
                            '9' => 'Pet Shops',
                            '10' => 'Pizzarias',
                            '11' => 'Lanchonetes',
                            '12' => 'Hotéis',
                            '13' => 'Bancos',
                            '14' => 'Consultórios Médicos',
                            '15' => 'Clínicas Odontológicas',
                            '16' => 'Escolas',
                            '17' => 'Academias de Dança',
                            '18' => 'Livrarias',
                            '19' => 'Quiosques de Internet',
                            '20' => 'Cabeleireiros',
                            '21' => 'Imobiliárias',
                            '22' => 'Postos de Combustíveis',
                            '23' => 'Bares',
                            '24' => 'Lojas de Eletrônicos',
                            '25' => 'Mercados e Feiras',
                            '26' => 'Armazéns',
                            '27' => 'Consultoria Empresarial',
                            '28' => 'Serviços de Design Gráfico',
                            '29' => 'Fotógrafos',
                            '30' => 'Eventos e Festas',
                            '31' => 'Autopeças',
                            '32' => 'Lojas de Informática',
                            '33' => 'Restaurantes Self-Service',
                            '34' => 'Agências de Viagens',
                            '35' => 'Clínicas de Estética',
                            '36' => 'Açougues',
                            '37' => 'Lavanderias',
                            '38' => 'Consertos de Eletrodomésticos',
                            '39' => 'Joalherias',
                            '40' => 'Relojoarias',
                            '41' => 'Hortifrúti',
                            '42' => 'Bancas de Jornal',
                            '43' => 'Petrolíferas',
                            '44' => 'Serviços de Transporte',
                            '45' => 'Estúdios de Gravação',
                            '46' => 'Ateliês de Arte',
                            '47' => 'Casas de Chá',
                            '48' => 'Bancos de Leite Humano',
                            '49' => 'Cursos de Idiomas',
                            '50' => 'Cooperativas de Trabalho',

                        ];
                        foreach ($categorias as $key => $value) {
                            $selected = ($categoria == $key) ? 'selected' : '';
                            echo "<option value=\"$key\" $selected>$value</option>";
                        }
                        ?>
                    </select>
                </div>

            </div>
        </div>

        <button id="button_cad" type="submit"><?= $comercio ? 'Salvar Alterações' : 'Cadastrar' ?></button>
    </form>


    <script>
        // Função para formatar o campo do CEP
        function formatCep(cep) {
            cep = cep.replace(/\D/g, ''); // Remove qualquer coisa que não seja número
            if (cep.length > 5) {
                cep = cep.slice(0, 5) + '-' + cep.slice(5, 8); // Adiciona o hífen após os 5 primeiros números
            }
            return cep;
        }


        // Evento de formatação ao digitar no campo CEP
        document.getElementById('cep').addEventListener('input', function() {
            var cep = this.value.replace(/\D/g, ''); // Remove qualquer coisa que não seja número
            this.value = formatCep(cep); // Aplica a formatação ao valor
        });
        document.getElementById('cep').addEventListener('blur', function() {
            var cep = this.value.replace(/\D/g, ''); // Remove tudo que não for número
            if (cep.length === 8) { // Verifica se o CEP tem 8 caracteres
                var url = `https://viacep.com.br/ws/${cep}/json/`;

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('endereco_comercio').value = data.logradouro;
                            document.getElementById('cidade_comercio').value = data.localidade;
                            document.getElementById('estado_comercio').value = data.uf;
                        } else {
                            alert('CEP não encontrado.');
                        }
                    })
                    .catch(error => alert('Erro ao buscar CEP.'));
            } else {
                alert('CEP inválido.');
            }
        });
    </script>



    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script>
        // Inicializando o campo de telefone com a biblioteca intl-tel-input
        var input = document.querySelector("#telefone_comercio");
        var iti = intlTelInput(input, {
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js", // Script para utilitários de formatação
            initialCountry: "br", // Define o Brasil como país inicial (com código +55)
            preferredCountries: ["br"], // Países preferenciais (Brasil)
            separateDialCode: true, // Exibe o código do país separado
        });

        // Evento de alteração no campo de telefone (input)
        input.addEventListener('input', function() {
            var phoneNumber = iti.getNumber(); // Obter o número completo (incluindo o código do país)
            var countryCode = iti.getSelectedCountryData().dialCode; // Obter o código do país
            document.getElementById("codigo_p").value = countryCode; // Armazena o código do país no campo oculto
            console.log("Número completo: " + phoneNumber); // Exibe o número completo
            console.log("Código do país: " + countryCode); // Exibe o código do país
        });

        // Caso o campo de telefone já tenha um número preenchido (e.g., do banco de dados), garante que o código do Brasil seja incluído
        var phoneValue = input.value;
        if (phoneValue && !phoneValue.startsWith("+55")) {
            input.value = "+55" + phoneValue; // Adiciona o código do Brasil (+55) se não estiver presente
        }
    </script>

    <script>
        // Função para permitir o evento de arrastar
        function allowDrop(event) {
            event.preventDefault();
            // Adiciona classe visual para quando o arquivo está sendo arrastado
            document.getElementById('uploadArea').classList.add('dragover');
        }

        // Função para remover a classe de arraste quando o usuário deixa de arrastar
        function handleDrop(event) {
            event.preventDefault();
            document.getElementById('uploadArea').classList.remove('dragover');

            const file = event.dataTransfer.files[0];
            if (file) {
                previewFile(file);
            }
        }

        // Função para tratar a seleção do arquivo via input
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                previewFile(file);
            }
        }

        // Função para mostrar a imagem selecionada ou arrastada
        function previewFile(file) {
            const reader = new FileReader();
            const imagePreview = document.getElementById('imagePreview');

            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block'; // Exibe a imagem
                image.style.Width = '100%';
                image.style.maxHeight = '100%';
            };

            reader.readAsDataURL(file);
        }
    </script>
</body>

</html>