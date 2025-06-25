<?php
include '../../backend/database/conexao.php';

header('Content-Type: application/json');

$response = ['erro' => '', 'sucesso' => ''];

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

// Verifica campos obrigatórios
if (!$nome || !$email || !$senha) {
    $response['erro'] = 'Preencha todos os campos.';
    echo json_encode($response);
    exit;
}

// Validação de e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['erro'] = 'E-mail inválido.';
    echo json_encode($response);
    exit;
}

// Upload da imagem (se enviada)
$caminhoRelativoImagem = null;

if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
    $tipoMime = mime_content_type($_FILES['imagem']['tmp_name']);
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if (!in_array($tipoMime, $tiposPermitidos)) {
        $response['erro'] = 'Tipo de imagem inválido. Envie JPG, PNG, GIF ou WEBP.';
        echo json_encode($response);
        exit;
    }

    if (!is_dir(CAMINHO_IMAGENS_FISICO_USUARIOS)) {
        mkdir(CAMINHO_IMAGENS_FISICO_USUARIOS, 0755, true);
    }

    $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
    $nomeImagem = uniqid('img_') . '.' . strtolower($extensao);
    $caminhoCompleto = CAMINHO_IMAGENS_FISICO_USUARIOS . $nomeImagem;

    if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoCompleto)) {
        $response['erro'] = 'Erro ao salvar a imagem.';
        echo json_encode($response);
        exit;
    }

    $caminhoRelativoImagem = '' . $nomeImagem;
}

// Verifica se o e-mail já está cadastrado
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $response['erro'] = 'Este e-mail já está cadastrado.';
    echo json_encode($response);
    exit;
}

$stmt->close();

// Inserir novo usuário
$hash = password_hash($senha, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, foto, data_cadastro) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("ssss", $nome, $email, $hash, $caminhoRelativoImagem);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $response['sucesso'] = 'Usuário cadastrado com sucesso.';
} else {
    $response['erro'] = 'Erro ao cadastrar usuário.';
}

$stmt->close();
echo json_encode($response);
?>
