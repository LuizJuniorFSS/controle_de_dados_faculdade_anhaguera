<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Iniciar sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário já está logado
if (isset($_SESSION['user_id'])) 
    redirect(BASE_URL);
// Remove the extra closing brace as it has no matching opening brace

// Processar o formulário de login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email']);
    $senha = $_POST['senha'];
    
    // Validar campos
    if (empty($email) || empty($senha)) {
        $error = "Por favor, preencha todos os campos.";
    } else {
        // Inicializar conexão com o banco de dados
        $db = new Database();
        
        // Buscar usuário pelo email
        $query = $db->query(
            "SELECT * FROM usuario WHERE EMAIL = ?", 
            [$email]
        );
        $usuario = $query ? $query->fetch() : null;
        
        if ($usuario && password_verify($senha, $usuario['SENHA'])) {
            // Login bem-sucedido
            $_SESSION['user_id'] = $usuario['IDUSUARIO'];
            $_SESSION['user_name'] = $usuario['NOME'];
            $_SESSION['user_email'] = $usuario['EMAIL'];
            $_SESSION['user_level'] = $usuario['NIVEL'];
            
            // Atualizar último acesso
            $db->query(
                "UPDATE usuario SET ULTIMO_ACESSO = NOW() WHERE IDUSUARIO = ?", 
                [$usuario['IDUSUARIO']]
            );
            
            // Registrar log de atividade
            logActivity($db, $usuario['IDUSUARIO'], 'Login', 'Usuário realizou login no sistema');
            
            // Redirecionar para a página inicial
            redirect(BASE_URL);
        } else {
            $error = "Email ou senha incorretos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 15px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .card-header {
            border-radius: 10px 10px 0 0 !important;
            background-color: #0d6efd;
            color: white;
            text-align: center;
            padding: 1.5rem;
        }
        .logo {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border-radius: 5px;
            padding: 10px 15px;
        }
        .btn-login {
            border-radius: 5px;
            padding: 10px;
            font-weight: bold;
        }
        .card-footer {
            background-color: transparent;
            border-top: none;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <div class="logo">
                    <i class="fas fa-store"></i>
                </div>
                <h3><?php echo SITE_NAME; ?></h3>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Seu email" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="senha" class="form-label">Senha</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="senha" name="senha" placeholder="Sua senha" required>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-login">Entrar</button>
                    </div>
                </form>
            </div>
            <div class="card-footer py-3">
                <div class="text-center text-muted">
                    <small>Sistema de Gerenciamento Comercial</small>
                </div>
            </div>
        </div>
        
        <div class="mt-3 text-center">
            <p class="text-muted">
                <small>Para acessar o sistema, use:</small><br>
                <small>Email: admin@sistema.com</small><br>
                <small>Senha: admin123</small>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>