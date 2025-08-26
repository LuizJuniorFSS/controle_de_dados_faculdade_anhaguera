<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Inicializar conexão com o banco de dados
$db = new Database();

// Obter estatísticas para o dashboard
$db->query("SELECT COUNT(*) as total FROM produto");
$db->execute();
$totalProdutos = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM categoria");
$db->execute();
$totalCategorias = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM marca");
$db->execute();
$totalMarcas = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM cliente");
$db->execute();
$totalClientes = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM pedido");
$db->execute();
$totalPedidos = $db->single()['total'];

// Obter produtos com estoque baixo (menos de 10 unidades)
$db->query(
    "SELECT p.IDPRODUTO, p.NOME, p.ESTOQUE, c.NOME as CATEGORIA, m.NOME as MARCA 
    FROM produto p 
    LEFT JOIN categoria c ON p.IDCATEGORIA = c.IDCATEGORIA 
    LEFT JOIN marca m ON p.IDMARCA = m.IDMARCA 
    WHERE p.ESTOQUE < 10 
    ORDER BY p.ESTOQUE ASC"
);
$db->execute();
$produtosEstoqueBaixo = $db->resultSet();

// Obter pedidos recentes
$db->query(
    "SELECT p.IDPEDIDO, p.DATAPEDIDO as DATA, c.NOME as CLIENTE, 
    (SELECT SUM(ip.QUANTIDADE * pr.PRECO) FROM item_pedido ip 
     JOIN produto pr ON ip.IDPRODUTO = pr.IDPRODUTO 
     WHERE ip.IDPEDIDO = p.IDPEDIDO) as TOTAL 
    FROM pedido p 
    JOIN cliente c ON p.IDCLIENTE = c.IDCLIENTE 
    ORDER BY p.DATAPEDIDO DESC 
    LIMIT 5"
);
$db->execute();
$pedidosRecentes = $db->resultSet();

// Incluir o cabeçalho
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="page-header">
            <h1 class="mb-4">Dashboard</h1>
        </div>
    </div>
</div>

<!-- Cards de estatísticas -->
<div class="row mb-5">
    <div class="col-md-4 col-lg-2-4 mb-3">
        <div class="card dashboard-card bg-primary h-100">
            <div class="card-body">
                <i class="fas fa-box"></i>
                <h3>Produtos</h3>
                <p><?php echo $totalProdutos; ?></p>
                <a href="pages/produtos.php" class="btn btn-sm btn-light">Ver todos</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-lg-2-4 mb-3">
        <div class="card dashboard-card bg-success h-100">
            <div class="card-body">
                <i class="fas fa-tags"></i>
                <h3>Categorias</h3>
                <p><?php echo $totalCategorias; ?></p>
                <a href="pages/categorias.php" class="btn btn-sm btn-light">Ver todas</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-lg-2-4 mb-3">
        <div class="card dashboard-card bg-info h-100">
            <div class="card-body">
                <i class="fas fa-trademark"></i>
                <h3>Marcas</h3>
                <p><?php echo $totalMarcas; ?></p>
                <a href="pages/marcas.php" class="btn btn-sm btn-light">Ver todas</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-2-4 mb-3">
        <div class="card dashboard-card bg-warning h-100">
            <div class="card-body">
                <i class="fas fa-users"></i>
                <h3>Clientes</h3>
                <p><?php echo $totalClientes; ?></p>
                <a href="pages/clientes.php" class="btn btn-sm btn-light">Ver todos</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-2-4 mb-3">
        <div class="card dashboard-card bg-danger h-100">
            <div class="card-body">
                <i class="fas fa-shopping-cart"></i>
                <h3>Pedidos</h3>
                <p><?php echo $totalPedidos; ?></p>
                <a href="pages/pedidos.php" class="btn btn-sm btn-light">Ver todos</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Produtos com estoque baixo -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Produtos com Estoque Baixo</h5>
                <a href="pages/produtos.php" class="btn btn-sm btn-light">Ver todos</a>
            </div>
            <div class="card-body">
                <?php if (count($produtosEstoqueBaixo) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Categoria</th>
                                    <th>Marca</th>
                                    <th class="text-center">Estoque</th>
                                    <th class="text-end">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtosEstoqueBaixo as $produto): ?>
                                    <tr>
                                        <td><?php echo $produto['NOME']; ?></td>
                                        <td><?php echo $produto['CATEGORIA']; ?></td>
                                        <td><?php echo $produto['MARCA']; ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?php echo $produto['ESTOQUE'] <= 5 ? 'danger' : 'warning'; ?>">
                                                <?php echo $produto['ESTOQUE']; ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="pages/produto-form.php?id=<?php echo $produto['IDPRODUTO']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i> Todos os produtos estão com estoque adequado.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Pedidos recentes -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-shopping-bag me-2"></i> Pedidos Recentes</h5>
                <a href="pages/pedidos.php" class="btn btn-sm btn-light">Ver todos</a>
            </div>
            <div class="card-body">
                <?php if (count($pedidosRecentes) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Data</th>
                                    <th>Cliente</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedidosRecentes as $pedido): ?>
                                    <tr>
                                        <td><?php echo $pedido['IDPEDIDO']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($pedido['DATA'])); ?></td>
                                        <td><?php echo $pedido['CLIENTE']; ?></td>
                                        <td class="text-end">
                                            R$ <?php echo number_format($pedido['TOTAL'], 2, ',', '.'); ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="pages/pedido-detalhes.php?id=<?php echo $pedido['IDPEDIDO']; ?>" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i> Detalhes
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Não há pedidos recentes.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir o rodapé
include 'includes/footer.php';
?>