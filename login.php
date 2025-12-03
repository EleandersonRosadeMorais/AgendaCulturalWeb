<?php
session_start();
require_once 'config.php';

$errors = [];
$email = '';

// Processa o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    // Validações
    if (empty($email)) {
        $errors[] = 'E-mail é obrigatório';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-mail inválido';
    }

    if (empty($password)) {
        $errors[] = 'Senha é obrigatória';
    }

    // Se não há erros, tenta fazer login
    if (empty($errors)) {
        try {
            // Busca usuário pelo email
            $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                // Verifica a senha
                if (password_verify($password, $usuario['senha'])) {
                    // Login bem-sucedido
                    $_SESSION['usuario'] = [
                        'id' => $usuario['id_pk'],
                        'nome' => $usuario['nome'],
                        'email' => $usuario['email'],
                        'tipo' => $usuario['tipo'],
                        'dataNasc' => $usuario['dataNascimento'],
                        'cpf' => $usuario['cpf']
                    ];
                    
                    // Redireciona para página principal
                    header('Location: index.php');
                    exit();
                } else {
                    $errors[] = 'Senha incorreta';
                }
            } else {
                $errors[] = 'E-mail não encontrado';
            }
        } catch (PDOException $e) {
            error_log("Erro no login: " . $e->getMessage());
            $errors[] = 'Erro no servidor. Tente novamente mais tarde.';
        }
    }
}

// Se já estiver logado, redireciona
if (isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Agenda Cultural</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1><i class="fas fa-calendar-alt"></i> Agenda Cultural</h1>
                <p>Faça login para acessar sua conta</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> E-mail</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo htmlspecialchars($email); ?>"
                           placeholder="Digite seu e-mail" required>
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Senha</label>
                    <input type="password" id="password" name="password"
                           placeholder="Digite sua senha" required>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>

                <a href="cadastroUsuario.php" class="register-btn">
                    <i class="fas fa-user-plus"></i> Cadastrar nova conta
                </a>

                <a href="index.php" class="register-btn">
                    Voltar para Página Principal
                </a>

            </form>

            <div class="login-footer">
                <p><i class="far fa-copyright"></i> 2025 Agenda Cultural. Todos os direitos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>