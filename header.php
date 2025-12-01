<?php
// Não precisa de session_start() aqui - já foi chamado no index.php
require_once 'config.php';

$usuarioAtual = getUsuarioAtual();
$favoritos = $usuarioAtual ? getEventosFavoritos($usuarioAtual['id']) : [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Cultural - Eventos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/header.css">
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <div class="logo-text">Agenda Cultural</div>
                    <div class="logo-subtitle">Não perca nenhum evento!</div>
                </div>
            </div>

            <nav>
                <ul>
                    <li><a href="index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i> Feed
                        </a></li>
                    <li><a href="favoritos.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'favoritos.php') ? 'active' : ''; ?>">
                            <i class="fas fa-heart"></i> Favoritos
                            <?php if (!empty($favoritos)): ?>
                                <span class="nav-badge"><?php echo count($favoritos); ?></span>
                            <?php endif; ?>
                        </a></li>

                    <?php if ($usuarioAtual): ?>
                        <!-- Menu do usuário logado -->
                        <li class="user-menu">
                            <a href="#" class="user-toggle">
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($usuarioAtual['nome'], 0, 1)); ?>
                                    </div>
                                    <span class="user-name"><?php echo htmlspecialchars($usuarioAtual['nome']); ?></span>
                                </div>
                                <i class="fas fa-chevron-down" style="font-size: 12px; margin-left: 5px;"></i>
                            </a>
                            <div class="user-dropdown">
                                <div class="user-welcome">
                                    <strong>Seja bem-vindo, <?php echo htmlspecialchars(explode(' ', $usuarioAtual['nome'])[0]); ?>!</strong>
                                </div>
                                <a href="perfil.php" class="dropdown-item">
                                    <i class="fas fa-user-edit"></i> Editar Perfil
                                </a>

                                <?php if ($usuarioAtual['tipo'] === 'admin'): ?>
                                    <div id="admin-menu">
                                        <a href="cadastroEvento.php" class="dropdown-item">
                                            <i class="fas fa-calendar-plus"></i> Criar Evento
                                        </a>
                                        <a href="admin_eventos.php" class="dropdown-item">
                                            <i class="fas fa-calendar-alt"></i> Gerenciar Eventos
                                        </a>
                                        <a href="admin_usuarios.php" class="dropdown-item">
                                            <i class="fas fa-users-cog"></i> Gerenciar Usuários
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <div class="dropdown-divider"></div>
                                <a href="logout.php" class="dropdown-item logout">
                                    <i class="fas fa-sign-out-alt"></i> Sair
                                </a>
                            </div>
                        </li>
                    <?php else: ?>
                        <!-- Link de login (mostrado quando usuário não está logado) -->
                        <li id="login-menu">
                            <a href="login.php" class="login-link">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
</body>

</html>