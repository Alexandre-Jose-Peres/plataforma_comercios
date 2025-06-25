<?php
$servername = "localhost";
$username = "root"; // Altere conforme necessário
$password = ""; // Altere conforme necessário
$dbname = "vitrinedevendas";

// Criando conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificando conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Define a URL base
$url_base = "http://{$_SERVER['HTTP_HOST']}/";

// Ajuste para ambiente local com subpasta
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    $url_base .= 'plataforma_comercios/';
}

// Caminhos para imagens
if (!defined('URL_IMAGENS_USUARIOS')) {
    define('URL_IMAGENS_USUARIOS', $url_base . 'uploads/usuarios/');
}

if (!defined('CAMINHO_IMAGENS_FISICO_USUARIOS')) {
    define('CAMINHO_IMAGENS_FISICO_USUARIOS', __DIR__ . '../../../uploads/usuarios/');
}

$nome_sistema = "SHOP SPACE";

$telefone_sistema = "(35) 99800-9831";
$endereco_sistema = "Rua Tancredo Neves, Nº 179 ";
$enderco_cidade = "Delfim Moreira - MG";
$rodape_relatorios = "";
$cnpj_sistema = '27.107.588/0001-28';
$fonte_comprovante = '11';
$icone_sistema = '../uploads/logo1.png'; // Caminho relativo para o ícone
$icone_index = 'uploads/logo1.png';
?>