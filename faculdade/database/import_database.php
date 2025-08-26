<?php
/**
 * Script para importar o banco de dados
 * Este script cria o banco de dados e importa as tabelas e dados iniciais
 */

// Configurações de conexão
$host = 'localhost';
$user = 'root'; // Usuário padrão do XAMPP
$pass = '';     // Senha padrão do XAMPP (vazia)

// Nome do banco de dados
$dbname = 'faculdade';

// Caminho para o arquivo SQL
$sqlFile = __DIR__ . '/create_database.sql';

try {
    // Verificar se o arquivo SQL existe
    if (!file_exists($sqlFile)) {
        throw new Exception("Arquivo SQL não encontrado: $sqlFile");
    }
    
    // Ler o conteúdo do arquivo SQL
    $sql = file_get_contents($sqlFile);
    if (!$sql) {
        throw new Exception("Não foi possível ler o arquivo SQL");
    }
    
    // Conectar ao MySQL sem selecionar um banco de dados
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar o banco de dados se não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "<p>Banco de dados '$dbname' criado ou já existente.</p>";
    
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
            $pdo->exec($query);
            $count++;
        }
    }
    
    echo "<p>Importação concluída com sucesso! $count comandos SQL executados.</p>";
    echo "<p><strong>Banco de dados '$dbname' está pronto para uso!</strong></p>";
    echo "<p><a href='../index.php' class='btn btn-primary'>Ir para o sistema</a></p>";
    
} catch (PDOException $e) {
    die("<p>Erro de conexão ou SQL: " . $e->getMessage() . "</p>");
} catch (Exception $e) {
    die("<p>Erro: " . $e->getMessage() . "</p>");
}

// Estilo básico para a página
echo <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importação do Banco de Dados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            color: #0d6efd;
            margin-bottom: 20px;
        }
        p {
            margin-bottom: 15px;
        }
        .success {
            color: #198754;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Importação do Banco de Dados</h1>
        <div class="card">
            <div class="card-body">
                <!-- As mensagens de status serão exibidas aqui -->
            </div>
        </div>
    </div>
</body>
</html>
HTML;