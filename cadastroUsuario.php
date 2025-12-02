<?php
session_start();
require_once 'config.php'; // Conexão com o banco

$errors = [];
$success = '';
$formData = [
    'nome_completo' => '',
    'dataNasc' => '',
    'cpf' => '',
    'email' => ''
];

// Função para formatar CPF
function formatarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) == 11) {
        return substr($cpf, 0, 3) . '.' . 
               substr($cpf, 3, 3) . '.' . 
               substr($cpf, 6, 3) . '-' . 
               substr($cpf, 9, 2);
    }
    return $cpf;
}

// Função para validar CPF
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    return true;
}

// Processa o formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitização dos dados
    $formData['nome_completo'] = htmlspecialchars(trim($_POST['nome_completo'] ?? ''));
    $formData['dataNasc'] = htmlspecialchars(trim($_POST['dataNasc'] ?? ''));
    $cpf_input = htmlspecialchars(trim($_POST['cpf'] ?? ''));
    $formData['cpf'] = $cpf_input;
    $formData['email'] = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validações
    if (empty($formData['nome_completo'])) {
        $errors[] = 'Nome completo é obrigatório';
    } elseif (strlen($formData['nome_completo']) < 3) {
        $errors[] = 'Nome deve ter pelo menos 3 caracteres';
    }

    if (empty($formData['dataNasc'])) {
        $errors[] = 'Data de nascimento é obrigatória';
    } else {
        $dataNasc = DateTime::createFromFormat('Y-m-d', $formData['dataNasc']);
        $hoje = new DateTime();
        
        if (!$dataNasc) {
            $errors[] = 'Data de nascimento inválida';
        } elseif ($dataNasc > $hoje) {
            $errors[] = 'Data de nascimento não pode ser no futuro';
        } elseif ($hoje->diff($dataNasc)->y < 13) {
            $errors[] = 'É necessário ter pelo menos 13 anos para se cadastrar';
        }
    }

    if (empty($formData['cpf'])) {
        $errors[] = 'CPF é obrigatório';
    } elseif (!validarCPF($formData['cpf'])) {
        $errors[] = 'CPF inválido';
    }

    if (empty($formData['email'])) {
        $errors[] = 'E-mail é obrigatório';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-mail inválido';
    }

    if (empty($password)) {
        $errors[] = 'Senha é obrigatória';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Senha deve ter pelo menos 6 caracteres';
    }

    if (empty($confirm_password)) {
        $errors[] = 'Confirmação de senha é obrigatória';
    } elseif ($password !== $confirm_password) {
        $errors[] = 'As senhas não coincidem';
    }

    // Se não há erros, tenta cadastrar no banco
    if (empty($errors)) {
        try {
            // Remove formatação do CPF para salvar no banco
            $cpf_limpo = preg_replace('/[^0-9]/', '', $formData['cpf']);
            
            // Verifica se email já existe
            $stmt = $pdo->prepare("SELECT id_pk FROM usuario WHERE email = ?");
            $stmt->execute([$formData['email']]);
            
            if ($stmt->fetch()) {
                $errors[] = 'Este e-mail já está cadastrado';
            } else {
                // Verifica se CPF já existe
                $stmt = $pdo->prepare("SELECT id_pk FROM usuario WHERE cpf = ?");
                $stmt->execute([$cpf_limpo]);
                
                if ($stmt->fetch()) {
                    $errors[] = 'Este CPF já está cadastrado';
                } else {
                    // Hash da senha
                    $senhaHash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insere no banco
                    $stmt = $pdo->prepare("
                        INSERT INTO usuario 
                        (nome, dataNascimento, cpf, email, tipo, senha) 
                        VALUES (?, ?, ?, ?, 'comum', ?)
                    ");
                    
                    $sucesso = $stmt->execute([
                        $formData['nome_completo'],
                        $formData['dataNasc'],
                        $cpf_limpo,
                        $formData['email'],
                        $senhaHash
                    ]);
                    
                    if ($sucesso) {
                        $success = 'Cadastro realizado com sucesso! Você pode fazer login agora.';
                        
                        // Limpa o formulário
                        $formData = [
                            'nome_completo' => '',
                            'dataNasc' => '',
                            'cpf' => '',
                            'email' => ''
                        ];
                    } else {
                        $errors[] = 'Erro ao cadastrar usuário. Tente novamente.';
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Erro no cadastro: " . $e->getMessage());
            $errors[] = 'Erro no servidor. Tente novamente mais tarde.';
        }
    }
}

// Calcula a data máxima (13 anos atrás) para o campo date
$data_maxima = date('Y-m-d', strtotime('-13 years'));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Agenda Cultural</title>
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
                            value="<?php echo htmlspecialchars($formData['nome_completo']); ?>"
                            placeholder="Digite seu nome completo" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="dataNasc">Data de Nascimento *</label>
                        <input type="date" id="dataNasc" name="dataNasc"
                            value="<?php echo htmlspecialchars($formData['dataNasc']); ?>"
                            max="<?php echo $data_maxima; ?>" required>
                        <small class="form-text">É necessário ter pelo menos 13 anos</small>
                    </div>

                    <div class="form-group">
                        <label for="cpf">CPF *</label>
                        <input type="text" id="cpf" name="cpf" 
                            value="<?php echo htmlspecialchars($formData['cpf']); ?>"
                            placeholder="000.000.000-00" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">E-mail *</label>
                        <input type="email" id="email" name="email"
                            value="<?php echo htmlspecialchars($formData['email']); ?>" 
                            placeholder="seu@email.com" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Senha *</label>
                        <input type="password" id="password" name="password" 
                            placeholder="Digite sua senha" minlength="6" required>
                        <small class="form-text">Mínimo 6 caracteres</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmar Senha *</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                            placeholder="Confirme sua senha" minlength="6" required>
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