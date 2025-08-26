<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Iniciar sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <!-- Animations CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/animations.css">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="<?php echo BASE_URL; ?>/index.php"><i class="fas fa-store me-2"></i><?php echo SITE_NAME; ?></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo isCurrentPage('index') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/index.php">
                                <i class="fas fa-home"></i> Início
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownCadastros" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-database"></i> Cadastros
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownCadastros">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/produtos.php">Produtos</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/categorias.php">Categorias</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/marcas.php">Marcas</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/fornecedores.php">Fornecedores</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/clientes.php">Clientes</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownVendas" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-shopping-cart"></i> Vendas
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownVendas">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/pedidos.php">Pedidos</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/novo-pedido.php">Novo Pedido</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isCurrentPage('relatorios') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/relatorios.php">
                                <i class="fas fa-chart-bar"></i> Relatórios
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <main class="container py-4 fade-in">
        <?php
        // Exibir mensagens de alerta se existirem
        if (isset($_SESSION['message'])) {
            echo showAlert($_SESSION['message'], $_SESSION['message_type']);
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>