<?php
session_start();

// Dados de exemplo
$usuarios = [
    [
        'id' => 1,
        'nome' => 'Administrador',
        'email' => 'admin@sistema.com',
        'username' => 'admin',
        'user_type' => 'admin',
        'idade' => 30,
        'cpf' => '123.456.789-00',
        'data_criacao' => '2024-01-01 10:00:00'
    ],
    [
        'id' => 2,
        'nome' => 'João Silva',
        'email' => 'joao@email.com',
        'username' => 'joaosilva',
        'user_type' => 'user',
        'idade' => 25,
        'cpf' => '987.654.321-00',
        'data_criacao' => '2024-01-02 15:30:00'
    ],
    [
        'id' => 3,
        'nome' => 'Maria Santos',
        'email' => 'maria@email.com',
        'username' => 'mariasantos',
        'user_type' => 'user',
        'idade' => 28,
        'cpf' => '456.789.123-00',
        'data_criacao' => '2024-01-03 09:15:00'
    ]
];

$mensagem_sucesso = '';
$mensagem_erro = '';

// Simulação de busca
$termo_busca = $_GET['buscar'] ?? '';
$usuarios_filtrados = $usuarios;

if (!empty($termo_busca)) {
    $usuarios_filtrados = array_filter($usuarios, function($usuario) use ($termo_busca) {
        return stripos($usuario['nome'], $termo_busca) !== false || 
               stripos($usuario['email'], $termo_busca) !== false ||
               stripos($usuario['username'], $termo_busca) !== false;
    });
}

// Processar ações
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);
    $mensagem_sucesso = "Usuário #{$id_excluir} excluído com sucesso!";
}

if (isset($_GET['tornar_admin'])) {
    $id_admin = intval($_GET['tornar_admin']);
    $mensagem_sucesso = "Usuário #{$id_admin} agora é administrador!";
}

