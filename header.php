<?php
// header.php - Verificar se a sessão já foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// INCLUIR config.php para ter acesso às funções
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Cultural</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php" class="logo-link">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Agenda Cultural</span>
                    </a>
                </div>

                <nav class="main-nav">
                    <ul class="nav-list">
                        <li><a href="index.php"><i class="fas fa-home"></i> Início</a></li>
                        
                        <?php if (isset($_SESSION['usuario'])): ?>
                            <!-- Menu para usuários logados -->
                            <li><a href="cadastroEvento.php"><i class="fas fa-plus-circle"></i> Criar Evento</a></li>
                            
                            <!-- MOSTRAR "Favoritos" APENAS PARA USUÁRIOS COMUNS (NÃO ADMIN) -->
                            <?php if (!isAdmin()): ?>
                                <li><a href="favoritos.php"><i class="fas fa-heart"></i> Favoritos</a></li>
                            <?php endif; ?>
                            
                            <!-- MOSTRAR "Gerenciador Admin" APENAS PARA ADMINS -->
                            
                            <li class="nav-dropdown">
                                <a href="#" class="user-menu">
                                    <i class="fas fa-user-circle"></i>
                                    <?php echo htmlspecialchars($_SESSION['usuario']['nome']); ?>
                                    <i class="fas fa-chevron-down"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="perfil.php"><i class="fas fa-user"></i> Meu Perfil</a></li>
                                    
                                    <!-- MOSTRAR "Gerenciador" APENAS PARA ADMINS NO DROPDOWN -->
                                    <?php if (isAdmin()): ?>
                                        <li><a href="admin_usuarios.php"><i class="fas fa-cog"></i> Gerenciador</a></li>
                                    <?php endif; ?>
                                    
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <!-- Menu para visitantes não logados -->
                            <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Entrar</a></li>
                            <li><a href="cadastroUsuario.php" class="register-btn">
                                <i class="fas fa-user-plus"></i> Cadastrar
                            </a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>