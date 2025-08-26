<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Inicializar conexão com o banco de dados
$db = new Database();

// Verificar se é edição ou novo cadastro
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$cliente = [];
$pageTitle = 'Novo Cliente';

if ($id > 0) {
    // Buscar dados do cliente para edição
    $query = $db->query("SELECT * FROM cliente WHERE IDCLIENTE = ?", [$id]);
    $cliente = $query ? $query->fetch() : null;
    
    if (!$cliente) {
        $_SESSION['message'] = "Cliente não encontrado.";
        $_SESSION['message_type'] = "danger";
        redirect(BASE_URL . '/pages/clientes.php');
    }
    
    $pageTitle = 'Editar Cliente';
}

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coletar e validar dados do formulário
    $nome = cleanInput($_POST['nome']);
    $email = cleanInput($_POST['email']);
    $telefone = cleanInput($_POST['telefone']);
    $endereco = cleanInput($_POST['endereco']);
    $cidade = cleanInput($_POST['cidade']);
    $estado = cleanInput($_POST['estado']);
    $cep = cleanInput($_POST['cep']);
    
    // Validação básica
    $errors = [];
    
    if (empty($nome)) {
        $errors[] = "O nome do cliente é obrigatório.";
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "O email informado não é válido.";
    }
    
    // Se não houver erros, salvar no banco de dados
    if (empty($errors)) {
        try {
            if ($id > 0) {
                // Atualizar cliente existente
                if ($db !== null) $db->query(
                    "UPDATE cliente SET NOME = ?, EMAIL = ?, TELEFONE = ?, ENDERECO = ?, CIDADE = ?, ESTADO = ?, CEP = ? WHERE IDCLIENTE = ?",
                    [$nome, $email, $telefone, $endereco, $cidade, $estado, $cep, $id]
                );
                
                $_SESSION['message'] = "Cliente atualizado com sucesso!";
            } else {
                // Inserir novo cliente
                $db->query(
                    "INSERT INTO cliente (NOME, EMAIL, TELEFONE, ENDERECO, CIDADE, ESTADO, CEP) VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$nome, $email, $telefone, $endereco, $cidade, $estado, $cep]
                );
                
                $_SESSION['message'] = "Cliente cadastrado com sucesso!";
            }
            
            $_SESSION['message_type'] = "success";
            redirect(BASE_URL . '/pages/clientes.php');
            
        } catch (Exception $e) {
            $_SESSION['message'] = "Erro ao salvar cliente: " . $e->getMessage();
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
    <a href="clientes.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="nome" class="form-label">Nome *</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo isset($cliente['NOME']) ? $cliente['NOME'] : ''; ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($cliente['EMAIL']) ? $cliente['EMAIL'] : ''; ?>">
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" class="form-control telefone-mask" id="telefone" name="telefone" value="<?php echo isset($cliente['TELEFONE']) ? $cliente['TELEFONE'] : ''; ?>">
                </div>
                
                <div class="col-md-8">
                    <label for="endereco" class="form-label">Endereço</label>
                    <input type="text" class="form-control" id="endereco" name="endereco" value="<?php echo isset($cliente['ENDERECO']) ? $cliente['ENDERECO'] : ''; ?>">
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="cidade" class="form-label">Cidade</label>
                    <input type="text" class="form-control" id="cidade" name="cidade" value="<?php echo isset($cliente['CIDADE']) ? $cliente['CIDADE'] : ''; ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Selecione</option>
                        <?php
                        $estados = [
                            'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA',
                            'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
                        ];
                        
                        foreach ($estados as $estado) {
                            $selected = (isset($cliente['ESTADO']) && $cliente['ESTADO'] == $estado) ? 'selected' : '';
                            echo "<option value=\"$estado\" $selected>$estado</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="cep" class="form-label">CEP</label>
                    <input type="text" class="form-control cep-mask" id="cep" name="cep" value="<?php echo isset($cliente['CEP']) ? $cliente['CEP'] : ''; ?>">
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
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