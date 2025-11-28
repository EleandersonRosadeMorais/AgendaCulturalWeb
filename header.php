<?php
require_once 'config.php';

// Determinar qual página mostrar
$pagina = 'feed';
if (isset($_GET['pagina'])) {
    $pagina = $_GET['pagina'];
}

// Obter eventos conforme a necessidade
$eventosFuturos = getEventosFuturos();
$eventosPassados = getEventosPassados();
$favoritos = getEventosFavoritos();

$usuarioAtual = getUsuarioAtual();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Escolar - Eventos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <div class="logo-text">Agenda Escolar</div>
                    <div class="logo-subtitle">Não perca nenhum evento!</div>
                </div>
            </div>
            <nav>
                <!-- No header.php ou outras páginas -->
                <script type="module">
                    import {
                        observarEstadoAuth
                    } from './js/auth.js';

                    observarEstadoAuth((estado) => {
                        if (estado.logado) {
                            console.log('Usuário logado:', estado.usuario);
                            // Atualizar interface
                        } else {
                            console.log('Usuário não logado');
                            // Redirecionar para login se necessário
                        }
                    });
                </script>
                <ul>
                    <li><a href="index.php" class="<?php echo $pagina == 'feed' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i> Feed
                        </a></li>
                    <li><a href="favoritos.php" class="<?php echo $pagina == 'favoritos' ? 'active' : ''; ?>">
                            <i class="fas fa-heart"></i> Favoritos
                            <?php if (!empty($favoritos)): ?>
                                <span class="nav-badge"><?php echo count($favoritos); ?></span>
                            <?php endif; ?>
                        </a></li>

                    <?php if ($usuarioAtual): ?>
                        <li class="user-menu">
                            <a href="#" class="user-toggle">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($usuarioAtual['nome']); ?>
                                <i class="fas fa-chevron-down" style="font-size: 12px; margin-left: 5px;"></i>
                            </a>
                            <div class="user-dropdown">
                                <div class="user-welcome">
                                    <strong>Seja bem-vindo, <?php echo htmlspecialchars(explode(' ', $usuarioAtual['nome'])[0]); ?>!</strong>
                                </div>
                                <a href="perfil.php" class="dropdown-item">
                                    <i class="fas fa-user-edit"></i> Editar Perfil
                                </a>

                                <?php if (isAdmin()): ?>
                                    <a href="gerenciar_eventos.php" class="dropdown-item">
                                        <i class="fas fa-calendar-plus"></i> Gerenciar Eventos
                                    </a>
                                    <a href="gerenciar_usuarios.php" class="dropdown-item">
                                        <i class="fas fa-users-cog"></i> Gerenciar Usuários
                                    </a>
                                <?php endif; ?>

                                <div class="dropdown-divider"></div>
                                <a href="logout.php" class="dropdown-item logout">
                                    <i class="fas fa-sign-out-alt"></i> Sair
                                </a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li><a href="login.php" class="<?php echo $pagina == 'login' ? 'active' : ''; ?>">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>