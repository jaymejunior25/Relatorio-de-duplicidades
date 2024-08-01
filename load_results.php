<?php
$cacheFile = 'cache/results.json';
$cacheTime = 3600; // Cache por uma hora

if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    // Carregar dados do cache
    $data = file_get_contents($cacheFile);
    echo $data;
} else {
    // Configurações de conexão com o banco de dados
    $host = '10.95.2.31'; // endereço do servidor PostgreSQL
    $dbname = 'sbs_prod'; // nome do banco de dados
    $port = "5432";
    $user = 'sbsadmin'; // usuário do banco de dados
    $password = 'sbs2011'; // senha do banco de dados

    // Conexão com o banco de dados PostgreSQL usando PDO
    try {
        $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die(json_encode(['error' => 'Erro na conexão: ' . $e->getMessage()]));
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
        $stmt = $pdo->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die(json_encode(['error' => 'Erro na execução da consulta: ' . $e->getMessage()]));
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

    // Dados a serem retornados
    $data = json_encode([
        'totalRows' => count($result),
        'totalDuplicates' => count($duplicidades),
        'data' => $result
    ]);

    // Salvar dados no cache
    file_put_contents($cacheFile, $data);

    // Retornar os resultados
    echo $data;
}
