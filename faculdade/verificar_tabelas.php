<?php
require_once 'includes/config.php';

// Configurações de conexão
$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASS;
$dbname = DB_NAME;

try {
    // Conectar ao banco de dados
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obter lista de tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Tabelas no banco de dados '$dbname':</h2>";
    echo "<ul>";
    if (count($tabelas) > 0) {
        foreach ($tabelas as $tabela) {
            echo "<li>$tabela</li>";
        }
    } else {
        echo "<li>Nenhuma tabela encontrada</li>";
    }
    echo "</ul>";
    
    // Verificar se as tabelas necessárias existem
    $tabelasNecessarias = ['produto', 'categoria', 'marca', 'fornecedor'];
    $tabelasFaltando = [];
    
    foreach ($tabelasNecessarias as $tabela) {
        if (!in_array($tabela, $tabelas)) {
            $tabelasFaltando[] = $tabela;
        }
    }
    
    if (count($tabelasFaltando) > 0) {
        echo "<h3>Tabelas necessárias que estão faltando:</h3>";
        echo "<ul>";
        foreach ($tabelasFaltando as $tabela) {
            echo "<li>$tabela</li>";
        }
        echo "</ul>";
        
        echo "<p><a href='importar_db.php'>Clique aqui para importar o banco de dados novamente</a></p>";
    } else {
        echo "<p>Todas as tabelas necessárias estão presentes.</p>";
    }
    
} catch (Exception $e) {
    echo "<p>Erro: " . $e->getMessage() . "</p>";
}
?>