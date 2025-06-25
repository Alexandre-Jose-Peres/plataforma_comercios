<?php
// Gerando uma chave de 32 bytes aleatórios para o AES-256-CBC
$key = bin2hex(random_bytes(32)); // Converte para uma string hexadecimal

echo $key;
?>