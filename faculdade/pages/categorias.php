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
        // Verificar se a categoria está sendo usada em algum produto
        $checkProdutos = $db->query("SELECT COUNT(*) as total FROM produto WHERE IDCATEGORIA = ?", [$id])->fetch();
        
        if ($checkProdutos['total'] > 0) {
            $_SESSION['message'] = "Esta categoria não pode ser excluída pois está associada a produtos.";
            $_SESSION['message_type'] = "danger";
        } else {
            // Excluir a categoria
            $db->query("DELETE FROM categoria WHERE IDCATEGORIA = ?", [$id]);
            $_SESSION['message'] = "Categoria excluída com sucesso!";
            $_SESSION['message_type'] = "success";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Erro ao excluir categoria: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    // Redirecionar para evitar reenvio do formulário
    redirect(BASE_URL . '/pages/categorias.php');
}

// Processar formulário de adição/edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nome = cleanInput($_POST['nome']);
    $descricao = cleanInput($_POST['descricao']);
    
    // Validar dados
    $errors = [];
    
    if (empty($nome)) {
        $errors[] = "O nome da categoria é obrigatório.";
    }
    
    // Se não houver erros, salvar no banco de dados
    if (empty($errors)) {
        try {
            if ($id > 0) {
                // Atualizar categoria existente
                $db->query(
                    "UPDATE categoria SET NOME = ?, DESCRICAO = ? WHERE IDCATEGORIA = ?",
                    [$nome, $descricao, $id]
                );
                
                $_SESSION['message'] = "Categoria atualizada com sucesso!";
            } else {
                // Inserir nova categoria
                $db->query(
                    "INSERT INTO categoria (NOME, DESCRICAO) VALUES (?, ?)",
                    [$nome, $descricao]
                );
                
                $_SESSION['message'] = "Categoria cadastrada com sucesso!";
            }
            
            $_SESSION['message_type'] = "success";
            
        } catch (Exception $e) {
            $_SESSION['message'] = "Erro ao salvar categoria: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
        
        // Redirecionar para evitar reenvio do formulário
        redirect(BASE_URL . '/pages/categorias.php');
    }
}

// Carregar categoria para edição se solicitado
$categoriaEdit = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $categoriaEdit = $db->query("SELECT * FROM categoria WHERE IDCATEGORIA = ?", [$editId])->fetch();
    
    if (!$categoriaEdit) {
        $_SESSION['message'] = "Categoria não encontrada.";
        $_SESSION['message_type'] = "danger";
        redirect(BASE_URL . '/pages/categorias.php');
    }
}

// Configuração de paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Configuração de busca
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';

// Construir a consulta SQL com filtros
$sql = "SELECT * FROM categoria WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (NOME LIKE ? OR DESCRICAO LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Contar total de registros para paginação
$countSql = str_replace("*", "COUNT(*) as total", $sql);
$totalItems = $db->query($countSql, $params)->fetch()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Adicionar ordenação e limite para paginação
$sql .= " ORDER BY NOME ASC LIMIT $offset, $itemsPerPage";

// Executar a consulta
$categorias = $db->query($sql, $params)->fetchAll();

// Incluir o cabeçalho
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Categorias</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoriaModal">
        <i class="fas fa-plus"></i> Nova Categoria
    </button>
</div>

<!-- Filtros e busca -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="categorias.php" class="row g-3">
            <div class="col-md-10">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" value="<?php echo $search; ?>" placeholder="Nome ou descrição">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Exibir mensagens de erro do formulário -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Tabela de categorias -->
<div class="card">
    <div class="card-body">
        <?php if (count($categorias) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Produtos</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorias as $categoria): 
                            // Contar produtos nesta categoria
                            $produtosCount = $db->query(
                                "SELECT COUNT(*) as total FROM produto WHERE IDCATEGORIA = ?", 
                                [$categoria['IDCATEGORIA']]
                            )->fetch()['total'];
                        ?>
                            <tr>
                                <td><?php echo $categoria['IDCATEGORIA']; ?></td>
                                <td><?php echo $categoria['NOME']; ?></td>
                                <td><?php echo !empty($categoria['DESCRICAO']) ? $categoria['DESCRICAO'] : '<span class="text-muted">Sem descrição</span>'; ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo $produtosCount; ?></span>
                                </td>
                                <td class="text-end">
                                    <a href="categorias.php?edit=<?php echo $categoria['IDCATEGORIA']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="categorias.php?delete=<?php echo $categoria['IDCATEGORIA']; ?>" class="btn btn-sm btn-danger btn-delete" data-bs-toggle="tooltip" title="Excluir">
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
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>" aria-label="Anterior">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>" aria-label="Próximo">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Nenhuma categoria encontrada.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para adicionar/editar categoria -->
<div class="modal fade" id="categoriaModal" tabindex="-1" aria-labelledby="categoriaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="categorias.php" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="categoriaModalLabel">
                        <?php echo $categoriaEdit ? 'Editar Categoria' : 'Nova Categoria'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <?php if ($categoriaEdit): ?>
                        <input type="hidden" name="id" value="<?php echo $categoriaEdit['IDCATEGORIA']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome*</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $categoriaEdit ? $categoriaEdit['NOME'] : ''; ?>" required>
                        <div class="invalid-feedback">Por favor, informe o nome da categoria.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"><?php echo $categoriaEdit ? $categoriaEdit['DESCRICAO'] : ''; ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Se estiver editando, abrir o modal automaticamente
if ($categoriaEdit): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var categoriaModal = new bootstrap.Modal(document.getElementById('categoriaModal'));
        categoriaModal.show();
    });
</script>
<?php endif; ?>

<?php
// Incluir o rodapé
include '../includes/footer.php';
?>