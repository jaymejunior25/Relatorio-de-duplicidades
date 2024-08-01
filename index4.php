<?php
// Configurações de conexão com o banco de dados
$host = '10.95.2.31'; // endereço do servidor PostgreSQL
$dbname = 'sbs_prod'; // nome do banco de dados
$port = "5432";
$user = 'sbsadmin'; // usuário do banco de dados
$password = 'sbs2011'; // senha do banco de dados

// Função para executar a consulta com tentativas de repetição
function executarConsulta($pdo, $sql, $tentativas = 3) {
    for ($i = 0; $i < $tentativas; $i++) {
        try {
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            if ($e->getCode() == '40001' || $e->getCode() == '25P02') {
                // Erro de serialização ou transação abortada, esperar e tentar novamente
                sleep(2);
                continue;
            } else {
                throw $e;
            }
        }
    }
    throw new Exception("Falha ao executar a consulta após $tentativas tentativas.");
}

// Conexão com o banco de dados PostgreSQL usando PDO
try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Consulta SQL
$sql = "
    SELECT p.cdpesfis, p.nmpesfis, p.dhnascto, p.nmpesfismae, d.tpdoctoident, d.nrdoctoident, d.cdexpedident, d.dtemissdocto
    FROM pessoafisica p
    INNER JOIN (
        SELECT nmpesfis, dhnascto, nmpesfismae
        FROM pessoafisica
        GROUP BY nmpesfis, dhnascto, nmpesfismae
        HAVING COUNT(*) > 1
    ) dup ON p.nmpesfis = dup.nmpesfis AND p.dhnascto = dup.dhnascto AND p.nmpesfismae = dup.nmpesfismae
    LEFT JOIN doctopessoafisica d ON p.cdpesfis = d.cdpesfis
";

// Execução da consulta com tentativas de repetição
try {
    $result = executarConsulta($pdo, $sql);
} catch (Exception $e) {
    die("Erro na execução da consulta: " . $e->getMessage());
}

// Contar duplicidades
$duplicidades = [];
foreach ($result as $row) {
    $key = $row['nmpesfis'] . ' ' . $row['dhnascto'];
    if (!isset($duplicidades[$key])) {
        $duplicidades[$key] = 1;
    }
}

// Fechar a conexão
$pdo = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Resultados da Consulta</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .theadfixed{
            position: sticky;
            top: 0;
            background-color: rgb(38, 168, 147);
            color: aliceblue;
        }
    </style>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <h1>Resultados da Consulta</h1>
    <p>Total de linhas encontradas: <?php echo count($result); ?></p>
    <p>Duplicidades encontradas: <?php echo count($duplicidades); ?></p>
    <table class="table table-bordered table-hover table-striped" >
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
        <?php foreach ($result as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['cdpesfis']); ?></td>
                <td><?php echo htmlspecialchars($row['nmpesfis']); ?></td>
                <td><?php echo htmlspecialchars(date("d-m-Y", strtotime($row['dhnascto']))); ?></td>
                <td><?php echo htmlspecialchars($row['nmpesfismae']); ?></td>
                <td><?php echo htmlspecialchars($row['tpdoctoident']); ?></td>
                <td><?php echo htmlspecialchars($row['nrdoctoident']); ?></td>
                <td><?php echo htmlspecialchars($row['cdexpedident']); ?></td>
                <td><?php echo htmlspecialchars($row['dtemissdocto']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <div class="fixed-bottom toggle-footer cursor_to_down" id="footer_fixed">
        <div class="fixed-bottom border-top bg-light text-center footer-content p-2" style="z-index:4;">
            <div class="footer-text">
                Desenvolvido com &#128151; por Gerencia de Informatica - GETIN <br>
                <a class="text-reset fw-bold" href="http://www.hemopa.pa.gov.br/site/">© Todos os direitos reservados 2024 Hemopa.</a>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
