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
    SELECT p.*, dup.cdpesfis
    FROM pessoafisica p
    INNER JOIN (
        SELECT nmpesfis, dhnascto, nmpesfismae, string_agg(cdpesfis::text, ', ') AS cdpesfis
        FROM pessoafisica
        GROUP BY nmpesfis, dhnascto, nmpesfismae
        HAVING COUNT(*) > 1
    ) dup ON p.nmpesfis = dup.nmpesfis AND p.dhnascto = dup.dhnascto AND p.nmpesfismae = dup.nmpesfismae
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
    </style>
</head>
<body>
    <h1>Resultados da Consulta</h1>
    
    <p>Duplicidades encontradas: <?php echo count($duplicidades); ?></p>
    <table>
        <tr>
            <th>CDPESFIS</th>
            <th>Nome</th>
            <th>Data de Nascimento</th>
            <th>Nome da Mãe</th>
        </tr>
        <?php foreach ($result as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['cdpesfis']); ?></td>
                <td><?php echo htmlspecialchars($row['nmpesfis']); ?></td>
                <td><?php echo htmlspecialchars($row['dhnascto']); ?></td>
                <td><?php echo htmlspecialchars($row['nmpesfismae']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
