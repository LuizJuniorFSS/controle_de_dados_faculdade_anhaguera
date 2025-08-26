<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Inicializar conexão com o banco de dados
$db = new Database();

// Definir período padrão (último mês)
$dataInicio = date('Y-m-d', strtotime('-30 days'));
$dataFim = date('Y-m-d');

// Processar filtros de data se enviados
if (isset($_GET['filtrar'])) {
    if (!empty($_GET['data_inicio'])) {
        $dataInicio = $_GET['data_inicio'];
    }
    if (!empty($_GET['data_fim'])) {
        $dataFim = $_GET['data_fim'];
    }
}

// Estatísticas gerais
$estatisticas = [
    'total_vendas' => $db->query(
        "SELECT COUNT(*) as total FROM pedido WHERE DATAPEDIDO BETWEEN ? AND ?", 
        [$dataInicio, $dataFim . ' 23:59:59']
    )->fetch()['total'],
    
    'valor_total' => $db->query(
        "SELECT SUM(VALORTOTAL) as total FROM pedido WHERE DATAPEDIDO BETWEEN ? AND ?", 
        [$dataInicio, $dataFim . ' 23:59:59']
    )->fetch()['total'] ?: 0,
    
    'ticket_medio' => 0,
    
    'produtos_vendidos' => $db->query(
        "SELECT SUM(ip.QUANTIDADE) as total 
         FROM item_pedido ip 
         JOIN pedido p ON ip.IDPEDIDO = p.IDPEDIDO 
         WHERE p.DATAPEDIDO BETWEEN ? AND ?", 
        [$dataInicio, $dataFim . ' 23:59:59']
    )->fetch()['total'] ?: 0,
    
    'clientes_ativos' => $db->query(
        "SELECT COUNT(DISTINCT IDCLIENTE) as total 
         FROM pedido 
         WHERE DATAPEDIDO BETWEEN ? AND ?", 
        [$dataInicio, $dataFim . ' 23:59:59']
    )->fetch()['total']
];

// Calcular ticket médio
if ($estatisticas['total_vendas'] > 0) {
    $estatisticas['ticket_medio'] = $estatisticas['valor_total'] / $estatisticas['total_vendas'];
}

// Produtos mais vendidos
$produtosMaisVendidos = $db->query(
    "SELECT p.IDPRODUTO, p.NOME, SUM(ip.QUANTIDADE) as total_vendido, 
            SUM(ip.SUBTOTAL) as valor_total, COUNT(DISTINCT ip.IDPEDIDO) as num_pedidos 
     FROM item_pedido ip 
     JOIN produto p ON ip.IDPRODUTO = p.IDPRODUTO 
     JOIN pedido pe ON ip.IDPEDIDO = pe.IDPEDIDO 
     WHERE pe.DATAPEDIDO BETWEEN ? AND ? 
     GROUP BY p.IDPRODUTO, p.NOME 
     ORDER BY total_vendido DESC 
     LIMIT 10", 
    [$dataInicio, $dataFim . ' 23:59:59']
)->fetchAll();

// Vendas por categoria
$vendasPorCategoria = $db->query(
    "SELECT c.NOME, SUM(ip.QUANTIDADE) as total_vendido, 
            SUM(ip.SUBTOTAL) as valor_total 
     FROM item_pedido ip 
     JOIN produto p ON ip.IDPRODUTO = p.IDPRODUTO 
     JOIN categoria c ON p.IDCATEGORIA = c.IDCATEGORIA 
     JOIN pedido pe ON ip.IDPEDIDO = pe.IDPEDIDO 
     WHERE pe.DATAPEDIDO BETWEEN ? AND ? 
     GROUP BY c.IDCATEGORIA, c.NOME 
     ORDER BY valor_total DESC", 
    [$dataInicio, $dataFim . ' 23:59:59']
)->fetchAll();

// Vendas por marca
$vendasPorMarca = $db->query(
    "SELECT m.NOME, SUM(ip.QUANTIDADE) as total_vendido, 
            SUM(ip.SUBTOTAL) as valor_total 
     FROM item_pedido ip 
     JOIN produto p ON ip.IDPRODUTO = p.IDPRODUTO 
     JOIN marca m ON p.IDMARCA = m.IDMARCA 
     JOIN pedido pe ON ip.IDPEDIDO = pe.IDPEDIDO 
     WHERE pe.DATAPEDIDO BETWEEN ? AND ? 
     GROUP BY m.IDMARCA, m.NOME 
     ORDER BY valor_total DESC", 
    [$dataInicio, $dataFim . ' 23:59:59']
)->fetchAll();

