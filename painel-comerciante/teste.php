<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Telefone com Código de País</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css">
    <style>
        .iti {
            width: 100%;
            max-width: 400px;
            margin: 20px auto;
        }
    </style>
</head>
<body>

    <div class="iti">
        <input id="phone" type="tel" name="phone">
    </div>

    <script>
        const input = document.querySelector("#phone");

        intlTelInput(input, {
            initialCountry: "br", // Define o Brasil como país inicial
            preferredCountries: ['br', 'us', 'pt'], // Países preferenciais
            separateDialCode: true, // Mostrar o código do país separado
        });
    </script>

</body>
</html>
