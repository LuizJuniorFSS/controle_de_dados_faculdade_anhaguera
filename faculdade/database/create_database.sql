-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS faculdade;
USE faculdade;

-- Tabela de Categorias
CREATE TABLE IF NOT EXISTS categoria (
    IDCATEGORIA INT AUTO_INCREMENT PRIMARY KEY,
    NOME VARCHAR(100) NOT NULL,
    DESCRICAO TEXT,
    DATACADASTRO TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Marcas
CREATE TABLE IF NOT EXISTS marca (
    IDMARCA INT AUTO_INCREMENT PRIMARY KEY,
    NOME VARCHAR(100) NOT NULL,
    DESCRICAO TEXT,
    DATACADASTRO TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Fornecedores
CREATE TABLE IF NOT EXISTS fornecedor (
    IDFORNECEDOR INT AUTO_INCREMENT PRIMARY KEY,
    NOME VARCHAR(100) NOT NULL,
    CNPJ VARCHAR(18),
    EMAIL VARCHAR(100),
    TELEFONE VARCHAR(20),
    ENDERECO VARCHAR(200),
    CIDADE VARCHAR(100),
    ESTADO VARCHAR(2),
    CEP VARCHAR(10),
    CONTATO VARCHAR(100),
    OBSERVACOES TEXT,
    DATACADASTRO TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Produtos
CREATE TABLE IF NOT EXISTS produto (
    IDPRODUTO INT AUTO_INCREMENT PRIMARY KEY,
    NOME VARCHAR(100) NOT NULL,
    DESCRICAO TEXT,
    PRECO DECIMAL(10,2) NOT NULL,
    ESTOQUE INT NOT NULL DEFAULT 0,
    IDCATEGORIA INT,
    IDMARCA INT,
    IDFORNECEDOR INT,
    DATACADASTRO TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (IDCATEGORIA) REFERENCES categoria(IDCATEGORIA) ON DELETE SET NULL,
    FOREIGN KEY (IDMARCA) REFERENCES marca(IDMARCA) ON DELETE SET NULL,
    FOREIGN KEY (IDFORNECEDOR) REFERENCES fornecedor(IDFORNECEDOR) ON DELETE SET NULL
);

-- Tabela de Clientes
CREATE TABLE IF NOT EXISTS cliente (
    IDCLIENTE INT AUTO_INCREMENT PRIMARY KEY,
    NOME VARCHAR(100) NOT NULL,
    EMAIL VARCHAR(100),
    TELEFONE VARCHAR(20),
    ENDERECO VARCHAR(200),
    CIDADE VARCHAR(100),
    ESTADO VARCHAR(2),
    CEP VARCHAR(10),
    DATACADASTRO TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Pedidos
CREATE TABLE IF NOT EXISTS pedido (
    IDPEDIDO INT AUTO_INCREMENT PRIMARY KEY,
    IDCLIENTE INT,
    DATAPEDIDO TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    VALORTOTAL DECIMAL(10,2) NOT NULL DEFAULT 0,
    STATUS VARCHAR(50) DEFAULT 'Pendente',
    OBSERVACOES TEXT,
    FOREIGN KEY (IDCLIENTE) REFERENCES cliente(IDCLIENTE) ON DELETE SET NULL
);

-- Tabela de Itens do Pedido
CREATE TABLE IF NOT EXISTS item_pedido (
    IDITEM INT AUTO_INCREMENT PRIMARY KEY,
    IDPEDIDO INT NOT NULL,
    IDPRODUTO INT,
    QUANTIDADE INT NOT NULL,
    PRECO DECIMAL(10,2) NOT NULL,
    SUBTOTAL DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (IDPEDIDO) REFERENCES pedido(IDPEDIDO) ON DELETE CASCADE,
    FOREIGN KEY (IDPRODUTO) REFERENCES produto(IDPRODUTO) ON DELETE SET NULL
);

-- Tabela de Usuários do Sistema
CREATE TABLE IF NOT EXISTS usuario (
    IDUSUARIO INT AUTO_INCREMENT PRIMARY KEY,
    NOME VARCHAR(100) NOT NULL,
    EMAIL VARCHAR(100) NOT NULL UNIQUE,
    SENHA VARCHAR(255) NOT NULL,
    NIVEL VARCHAR(20) DEFAULT 'usuario',
    ULTIMO_ACESSO TIMESTAMP NULL,
    DATACADASTRO TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Log de Atividades
CREATE TABLE IF NOT EXISTS log_atividade (
    IDLOG INT AUTO_INCREMENT PRIMARY KEY,
    IDUSUARIO INT,
    ACAO VARCHAR(100) NOT NULL,
    DESCRICAO TEXT,
    IP VARCHAR(45),
    DATA TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (IDUSUARIO) REFERENCES usuario(IDUSUARIO) ON DELETE SET NULL
);

-- Inserir dados iniciais para categorias
INSERT INTO categoria (NOME, DESCRICAO) VALUES
('Eletrônicos', 'Produtos eletrônicos em geral'),
('Informática', 'Produtos para computadores e notebooks'),
('Celulares', 'Smartphones e acessórios'),
('Áudio e Vídeo', 'Equipamentos de som e imagem'),
('Acessórios', 'Acessórios diversos para eletrônicos');

-- Inserir dados iniciais para marcas
INSERT INTO marca (NOME, DESCRICAO) VALUES
('Samsung', 'Fabricante sul-coreana de eletrônicos'),
('Apple', 'Fabricante americana de eletrônicos'),
('LG', 'Fabricante sul-coreana de eletrônicos'),
('Sony', 'Fabricante japonesa de eletrônicos'),
('Xiaomi', 'Fabricante chinesa de eletrônicos');

-- Inserir dados iniciais para fornecedores
INSERT INTO fornecedor (NOME, CNPJ, EMAIL, TELEFONE, ENDERECO, CIDADE, ESTADO, CEP, CONTATO) VALUES
('Tech Distribuidora', '12.345.678/0001-90', 'contato@techdist.com', '(11) 3456-7890', 'Av. Paulista, 1000', 'São Paulo', 'SP', '01310-100', 'João Silva'),
('Eletrônicos Brasil', '98.765.432/0001-10', 'vendas@eletrobrasil.com', '(21) 2345-6789', 'Rua do Comércio, 500', 'Rio de Janeiro', 'RJ', '20010-020', 'Maria Souza'),
('Importadora Tech', '45.678.901/0001-23', 'contato@importech.com', '(31) 3456-7890', 'Av. Afonso Pena, 2000', 'Belo Horizonte', 'MG', '30130-007', 'Carlos Oliveira');

-- Inserir dados iniciais para produtos
INSERT INTO produto (NOME, DESCRICAO, PRECO, ESTOQUE, IDCATEGORIA, IDMARCA, IDFORNECEDOR) VALUES
('Smartphone Galaxy S21', 'Smartphone Samsung Galaxy S21 128GB', 3999.90, 50, 3, 1, 1),
('iPhone 13', 'Apple iPhone 13 128GB', 5999.90, 30, 3, 2, 2),
('Smart TV 55"', 'Smart TV LED 55" 4K Samsung', 2799.90, 20, 4, 1, 1),
('Notebook Gamer', 'Notebook Gamer 16GB RAM 512GB SSD', 4599.90, 15, 2, 3, 3),
('Fone de Ouvido Bluetooth', 'Fone de Ouvido Bluetooth Sony', 299.90, 100, 5, 4, 2),
('Tablet Galaxy Tab S7', 'Tablet Samsung Galaxy Tab S7', 2499.90, 25, 2, 1, 1),
('Monitor 24"', 'Monitor LED 24" Full HD LG', 899.90, 40, 2, 3, 3),
('Caixa de Som Bluetooth', 'Caixa de Som Bluetooth Portátil', 199.90, 60, 4, 5, 2),
('Carregador Wireless', 'Carregador Wireless 15W', 149.90, 80, 5, 1, 1),
('Smartwatch', 'Smartwatch com Monitor Cardíaco', 599.90, 35, 3, 5, 3);

-- Inserir dados iniciais para clientes
INSERT INTO cliente (NOME, EMAIL, TELEFONE, ENDERECO, CIDADE, ESTADO, CEP) VALUES
('Ana Silva', 'ana.silva@email.com', '(11) 98765-4321', 'Rua das Flores, 123', 'São Paulo', 'SP', '01234-567'),
('Pedro Santos', 'pedro.santos@email.com', '(21) 97654-3210', 'Av. Rio Branco, 456', 'Rio de Janeiro', 'RJ', '20040-002'),
('Carla Oliveira', 'carla.oliveira@email.com', '(31) 96543-2109', 'Rua Sergipe, 789', 'Belo Horizonte', 'MG', '30130-170'),
('Marcos Pereira', 'marcos.pereira@email.com', '(41) 95432-1098', 'Av. Sete de Setembro, 1010', 'Curitiba', 'PR', '80020-010'),
('Juliana Costa', 'juliana.costa@email.com', '(51) 94321-0987', 'Rua dos Andradas, 500', 'Porto Alegre', 'RS', '90020-008');

-- Inserir dados iniciais para pedidos
INSERT INTO pedido (IDCLIENTE, DATAPEDIDO, VALORTOTAL, STATUS, OBSERVACOES) VALUES
(1, DATE_SUB(NOW(), INTERVAL 10 DAY), 3999.90, 'Concluído', 'Entrega expressa'),
(2, DATE_SUB(NOW(), INTERVAL 7 DAY), 6299.80, 'Concluído', 'Cliente preferencial'),
(3, DATE_SUB(NOW(), INTERVAL 5 DAY), 899.90, 'Em processamento', 'Aguardando confirmação de pagamento'),
(4, DATE_SUB(NOW(), INTERVAL 2 DAY), 4899.80, 'Pendente', 'Pagamento via boleto'),
(5, DATE_SUB(NOW(), INTERVAL 1 DAY), 749.80, 'Pendente', NULL),
(1, DATE_SUB(NOW(), INTERVAL 15 DAY), 2799.90, 'Concluído', NULL),
(2, DATE_SUB(NOW(), INTERVAL 20 DAY), 599.90, 'Concluído', NULL),
(3, DATE_SUB(NOW(), INTERVAL 25 DAY), 4599.90, 'Cancelado', 'Cliente desistiu da compra');

-- Inserir dados iniciais para itens de pedido
INSERT INTO item_pedido (IDPEDIDO, IDPRODUTO, QUANTIDADE, PRECO, SUBTOTAL) VALUES
(1, 1, 1, 3999.90, 3999.90),
(2, 2, 1, 5999.90, 5999.90),
(2, 5, 1, 299.90, 299.90),
(3, 7, 1, 899.90, 899.90),
(4, 4, 1, 4599.90, 4599.90),
(4, 9, 2, 149.90, 299.80),
(5, 5, 1, 299.90, 299.90),
(5, 8, 1, 199.90, 199.90),
(5, 9, 1, 149.90, 149.90),
(5, 10, 1, 599.90, 599.90),
(6, 3, 1, 2799.90, 2799.90),
(7, 10, 1, 599.90, 599.90),
(8, 4, 1, 4599.90, 4599.90);

-- Inserir usuário administrador padrão (senha: admin123)
INSERT INTO usuario (NOME, EMAIL, SENHA, NIVEL) VALUES
('Administrador', 'admin@sistema.com', '$2y$10$8tDjLmFzDL.Cz7yStJVbzO0HGBRLBTiPP/rVrLkLmdNUBPNbXMgaC', 'administrador');