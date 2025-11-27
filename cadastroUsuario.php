<?php
session_start();

$errors = [];
$success = '';
$formData = [
    'nome_completo' => '',
    'idade' => '',
    'cpf' => '',
    'email' => '',
    'username' => '',
    'user_type' => 'user'
];

// Processa o formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulação de processamento
    $formData['nome_completo'] = htmlspecialchars(trim($_POST['nome_completo']));
    $formData['idade'] = htmlspecialchars(trim($_POST['idade']));
    $formData['cpf'] = htmlspecialchars(trim($_POST['cpf']));
    $formData['email'] = htmlspecialchars(trim($_POST['email']));
    $formData['username'] = htmlspecialchars(trim($_POST['username']));
    $formData['user_type'] = htmlspecialchars(trim($_POST['user_type']));

    // Simulação de sucesso no cadastro
    $success = 'Cadastro realizado com sucesso! Você pode fazer login agora.';

    // Limpa o formulário após sucesso
    if ($success) {
        $formData = [
            'nome_completo' => '',
            'idade' => '',
            'cpf' => '',
            'email' => '',
            'username' => '',
            'user_type' => 'user'
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Sistema de Eventos</title>
    <link rel="stylesheet" href="css/cadastroUsuario.css">
</head>

<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>Criar Conta</h1>
                <p>Preencha os dados para se cadastrar</p>
            </div>

            <?php if ($success): ?>
                <div class="success-message">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form class="register-form" method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nome_completo">Nome Completo *</label>
                        <input type="text" id="nome_completo" name="nome_completo"
                            value="<?php echo htmlspecialchars($formData['nome_completo']); ?>"
                            placeholder="Digite seu nome completo">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="datanasc">Data de Nascimento *</label>
                        <input type="data" id="dataNasc" name="data"
                            value="<?php echo htmlspecialchars($formData['idade']); ?>"
                            placeholder="Digite sua Data de Nascimento" min="1" max="120">
                    </div>

                    <div class="form-group">
                        <label for="cpf">CPF *</label>
                        <input type="text" id="cpf" name="cpf" value="<?php echo htmlspecialchars($formData['cpf']); ?>"
                            placeholder="000.000.000-00">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">E-mail *</label>
                        <input type="email" id="email" name="email"
                            value="<?php echo htmlspecialchars($formData['email']); ?>" placeholder="seu@email.com">
                    </div>


                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Senha *</label>
                        <input type="password" id="password" name="password" placeholder="Digite sua senha">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmar Senha *</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                            placeholder="Confirme sua senha">
                    </div>
                </div>

                

                <button type="submit" class="register-btn">Cadastrar</button>

                <div class="login-link">
                    <p>Já tem uma conta? <a href="login.php">Faça login aqui</a></p>
                </div>
            </form>
        </div>
    </div>
</body>

</html>