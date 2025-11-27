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
    <style>
        :root {
            --primary: #02416D;
            --secondary: #F8F8EC;
            --accent: #CDD071;
            --dark: #000000;
            --text: #333333;
            --palestra: #FF6B6B;
            --feira: #4ECDC4;
            --jogos: #45B7D1;
            --reuniao: #96CEB4;
            --realizado: #E0E0E0;
            --text-realizado: #888888;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--secondary) 0%, #ffffff 100%);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: linear-gradient(135deg, var(--primary) 0%, #012a4a 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--palestra), var(--feira), var(--jogos), var(--reuniao));
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-icon {
            font-size: 28px;
            background: var(--accent);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }

        .logo-text {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .logo-subtitle {
            font-size: 14px;
            opacity: 0.8;
            margin-top: -5px;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 15px;
            align-items: center;
        }

        nav li {
            position: relative;
        }

        nav a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        nav a:hover,
        nav a.active {
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .nav-badge {
            background: var(--accent);
            color: var(--primary);
            border-radius: 10px;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: bold;
        }

        /* Menu do Usuário */
        .user-menu {
            position: relative;
        }

        .user-toggle {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            min-width: 220px;
            padding: 10px 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .user-menu:hover .user-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .user-welcome {
            padding: 10px 15px;
            color: var(--primary);
            border-bottom: 1px solid #f0f0f0;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            color: var(--text);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 14px;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .dropdown-item:hover {
            background: var(--secondary);
            color: var(--primary);
        }

        .dropdown-item.logout {
            color: #FF6B6B;
        }

        .dropdown-item.logout:hover {
            background: #FF6B6B;
            color: white;
        }

        .dropdown-divider {
            height: 1px;
            background: #f0f0f0;
            margin: 8px 0;
        }

        .page-title {
            margin-bottom: 25px;
            color: var(--primary);
            padding-bottom: 10px;
            border-bottom: 3px solid var(--accent);
            display: inline-block;
            font-size: 28px;
            font-weight: 700;
        }

        .eventos-lista {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .evento-card {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            background: white;
            position: relative;
        }

        .evento-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }

        .evento-card.passado {
            background: var(--realizado);
            color: var(--text-realizado);
        }

        .evento-card.passado .evento-banner {
            filter: grayscale(0.7);
            opacity: 0.7;
        }

        .evento-banner {
            height: 180px;
            background: linear-gradient(135deg, var(--primary) 0%, #012a4a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            position: relative;
            overflow: hidden;
        }

        .evento-banner::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.2);
        }

        .banner-content {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 0 15px;
        }

        .evento-tipo {
            background: rgba(255, 255, 255, 0.9);
            color: var(--text);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 10px;
        }

        .evento-info {
            padding: 20px;
        }

        .evento-titulo {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.3;
            color: var(--primary);
        }

        .evento-card.passado .evento-titulo {
            color: var(--text-realizado);
        }

        .evento-detalhes {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 15px;
        }

        .detalhe-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        .detalhe-destaque {
            background: var(--accent);
            color: var(--primary);
            padding: 5px 10px;
            border-radius: 8px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .evento-descricao {
            font-size: 14px;
            margin-bottom: 20px;
            color: #666;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .evento-card.passado .evento-descricao {
            color: #999;
        }

        .evento-acoes {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: #012a4a;
            transform: translateY(-2px);
        }

        .btn-favorito {
            background: var(--accent);
            color: var(--primary);
        }

        .btn-favorito:hover {
            background: #c4c95a;
            transform: translateY(-2px);
        }

        .btn-remover {
            background: #FF6B6B;
            color: white;
        }

        .btn-remover:hover {
            background: #e55a5a;
            transform: translateY(-2px);
        }

        .evento-detalhe {
            max-width: 800px;
            margin: 0 auto;
        }

        .detalhe-banner {
            height: 300px;
            background: linear-gradient(135deg, var(--primary) 0%, #012a4a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-bottom: 30px;
            border-radius: 16px;
            position: relative;
            overflow: hidden;
        }

        .detalhe-banner::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
        }

        .detalhe-banner-content {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 0 20px;
        }

        .detalhe-info {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .info-item {
            margin-bottom: 20px;
            display: flex;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 15px;
        }

        .info-label {
            font-weight: 700;
            width: 140px;
            color: var(--primary);
            font-size: 16px;
        }

        .info-value {
            flex: 1;
            font-size: 16px;
        }

        .empty-message {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            font-style: italic;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .destaque-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--accent);
            color: var(--primary);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            z-index: 2;
        }

        .status-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            z-index: 2;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            nav ul {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }

            .eventos-lista {
                grid-template-columns: 1fr;
            }

            .info-item {
                flex-direction: column;
                gap: 5px;
            }

            .info-label {
                width: 100%;
            }

            .user-dropdown {
                right: -50px;
            }
        }
    </style>
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