<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Inicializar conexão com o banco de dados
$db = new Database();

// Processar exclusão se solicitado
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    try {
        // Verificar se o pedido tem itens associados
        $checkItens = $db->query("SELECT COUNT(*) as total FROM item_pedido WHERE IDPEDIDO = ?", [$id])->fetch();
        
        if ($checkItens['total'] > 0) {
            // Excluir os itens do pedido primeiro
            $db->query("DELETE FROM item_pedido WHERE IDPEDIDO = ?", [$id]);
        }
        
        // Excluir o pedido
        $db->query("DELETE FROM pedido WHERE IDPEDIDO = ?", [$id]);
        $_SESSION['message'] = "Pedido excluído com sucesso!";
        $_SESSION['message_type'] = "success";
        
    } catch (Exception $e) {
        $_SESSION['message'] = "Erro ao excluir pedido: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    // Redirecionar para evitar reenvio do formulário
    redirect(BASE_URL . '/pages/pedidos.php');
}

// Configuração de paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Configuração de busca e filtros
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$cliente = isset($_GET['cliente']) ? (int)$_GET['cliente'] : 0;
$status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
$dataInicio = isset($_GET['data_inicio']) ? cleanInput($_GET['data_inicio']) : '';
$dataFim = isset($_GET['data_fim']) ? cleanInput($_GET['data_fim']) : '';

// Construir a consulta SQL com filtros
$sql = "SELECT p.*, c.NOME as cliente_nome FROM pedido p 
        LEFT JOIN cliente c ON p.IDCLIENTE = c.IDCLIENTE 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (p.IDPEDIDO LIKE ? OR c.NOME LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($cliente > 0) {
    $sql .= " AND p.IDCLIENTE = ?";
    $params[] = $cliente;
}

if (!empty($status)) {
    $sql .= " AND p.STATUS = ?";
    $params[] = $status;
}

if (!empty($dataInicio)) {
    $sql .= " AND p.DATAPEDIDO >= ?";
    $params[] = $dataInicio . ' 00:00:00';
}

if (!empty($dataFim)) {
    $sql .= " AND p.DATAPEDIDO <= ?";
    $params[] = $dataFim . ' 23:59:59';
}

// Contar total de registros para paginação
$countSql = str_replace("p.*, c.NOME as cliente_nome", "COUNT(*) as total", $sql);
$totalItems = $db->query($countSql, $params)->fetch()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Adicionar ordenação e limite para paginação
$sql .= " ORDER BY p.DATAPEDIDO DESC LIMIT $offset, $itemsPerPage";

// Executar a consulta
$pedidos = $db->query($sql, $params)->fetchAll();

// Buscar todos os clientes para o filtro
$clientes = $db->query("SELECT IDCLIENTE, NOME FROM cliente ORDER BY NOME ASC")->fetchAll();

// Incluir o cabeçalho
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Pedidos</h1>
    <a href="pedido-form.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Pedido
    </a>
</div>

<!-- Filtros e busca -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="pedidos.php" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" value="<?php echo $search; ?>" placeholder="ID ou cliente">
            </div>
            
            <div class="col-md-3">
                <label for="cliente" class="form-label">Cliente</label>
                <select class="form-select" id="cliente" name="cliente">
                    <option value="">Todos</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?php echo $c['IDCLIENTE']; ?>" <?php echo ($cliente == $c['IDCLIENTE']) ? 'selected' : ''; ?>>
                            <?php echo $c['NOME']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="Pendente" <?php echo ($status == 'Pendente') ? 'selected' : ''; ?>>Pendente</option>
                    <option value="Em processamento" <?php echo ($status == 'Em processamento') ? 'selected' : ''; ?>>Em processamento</option>
                    <option value="Concluído" <?php echo ($status == 'Concluído') ? 'selected' : ''; ?>>Concluído</option>
                    <option value="Cancelado" <?php echo ($status == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="data_inicio" class="form-label">Data Início</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo $dataInicio; ?>">
            </div>
            
            <div class="col-md-2">
                <label for="data_fim" class="form-label">Data Fim</label>
                <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo $dataFim; ?>">
            </div>
            
            <div class="col-12 d-flex justify-content-end gap-2">
                <a href="pedidos.php" class="btn btn-secondary">
                    <i class="fas fa-eraser"></i> Limpar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de pedidos -->
<div class="card">
    <div class="card-body">
        <?php if (count($pedidos) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Cliente</th>
                            <th>Itens</th>
                            <th>Valor Total</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $pedido): 
                            // Contar itens deste pedido
                            $itensCount = $db->query(
                                "SELECT COUNT(*) as total FROM item_pedido WHERE IDPEDIDO = ?", 
                                [$pedido['IDPEDIDO']]
                            )->fetch()['total'];
                        ?>
                            <tr>
                                <td><?php echo $pedido['IDPEDIDO']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($pedido['DATAPEDIDO'])); ?></td>
                                <td>
                                    <?php if (!empty($pedido['cliente_nome'])): ?>
                                        <a href="cliente-detalhes.php?id=<?php echo $pedido['IDCLIENTE']; ?>">
                                            <?php echo $pedido['cliente_nome']; ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Cliente não encontrado</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $itensCount; ?></td>
                                <td>R$ <?php echo number_format($pedido['VALORTOTAL'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    switch ($pedido['STATUS']) {
                                        case 'Pendente':
                                            $statusClass = 'warning';
                                            break;
                                        case 'Em processamento':
                                            $statusClass = 'info';
                                            break;
                                        case 'Concluído':
                                            $statusClass = 'success';
                                            break;
                                        case 'Cancelado':
                                            $statusClass = 'danger';
                                            break;
                                        default:
                                            $statusClass = 'secondary';
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                        <?php echo $pedido['STATUS']; ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="pedido-detalhes.php?id=<?php echo $pedido['IDPEDIDO']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="pedido-form.php?id=<?php echo $pedido['IDPEDIDO']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="pedidos.php?delete=<?php echo $pedido['IDPEDIDO']; ?>" class="btn btn-sm btn-danger btn-delete" data-bs-toggle="tooltip" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Navegação de página">
                    <ul class="pagination justify-content-center mt-4">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&cliente=<?php echo $cliente; ?>&status=<?php echo $status; ?>&data_inicio=<?php echo $dataInicio; ?>&data_fim=<?php echo $dataFim; ?>" aria-label="Anterior">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&cliente=<?php echo $cliente; ?>&status=<?php echo $status; ?>&data_inicio=<?php echo $dataInicio; ?>&data_fim=<?php echo $dataFim; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&cliente=<?php echo $cliente; ?>&status=<?php echo $status; ?>&data_inicio=<?php echo $dataInicio; ?>&data_fim=<?php echo $dataFim; ?>" aria-label="Próximo">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Nenhum pedido encontrado.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Incluir o rodapé
include '../includes/footer.php';
?>