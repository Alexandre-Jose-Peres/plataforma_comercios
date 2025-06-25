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
        header("Location: comercio.php?msg=Dados atualizados com sucesso!"); // Redireciona após salvar
        exit();
    } else {
        // Se ocorrer um erro ao salvar, exibe a mensagem
        echo "Erro ao executar a query: " . $stmt->error;
        exit();
    }
}
?>