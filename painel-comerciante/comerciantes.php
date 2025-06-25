<?php
require '../backend/database/conexao.php';
$pag = 'comerciantes';
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
$sqlUser = "SELECT nome, email, telefone, cadastro_completo FROM usuarios WHERE id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $usuario_id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$usuario = $resultUser->fetch_assoc();

// 2️⃣ Buscar dados do comerciante associado ao usuário
$sqlComerciante = "SELECT * FROM comerciantes WHERE usuario_id = ?";
$stmtComerciante = $conn->prepare($sqlComerciante);
$stmtComerciante->bind_param("i", $usuario_id);
$stmtComerciante->execute();
$resultComerciante = $stmtComerciante->get_result();
$comerciante = $resultComerciante->fetch_assoc();

// Se o formulário for enviado (cadastro ou edição)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta os dados do formulário
    $empresa_nome = $_POST['empresa_nome'];
    $cep = $_POST['cep'];
    $endereco = $_POST['endereco'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $telefone = $_POST['telefone'];
    $redes_sociais = $_POST['redes_sociais'];
    $foto = $comerciante['foto'] ?? null; // Foto atual
    $status = 1; // Status ativo por padrão
    $criado_em = date('Y-m-d H:i:s'); // Data e hora de criação

    // Verifica e faz upload da nova foto se enviada
    if (!empty($_FILES['foto']['name'])) {
        // Verifica a extensão da imagem
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        $pastaDestino = '../uploads/usuarios/';  // Diretório correto

        if (in_array($extensao, $extensoesPermitidas)) {
            $nomeFoto = uniqid('comerciante_') . '.' . $extensao; // Gera um nome único para a foto
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
                $foto = 'uploads/usuarios/' . $nomeFoto; // Atualiza o caminho da foto (no banco de dados)
            }
        } else {
            echo "Apenas arquivos de imagem (JPG, JPEG, PNG, GIF) são permitidos.";
        }
    }

    // Verificação se todos os campos obrigatórios foram preenchidos
    if (!empty($empresa_nome) && !empty($cep) && !empty($endereco) && !empty($cidade) && !empty($estado) && !empty($telefone)) {
        // Altera o campo cadastro_completo para 1, já que o cadastro está completo
        $cadastro_completo = 1;
    } else {
        // Se algum campo obrigatório não foi preenchido, mantenha o cadastro_completo como 0
        $cadastro_completo = 0;
    }

    // Inserção ou atualização no banco de dados para comerciante
    if (!$comerciante) {
        $sql = "INSERT INTO comerciantes (usuario_id, empresa_nome, cep, endereco, cidade, estado, telefone, redes_sociais, foto, status, criado_em) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssssis", $usuario_id, $empresa_nome, $cep, $endereco, $cidade, $estado, $telefone, $redes_sociais, $foto, $status, $criado_em);
    } else {
        $sql = "UPDATE comerciantes SET empresa_nome=?, cep=?, endereco=?, cidade=?, estado=?, telefone=?, redes_sociais=?, foto=? WHERE usuario_id=?";
        $stmt = $conn->prepare($sql);
        // Se a foto não for enviada, passamos NULL para o parâmetro de foto
        $fotoParam = $foto ?: NULL;
        $stmt->bind_param("ssssssssi", $empresa_nome, $cep, $endereco, $cidade, $estado, $telefone, $redes_sociais, $fotoParam, $usuario_id);
    }

    // Executa a query
    $stmt->execute();

    // Atualizar o campo cadastro_completo na tabela usuarios
    $sqlUpdateCadastro = "UPDATE usuarios SET cadastro_completo = ? WHERE id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdateCadastro);
    $stmtUpdate->bind_param("ii", $cadastro_completo, $usuario_id);
    $stmtUpdate->execute();

    // Redireciona após salvar
    header("Location: index.php?pagina=<?php echo $menu2 ?>?msg=Dados atualizados com sucesso!");
    exit();
}
?>



<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <!-- Incluindo o CSS da biblioteca intl-tel-input -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css">
    <link rel="stylesheet" href="../assets/css/dasshboard.css">
    <link rel="stylesheet" href="../assets/css/formularios.css">
    <!-- Incluindo o JavaScript da biblioteca intl-tel-input -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <title>Gerenciar Cadastro</title>
    

</head>

<body>

    

    <form action="comerciantes.php" method="POST" enctype="multipart/form-data">
        <h2><?= empty($comerciante) ? 'Complete seu cadastro' : 'Edite seu perfil' ?></h2>

        <div class="container_image">


            <?php
            if (!empty($comerciante['foto'])) : ?>
                <img id="imagePreview" src="../<?= $comerciante['foto']; ?>" width="150"><br>
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
                <!-- Dados do usuário -->

                <div class="form-group">
                    <label>Nome do Usuário:</label>
                    <input type="text" name="nome_usuario" value="<?= $usuario['nome'] ?>" disabled><br>
                </div>

                <div class="form-group">
                    <label>Email do Usuário:</label>
                    <input type="email" name="email" value="<?= $usuario['email'] ?>" disabled><br>
                </div>


                <!-- Dados do comerciante -->
                <div class="form-group">
                    <label>Nome da Empresa:</label>
                    <input type="text" name="empresa_nome" value="<?= $comerciante['empresa_nome'] ?? '' ?>" required><br>

                </div>

                <div class="form-group">
                    <label for="telefone">Telefone:</label>
                    <div class="tel">

                        <input
                            type="tel"
                            id="telefone"
                            name="telefone"
                            value="<?= $comerciante['telefone'] ?? $usuario['telefone'] ?? '' ?>"
                            required
                            placeholder="Telefone"

                            title="Por favor, insira um número de telefone válido."
                            maxlength="20">
                    </div>
                </div>
            </div>

            <div class="grupo">
                <div class="form-group">
                    <label>CEP:</label>
                    <input type="text" name="cep" id="cep" value="<?= $comerciante['cep'] ?? '' ?>" required><br>
                </div>

                <div class="form-group">
                    <label>Endereço:</label>
                    <input type="text" name="endereco" id="endereco" value="<?= $comerciante['endereco'] ?? '' ?>" required><br>
                </div>


                <div class="form-group">
                    <label>Cidade:</label>
                    <input type="text" name="cidade" id="cidade" value="<?= $comerciante['cidade'] ?? '' ?>" required><br>
                </div>

                <div class="form-group">
                    <label>Estado:</label>
                    <input type="text" name="estado" id="estado" value="<?= $comerciante['estado'] ?? '' ?>" required><br>
                </div>

                <div class="form-group">
                    <label>Redes Sociais:</label>
                    <input type="text" name="redes_sociais" value="<?= $comerciante['redes_sociais'] ?? '' ?>"><br>
                </div>
            </div>


        </div>

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
                                document.getElementById('endereco').value = data.logradouro;
                                document.getElementById('cidade').value = data.localidade;
                                document.getElementById('estado').value = data.uf;
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
            var input = document.querySelector("#telefone");
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





        <button id="button_cad" type="submit"><?= $comerciante ? 'Salvar Alterações' : 'Cadastrar' ?></button>
    </form>

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