<?php
// Simulação de banco de dados com arrays
session_start();

// Inicializar sessões se não existirem
if (!isset($_SESSION['eventos'])) {
    $_SESSION['eventos'] = [
        [
            'id' => 1,
            'titulo' => 'Festival de Música',
            'data' => '2024-06-15',
            'hora' => '18:00',
            'local' => 'Parque Central',
            'descricao' => 'Um festival com diversas bandas locais e nacionais.',
            'tipo' => 'Música',
            'responsavel' => 'Produções ABC',
            'banner' => 'banner1.jpg',
            'cor' => '#E8F4FD'
        ],
        [
            'id' => 2,
            'titulo' => 'Workshop de Tecnologia',
            'data' => '2024-06-20',
            'hora' => '14:00',
            'local' => 'Centro de Convenções',
            'descricao' => 'Workshop sobre as últimas tendências em tecnologia.',
            'tipo' => 'Educação',
            'responsavel' => 'Tech Solutions',
            'banner' => 'banner2.jpg',
            'cor' => '#FDE8E8'
        ],
        [
            'id' => 3,
            'titulo' => 'Feira de Artesanato',
            'data' => '2024-06-25',
            'hora' => '10:00',
            'local' => 'Praça da Matriz',
            'descricao' => 'Feira com artesanatos locais e comida típica.',
            'tipo' => 'Cultura',
            'responsavel' => 'Associação de Artesãos',
            'banner' => 'banner3.jpg',
            'cor' => '#E8F8E8'
        ]
    ];
}

if (!isset($_SESSION['favoritos'])) {
    $_SESSION['favoritos'] = [];
}

// Funções do sistema
function adicionarFavorito($eventoId)
{
    if (!in_array($eventoId, $_SESSION['favoritos'])) {
        $_SESSION['favoritos'][] = $eventoId;
        return true;
    }
    return false;
}

function removerFavorito($eventoId)
{
    $key = array_search($eventoId, $_SESSION['favoritos']);
    if ($key !== false) {
        unset($_SESSION['favoritos'][$key]);
        return true;
    }
    return false;
}

function getEventoById($id)
{
    foreach ($_SESSION['eventos'] as $evento) {
        if ($evento['id'] == $id) {
            return $evento;
        }
    }
    return null;
}

function getEventosFavoritos()
{
    $favoritos = [];
    foreach ($_SESSION['favoritos'] as $eventoId) {
        $evento = getEventoById($eventoId);
        if ($evento) {
            $favoritos[] = $evento;
        }
    }
    return $favoritos;
}

// Processar ações
if (isset($_GET['acao'])) {
    switch ($_GET['acao']) {
        case 'adicionar_favorito':
            if (isset($_GET['id'])) {
                adicionarFavorito($_GET['id']);
            }
            break;
        case 'remover_favorito':
            if (isset($_GET['id'])) {
                removerFavorito($_GET['id']);
            }
            break;
    }
}

