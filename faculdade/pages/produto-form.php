<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Inicializar conexão com o banco de dados
$db = new Database();

// Verificar se é edição ou novo produto
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = ($id > 0);
$pageTitle = $isEdit ? 'Editar Produto' : 'Novo Produto';

// Inicializar variáveis
$produto = [
    'IDPRODUTO' => 0,
    'IDCATEGORIA' => 0,
    'IDMARCA' => 0,
    'NOME' => '',
    'DESCRICAO' => '',
    'ESTOQUE' => 0,
    'PRECO' => 0.00
];

// Carregar dados do produto se for edição
if ($isEdit) {
    $produtoData = $db->query("SELECT * FROM produto WHERE IDPRODUTO = ?", [$id])->fetch();
    
    if ($produtoData) {
        $produto = $produtoData;
    } else {
        $_SESSION['message'] = "Produto não encontrado.";
        $_SESSION['message_type'] = "danger";
        redirect(BASE_URL . '/pages/produtos.php');
    }
}

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter e validar dados do formulário
    $produto['IDCATEGORIA'] = isset($_POST['categoria']) ? (int)$_POST['categoria'] : 0;
    $produto['IDMARCA'] = isset($_POST['marca']) ? (int)$_POST['marca'] : 0;
    $produto['NOME'] = cleanInput($_POST['nome']);
    $produto['DESCRICAO'] = cleanInput($_POST['descricao']);
    $produto['ESTOQUE'] = isset($_POST['estoque']) ? (int)$_POST['estoque'] : 0;
    $produto['PRECO'] = isset($_POST['preco']) ? convertMoneyToFloat($_POST['preco']) : 0;
    
    // Validar dados
    $errors = [];
    
    if (empty($produto['NOME'])) {
        $errors[] = "O nome do produto é obrigatório.";
    }
    
    if ($produto['IDCATEGORIA'] <= 0) {
        $errors[] = "Selecione uma categoria válida.";
    }
    
    if ($produto['IDMARCA'] <= 0) {
        $errors[] = "Selecione uma marca válida.";
    }
    
    if ($produto['ESTOQUE'] < 0) {
        $errors[] = "O estoque não pode ser negativo.";
    }
    
    if ($produto['PRECO'] <= 0) {
        $errors[] = "O preço deve ser maior que zero.";
    }
    
    // Se não houver erros, salvar no banco de dados
    if (empty($errors)) {
        try {
            if ($isEdit) {
                // Atualizar produto existente
                $db->query(
                    "UPDATE produto SET IDCATEGORIA = ?, IDMARCA = ?, NOME = ?, DESCRICAO = ?, ESTOQUE = ?, PRECO = ? WHERE IDPRODUTO = ?",
                    [
                        $produto['IDCATEGORIA'],
                        $produto['IDMARCA'],
                        $produto['NOME'],
                        $produto['DESCRICAO'],
                        $produto['ESTOQUE'],
                        $produto['PRECO'],
                        $id
                    ]
                );
                
                $_SESSION['message'] = "Produto atualizado com sucesso!";
            } else {
                // Inserir novo produto
                $db->query(
                    "INSERT INTO produto (IDCATEGORIA, IDMARCA, NOME, DESCRICAO, ESTOQUE, PRECO) VALUES (?, ?, ?, ?, ?, ?)",
                    [
                        $produto['IDCATEGORIA'],
                        $produto['IDMARCA'],
                        $produto['NOME'],
                        $produto['DESCRICAO'],
                        $produto['ESTOQUE'],
                        $produto['PRECO']
                    ]
                );
                
                $_SESSION['message'] = "Produto cadastrado com sucesso!";
            }
            
            $_SESSION['message_type'] = "success";
            redirect(BASE_URL . '/pages/produtos.php');
            
        } catch (Exception $e) {
            $errors[] = "Erro ao salvar produto: " . $e->getMessage();
        }
    }
}

// Obter categorias e marcas para os selects
$categorias = $db->query("SELECT * FROM categoria ORDER BY NOME ASC")->fetchAll();
$marcas = $db->query("SELECT * FROM marca ORDER BY NOME ASC")->fetchAll();

// Incluir o cabeçalho
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?php echo $pageTitle; ?></h1>
    <a href="produtos.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="needs-validation" novalidate>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="nome" class="form-label">Nome do Produto*</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $produto['NOME']; ?>" required>
                    <div class="invalid-feedback">Por favor, informe o nome do produto.</div>
                </div>
                
                <div class="col-md-3">
                    <label for="categoria" class="form-label">Categoria*</label>
                    <select class="form-select" id="categoria" name="categoria" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo $categoria['IDCATEGORIA']; ?>" <?php echo ($produto['IDCATEGORIA'] == $categoria['IDCATEGORIA']) ? 'selected' : ''; ?>>
                                <?php echo $categoria['NOME']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Por favor, selecione uma categoria.</div>
                </div>
                
                <div class="col-md-3">
                    <label for="marca" class="form-label">Marca*</label>
                    <select class="form-select" id="marca" name="marca" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($marcas as $marca): ?>
                            <option value="<?php echo $marca['IDMARCA']; ?>" <?php echo ($produto['IDMARCA'] == $marca['IDMARCA']) ? 'selected' : ''; ?>>
                                <?php echo $marca['NOME']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Por favor, selecione uma marca.</div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea class="form-control" id="descricao" name="descricao" rows="3"><?php echo $produto['DESCRICAO']; ?></textarea>
                </div>
                
                <div class="col-md-3">
                    <label for="estoque" class="form-label">Estoque*</label>
                    <input type="number" class="form-control" id="estoque" name="estoque" value="<?php echo $produto['ESTOQUE']; ?>" min="0" required>
                    <div class="invalid-feedback">Por favor, informe a quantidade em estoque.</div>
                </div>
                
                <div class="col-md-3">
                    <label for="preco" class="form-label">Preço (R$)*</label>
                    <input type="text" class="form-control mask-money" id="preco" name="preco" value="<?php echo number_format($produto['PRECO'], 2, ',', '.'); ?>" required>
                    <div class="invalid-feedback">Por favor, informe o preço do produto.</div>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="reset" class="btn btn-outline-secondary">
                    <i class="fas fa-eraser"></i> Limpar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Incluir o rodapé
include '../includes/footer.php';
?>