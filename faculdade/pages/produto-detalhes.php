<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Inicializar conexão com o banco de dados
$db = new Database();

// Verificar se o ID foi fornecido
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['message'] = "ID do produto não fornecido.";
    $_SESSION['message_type'] = "danger";
    redirect(BASE_URL . '/pages/produtos.php');
}

// Buscar dados do produto
$produto = $db->query(
    "SELECT p.*, c.NOME as CATEGORIA, m.NOME as MARCA 
    FROM produto p 
    LEFT JOIN categoria c ON p.IDCATEGORIA = c.IDCATEGORIA 
    LEFT JOIN marca m ON p.IDMARCA = m.IDMARCA 
    WHERE p.IDPRODUTO = ?",
    [$id]
)->fetch();

if (!$produto) {
    $_SESSION['message'] = "Produto não encontrado.";
    $_SESSION['message_type'] = "danger";
    redirect(BASE_URL . '/pages/produtos.php');
}

// Buscar histórico de pedidos deste produto
$historicoPedidos = $db->query(
    "SELECT ip.IDPEDIDO, ip.QUANTIDADE, ip.PRECO as PRECO_VENDA, p.DATAPEDIDO, c.NOME as CLIENTE 
    FROM item_pedido ip 
    JOIN pedido p ON ip.IDPEDIDO = p.IDPEDIDO 
    JOIN cliente c ON p.IDCLIENTE = c.IDCLIENTE 
    WHERE ip.IDPRODUTO = ? 
    ORDER BY p.DATAPEDIDO DESC 
    LIMIT 10",
    [$id]
)->fetchAll();

// Incluir o cabeçalho
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Detalhes do Produto</h1>
    <div>
        <a href="produto-form.php?id=<?php echo $produto['IDPRODUTO']; ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="produtos.php" class="btn btn-secondary ms-2">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <!-- Informações do produto -->
    <div class="col-md-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-box"></i> Informações do Produto</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted">Nome</h6>
                        <p class="fs-5"><?php echo $produto['NOME']; ?></p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h6 class="text-muted">Categoria</h6>
                        <p><?php echo $produto['CATEGORIA']; ?></p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h6 class="text-muted">Marca</h6>
                        <p><?php echo $produto['MARCA']; ?></p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <h6 class="text-muted">Descrição</h6>
                        <p><?php echo !empty($produto['DESCRICAO']) ? $produto['DESCRICAO'] : '<span class="text-muted">Sem descrição</span>'; ?></p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <h6 class="text-muted">Código</h6>
                        <p>#<?php echo str_pad($produto['IDPRODUTO'], 5, '0', STR_PAD_LEFT); ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h6 class="text-muted">Estoque</h6>
                        <p>
                            <span class="badge bg-<?php echo getEstoqueStatusClass($produto['ESTOQUE']); ?> fs-6">
                                <?php echo $produto['ESTOQUE']; ?> unidades
                            </span>
                        </p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h6 class="text-muted">Preço</h6>
                        <p class="fs-5 fw-bold text-primary">R$ <?php echo number_format($produto['PRECO'], 2, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Estatísticas -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Estatísticas</h5>
            </div>
            <div class="card-body">
                <?php
                // Calcular estatísticas
                $totalVendido = 0;
                $valorTotalVendas = 0;
                
                if (!empty($historicoPedidos)) {
                    foreach ($historicoPedidos as $pedido) {
                        $totalVendido += $pedido['QUANTIDADE'];
                        $valorTotalVendas += $pedido['QUANTIDADE'] * $pedido['PRECO_VENDA'];
                    }
                }
                ?>
                
                <div class="mb-4 text-center">
                    <div class="display-6 mb-2"><?php echo $totalVendido; ?></div>
                    <p class="text-muted">Unidades vendidas</p>
                </div>
                
                <div class="mb-4 text-center">
                    <div class="display-6 mb-2">R$ <?php echo number_format($valorTotalVendas, 2, ',', '.'); ?></div>
                    <p class="text-muted">Valor total em vendas</p>
                </div>
                
                <div class="mb-4 text-center">
                    <div class="display-6 mb-2"><?php echo count($historicoPedidos); ?></div>
                    <p class="text-muted">Pedidos realizados</p>
                </div>
                
                <div class="text-center">
                    <div class="display-6 mb-2">
                        <?php 
                        $valorEstoque = $produto['ESTOQUE'] * $produto['PRECO'];
                        echo 'R$ ' . number_format($valorEstoque, 2, ',', '.');
                        ?>
                    </div>
                    <p class="text-muted">Valor em estoque</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Histórico de pedidos -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-history"></i> Histórico de Pedidos</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($historicoPedidos)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Data</th>
                            <th>Cliente</th>
                            <th class="text-center">Quantidade</th>
                            <th class="text-end">Preço Unitário</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historicoPedidos as $pedido): ?>
                            <tr>
                                <td>#<?php echo $pedido['IDPEDIDO']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($pedido['DATAPEDIDO'])); ?></td>
                                <td><?php echo $pedido['CLIENTE']; ?></td>
                                <td class="text-center"><?php echo $pedido['QUANTIDADE']; ?></td>
                                <td class="text-end">R$ <?php echo number_format($pedido['PRECO_VENDA'], 2, ',', '.'); ?></td>
                                <td class="text-end">R$ <?php echo number_format($pedido['QUANTIDADE'] * $pedido['PRECO_VENDA'], 2, ',', '.'); ?></td>
                                <td class="text-end">
                                    <a href="pedido-detalhes.php?id=<?php echo $pedido['IDPEDIDO']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Este produto ainda não foi vendido.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Função para determinar a classe de status do estoque
function getEstoqueStatusClass($estoque) {
    if ($estoque <= 0) {
        return 'danger';
    } elseif ($estoque <= 5) {
        return 'warning';
    } else {
        return 'success';
    }
}

// Incluir o rodapé
include '../includes/footer.php';
?>