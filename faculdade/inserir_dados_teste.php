<?php
require_once 'includes/config.php';
require_once 'includes/database.php';

// Inicializar conexão com o banco de dados
$db = new Database();

try {
    // Inserir categorias
    $categorias = [
        ['NOME' => 'Eletrônicos', 'DESCRICAO' => 'Produtos eletrônicos em geral'],
        ['NOME' => 'Informática', 'DESCRICAO' => 'Produtos para computadores'],
        ['NOME' => 'Escritório', 'DESCRICAO' => 'Material de escritório']
    ];
    
    foreach ($categorias as $categoria) {
        $db->query("INSERT INTO categoria (NOME, DESCRICAO) VALUES (:nome, :descricao)");
        $db->bind(':nome', $categoria['NOME']);
        $db->bind(':descricao', $categoria['DESCRICAO']);
        $db->execute();
    }
    echo "<p>Categorias inseridas com sucesso!</p>";
    
    // Inserir marcas
    $marcas = [
        ['NOME' => 'Samsung', 'DESCRICAO' => 'Fabricante de eletrônicos'],
        ['NOME' => 'Apple', 'DESCRICAO' => 'Fabricante de eletrônicos premium'],
        ['NOME' => 'Dell', 'DESCRICAO' => 'Fabricante de computadores']
    ];
    
    foreach ($marcas as $marca) {
        $db->query("INSERT INTO marca (NOME, DESCRICAO) VALUES (:nome, :descricao)");
        $db->bind(':nome', $marca['NOME']);
        $db->bind(':descricao', $marca['DESCRICAO']);
        $db->execute();
    }
    echo "<p>Marcas inseridas com sucesso!</p>";
    
    // Inserir fornecedores
    $fornecedores = [
        ['NOME' => 'Distribuidor A', 'CNPJ' => '12.345.678/0001-90', 'EMAIL' => 'contato@distribuidora.com', 'TELEFONE' => '(11) 1234-5678'],
        ['NOME' => 'Fornecedor B', 'CNPJ' => '98.765.432/0001-10', 'EMAIL' => 'contato@fornecedorb.com', 'TELEFONE' => '(11) 8765-4321']
    ];
    
    foreach ($fornecedores as $fornecedor) {
        $db->query("INSERT INTO fornecedor (NOME, CNPJ, EMAIL, TELEFONE) VALUES (:nome, :cnpj, :email, :telefone)");
        $db->bind(':nome', $fornecedor['NOME']);
        $db->bind(':cnpj', $fornecedor['CNPJ']);
        $db->bind(':email', $fornecedor['EMAIL']);
        $db->bind(':telefone', $fornecedor['TELEFONE']);
        $db->execute();
    }
    echo "<p>Fornecedores inseridos com sucesso!</p>";
    
    // Inserir produtos
    $produtos = [
        ['NOME' => 'Smartphone Galaxy S21', 'DESCRICAO' => 'Smartphone Samsung Galaxy S21', 'PRECO' => 3999.99, 'ESTOQUE' => 15, 'IDCATEGORIA' => 1, 'IDMARCA' => 1, 'IDFORNECEDOR' => 1],
        ['NOME' => 'iPhone 13', 'DESCRICAO' => 'Smartphone Apple iPhone 13', 'PRECO' => 5999.99, 'ESTOQUE' => 10, 'IDCATEGORIA' => 1, 'IDMARCA' => 2, 'IDFORNECEDOR' => 1],
        ['NOME' => 'Notebook Dell Inspiron', 'DESCRICAO' => 'Notebook Dell Inspiron 15 polegadas', 'PRECO' => 4500.00, 'ESTOQUE' => 8, 'IDCATEGORIA' => 2, 'IDMARCA' => 3, 'IDFORNECEDOR' => 2],
        ['NOME' => 'Monitor Dell 24"', 'DESCRICAO' => 'Monitor Dell 24 polegadas Full HD', 'PRECO' => 1200.00, 'ESTOQUE' => 5, 'IDCATEGORIA' => 2, 'IDMARCA' => 3, 'IDFORNECEDOR' => 2],
        ['NOME' => 'Teclado sem fio', 'DESCRICAO' => 'Teclado sem fio para computador', 'PRECO' => 150.00, 'ESTOQUE' => 3, 'IDCATEGORIA' => 2, 'IDMARCA' => 1, 'IDFORNECEDOR' => 1]
    ];
    
    foreach ($produtos as $produto) {
        $db->query("INSERT INTO produto (NOME, DESCRICAO, PRECO, ESTOQUE, IDCATEGORIA, IDMARCA, IDFORNECEDOR) VALUES (:nome, :descricao, :preco, :estoque, :idcategoria, :idmarca, :idfornecedor)");
        $db->bind(':nome', $produto['NOME']);
        $db->bind(':descricao', $produto['DESCRICAO']);
        $db->bind(':preco', $produto['PRECO']);
        $db->bind(':estoque', $produto['ESTOQUE']);
        $db->bind(':idcategoria', $produto['IDCATEGORIA']);
        $db->bind(':idmarca', $produto['IDMARCA']);
        $db->bind(':idfornecedor', $produto['IDFORNECEDOR']);
        $db->execute();
    }
    echo "<p>Produtos inseridos com sucesso!</p>";
    
    // Inserir clientes
    $clientes = [
        ['NOME' => 'João Silva', 'EMAIL' => 'joao@email.com', 'TELEFONE' => '(11) 99999-8888', 'ENDERECO' => 'Rua A, 123', 'CIDADE' => 'São Paulo', 'ESTADO' => 'SP'],
        ['NOME' => 'Maria Oliveira', 'EMAIL' => 'maria@email.com', 'TELEFONE' => '(11) 99999-7777', 'ENDERECO' => 'Rua B, 456', 'CIDADE' => 'São Paulo', 'ESTADO' => 'SP']
    ];
    
    foreach ($clientes as $cliente) {
        $db->query("INSERT INTO cliente (NOME, EMAIL, TELEFONE, ENDERECO, CIDADE, ESTADO) VALUES (:nome, :email, :telefone, :endereco, :cidade, :estado)");
        $db->bind(':nome', $cliente['NOME']);
        $db->bind(':email', $cliente['EMAIL']);
        $db->bind(':telefone', $cliente['TELEFONE']);
        $db->bind(':endereco', $cliente['ENDERECO']);
        $db->bind(':cidade', $cliente['CIDADE']);
        $db->bind(':estado', $cliente['ESTADO']);
        $db->execute();
    }
    echo "<p>Clientes inseridos com sucesso!</p>";
    
    // Inserir pedidos
    $db->query("INSERT INTO pedido (IDCLIENTE, VALORTOTAL, STATUS) VALUES (1, 4149.99, 'Concluído')");
    $db->execute();
    $idPedido1 = $db->lastInsertId();
    
    $db->query("INSERT INTO pedido (IDCLIENTE, VALORTOTAL, STATUS) VALUES (2, 5999.99, 'Em processamento')");
    $db->execute();
    $idPedido2 = $db->lastInsertId();
    
    echo "<p>Pedidos inseridos com sucesso!</p>";
    
    // Inserir itens de pedido
    $itensPedido = [
        ['IDPEDIDO' => $idPedido1, 'IDPRODUTO' => 1, 'QUANTIDADE' => 1, 'PRECO' => 3999.99, 'SUBTOTAL' => 3999.99],
        ['IDPEDIDO' => $idPedido1, 'IDPRODUTO' => 5, 'QUANTIDADE' => 1, 'PRECO' => 150.00, 'SUBTOTAL' => 150.00],
        ['IDPEDIDO' => $idPedido2, 'IDPRODUTO' => 2, 'QUANTIDADE' => 1, 'PRECO' => 5999.99, 'SUBTOTAL' => 5999.99]
    ];
    
    foreach ($itensPedido as $item) {
        $db->query("INSERT INTO item_pedido (IDPEDIDO, IDPRODUTO, QUANTIDADE, PRECO, SUBTOTAL) VALUES (:idpedido, :idproduto, :quantidade, :preco, :subtotal)");
        $db->bind(':idpedido', $item['IDPEDIDO']);
        $db->bind(':idproduto', $item['IDPRODUTO']);
        $db->bind(':quantidade', $item['QUANTIDADE']);
        $db->bind(':preco', $item['PRECO']);
        $db->bind(':subtotal', $item['SUBTOTAL']);
        $db->execute();
    }
    echo "<p>Itens de pedido inseridos com sucesso!</p>";
    
    echo "<h3>Dados de teste inseridos com sucesso!</h3>";
    echo "<p><a href='index.php'>Voltar para a página inicial</a></p>";
    
} catch (Exception $e) {
    echo "<p>Erro: " . $e->getMessage() . "</p>";
}
?>