// Melhores clientes
$melhoresClientes = $db->query(
    "SELECT c.IDCLIENTE, c.NOME, COUNT(p.IDPEDIDO) as total_pedidos, 
            SUM(p.VALORTOTAL) as valor_total 
     FROM pedido p 
     JOIN cliente c ON p.IDCLIENTE = c.IDCLIENTE 
     WHERE p.DATAPEDIDO BETWEEN ? AND ? 
     GROUP BY c.IDCLIENTE, c.NOME 
     ORDER BY valor_total DESC 
     LIMIT 10", 
    [$dataInicio, $dataFim . ' 23:59:59']
)->fetchAll();

// Vendas por status
$vendasPorStatus = $db->query(
    "SELECT STATUS, COUNT(*) as total, SUM(VALORTOTAL) as valor_total 
     FROM pedido 
     WHERE DATAPEDIDO BETWEEN ? AND ? 
     GROUP BY STATUS 
     ORDER BY total DESC", 
    [$dataInicio, $dataFim . ' 23:59:59']
)->fetchAll();

// Vendas por dia (para gráfico)
$vendasPorDia = $db->query(
    "SELECT DATE(DATAPEDIDO) as data, COUNT(*) as total_pedidos, 
            SUM(VALORTOTAL) as valor_total 
     FROM pedido 
     WHERE DATAPEDIDO BETWEEN ? AND ? 
     GROUP BY DATE(DATAPEDIDO) 
     ORDER BY data ASC", 
    [$dataInicio, $dataFim . ' 23:59:59']
)->fetchAll();

// Incluir o cabeçalho
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Relatórios e Estatísticas</h1>
    <div>
        <button type="button" class="btn btn-success" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir Relatório
        </button>
    </div>
</div>

<!-- Filtro de período -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0"><i class="fas fa-filter"></i> Filtrar por Período</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <label for="data_inicio" class="form-label">Data Inicial</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo $dataInicio; ?>">
            </div>
            <div class="col-md-4">
                <label for="data_fim" class="form-label">Data Final</label>
                <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo $dataFim; ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" name="filtrar" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="relatorios.php" class="btn btn-secondary ms-2">
                    <i class="fas fa-sync-alt"></i> Resetar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Resumo do período -->
<div class="alert alert-info">
    <strong>Período analisado:</strong> <?php echo date('d/m/Y', strtotime($dataInicio)); ?> até <?php echo date('d/m/Y', strtotime($dataFim)); ?>
</div>

