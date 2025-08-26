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
        // Verificar se a marca está sendo usada em algum produto
        $checkProdutos = $db->query("SELECT COUNT(*) as total FROM produto WHERE IDMARCA = ?", [$id])->fetch();
        
        if ($checkProdutos['total'] > 0) {
            $_SESSION['message'] = "Esta marca não pode ser excluída pois está associada a produtos.";
            $_SESSION['message_type'] = "danger";
        } else {
            // Excluir a marca
            $db->query("DELETE FROM marca WHERE IDMARCA = ?", [$id]);
            $_SESSION['message'] = "Marca excluída com sucesso!";
            $_SESSION['message_type'] = "success";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Erro ao excluir marca: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    // Redirecionar para evitar reenvio do formulário
    redirect(BASE_URL . '/pages/marcas.php');
}

// Processar formulário de adição/edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nome = cleanInput($_POST['nome']);
    $site = cleanInput($_POST['site']);
    
    // Validar dados
    $errors = [];
    
    if (empty($nome)) {
        $errors[] = "O nome da marca é obrigatório.";
    }
    
    // Se não houver erros, salvar no banco de dados
    if (empty($errors)) {
        try {
            if ($id > 0) {
                // Atualizar marca existente
                $db->query(
                    "UPDATE marca SET NOME = ?, SITE = ? WHERE IDMARCA = ?",
                    [$nome, $site, $id]
                );
                
                $_SESSION['message'] = "Marca atualizada com sucesso!";
            } else {
                // Inserir nova marca
                $db->query(
                    "INSERT INTO marca (NOME, SITE) VALUES (?, ?)",
                    [$nome, $site]
                );
                
                $_SESSION['message'] = "Marca cadastrada com sucesso!";
            }
            
            $_SESSION['message_type'] = "success";
            
        } catch (Exception $e) {
            $_SESSION['message'] = "Erro ao salvar marca: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
        
        // Redirecionar para evitar reenvio do formulário
        redirect(BASE_URL . '/pages/marcas.php');
    }
}

// Carregar marca para edição se solicitado
$marcaEdit = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $marcaEdit = $db->query("SELECT * FROM marca WHERE IDMARCA = ?", [$editId])->fetch();
    
    if (!$marcaEdit) {
        $_SESSION['message'] = "Marca não encontrada.";
        $_SESSION['message_type'] = "danger";
        redirect(BASE_URL . '/pages/marcas.php');
    }
}

// Configuração de paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Configuração de busca
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';

// Construir a consulta SQL com filtros
$sql = "SELECT * FROM marca WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (NOME LIKE ? OR SITE LIKE ?)";
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
$marcas = $db->query($sql, $params)->fetchAll();

// Incluir o cabeçalho
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Marcas</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#marcaModal">
        <i class="fas fa-plus"></i> Nova Marca
    </button>
</div>

<!-- Filtros e busca -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="marcas.php" class="row g-3">
            <div class="col-md-10">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" value="<?php echo $search; ?>" placeholder="Nome ou site">
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

<!-- Tabela de marcas -->
<div class="card">
    <div class="card-body">
        <?php if (count($marcas) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Site</th>
                            <th>Produtos</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($marcas as $marca): 
                            // Contar produtos desta marca
                            $produtosCount = $db->query(
                                "SELECT COUNT(*) as total FROM produto WHERE IDMARCA = ?", 
                                [$marca['IDMARCA']]
                            )->fetch()['total'];
                        ?>
                            <tr>
                                <td><?php echo $marca['IDMARCA']; ?></td>
                                <td><?php echo $marca['NOME']; ?></td>
                                <td>
                                    <?php if (!empty($marca['SITE'])): ?>
                                        <a href="<?php echo (!strstr($marca['SITE'], 'http')) ? 'http://' . $marca['SITE'] : $marca['SITE']; ?>" target="_blank">
                                            <?php echo $marca['SITE']; ?>
                                            <i class="fas fa-external-link-alt small"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Não informado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $produtosCount; ?></span>
                                </td>
                                <td class="text-end">
                                    <a href="marcas.php?edit=<?php echo $marca['IDMARCA']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="marcas.php?delete=<?php echo $marca['IDMARCA']; ?>" class="btn btn-sm btn-danger btn-delete" data-bs-toggle="tooltip" title="Excluir">
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
                <i class="fas fa-info-circle"></i> Nenhuma marca encontrada.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para adicionar/editar marca -->
<div class="modal fade" id="marcaModal" tabindex="-1" aria-labelledby="marcaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="marcas.php" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="marcaModalLabel">
                        <?php echo $marcaEdit ? 'Editar Marca' : 'Nova Marca'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <?php if ($marcaEdit): ?>
                        <input type="hidden" name="id" value="<?php echo $marcaEdit['IDMARCA']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome*</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $marcaEdit ? $marcaEdit['NOME'] : ''; ?>" required>
                        <div class="invalid-feedback">Por favor, informe o nome da marca.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="site" class="form-label">Site</label>
                        <input type="url" class="form-control" id="site" name="site" value="<?php echo $marcaEdit ? $marcaEdit['SITE'] : ''; ?>" placeholder="https://www.exemplo.com">
                        <div class="form-text">Informe a URL completa, incluindo http:// ou https://</div>
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
if ($marcaEdit): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var marcaModal = new bootstrap.Modal(document.getElementById('marcaModal'));
        marcaModal.show();
    });
</script>
<?php endif; ?>

<?php
// Incluir o rodapé
include '../includes/footer.php';
?>