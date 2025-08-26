<?php
// Script para importar o banco de dados
require_once 'includes/config.php';

// Configurações de conexão
$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASS;
$dbname = DB_NAME;

// Caminho para o arquivo SQL
$sqlFile = __DIR__ . '/database/create_database.sql';

try {
    // Verificar se o arquivo SQL existe
    if (!file_exists($sqlFile)) {
        throw new Exception("Arquivo SQL não encontrado: $sqlFile");
    }
    
    echo "<p>Arquivo SQL encontrado: $sqlFile</p>";
    
    // Ler o conteúdo do arquivo SQL
    $sql = file_get_contents($sqlFile);
    if (!$sql) {
        throw new Exception("Não foi possível ler o arquivo SQL");
    }
    
    echo "<p>Conteúdo do arquivo SQL lido com sucesso.</p>";
    
    // Conectar ao MySQL sem selecionar um banco de dados
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>Conexão com o MySQL estabelecida com sucesso.</p>";
    
    // Verificar se o banco de dados já existe
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    $dbExists = $stmt->rowCount() > 0;
    
    if ($dbExists) {
        echo "<p>O banco de dados '$dbname' já existe. Excluindo para recriar...</p>";
        $pdo->exec("DROP DATABASE `$dbname`");
    }
    
    // Criar o banco de dados
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "<p>Banco de dados '$dbname' criado com sucesso.</p>";
    
    // Selecionar o banco de dados
    $pdo->exec("USE `$dbname`");
    echo "<p>Banco de dados '$dbname' selecionado.</p>";
    
    // Dividir o arquivo SQL em comandos individuais
    $queries = explode(';', $sql);
    
    // Executar cada comando SQL
    $count = 0;
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            try {
                $pdo->exec($query);
                $count++;
            } catch (PDOException $e) {
                echo "<p>Erro ao executar consulta: " . $e->getMessage() . "</p>";
                echo "<pre>$query</pre>";
            }
        }
    }
    
    echo "<p>Importação concluída com sucesso! $count consultas executadas.</p>";
    
    // Verificar se as tabelas foram criadas
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Tabelas criadas:</h3>";
    echo "<ul>";
    foreach ($tabelas as $tabela) {
        echo "<li>$tabela</li>";
    }
    echo "</ul>";
    
    echo "<p><a href='index.php'>Voltar para a página inicial</a></p>";
    
} catch (Exception $e) {
    echo "<p>Erro: " . $e->getMessage() . "</p>";
}
?>