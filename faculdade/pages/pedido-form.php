<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Inicializar conexão com o banco de dados
$db = new Database();

// Verificar se é edição ou novo cadastro
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$clientePreSelecionado = isset($_GET['cliente']) ? (int)$_GET['cliente'] : 0;
$pedido = [];
$itensPedido = [];
$pageTitle = 'Novo Pedido';

if ($id > 0) {
    // Buscar dados do pedido para edição
    $pedido = $db->query("SELECT * FROM pedido WHERE IDPEDIDO = ?", [$id])->fetch();
    
    if (!$pedido) {
        $_SESSION['message'] = "Pedido não encontrado.";
        $_SESSION['message_type'] = "danger";
        redirect(BASE_URL . '/pages/pedidos.php');
    }
    
    // Buscar itens do pedido
    $itensPedido = $db->query(
        "SELECT i.*, p.NOME as produto_nome, p.PRECO as produto_preco 
         FROM item_pedido i 
         JOIN produto p ON i.IDPRODUTO = p.IDPRODUTO 
         WHERE i.IDPEDIDO = ?", 
        [$id]
    )->fetchAll();
    
    $pageTitle = 'Editar Pedido';
}

// Buscar todos os clientes para o select
$clientes = $db->query("SELECT IDCLIENTE, NOME FROM cliente ORDER BY NOME ASC")->fetchAll();

// Buscar todos os produtos para o select
$produtos = $db->query(
    "SELECT p.IDPRODUTO, p.NOME, p.PRECO, p.ESTOQUE, c.NOME as categoria_nome 
     FROM produto p 
     LEFT JOIN categoria c ON p.IDCATEGORIA = c.IDCATEGORIA 
     WHERE p.ESTOQUE > 0 
     ORDER BY p.NOME ASC"
)->fetchAll();

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coletar dados do formulário
    $clienteId = (int)$_POST['cliente_id'];
    $status = cleanInput($_POST['status']);
    $observacoes = cleanInput($_POST['observacoes']);
    $produtosIds = isset($_POST['produto_id']) ? $_POST['produto_id'] : [];
    $quantidades = isset($_POST['quantidade']) ? $_POST['quantidade'] : [];
    $precos = isset($_POST['preco']) ? $_POST['preco'] : [];
    
    // Validação básica
    $errors = [];
    
    if ($clienteId <= 0) {
        $errors[] = "Selecione um cliente.";
    }
    
    if (empty($produtosIds)) {
        $errors[] = "Adicione pelo menos um produto ao pedido.";
    }
    
    // Se não houver erros, salvar no banco de dados
    if (empty($errors)) {
        try {
            $db->beginTransaction();
            
            // Calcular valor total do pedido
            $valorTotal = 0;
            foreach ($produtosIds as $index => $produtoId) {
                if (isset($quantidades[$index]) && isset($precos[$index])) {
                    $valorTotal += $quantidades[$index] * $precos[$index];
                }
            }
            
            if ($id > 0) {
                // Atualizar pedido existente
                $db->query(
                    "UPDATE pedido SET IDCLIENTE = ?, STATUS = ?, OBSERVACOES = ?, VALORTOTAL = ? WHERE IDPEDIDO = ?",
                    [$clienteId, $status, $observacoes, $valorTotal, $id]
                );
                
                // Excluir itens antigos
                $db->query("DELETE FROM item_pedido WHERE IDPEDIDO = ?", [$id]);
                
                $pedidoId = $id;
                $_SESSION['message'] = "Pedido atualizado com sucesso!";
            } else {
                // Inserir novo pedido
                $db->query(
                    "INSERT INTO pedido (IDCLIENTE, DATAPEDIDO, STATUS, OBSERVACOES, VALORTOTAL) VALUES (?, NOW(), ?, ?, ?)",
                    [$clienteId, $status, $observacoes, $valorTotal]
                );
                
                $pedidoId = $db->lastInsertId();
                $_SESSION['message'] = "Pedido cadastrado com sucesso!";
            }
            
            // Inserir itens do pedido
            foreach ($produtosIds as $index => $produtoId) {
                if (!empty($produtoId) && isset($quantidades[$index]) && isset($precos[$index])) {
                    $quantidade = (int)$quantidades[$index];
                    $preco = convertMoneyToFloat($precos[$index]);
                    $subtotal = $quantidade * $preco;
                    
                    if ($quantidade > 0) {
                        $db->query(
                            "INSERT INTO item_pedido (IDPEDIDO, IDPRODUTO, QUANTIDADE, PRECO, SUBTOTAL) VALUES (?, ?, ?, ?, ?)",
                            [$pedidoId, $produtoId, $quantidade, $preco, $subtotal]
                        );
                        
                        // Atualizar estoque do produto
                        $db->query(
                            "UPDATE produto SET ESTOQUE = ESTOQUE - ? WHERE IDPRODUTO = ?",
                            [$quantidade, $produtoId]
                        );
                    }
                }
            }
            
            $db->commit();
            $_SESSION['message_type'] = "success";
            redirect(BASE_URL . '/pages/pedido-detalhes.php?id=' . $pedidoId);
            
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['message'] = "Erro ao salvar pedido: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    } else {
        // Se houver erros, exibir mensagens
        $_SESSION['message'] = implode("<br>", $errors);
        $_SESSION['message_type'] = "danger";
    }
}

