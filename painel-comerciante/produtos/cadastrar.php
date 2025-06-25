<?php
require '../../backend/database/conexao.php';

// Inicia a sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado! Faça login.']);
    exit();
}

// Captura o ID do usuário logado
$usuario_id = $_SESSION['user_id']; 

// Busca o comerciante associado ao usuário
$sql_comerciante = "SELECT id FROM comerciantes WHERE usuario_id = ?";
$stmt_comerciante = $conn->prepare($sql_comerciante);
$stmt_comerciante->bind_param("i", $usuario_id);
$stmt_comerciante->execute();
$result_comerciante = $stmt_comerciante->get_result();

if ($result_comerciante->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Erro: Este usuário não possui um comerciante associado.']);
    exit();
}

$comerciante = $result_comerciante->fetch_assoc();
$comerciante_id = $comerciante['id'];  

// Buscar um comércio associado ao comerciante
$sql_comercio = "SELECT id FROM comercios WHERE comerciante_id = ?";
$stmt_comercio = $conn->prepare($sql_comercio);
$stmt_comercio->bind_param("i", $comerciante_id);
$stmt_comercio->execute();
$result_comercio = $stmt_comercio->get_result();

if ($result_comercio->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Erro: Este comerciante não possui um comércio associado.']);
    exit();
}

$comercio = $result_comercio->fetch_assoc();
$comercio_id = $comercio['id'];  

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $preco = floatval($_POST['preco']);
    $categoria = trim($_POST['categoria']);
    $status = $_POST['status'];

    // Validação dos dados
    if (empty($nome) || empty($descricao) || $preco <= 0  || empty($categoria) || !in_array($status, ['ativo', 'inativo'])) {
        echo json_encode(['status' => 'error', 'message' => 'Erro: Dados inválidos, preencha corretamente.']);
        exit();
    }

    // Processar upload de imagem
    $uploads = [];
    if (!empty($_FILES['arquivos']['name'][0])) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/plataforma_comercios/uploads/produtos/"; // Correção do caminho
        
        foreach ($_FILES['arquivos']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['arquivos']['name'][$key];
            $file_tmp = $_FILES['arquivos']['tmp_name'][$key];
            $file_type = $_FILES['arquivos']['type'][$key];
            $extensao = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $nome_arquivo = uniqid() . '.' . $extensao;
            $destino = $upload_dir . $nome_arquivo;
            $destino_banco = "/uploads/produtos/" . $nome_arquivo; // Ajuste aqui para o caminho relativo

            $tipos_validos = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/avi'];

            // Validação de tipo de arquivo
            if (in_array($file_type, $tipos_validos)) {
                $max_size = 5 * 1024 * 1024; // 5MB
                if ($_FILES['arquivos']['size'][$key] > $max_size) {
                    echo json_encode(['status' => 'error', 'message' => "Arquivo muito grande: $file_name"]);
                    exit();
                }

                if (move_uploaded_file($file_tmp, $destino)) {
                    $uploads[] = $destino_banco;
                } else {
                    echo json_encode(['status' => 'error', 'message' => "Erro ao mover o arquivo: $file_name"]);
                    exit();
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => "Arquivo inválido: $file_name ($file_type)"]);
                exit();
            }
        }
    }

    // Converte a lista de arquivos para string
    $uploads_str = !empty($uploads) ? implode(",", $uploads) : null;

    // Inserir produto no banco de dados
    $sql = "INSERT INTO produtos (nome, descricao, preco, foto, categoria, status, comerciante_id, comercio_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsssii", $nome, $descricao, $preco, $uploads_str, $categoria, $status, $comerciante_id, $comercio_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Produto cadastrado com sucesso!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao cadastrar o produto.']);
    }

    $stmt->close();
}

$conn->close();
?>