// Determinar qual página mostrar
$pagina = 'feed';
if (isset($_GET['pagina'])) {
    $pagina = $_GET['pagina'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Eventos</title>
    <style>
        :root {
            --primary-color: #F8F8EC;
            --secondary-color: #02416D;
            --accent-color: #000000;
            --event-color-1: #E8F4FD;
            --event-color-2: #FDE8E8;
            --event-color-3: #E8F8E8;
            --event-color-4: #F8F4E8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--primary-color);
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background-color: var(--secondary-color);
            color: white;
            padding: 15px 0;
            margin-bottom: 20px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        nav ul {
            display: flex;
            list-style: none;
        }

        nav li {
            margin-left: 20px;
        }

        nav a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        nav a:hover,
        nav a.active {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .page-title {
            margin-bottom: 20px;
            color: var(--secondary-color);
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 10px;
        }

        .eventos-lista {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .evento-card {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .evento-card:hover {
            transform: translateY(-5px);
        }

        .evento-banner {
            height: 150px;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-weight: bold;
        }

        .evento-info {
            padding: 15px;
        }

        .evento-titulo {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .evento-detalhes {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .evento-descricao {
            font-size: 14px;
            margin-bottom: 15px;
            color: #666;
        }

        .evento-acoes {
            display: flex;
            justify-content: space-between;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #013052;
        }

        .btn-favorito {
            background-color: #ffc107;
            color: #333;
        }

        .btn-favorito:hover {
            background-color: #e0a800;
        }

        .btn-remover {
            background-color: #dc3545;
            color: white;
        }

        .btn-remover:hover {
            background-color: #c82333;
        }

        .evento-detalhe {
            max-width: 800px;
            margin: 0 auto;
        }

        .detalhe-banner {
            height: 300px;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-weight: bold;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .detalhe-info {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .info-item {
            margin-bottom: 15px;
            display: flex;
        }

        .info-label {
            font-weight: bold;
            width: 120px;
        }

        .info-value {
            flex: 1;
        }

        .empty-message {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>

<body>
    <header>
        <div class="header-content">
            <div class="logo">Sistema de Eventos</div>
            <nav>
                <ul>
                    <li><a href="?pagina=feed" class="<?php echo $pagina == 'feed' ? 'active' : ''; ?>">Feed</a></li>
                    <li><a href="?pagina=favoritos" class="<?php echo $pagina == 'favoritos' ? 'active' : ''; ?>">Favoritos</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <?php if ($pagina == 'feed'): ?>
            <h1 class="page-title">Feed de Eventos</h1>

            <div class="eventos-lista">
                <?php
                // Ordenar eventos por data (mais recentes primeiro)
                $eventos = $_SESSION['eventos'];
                usort($eventos, function ($a, $b) {
                    return strcmp($a['data'], $b['data']);
                });

                if (empty($eventos)): ?>
                    <div class="empty-message">Nenhum evento cadastrado.</div>
                <?php else: ?>
                    <?php foreach ($eventos as $evento): ?>
                        <div class="evento-card" style="background-color: <?php echo $evento['cor']; ?>">
                            <div class="evento-banner">
                                Banner: <?php echo $evento['banner']; ?>
                            </div>
                            <div class="evento-info">
                                <div class="evento-titulo"><?php echo $evento['titulo']; ?></div>
                                <div class="evento-detalhes">
                                    <span><?php echo date('d/m/Y', strtotime($evento['data'])); ?></span>
                                    <span><?php echo $evento['hora']; ?></span>
                                </div>
                                <div class="evento-descricao"><?php echo $evento['descricao']; ?></div>
                                <div class="evento-acoes">
                                    <a href="?pagina=evento&id=<?php echo $evento['id']; ?>" class="btn btn-primary">Ver Detalhes</a>
                                    <?php if (in_array($evento['id'], $_SESSION['favoritos'])): ?>
                                        <a href="?acao=remover_favorito&id=<?php echo $evento['id']; ?>&pagina=feed" class="btn btn-remover">Remover Favorito</a>
                                    <?php else: ?>
                                        <a href="?acao=adicionar_favorito&id=<?php echo $evento['id']; ?>&pagina=feed" class="btn btn-favorito">Adicionar Favorito</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php elseif ($pagina == 'favoritos'): ?>
            <h1 class="page-title">Meus Eventos Favoritos</h1>

            <div class="eventos-lista">
                <?php
                $favoritos = getEventosFavoritos();

                if (empty($favoritos)): ?>
                    <div class="empty-message">Você ainda não adicionou nenhum evento aos favoritos.</div>
                <?php else: ?>
                    <?php foreach ($favoritos as $evento): ?>
                        <div class="evento-card" style="background-color: <?php echo $evento['cor']; ?>">
                            <div class="evento-banner">
                                Banner: <?php echo $evento['banner']; ?>
                            </div>
                            <div class="evento-info">
                                <div class="evento-titulo"><?php echo $evento['titulo']; ?></div>
                                <div class="evento-detalhes">
                                    <span><?php echo date('d/m/Y', strtotime($evento['data'])); ?></span>
                                    <span><?php echo $evento['hora']; ?></span>
                                </div>
                                <div class="evento-descricao"><?php echo $evento['descricao']; ?></div>
                                <div class="evento-acoes">
                                    <a href="?pagina=evento&id=<?php echo $evento['id']; ?>" class="btn btn-primary">Ver Detalhes</a>
                                    <a href="?acao=remover_favorito&id=<?php echo $evento['id']; ?>&pagina=favoritos" class="btn btn-remover">Remover Favorito</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php elseif ($pagina == 'evento' && isset($_GET['id'])): ?>
            <?php
            $evento = getEventoById($_GET['id']);
            if ($evento): ?>
                <div class="evento-detalhe">
                    <div class="detalhe-banner">
                        Banner: <?php echo $evento['banner']; ?>
                    </div>

                    <div class="detalhe-info">
                        <h1 class="page-title"><?php echo $evento['titulo']; ?></h1>

                        <div class="info-item">
                            <div class="info-label">Data:</div>
                            <div class="info-value"><?php echo date('d/m/Y', strtotime($evento['data'])); ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Hora:</div>
                            <div class="info-value"><?php echo $evento['hora']; ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Local:</div>
                            <div class="info-value"><?php echo $evento['local']; ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Descrição:</div>
                            <div class="info-value"><?php echo $evento['descricao']; ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Tipo:</div>
                            <div class="info-value"><?php echo $evento['tipo']; ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Responsável:</div>
                            <div class="info-value"><?php echo $evento['responsavel']; ?></div>
                        </div>

                        <div class="evento-acoes" style="margin-top: 20px;">
                            <a href="?pagina=feed" class="btn btn-primary">Voltar ao Feed</a>

                            <?php if (in_array($evento['id'], $_SESSION['favoritos'])): ?>
                                <a href="?acao=remover_favorito&id=<?php echo $evento['id']; ?>&pagina=evento&id=<?php echo $evento['id']; ?>" class="btn btn-remover">Remover dos Favoritos</a>
                            <?php else: ?>
                                <a href="?acao=adicionar_favorito&id=<?php echo $evento['id']; ?>&pagina=evento&id=<?php echo $evento['id']; ?>" class="btn btn-favorito">Adicionar aos Favoritos</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-message">Evento não encontrado.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>

</html>