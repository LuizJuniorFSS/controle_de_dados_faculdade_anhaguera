<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Inicializar conexão com o banco de dados
$db = new Database();

// Verificar se o ID do pedido foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "ID do pedido não fornecido.";
    $_SESSION['message_type'] = "danger";
    redirect(BASE_URL . '/pages/pedidos.php');
}

$id = (int)$_GET['id'];

// Buscar dados do pedido
$pedido = $db->query(
    "SELECT p.*, c.NOME as cliente_nome, c.EMAIL as cliente_email, c.TELEFONE as cliente_telefone 
     FROM pedido p 
     LEFT JOIN cliente c ON p.IDCLIENTE = c.IDCLIENTE 
     WHERE p.IDPEDIDO = ?", 
    [$id]
)->fetch();

if (!$pedido) {
    $_SESSION['message'] = "Pedido não encontrado.";
    $_SESSION['message_type'] = "danger";
    redirect(BASE_URL . '/pages/pedidos.php');
}

// Buscar itens do pedido
$itens = $db->query(
    "SELECT i.*, p.NOME as produto_nome, p.DESCRICAO as produto_descricao, 
            c.NOME as categoria_nome, m.NOME as marca_nome 
     FROM item_pedido i 
     JOIN produto p ON i.IDPRODUTO = p.IDPRODUTO 
     LEFT JOIN categoria c ON p.IDCATEGORIA = c.IDCATEGORIA 
     LEFT JOIN marca m ON p.IDMARCA = m.IDMARCA 
     WHERE i.IDPEDIDO = ? 
     ORDER BY p.NOME ASC", 
    [$id]
)->fetchAll();

// Processar atualização de status se solicitado
if (isset($_POST['atualizar_status'])) {
    $novoStatus = cleanInput($_POST['status']);
    
    try {
        $db->query("UPDATE pedido SET STATUS = ? WHERE IDPEDIDO = ?", [$novoStatus, $id]);
        
        $_SESSION['message'] = "Status do pedido atualizado com sucesso!";
        $_SESSION['message_type'] = "success";
        
        // Atualizar o status na variável do pedido
        $pedido['STATUS'] = $novoStatus;
        
    } catch (Exception $e) {
        $_SESSION['message'] = "Erro ao atualizar status: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
}

// Incluir o cabeçalho
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Pedido #<?php echo $pedido['IDPEDIDO']; ?></h1>
    <div>
        <a href="pedido-form.php?id=<?php echo $pedido['IDPEDIDO']; ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="pedidos.php" class="btn btn-secondary ms-2">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <!-- Informações do Pedido -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> Informações do Pedido</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Data do Pedido:</strong>
                        <p><?php echo date('d/m/Y H:i', strtotime($pedido['DATAPEDIDO'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Valor Total:</strong>
                        <p class="text-success fw-bold">R$ <?php echo number_format($pedido['VALORTOTAL'], 2, ',', '.'); ?></p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong>Status:</strong>
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
                    <span class="badge bg-<?php echo $statusClass; ?> fs-6">
                        <?php echo $pedido['STATUS']; ?>
                    </span>
                </div>
                
                <!-- Formulário para atualizar status -->
                <form method="POST" action="" class="mb-4">
                    <div class="input-group">
                        <select class="form-select" name="status">
                            <option value="Pendente" <?php echo ($pedido['STATUS'] == 'Pendente') ? 'selected' : ''; ?>>Pendente</option>
                            <option value="Em processamento" <?php echo ($pedido['STATUS'] == 'Em processamento') ? 'selected' : ''; ?>>Em processamento</option>
                            <option value="Concluído" <?php echo ($pedido['STATUS'] == 'Concluído') ? 'selected' : ''; ?>>Concluído</option>
                            <option value="Cancelado" <?php echo ($pedido['STATUS'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                        <button type="submit" name="atualizar_status" class="btn btn-primary">
                            <i class="fas fa-sync-alt"></i> Atualizar Status
                        </button>
                    </div>
                </form>
                
                <?php if (!empty($pedido['OBSERVACOES'])): ?>
                    <div class="mb-3">
                        <strong>Observações:</strong>
                        <p><?php echo nl2br($pedido['OBSERVACOES']); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Informações do Cliente -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0"><i class="fas fa-user"></i> Informações do Cliente</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($pedido['cliente_nome'])): ?>
                    <h4 class="mb-3"><?php echo $pedido['cliente_nome']; ?></h4>
                    
                    <div class="mb-3">
                        <strong><i class="fas fa-envelope"></i> Email:</strong>
                        <?php if (!empty($pedido['cliente_email'])): ?>
                            <a href="mailto:<?php echo $pedido['cliente_email']; ?>"><?php echo $pedido['cliente_email']; ?></a>
                        <?php else: ?>
                            <span class="text-muted">Não informado</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <strong><i class="fas fa-phone"></i> Telefone:</strong>
                        <?php if (!empty($pedido['cliente_telefone'])): ?>
                            <a href="tel:<?php echo $pedido['cliente_telefone']; ?>"><?php echo $pedido['cliente_telefone']; ?></a>
                        <?php else: ?>
                            <span class="text-muted">Não informado</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-3">
                        <a href="cliente-detalhes.php?id=<?php echo $pedido['IDCLIENTE']; ?>" class="btn btn-outline-info">
                            <i class="fas fa-eye"></i> Ver Detalhes do Cliente
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Cliente não encontrado ou removido.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Itens do Pedido -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="card-title mb-0"><i class="fas fa-shopping-basket"></i> Itens do Pedido</h5>
    </div>
    <div class="card-body">
        <?php if (count($itens) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Marca</th>
                            <th class="text-center">Quantidade</th>
                            <th class="text-end">Preço Unit.</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itens as $item): ?>
                            <tr>
                                <td>
                                    <a href="produto-detalhes.php?id=<?php echo $item['IDPRODUTO']; ?>">
                                        <?php echo $item['produto_nome']; ?>
                                    </a>
                                    <?php if (!empty($item['produto_descricao'])): ?>
                                        <small class="d-block text-muted"><?php echo substr($item['produto_descricao'], 0, 50); ?><?php echo (strlen($item['produto_descricao']) > 50) ? '...' : ''; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $item['categoria_nome'] ?: 'N/A'; ?></td>
                                <td><?php echo $item['marca_nome'] ?: 'N/A'; ?></td>
                                <td class="text-center"><?php echo $item['QUANTIDADE']; ?></td>
                                <td class="text-end">R$ <?php echo number_format($item['PRECO'], 2, ',', '.'); ?></td>
                                <td class="text-end fw-bold">R$ <?php echo number_format($item['SUBTOTAL'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end"><strong>Total:</strong></td>
                            <td class="text-end fw-bold fs-5 text-success">R$ <?php echo number_format($pedido['VALORTOTAL'], 2, ',', '.'); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Este pedido não possui itens.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Ações do Pedido -->
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="card-title mb-0"><i class="fas fa-cogs"></i> Ações</h5>
    </div>
    <div class="card-body">
        <div class="d-flex gap-2">
            <a href="pedido-form.php?id=<?php echo $pedido['IDPEDIDO']; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar Pedido
            </a>
            
            <a href="pedidos.php?delete=<?php echo $pedido['IDPEDIDO']; ?>" class="btn btn-danger btn-delete">
                <i class="fas fa-trash"></i> Excluir Pedido
            </a>
            
            <a href="#" class="btn btn-success" onclick="window.print(); return false;">
                <i class="fas fa-print"></i> Imprimir
            </a>
        </div>
    </div>
</div>

<?php
// Incluir o rodapé
include '../includes/footer.php';
?>