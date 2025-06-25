<?php
require '../database/conexao.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if (isset($_POST['email']) && !empty($_POST['email'])) {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(50));
    $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Verifica se o e-mail existe na base de dados
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Atualiza o token no banco
        $stmtUpdate = $conn->prepare("UPDATE usuarios SET token_recuperacao = ?, token_expira = ? WHERE email = ?");
        $stmtUpdate->bind_param("sss", $token, $expira, $email);
        $stmtUpdate->execute();

        // Gera o link
        $base_url = "http://localhost/plataforma_comercios/";
        $link = $base_url . "pages/recuperar/nova_senha.php?token=$token";

        // Envia o e-mail
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'ajpprodutos50@gmail.com';
            $mail->Password   = 'lavbqzrdfpmnzizd'; // Considere usar variável de ambiente!
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('ajpprodutos50@gmail.com', 'Plataforma Comercios');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Recuperação de Senha';
            $mail->Body    = "
                <p>Você solicitou a redefinição de senha.</p>
                <p><strong>Clique no link abaixo para criar uma nova senha:</strong></p>
                <p><a href='$link'>$link</a></p>
                <p>Este link irá expirar em 1 hora.</p>
            ";

            $mail->send();

            $_SESSION['mensagem'] = "Link de recuperação enviado. Verifique seu e-mail.";
            $_SESSION['tipo_mensagem'] = "sucesso";
        } catch (Exception $e) {
            $_SESSION['mensagem'] = "Erro ao enviar o e-mail: {$mail->ErrorInfo}";
            $_SESSION['tipo_mensagem'] = "erro";
        }
    } else {
        $_SESSION['mensagem'] = "E-mail não encontrado no sistema.";
        $_SESSION['tipo_mensagem'] = "erro";
    }
} else {
    $_SESSION['mensagem'] = "Informe um e-mail válido.";
    $_SESSION['tipo_mensagem'] = "erro";
}

// Redireciona de volta para a página de solicitação
header("Location: ../../pages/recuperar/solicitar.php");
exit;