<!-- Cards de estatísticas -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Total de Vendas</h5>
                <p class="card-text display-6"><?php echo $estatisticas['total_vendas']; ?></p>
                <p class="card-text">pedidos no período</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Valor Total</h5>
                <p class="card-text display-6">R$ <?php echo number_format($estatisticas['valor_total'], 2, ',', '.'); ?></p>
                <p class="card-text">em vendas no período</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Ticket Médio</h5>
                <p class="card-text display-6">R$ <?php echo number_format($estatisticas['ticket_medio'], 2, ',', '.'); ?></p>
                <p class="card-text">valor médio por pedido</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <h5 class="card-title">Produtos Vendidos</h5>
                <p class="card-text display-6"><?php echo $estatisticas['produtos_vendidos']; ?></p>
                <p class="card-text">unidades no período</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Produtos mais vendidos -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><i class="fas fa-chart-bar"></i> Produtos Mais Vendidos</h5>
            </div>
            <div class="card-body">
                <?php if (count($produtosMaisVendidos) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-center">Qtd. Vendida</th>
                                    <th class="text-end">Valor Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtosMaisVendidos as $produto): ?>
                                    <tr>
                                        <td>
                                            <a href="produto-detalhes.php?id=<?php echo $produto['IDPRODUTO']; ?>">
                                                <?php echo $produto['NOME']; ?>
                                            </a>
                                        </td>
                                        <td class="text-center"><?php echo $produto['total_vendido']; ?></td>
                                        <td class="text-end">R$ <?php echo number_format($produto['valor_total'], 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Nenhum produto vendido no período selecionado.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Melhores clientes -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0"><i class="fas fa-users"></i> Melhores Clientes</h5>
            </div>
            <div class="card-body">
                <?php if (count($melhoresClientes) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th class="text-center">Pedidos</th>
                                    <th class="text-end">Valor Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($melhoresClientes as $cliente): ?>
                                    <tr>
                                        <td>
                                            <a href="cliente-detalhes.php?id=<?php echo $cliente['IDCLIENTE']; ?>">
                                                <?php echo $cliente['NOME']; ?>
                                            </a>
                                        </td>
                                        <td class="text-center"><?php echo $cliente['total_pedidos']; ?></td>
                                        <td class="text-end">R$ <?php echo number_format($cliente['valor_total'], 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Nenhum cliente com compras no período selecionado.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Vendas por categoria -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-tags"></i> Vendas por Categoria</h5>
            </div>
            <div class="card-body">
                <?php if (count($vendasPorCategoria) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Categoria</th>
                                    <th class="text-center">Qtd. Vendida</th>
                                    <th class="text-end">Valor Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vendasPorCategoria as $categoria): ?>
                                    <tr>
                                        <td><?php echo $categoria['NOME'] ?: 'Sem categoria'; ?></td>
                                        <td class="text-center"><?php echo $categoria['total_vendido']; ?></td>
                                        <td class="text-end">R$ <?php echo number_format($categoria['valor_total'], 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Nenhuma venda por categoria no período selecionado.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Vendas por marca -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-secondary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-copyright"></i> Vendas por Marca</h5>
            </div>
            <div class="card-body">
                <?php if (count($vendasPorMarca) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Marca</th>
                                    <th class="text-center">Qtd. Vendida</th>
                                    <th class="text-end">Valor Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vendasPorMarca as $marca): ?>
                                    <tr>
                                        <td><?php echo $marca['NOME'] ?: 'Sem marca'; ?></td>
                                        <td class="text-center"><?php echo $marca['total_vendido']; ?></td>
                                        <td class="text-end">R$ <?php echo number_format($marca['valor_total'], 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Nenhuma venda por marca no período selecionado.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Vendas por status -->
<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="card-title mb-0"><i class="fas fa-tasks"></i> Vendas por Status</h5>
    </div>
    <div class="card-body">
        <?php if (count($vendasPorStatus) > 0): ?>
            <div class="row">
                <?php foreach ($vendasPorStatus as $status): ?>
                    <div class="col-md-3 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <?php
                                $statusClass = '';
                                switch ($status['STATUS']) {
                                    case 'Pendente':
                                        $statusClass = 'warning';
                                        $icon = 'clock';
                                        break;
                                    case 'Em processamento':
                                        $statusClass = 'info';
                                        $icon = 'spinner';
                                        break;
                                    case 'Concluído':
                                        $statusClass = 'success';
                                        $icon = 'check-circle';
                                        break;
                                    case 'Cancelado':
                                        $statusClass = 'danger';
                                        $icon = 'times-circle';
                                        break;
                                    default:
                                        $statusClass = 'secondary';
                                        $icon = 'question-circle';
                                }
                                ?>
                                <h5 class="card-title text-<?php echo $statusClass; ?>">
                                    <i class="fas fa-<?php echo $icon; ?>"></i> <?php echo $status['STATUS']; ?>
                                </h5>
                                <h3 class="mb-2"><?php echo $status['total']; ?> pedidos</h3>
                                <p class="card-text">R$ <?php echo number_format($status['valor_total'], 2, ',', '.'); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Nenhuma venda no período selecionado.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Gráfico de vendas por dia -->
<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="card-title mb-0"><i class="fas fa-chart-line"></i> Evolução de Vendas no Período</h5>
    </div>
    <div class="card-body">
        <?php if (count($vendasPorDia) > 0): ?>
            <canvas id="vendasChart" height="100"></canvas>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Preparar dados para o gráfico
                    const datas = [];
                    const valores = [];
                    const quantidades = [];
                    
                    <?php foreach ($vendasPorDia as $venda): ?>
                        datas.push('<?php echo date("d/m", strtotime($venda["data"])); ?>');
                        valores.push(<?php echo $venda['valor_total']; ?>);
                        quantidades.push(<?php echo $venda['total_pedidos']; ?>);
                    <?php endforeach; ?>
                    
                    // Criar o gráfico
                    const ctx = document.getElementById('vendasChart').getContext('2d');
                    const chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: datas,
                            datasets: [
                                {
                                    label: 'Valor Total (R$)',
                                    data: valores,
                                    borderColor: 'rgba(40, 167, 69, 1)',
                                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    yAxisID: 'y'
                                },
                                {
                                    label: 'Quantidade de Pedidos',
                                    data: quantidades,
                                    borderColor: 'rgba(0, 123, 255, 1)',
                                    backgroundColor: 'rgba(0, 123, 255, 0)',
                                    borderWidth: 2,
                                    borderDash: [5, 5],
                                    pointRadius: 4,
                                    yAxisID: 'y1'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            scales: {
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    title: {
                                        display: true,
                                        text: 'Valor Total (R$)'
                                    },
                                    beginAtZero: true
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    title: {
                                        display: true,
                                        text: 'Quantidade de Pedidos'
                                    },
                                    beginAtZero: true,
                                    grid: {
                                        drawOnChartArea: false
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        <?php else: ?>
            <div class="alert alert-info">Não há dados suficientes para gerar o gráfico no período selecionado.</div>
        <?php endif; ?>
    </div>
</div>

<?php
// Incluir o rodapé
include '../includes/footer.php';
?>