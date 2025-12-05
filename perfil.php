<?php
session_start();
require_once 'config.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$usuarioAtual = getUsuarioAtual();

// Buscar dados completos do usuário
function getDadosUsuarioCompleto($usuarioId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                u.*,
                COUNT(DISTINCT f.id_pk) as total_favoritos,
                COUNT(DISTINCT e.id_pk) as total_eventos_criados
            FROM usuario u
            LEFT JOIN favorito f ON u.id_pk = f.usuario_fk
            LEFT JOIN evento e ON u.id_pk = e.usuario_fk
            WHERE u.id_pk = ?
            GROUP BY u.id_pk
        ");
        $stmt->execute([$usuarioId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar dados do usuário: " . $e->getMessage());
        return null;
    }
}

// Processar atualização do perfil
$erros = [];
$sucesso = false;
$mensagem_sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar qual formulário foi enviado
    if (isset($_POST['alterar_senha'])) {
        // Processar alteração de senha
        $senha_atual = $_POST['senha_atual'] ?? '';
        $nova_senha = $_POST['nova_senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';
        
        if (empty($senha_atual)) {
            $erros[] = "A senha atual é obrigatória";
        }
        
        if (empty($nova_senha)) {
            $erros[] = "A nova senha é obrigatória";
        } elseif (strlen($nova_senha) < 6) {
            $erros[] = "A nova senha deve ter no mínimo 6 caracteres";
        }
        
        if ($nova_senha !== $confirmar_senha) {
            $erros[] = "As senhas não coincidem";
        }
        
        if (empty($erros)) {
            global $pdo;
            
            // Buscar senha atual do banco
            $stmt = $pdo->prepare("SELECT senha FROM usuario WHERE id_pk = ?");
            $stmt->execute([$usuarioAtual['id']]);
            $usuarioDB = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuarioDB && password_verify($senha_atual, $usuarioDB['senha'])) {
                // Atualizar senha
                $novaSenhaHash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuario SET senha = ? WHERE id_pk = ?");
                $stmt->execute([$novaSenhaHash, $usuarioAtual['id']]);
                $sucesso = true;
                $mensagem_sucesso = "Senha alterada com sucesso!";
            } else {
                $erros[] = "Senha atual incorreta";
            }
        }
    } else {
        // Processar atualização de dados do perfil
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $dataNascimento = trim($_POST['dataNascimento'] ?? '');
        
        if (empty($nome)) {
            $erros[] = "O nome é obrigatório";
        }
        
        if (empty($email)) {
            $erros[] = "O email é obrigatório";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros[] = "Email inválido";
        }
        
        if (empty($dataNascimento)) {
            $erros[] = "A data de nascimento é obrigatória";
        }
        
        if (empty($erros)) {
            try {
                global $pdo;
                
                // Verificar se email já existe (exceto para o próprio usuário)
                $stmt = $pdo->prepare("SELECT id_pk FROM usuario WHERE email = ? AND id_pk != ?");
                $stmt->execute([$email, $usuarioAtual['id']]);
                
                if ($stmt->rowCount() > 0) {
                    $erros[] = "Este email já está em uso por outro usuário";
                } else {
                    // Atualizar perfil
                    $stmt = $pdo->prepare("
                        UPDATE usuario 
                        SET nome = ?, email = ?, dataNascimento = ?
                        WHERE id_pk = ?
                    ");
                    
                    $stmt->execute([
                        $nome,
                        $email,
                        $dataNascimento,
                        $usuarioAtual['id']
                    ]);
                    
                    // Atualizar dados na sessão
                    $_SESSION['usuario']['nome'] = $nome;
                    $_SESSION['usuario']['email'] = $email;
                    $_SESSION['usuario']['dataNascimento'] = $dataNascimento;
                    
                    $sucesso = true;
                    $mensagem_sucesso = "Perfil atualizado com sucesso!";
                }
                
            } catch (PDOException $e) {
                error_log("Erro ao atualizar perfil: " . $e->getMessage());
                $erros[] = "Erro ao atualizar perfil. Tente novamente.";
            }
        }
    }
}

// Buscar dados completos do usuário
$dadosUsuario = getDadosUsuarioCompleto($usuarioAtual['id']);

// Calcular idade
$idade = '';
if (!empty($usuarioAtual['dataNascimento'])) {
    $nascimento = new DateTime($usuarioAtual['dataNascimento']);
    $hoje = new DateTime();
    $idade = $hoje->diff($nascimento)->y;
}

// Calcular tempo desde o cadastro
$tempo_cadastro = '';
if (!empty($dadosUsuario['dataCriacao'])) {
    $cadastro = new DateTime($dadosUsuario['dataCriacao']);
    $hoje = new DateTime();
    $diferenca = $hoje->diff($cadastro);
    $tempo_cadastro = $diferenca->y . ' anos, ' . $diferenca->m . ' meses';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Agenda Cultural</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/perfil.css">
</head>
<body>
    <?php require_once 'header.php'; ?>
    
    <div class="container">
        <div class="perfil-container">
            
            
            
            <?php if ($sucesso && !empty($mensagem_sucesso)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($mensagem_sucesso); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($erros)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php foreach ($erros as $erro): ?>
                        <div><?php echo htmlspecialchars($erro); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            
            <div class="perfil-content">
                
                <div class="info-card">
                    <h3><i class="fas fa-user-edit"></i> Editar Perfil</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="nome"><i class="fas fa-user"></i> Nome Completo</label>
                            <input type="text" id="nome" name="nome" class="form-control" 
                                   value="<?php echo htmlspecialchars($usuarioAtual['nome'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($usuarioAtual['email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="dataNascimento"><i class="fas fa-birthday-cake"></i> Data de Nascimento</label>
                            <input type="date" id="dataNascimento" name="dataNascimento" class="form-control" 
                                   value="<?php echo htmlspecialchars($usuarioAtual['dataNascimento'] ?? ''); ?>" required>
                        </div>
                        
                        <button type="submit" class="btn-salvar">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </form>
                    
                   
                    <details class="alterar-senha-details">
                        <summary class="btn-alterar-senha">
                            <i class="fas fa-key"></i> Alterar Senha
                        </summary>
                        
                        <div class="senha-fields">
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="senha_atual"><i class="fas fa-lock"></i> Senha Atual</label>
                                    <input type="password" id="senha_atual" name="senha_atual" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="nova_senha"><i class="fas fa-lock"></i> Nova Senha</label>
                                    <input type="password" id="nova_senha" name="nova_senha" class="form-control" required minlength="6">
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirmar_senha"><i class="fas fa-lock"></i> Confirmar Nova Senha</label>
                                    <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" required minlength="6">
                                </div>
                                
                                <button type="submit" class="btn-salvar" name="alterar_senha">
                                    <i class="fas fa-key"></i> Alterar Senha
                                </button>
                            </form>
                        </div>
                    </details>
                </div>
                
               
                <div class="info-card">
                    <h3><i class="fas fa-info-circle"></i> Informações da Conta</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">CPF</div>
                            <div class="info-value"><?php echo htmlspecialchars($usuarioAtual['cpf'] ?? 'Não informado'); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Tipo de Conta</div>
                            <div class="info-value">
                                <span class="tipo-badge tipo-<?php echo htmlspecialchars($usuarioAtual['tipo']); ?>">
                                    <i class="fas fa-<?php echo $usuarioAtual['tipo'] === 'admin' ? 'crown' : 'user'; ?>"></i>
                                    <?php echo htmlspecialchars(ucfirst($usuarioAtual['tipo'])); ?>
                                </span>
                            </div>
                        </div>
                        
                        
                    </div>
                    
                    
                    
                    
                    <h3 class="section-title"><i class="fas fa-cog"></i> Ações da Conta</h3>
                    <div class="acoes-conta">
                        
                        
                        <a href="index.php" class="btn-acao btn-eventos">
                             Ir para Página Principal
                        </a>
                        
                        <a href="logout.php" class="btn-acao btn-sair">
                            <i class="fas fa-sign-out-alt"></i> Sair
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>