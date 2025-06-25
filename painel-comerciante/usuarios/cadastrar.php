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