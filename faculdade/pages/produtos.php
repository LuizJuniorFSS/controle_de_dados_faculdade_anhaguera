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
        // Verificar se o produto está em algum pedido
        $checkPedido = $db->query("SELECT COUNT(*) as total FROM item_pedido WHERE IDPRODUTO = ?", [$id])->fetch();
        
        if ($checkPedido['total'] > 0) {
            $_SESSION['message'] = "Este produto não pode ser excluído pois está associado a pedidos.";
            $_SESSION['message_type'] = "danger";
        } else {
            // Excluir o produto
            $db->query("DELETE FROM produto WHERE IDPRODUTO = ?", [$id]);
            $_SESSION['message'] = "Produto excluído com sucesso!";
            $_SESSION['message_type'] = "success";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Erro ao excluir produto: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    // Redirecionar para evitar reenvio do formulário
    redirect(BASE_URL . '/pages/produtos.php');
}

// Configuração de paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Configuração de busca e filtros
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$marca = isset($_GET['marca']) ? (int)$_GET['marca'] : 0;

// Construir a consulta SQL com filtros
$sql = "SELECT p.*, c.NOME as CATEGORIA, m.NOME as MARCA 
        FROM produto p 
        LEFT JOIN categoria c ON p.IDCATEGORIA = c.IDCATEGORIA 
        LEFT JOIN marca m ON p.IDMARCA = m.IDMARCA 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (p.NOME LIKE ? OR p.DESCRICAO LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categoria > 0) {
    $sql .= " AND p.IDCATEGORIA = ?";
    $params[] = $categoria;
}

if ($marca > 0) {
    $sql .= " AND p.IDMARCA = ?";
    $params[] = $marca;
}

// Contar total de registros para paginação
$countSql = str_replace("p.*, c.NOME as CATEGORIA, m.NOME as MARCA", "COUNT(*) as total", $sql);
$totalItems = $db->query($countSql, $params)->fetch()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Adicionar ordenação e limite para paginação
$sql .= " ORDER BY p.NOME ASC LIMIT $offset, $itemsPerPage";

// Executar a consulta
$produtos = $db->query($sql, $params)->fetchAll();

// Obter categorias e marcas para filtros
$categorias = $db->query("SELECT * FROM categoria ORDER BY NOME ASC")->fetchAll();
$marcas = $db->query("SELECT * FROM marca ORDER BY NOME ASC")->fetchAll();

// Incluir o cabeçalho
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Produtos</h1>
    <a href="produto-form.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Produto
    </a>
</div>

<!-- Filtros e busca -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="produtos.php" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" value="<?php echo $search; ?>" placeholder="Nome ou descrição">
            </div>
            <div class="col-md-3">
                <label for="categoria" class="form-label">Categoria</label>
                <select class="form-select" id="categoria" name="categoria">
                    <option value="0">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['IDCATEGORIA']; ?>" <?php echo ($categoria == $cat['IDCATEGORIA']) ? 'selected' : ''; ?>>
                            <?php echo $cat['NOME']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="marca" class="form-label">Marca</label>
                <select class="form-select" id="marca" name="marca">
                    <option value="0">Todas</option>
                    <?php foreach ($marcas as $m): ?>
                        <option value="<?php echo $m['IDMARCA']; ?>" <?php echo ($marca == $m['IDMARCA']) ? 'selected' : ''; ?>>
                            <?php echo $m['NOME']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de produtos -->
<div class="card">
    <div class="card-body">
        <?php if (count($produtos) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Marca</th>
                            <th class="text-center">Estoque</th>
                            <th class="text-end">Preço</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $produto): ?>
                            <tr>
                                <td><?php echo $produto['IDPRODUTO']; ?></td>
                                <td><?php echo $produto['NOME']; ?></td>
                                <td><?php echo $produto['CATEGORIA']; ?></td>
                                <td><?php echo $produto['MARCA']; ?></td>
                                <td class="text-center">
                                    <span class="badge bg-<?php echo getEstoqueStatusClass($produto['ESTOQUE']); ?>">
                                        <?php echo $produto['ESTOQUE']; ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    R$ <?php echo number_format($produto['PRECO'], 2, ',', '.'); ?>
                                </td>
                                <td class="text-end">
                                    <a href="produto-detalhes.php?id=<?php echo $produto['IDPRODUTO']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="produto-form.php?id=<?php echo $produto['IDPRODUTO']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="produtos.php?delete=<?php echo $produto['IDPRODUTO']; ?>" class="btn btn-sm btn-danger btn-delete" data-bs-toggle="tooltip" title="Excluir">
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
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&categoria=<?php echo $categoria; ?>&marca=<?php echo $marca; ?>" aria-label="Anterior">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&categoria=<?php echo $categoria; ?>&marca=<?php echo $marca; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&categoria=<?php echo $categoria; ?>&marca=<?php echo $marca; ?>" aria-label="Próximo">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Nenhum produto encontrado.
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