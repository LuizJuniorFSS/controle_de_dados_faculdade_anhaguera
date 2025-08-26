<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Inicializar conexão com o banco de dados
$db = new Database();

// Verificar se o ID do cliente foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "ID do cliente não fornecido.";
    $_SESSION['message_type'] = "danger";
    redirect(BASE_URL . '/pages/clientes.php');
}

$id = (int)$_GET['id'];

// Buscar dados do cliente
$query = $db->query("SELECT * FROM cliente WHERE IDCLIENTE = ?", [$id]);
$cliente = $query ? $query->fetch() : null;

if (!$cliente) {
    $_SESSION['message'] = "Cliente não encontrado.";
    $_SESSION['message_type'] = "danger";
    redirect(BASE_URL . '/pages/clientes.php');
}

// Buscar estatísticas do cliente
$query = $db->query(
    "SELECT 
        COUNT(p.IDPEDIDO) as total_pedidos,
        SUM(p.VALORTOTAL) as valor_total_pedidos,
        MAX(p.DATAPEDIDO) as ultimo_pedido
    FROM pedido p 
    WHERE p.IDCLIENTE = ?", 
    [$id]
);

$estatisticas = $query ? $query->fetch() : [
    'total_pedidos' => 0,
    'valor_total_pedidos' => 0,
    'ultimo_pedido' => null
];

// Buscar pedidos do cliente
$query = $db->query(
    "SELECT 
        p.IDPEDIDO, 
        p.DATAPEDIDO, 
        p.VALORTOTAL, 
        p.STATUS,
        COUNT(i.IDITEM) as total_itens
    FROM pedido p
    LEFT JOIN item_pedido i ON p.IDPEDIDO = i.IDPEDIDO
    WHERE p.IDCLIENTE = ?
    GROUP BY p.IDPEDIDO
    ORDER BY p.DATAPEDIDO DESC
    LIMIT 10", 
    [$id]
);

$pedidos = $query ? $query->fetchAll() : [];

// Incluir o cabeçalho
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Detalhes do Cliente</h1>
    <div>
        <a href="cliente-form.php?id=<?php echo $cliente['IDCLIENTE']; ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="clientes.php" class="btn btn-secondary ms-2">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <!-- Informações do Cliente -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-user"></i> Informações do Cliente</h5>
            </div>
            <div class="card-body">
                <h3 class="mb-3"><?php echo $cliente['NOME']; ?></h3>
                
                <div class="mb-3">
                    <strong><i class="fas fa-envelope"></i> Email:</strong>
                    <?php if (!empty($cliente['EMAIL'])): ?>
                        <a href="mailto:<?php echo $cliente['EMAIL']; ?>"><?php echo $cliente['EMAIL']; ?></a>
                    <?php else: ?>
                        <span class="text-muted">Não informado</span>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <strong><i class="fas fa-phone"></i> Telefone:</strong>
                    <?php if (!empty($cliente['TELEFONE'])): ?>
                        <a href="tel:<?php echo $cliente['TELEFONE']; ?>"><?php echo $cliente['TELEFONE']; ?></a>
                    <?php else: ?>
                        <span class="text-muted">Não informado</span>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <strong><i class="fas fa-map-marker-alt"></i> Endereço:</strong>
                    <?php if (!empty($cliente['ENDERECO'])): ?>
                        <address>
                            <?php echo $cliente['ENDERECO']; ?><br>
                            <?php if (!empty($cliente['CIDADE']) || !empty($cliente['ESTADO'])): ?>
                                <?php echo !empty($cliente['CIDADE']) ? $cliente['CIDADE'] : ''; ?>
                                <?php echo !empty($cliente['ESTADO']) ? ' - ' . $cliente['ESTADO'] : ''; ?><br>
                            <?php endif; ?>
                            <?php echo !empty($cliente['CEP']) ? 'CEP: ' . $cliente['CEP'] : ''; ?>
                        </address>
                    <?php else: ?>
                        <span class="text-muted">Não informado</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Estatísticas do Cliente -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0"><i class="fas fa-chart-bar"></i> Estatísticas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h2 class="display-4"><?php echo $estatisticas['total_pedidos'] ?: 0; ?></h2>
                                <p class="text-muted">Pedidos Realizados</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h2 class="display-4">
                                    R$ <?php echo number_format($estatisticas['valor_total_pedidos'] ?: 0, 2, ',', '.'); ?>
                                </h2>
                                <p class="text-muted">Total em Compras</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <strong>Último Pedido:</strong>
                    <?php if (!empty($estatisticas['ultimo_pedido'])): ?>
                        <?php echo date('d/m/Y H:i', strtotime($estatisticas['ultimo_pedido'])); ?>
                    <?php else: ?>
                        <span class="text-muted">Nenhum pedido realizado</span>
                    <?php endif; ?>
                </div>
                
                <div class="mt-4">
                    <a href="pedido-form.php?cliente=<?php echo $cliente['IDCLIENTE']; ?>" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i> Novo Pedido
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Histórico de Pedidos -->
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="card-title mb-0"><i class="fas fa-history"></i> Histórico de Pedidos</h5>
    </div>
    <div class="card-body">
        <?php if (count($pedidos) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Itens</th>
                            <th>Valor Total</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $pedido): ?>
                            <tr>
                                <td><?php echo $pedido['IDPEDIDO']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($pedido['DATAPEDIDO'])); ?></td>
                                <td><?php echo $pedido['total_itens']; ?></td>
                                <td>R$ <?php echo number_format($pedido['VALORTOTAL'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    switch ($pedido['STATUS']) {
                                        case 'Pendente':
                                            $statusClass = 'warning';
                                            break;
                                        case 'Concluído':
                                            $statusClass = 'success';
                                            break;
                                        case 'Cancelado':
                                            $statusClass = 'danger';
                                            break;
                                        default:
                                            $statusClass = 'info';
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                        <?php echo $pedido['STATUS']; ?>
                                    </span>
                                </td>
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
            
            <?php if ($estatisticas['total_pedidos'] > 10): ?>
                <div class="text-center mt-3">
                    <a href="pedidos.php?cliente=<?php echo $cliente['IDCLIENTE']; ?>" class="btn btn-outline-primary">
                        Ver todos os pedidos
                    </a>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Este cliente ainda não realizou nenhum pedido.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Incluir o rodapé
include '../includes/footer.php';
?>