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
        // Verificar se o cliente está associado a algum pedido
        $query = $db->query("SELECT COUNT(*) as total FROM pedido WHERE IDCLIENTE = ?", [$id]);
        if ($query === null) {
            throw new Exception("Failed to execute query");
        }
        $checkPedidos = $query->fetch();
        
        if ($checkPedidos['total'] > 0) {
            $_SESSION['message'] = "Este cliente não pode ser excluído pois está associado a pedidos.";
            $_SESSION['message_type'] = "danger";
        } else {
            // Excluir o cliente
            $db->query("DELETE FROM cliente WHERE IDCLIENTE = ?", [$id]);
            $_SESSION['message'] = "Cliente excluído com sucesso!";
            $_SESSION['message_type'] = "success";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Erro ao excluir cliente: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    // Redirecionar para evitar reenvio do formulário
    redirect(BASE_URL . '/pages/clientes.php');
}

// Configuração de paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Configuração de busca
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';

// Construir a consulta SQL com filtros
$sql = "SELECT * FROM cliente WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (NOME LIKE ? OR EMAIL LIKE ? OR TELEFONE LIKE ? OR ENDERECO LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Contar total de registros para paginação
$countSql = str_replace("*", "COUNT(*) as total", $sql);
$query = $db->query($countSql, $params);
if ($query === null) {
    throw new Exception("Failed to execute count query");
}
$totalItems = $query->fetch()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Adicionar ordenação e limite para paginação
$sql .= " ORDER BY NOME ASC LIMIT $offset, $itemsPerPage";

// Executar a consulta
$query = $db->query($sql, $params);
if ($query === null) {
    throw new Exception("Failed to execute query");
}
$clientes = $query->fetchAll();

// Incluir o cabeçalho
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Clientes</h1>
    <a href="cliente-form.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Cliente
    </a>
</div>

<!-- Filtros e busca -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="clientes.php" class="row g-3">
            <div class="col-md-10">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" value="<?php echo $search; ?>" placeholder="Nome, email, telefone ou endereço">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de clientes -->
<div class="card">
    <div class="card-body">
        <?php if (count($clientes) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Pedidos</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): 
                            // Contar pedidos deste cliente
                            $query = $db->query(
                                "SELECT COUNT(*) as total FROM pedido WHERE IDCLIENTE = ?", 
                                [$cliente['IDCLIENTE']]
                            );
                            if ($query === null) {
                                throw new Exception("Failed to execute count query for client orders");
                            }
                            $pedidosCount = $query->fetch()['total'];
                        ?>
                            <tr>
                                <td><?php echo $cliente['IDCLIENTE']; ?></td>
                                <td><?php echo $cliente['NOME']; ?></td>
                                <td>
                                    <?php if (!empty($cliente['EMAIL'])): ?>
                                        <a href="mailto:<?php echo $cliente['EMAIL']; ?>">
                                            <?php echo $cliente['EMAIL']; ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Não informado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($cliente['TELEFONE'])): ?>
                                        <a href="tel:<?php echo $cliente['TELEFONE']; ?>">
                                            <?php echo $cliente['TELEFONE']; ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Não informado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $pedidosCount; ?></span>
                                </td>
                                <td class="text-end">
                                    <a href="cliente-detalhes.php?id=<?php echo $cliente['IDCLIENTE']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="cliente-form.php?id=<?php echo $cliente['IDCLIENTE']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="clientes.php?delete=<?php echo $cliente['IDCLIENTE']; ?>" class="btn btn-sm btn-danger btn-delete" data-bs-toggle="tooltip" title="Excluir">
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
                <i class="fas fa-info-circle"></i> Nenhum cliente encontrado.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Incluir o rodapé
include '../includes/footer.php';
?>