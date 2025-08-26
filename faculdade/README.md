# Sistema de Gerenciamento - Faculdade

Um sistema completo de gerenciamento desenvolvido em PHP para controle de produtos, clientes, pedidos e relatórios.

## Funcionalidades

### Dashboard
- Visão geral com estatísticas do sistema
- Alertas de produtos com estoque baixo
- Listagem de pedidos recentes

### Gestão de Produtos
- Cadastro e edição de produtos
- Controle de estoque
- Categorização por marcas e categorias
- Associação com fornecedores

### Gestão de Clientes
- Cadastro completo de clientes
- Visualização detalhada de informações
- Histórico de pedidos por cliente

### Gestão de Pedidos
- Criação e edição de pedidos
- Adição de múltiplos itens por pedido
- Cálculo automático de valores
- Detalhamento completo de pedidos

### Cadastros Auxiliares
- Gerenciamento de categorias
- Gerenciamento de marcas
- Gerenciamento de fornecedores

### Relatórios
- Relatórios gerenciais
- Análise de vendas
- Estatísticas de produtos e clientes

## Tecnologias Utilizadas

- PHP
- MySQL
- HTML/CSS
- JavaScript
- Bootstrap (para interface responsiva)

## Estrutura do Projeto

```
├── assets/            # Arquivos estáticos (CSS, JS)
│   ├── css/           # Folhas de estilo
│   └── js/            # Scripts JavaScript
├── database/          # Scripts de banco de dados
├── includes/          # Arquivos de configuração e funções
├── pages/             # Páginas do sistema
└── index.php          # Página inicial (Dashboard)
```

## Requisitos

- Servidor web (Apache/Nginx)
- PHP 7.0 ou superior
- MySQL 5.6 ou superior
- Extensão PDO para PHP

## Instalação

1. Clone ou baixe este repositório para seu servidor web
2. Importe o arquivo `database/create_database.sql` para criar o banco de dados
3. Configure as credenciais de acesso ao banco em `includes/config.php`
4. Acesse o sistema pelo navegador

## Configuração

As principais configurações do sistema estão no arquivo `includes/config.php`:

```php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'faculdade');

// Configurações do sistema
define('SITE_NAME', 'Sistema de Gerenciamento - Faculdade');
define('BASE_URL', 'http://localhost/faculdade');
```

## Dados de Teste

O sistema inclui scripts para importação de dados de teste:

- `importar_db.php`: Importa a estrutura do banco de dados
- `inserir_dados_teste.php`: Insere dados de exemplo para testes
- `verificar_tabelas.php`: Verifica a integridade das tabelas

## Licença

Este projeto é para fins educacionais.