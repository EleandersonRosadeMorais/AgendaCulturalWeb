<?php
session_start();

// Simulação de dados do usuário (em um sistema real, viria do banco de dados)
$usuario = [
    'id' => 1,
    'nome_completo' => 'João Silva',
    'data_nascimento' => '1995-05-15',
    'idade' => 28,
    'cpf' => '123.456.789-00',
    'email' => 'joao@email.com',
    'username' => 'joaosilva',
    'user_type' => 'user'
];

$errors = [];
$success = '';

// Processa o formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação e processamento dos dados
    $nome_completo = htmlspecialchars(trim($_POST['nome_completo']));
    $data_nascimento = htmlspecialchars(trim($_POST['data_nascimento']));
    $cpf = htmlspecialchars(trim($_POST['cpf']));
    $email = htmlspecialchars(trim($_POST['email']));
    $username = htmlspecialchars(trim($_POST['username']));
    $user_type = htmlspecialchars(trim($_POST['user_type']));

    // Validações básicas
    if (empty($nome_completo)) {
        $errors[] = 'Nome completo é obrigatório';
    }
    
    if (empty($data_nascimento)) {
        $errors[] = 'Data de nascimento é obrigatória';
    }
    
    if (empty($cpf)) {
        $errors[] = 'CPF é obrigatório';
    }
    
    if (empty($email)) {
        $errors[] = 'E-mail é obrigatório';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-mail inválido';
    }
    
    if (empty($username)) {
        $errors[] = 'Nome de usuário é obrigatório';
    }

    // Se não há erros, simula sucesso
    if (empty($errors)) {
        $success = 'Usuário atualizado com sucesso!';
        
        // Atualiza os dados do usuário (em um sistema real, salvaria no banco)
        $usuario['nome_completo'] = $nome_completo;
        $usuario['data_nascimento'] = $data_nascimento;
        $usuario['cpf'] = $cpf;
        $usuario['email'] = $email;
        $usuario['username'] = $username;
        $usuario['user_type'] = $user_type;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário - Sistema de Eventos</title>
    <link rel="stylesheet" href="css/cadastroUsuario.css">
</head>

<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>Editar Usuário</h1>
                <p>Atualize os dados do usuário</p>
            </div>

            <?php if ($success): ?>
                <div class="success-message">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="register-form" method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nome_completo">Nome Completo *</label>
                        <input type="text" id="nome_completo" name="nome_completo"
                            value="<?php echo htmlspecialchars($usuario['nome_completo']); ?>"
                            placeholder="Digite o nome completo" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="data_nascimento">Data de Nascimento *</label>
                        <input type="date" id="data_nascimento" name="data_nascimento"
                            value="<?php echo htmlspecialchars($usuario['data_nascimento']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="cpf">CPF *</label>
                        <input type="text" id="cpf" name="cpf" 
                            value="<?php echo htmlspecialchars($usuario['cpf']); ?>"
                            placeholder="000.000.000-00" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">E-mail *</label>
                        <input type="email" id="email" name="email"
                            value="<?php echo htmlspecialchars($usuario['email']); ?>" 
                            placeholder="seu@email.com" required>
                    </div>

                    
                </div>

                

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Nova Senha</label>
                        <input type="password" id="password" name="password" 
                            placeholder="Deixe em branco para manter a atual">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmar Nova Senha</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                            placeholder="Confirme a nova senha">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="register-btn">Atualizar Usuário</button>
                    <a href="admin_usuarios.php" class="voltar-btn">Voltar</a>
                </div>

                <div class="login-link">
                    <p><a href="admin_usuarios.php">← Voltar para a lista de usuários</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>