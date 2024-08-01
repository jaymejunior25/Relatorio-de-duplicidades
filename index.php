<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Duplicidades SBS</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    
        <div id="welcome-message">
            <h1>Bem-vindo ao Painel de Duplicidades SBS</h1>
            <p>Carregando dados, por favor, aguarde...</p>
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: 0%;" id="progress-bar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
        <div id="content" class="d-none">
            <h1>Resultados da Consulta</h1>
            <p>Total de linhas encontradas: <span id="total-rows"></span></p>
            <p>Duplicidades encontradas: <span id="total-duplicates"></span></p>
            <table class="table table-bordered table-hover table-striped table-rounded">
                <thead class="theadfixed">
                    <tr>
                        <th>CDPESFIS</th>
                        <th>Nome</th>
                        <th>Data de Nascimento</th>
                        <th>Nome da Mãe</th>
                        <th>Tipo de Documento</th>
                        <th>Número do Documento</th>
                        <th>Órgão Expedidor</th>
                        <th>Data de Emissão</th>
                    </tr>
                </thead>
                <tbody id="results-table-body"></tbody>
            </table>

        </div>
        <div class="fixed-bottom toggle-footer cursor_to_down" id="footer_fixed">
            <div class="fixed-bottom border-top bg-light text-center footer-content p-2" style="z-index:4;">
                <div class="footer-text">
                    Desenvolvido com &#128151; por Gerencia de Informatica - GETIN <br>
                    <a class="text-reset fw-bold" href="http://www.hemopa.pa.gov.br/site/">© Todos os direitos reservados 2024 Hemopa.</a>
                </div>
            </div>
        </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
