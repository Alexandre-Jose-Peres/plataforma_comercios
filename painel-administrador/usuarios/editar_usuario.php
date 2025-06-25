<?php
header('Content-Type: application/json');
include '../../backend/database/conexao.php'; // Usa $conn

$response = ['erro' => '', 'sucesso' => ''];

// Buscar usuário para edição
if (isset($_GET['buscar_usuario'])) {
    $id = (int)$_GET['buscar_usuario'];

    $stmt = $conn->prepare("SELECT id, nome, email, foto AS imagem FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();

    echo json_encode($usuario ?: [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Atualizar usuário via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($nome && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Verifica se o e-mail já está em uso por outro usuário
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            http_response_code(400);
            $response['erro'] = 'Este e-mail já está cadastrado.';
            echo json_encode($response);
            exit;
        }
        $stmt->close();

        $sql = "UPDATE usuarios SET nome = ?, email = ?";
        $params = [$nome, $email];
        $types = "ss";

        if (!empty($senha)) {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql .= ", senha = ?";
            $params[] = $hash;
            $types .= "s";
        }

        // Upload da nova imagem
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['imagem']['tmp_name'];
            $mimeType = mime_content_type($tmpName);
            $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (!in_array($mimeType, $tiposPermitidos)) {
                http_response_code(400);
                echo json_encode(['erro' => 'Tipo de imagem inválido. Use JPG, PNG, GIF ou WEBP.']);
                exit;
            }

            // Apaga imagem antiga
            $stmt = $conn->prepare("SELECT foto FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $dados = $resultado->fetch_assoc();

            if (!empty($dados['foto'])) {
                $caminhoAntigo = str_replace(URL_IMAGENS_USUARIOS, CAMINHO_IMAGENS_FISICO_USUARIOS, $dados['foto']);
                if (file_exists($caminhoAntigo)) {
                    unlink($caminhoAntigo);
                }
            }

            // Salva nova imagem
            $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            $nomeImagem = uniqid('img_') . '.' . strtolower($extensao);
            $caminhoImagemCompleto = CAMINHO_IMAGENS_FISICO_USUARIOS . $nomeImagem;

            if (!is_dir(CAMINHO_IMAGENS_FISICO_USUARIOS)) {
                mkdir(CAMINHO_IMAGENS_FISICO_USUARIOS, 0755, true);
            }

            if (!move_uploaded_file($tmpName, $caminhoImagemCompleto)) {
                http_response_code(500);
                echo json_encode(['erro' => 'Erro ao salvar a nova imagem.']);
                exit;
            }

            $caminhoRelativo = '' . $nomeImagem;
            $sql .= ", foto = ?";
            $params[] = $caminhoRelativo;
            $types .= "s";
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        $response['sucesso'] = 'Usuário atualizado com sucesso.';
        echo json_encode($response);
        exit;
    }

    http_response_code(400);
    $response['erro'] = 'Dados inválidos.';
    echo json_encode($response);
    exit;
}