// Incluir o cabeçalho
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?php echo $pageTitle; ?></h1>
    <a href="pedidos.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<form method="POST" action="" id="pedidoForm">
    <div class="row">
        <!-- Informações do Pedido -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> Informações do Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="cliente_id" class="form-label">Cliente *</label>
                        <select class="form-select" id="cliente_id" name="cliente_id" required>
                            <option value="">Selecione um cliente</option>
                            <?php foreach ($clientes as $cliente): 
                                $selected = '';
                                if (isset($pedido['IDCLIENTE']) && $pedido['IDCLIENTE'] == $cliente['IDCLIENTE']) {
                                    $selected = 'selected';
                                } elseif ($clientePreSelecionado == $cliente['IDCLIENTE']) {
                                    $selected = 'selected';
                                }
                            ?>
                                <option value="<?php echo $cliente['IDCLIENTE']; ?>" <?php echo $selected; ?>>
                                    <?php echo $cliente['NOME']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status *</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Pendente" <?php echo (isset($pedido['STATUS']) && $pedido['STATUS'] == 'Pendente') ? 'selected' : ''; ?>>
                                Pendente
                            </option>
                            <option value="Em processamento" <?php echo (isset($pedido['STATUS']) && $pedido['STATUS'] == 'Em processamento') ? 'selected' : ''; ?>>
                                Em processamento
                            </option>
                            <option value="Concluído" <?php echo (isset($pedido['STATUS']) && $pedido['STATUS'] == 'Concluído') ? 'selected' : ''; ?>>
                                Concluído
                            </option>
                            <option value="Cancelado" <?php echo (isset($pedido['STATUS']) && $pedido['STATUS'] == 'Cancelado') ? 'selected' : ''; ?>>
                                Cancelado
                            </option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?php echo isset($pedido['OBSERVACOES']) ? $pedido['OBSERVACOES'] : ''; ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Produtos do Pedido -->
        <div class="col-md-8 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-shopping-cart"></i> Produtos do Pedido</h5>
                    <button type="button" class="btn btn-light btn-sm" id="addProdutoBtn">
                        <i class="fas fa-plus"></i> Adicionar Produto
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="produtosTable">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th width="120">Quantidade</th>
                                    <th width="150">Preço Unit.</th>
                                    <th width="150">Subtotal</th>
                                    <th width="50">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($itensPedido)): ?>
                                    <?php foreach ($itensPedido as $index => $item): ?>
                                        <tr class="produto-row">
                                            <td>
                                                <select class="form-select produto-select" name="produto_id[]" required>
                                                    <option value="">Selecione um produto</option>
                                                    <?php foreach ($produtos as $produto): ?>
                                                        <option value="<?php echo $produto['IDPRODUTO']; ?>" 
                                                                data-preco="<?php echo $produto['PRECO']; ?>" 
                                                                data-estoque="<?php echo $produto['ESTOQUE']; ?>"
                                                                <?php echo ($item['IDPRODUTO'] == $produto['IDPRODUTO']) ? 'selected' : ''; ?>>
                                                            <?php echo $produto['NOME']; ?> 
                                                            (<?php echo $produto['categoria_nome']; ?>) - 
                                                            R$ <?php echo number_format($produto['PRECO'], 2, ',', '.'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control quantidade-input" name="quantidade[]" min="1" value="<?php echo $item['QUANTIDADE']; ?>" required>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-text">R$</span>
                                                    <input type="number" class="form-control preco-input" name="preco[]" step="0.01" min="0.01" value="<?php echo $item['PRECO']; ?>" required>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-text">R$</span>
                                                    <input type="text" class="form-control subtotal-input" value="<?php echo number_format($item['SUBTOTAL'], 2, '.', ''); ?>" readonly>
                                                </div>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm remove-produto">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="produto-row">
                                        <td>
                                            <select class="form-select produto-select" name="produto_id[]" required>
                                                <option value="">Selecione um produto</option>
                                                <?php foreach ($produtos as $produto): ?>
                                                    <option value="<?php echo $produto['IDPRODUTO']; ?>" 
                                                            data-preco="<?php echo $produto['PRECO']; ?>" 
                                                            data-estoque="<?php echo $produto['ESTOQUE']; ?>">
                                                        <?php echo $produto['NOME']; ?> 
                                                        (<?php echo $produto['categoria_nome']; ?>) - 
                                                        R$ <?php echo number_format($produto['PRECO'], 2, ',', '.'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control quantidade-input" name="quantidade[]" min="1" value="1" required>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text">R$</span>
                                                <input type="number" class="form-control preco-input" name="preco[]" step="0.01" min="0.01" value="0.00" required>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text">R$</span>
                                                <input type="text" class="form-control subtotal-input" value="0.00" readonly>
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-produto">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">R$</span>
                                            <input type="text" class="form-control" id="totalPedido" value="0.00" readonly>
                                        </div>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-save"></i> Salvar Pedido
        </button>
    </div>
</form>

<!-- Template para nova linha de produto -->
<template id="novoProdutoTemplate">
    <tr class="produto-row">
        <td>
            <select class="form-select produto-select" name="produto_id[]" required>
                <option value="">Selecione um produto</option>
                <?php foreach ($produtos as $produto): ?>
                    <option value="<?php echo $produto['IDPRODUTO']; ?>" 
                            data-preco="<?php echo $produto['PRECO']; ?>" 
                            data-estoque="<?php echo $produto['ESTOQUE']; ?>">
                        <?php echo $produto['NOME']; ?> 
                        (<?php echo $produto['categoria_nome']; ?>) - 
                        R$ <?php echo number_format($produto['PRECO'], 2, ',', '.'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <input type="number" class="form-control quantidade-input" name="quantidade[]" min="1" value="1" required>
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-text">R$</span>
                <input type="number" class="form-control preco-input" name="preco[]" step="0.01" min="0.01" value="0.00" required>
            </div>
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-text">R$</span>
                <input type="text" class="form-control subtotal-input" value="0.00" readonly>
            </div>
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm remove-produto">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Função para calcular subtotal de um item
        function calcularSubtotal(row) {
            const quantidade = parseFloat(row.querySelector('.quantidade-input').value) || 0;
            const preco = parseFloat(row.querySelector('.preco-input').value) || 0;
            const subtotal = quantidade * preco;
            row.querySelector('.subtotal-input').value = subtotal.toFixed(2);
            return subtotal;
        }
        
        // Função para calcular o total do pedido
        function calcularTotal() {
            let total = 0;
            document.querySelectorAll('.produto-row').forEach(row => {
                total += calcularSubtotal(row);
            });
            document.getElementById('totalPedido').value = total.toFixed(2);
        }
        
        // Calcular total inicial
        calcularTotal();
        
        // Adicionar novo produto
        document.getElementById('addProdutoBtn').addEventListener('click', function() {
            const template = document.getElementById('novoProdutoTemplate');
            const clone = document.importNode(template.content, true);
            document.querySelector('#produtosTable tbody').appendChild(clone);
            
            // Adicionar eventos à nova linha
            const newRow = document.querySelector('#produtosTable tbody tr:last-child');
            addRowEvents(newRow);
        });
        
        // Função para adicionar eventos a uma linha
        function addRowEvents(row) {
            // Evento de seleção de produto
            row.querySelector('.produto-select').addEventListener('change', function() {
                const option = this.options[this.selectedIndex];
                const preco = option.dataset.preco || 0;
                row.querySelector('.preco-input').value = preco;
                calcularTotal();
            });
            
            // Eventos de alteração de quantidade e preço
            row.querySelector('.quantidade-input').addEventListener('input', function() {
                calcularTotal();
            });
            
            row.querySelector('.preco-input').addEventListener('input', function() {
                calcularTotal();
            });
            
            // Evento de remoção de produto
            row.querySelector('.remove-produto').addEventListener('click', function() {
                if (document.querySelectorAll('.produto-row').length > 1) {
                    row.remove();
                    calcularTotal();
                } else {
                    alert('O pedido deve ter pelo menos um produto.');
                }
            });
        }
        
        // Adicionar eventos às linhas existentes
        document.querySelectorAll('.produto-row').forEach(row => {
            addRowEvents(row);
        });
    });
</script>

<?php
// Incluir o rodapé
include '../includes/footer.php';
?>