if (isset($_GET['remover_admin'])) {
    $id_remove_admin = intval($_GET['remover_admin']);
    $mensagem_sucesso = "Usuário #{$id_remove_admin} não é mais administrador!";
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - Admin - Sistema de Eventos</title>
    <link rel="stylesheet" href="css/admin_usuarios.css">
</head>

<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>Sistema de Eventos</h1>
                </div>
                <div class="nav-links">
                    <span class="admin-badge">👑 ADMIN</span>
                    <a href="index.php">Feed</a>
                    <a href="cadastrousuario.php">Cadastrar Usuário</a>
                    <a href="admin_eventos.php">Gerenciar Eventos</a>
                    <a href="index.php">Sair</a>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="page-header">
            <h2>Gerenciador de Usuários</h2>
            <p>Painel administrativo - Gerencie todos os usuários do sistema</p>
        </div>

        <?php if ($mensagem_sucesso): ?>
            <div class="alert alert-success">
                ✅ <?php echo htmlspecialchars($mensagem_sucesso); ?>
            </div>
        <?php endif; ?>

        <?php if ($mensagem_erro): ?>
            <div class="alert alert-error">
                ❌ <?php echo htmlspecialchars($mensagem_erro); ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($usuarios); ?></div>
                <div class="stat-label">Total de Usuários</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($usuarios, function($u) { return $u['user_type'] === 'admin'; })); ?></div>
                <div class="stat-label">Administradores</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($usuarios, function($u) { return $u['user_type'] === 'user'; })); ?></div>
                <div class="stat-label">Usuários Comuns</div>
            </div>
        </div>

        <div class="users-section">
            <div class="section-header">
                <h3>Lista de Usuários</h3>
                <form method="GET" class="search-box">
                    <input type="text"
                        class="search-input"
                        name="buscar"
                        placeholder="Buscar por nome, email ou usuário..."
                        value="<?php echo htmlspecialchars($termo_busca); ?>">
                    <button type="submit" class="btn-search">🔍</button>
                </form>
            </div>

            <?php if (empty($usuarios_filtrados)): ?>
                <div class="empty-state">
                    <h3>
                        <?php if (!empty($termo_busca)): ?>
                            Nenhum usuário encontrado para "<?php echo htmlspecialchars($termo_busca); ?>"
                        <?php else: ?>
                            Nenhum usuário cadastrado
                        <?php endif; ?>
                    </h3>
                    <p>
                        <?php if (!empty($termo_busca)): ?>
                            Nenhum usuário foi encontrado com esses termos.
                        <?php else: ?>
                            Comece cadastrando o primeiro usuário no sistema.
                        <?php endif; ?>
                    </p>
                    <?php if (empty($termo_busca)): ?>
                        <a href="cadastrousuario.php" class="btn btn-primary">Cadastrar Primeiro Usuário</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php if (!empty($termo_busca)): ?>
                    <div class="search-results-info">
                        <p>Encontrados <strong><?php echo count($usuarios_filtrados); ?></strong> usuário(s) para "<?php echo htmlspecialchars($termo_busca); ?>"</p>
                    </div>
                <?php endif; ?>

                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Usuário</th>
                            <th>Informações</th>
                            <th>Idade</th>
                            <th>Tipo</th>
                            <th>Data de Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios_filtrados as $usuario): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar <?php echo $usuario['user_type']; ?>">
                                            <?php echo strtoupper(substr($usuario['nome'], 0, 1)); ?>
                                        </div>
                                        <div class="user-details">
                                            <h4>
                                                <?php echo htmlspecialchars($usuario['nome']); ?>
                                                <?php if ($usuario['user_type'] === 'admin'): ?>
                                                    <span class="admin-tag">ADMIN</span>
                                                <?php endif; ?>
                                            </h4>
                                            <p>@<?php echo htmlspecialchars($usuario['username']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?><br>
                                    <strong>CPF:</strong> <?php echo $usuario['cpf']; ?>
                                </td>
                                <td>
                                    <?php echo $usuario['idade']; ?> anos
                                </td>
                                <td>
                                    <span class="user-type <?php echo $usuario['user_type']; ?>">
                                        <?php
                                        $tipos = [
                                            'admin' => 'Administrador',
                                            'user' => 'Usuário'
                                        ];
                                        echo $tipos[$usuario['user_type']] ?? 'Desconhecido';
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y H:i', strtotime($usuario['data_criacao'])); ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn btn-edit">
                                            ✏️ Editar
                                        </a>
                                        
                                        <?php if ($usuario['user_type'] === 'admin'): ?>
                                            <?php if ($usuario['id'] != 1): ?>
                                                <a href="admin_usuarios.php?remover_admin=<?php echo $usuario['id']; ?><?php echo !empty($termo_busca) ? '&buscar=' . urlencode($termo_busca) : ''; ?>"
                                                    class="btn btn-warning"
                                                    onclick="return confirm('Tem certeza que deseja remover os privilégios de administrador de <?php echo htmlspecialchars($usuario['nome']); ?>?')">
                                                    👑 Remover Admin
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="admin_usuarios.php?tornar_admin=<?php echo $usuario['id']; ?><?php echo !empty($termo_busca) ? '&buscar=' . urlencode($termo_busca) : ''; ?>"
                                                class="btn btn-admin"
                                                onclick="return confirm('Tem certeza que deseja tornar <?php echo htmlspecialchars($usuario['nome']); ?> administrador?')">
                                                👑 Tornar Admin
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($usuario['id'] == 1): ?>
                                            <button class="btn btn-disabled" disabled>
                                                🚫 Excluir
                                            </button>
                                        <?php else: ?>
                                            <a href="admin_usuarios.php?excluir=<?php echo $usuario['id']; ?><?php echo !empty($termo_busca) ? '&buscar=' . urlencode($termo_busca) : ''; ?>"
                                                class="btn btn-delete"
                                                onclick="return confirm('Tem certeza que deseja excluir o usuário <?php echo htmlspecialchars($usuario['nome']); ?>? Esta ação não pode ser desfeita!')">
                                                🗑️ Excluir
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Eventos - Painel Administrativo. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>

</html>