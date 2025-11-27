<?php
session_start();

// Dados de exemplo
$eventos = [
    [
        'id' => 1,
        'titulo' => 'Festival de Música',
        'data' => '2024-01-15',
        'hora' => '18:00',
        'local' => 'Parque Central',
        'tipo_evento' => 'cultural',
        'responsavel' => 'João Silva',
        'data_criacao' => '2024-01-01 10:00:00'
    ],
    [
        'id' => 2,
        'titulo' => 'Campeonato de Futebol',
        'data' => '2024-01-20',
        'hora' => '14:00',
        'local' => 'Estádio Municipal',
        'tipo_evento' => 'esportivo',
        'responsavel' => 'Maria Santos',
        'data_criacao' => '2024-01-02 15:30:00'
    ]
];

$mensagem_sucesso = '';
$mensagem_erro = '';

// Simulação de busca
$termo_busca = $_GET['buscar'] ?? '';
$eventos_filtrados = $eventos;

if (!empty($termo_busca)) {
    $eventos_filtrados = array_filter($eventos, function($evento) use ($termo_busca) {
        return stripos($evento['titulo'], $termo_busca) !== false || 
               stripos($evento['local'], $termo_busca) !== false;
    });
}

// Simulação de exclusão
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);
    $mensagem_sucesso = "Evento #{$id_excluir} excluído com sucesso!";
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Eventos - Admin - Sistema de Eventos</title>
    <link rel="stylesheet" href="css/admin_eventos.css">
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
                    <a href="cadastroevento.php">Cadastrar Evento</a>
                    <a href="index.php">Sair</a>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="page-header">
            <h2>Gerenciador de Eventos</h2>
            <p>Painel administrativo - Gerencie todos os eventos do sistema</p>
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
                <div class="stat-number"><?php echo count($eventos); ?></div>
                <div class="stat-label">Total de Eventos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($eventos, function($e) { return $e['tipo_evento'] === 'cultural'; })); ?></div>
                <div class="stat-label">Eventos Culturais</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($eventos, function($e) { return $e['tipo_evento'] === 'esportivo'; })); ?></div>
                <div class="stat-label">Eventos Esportivos</div>
            </div>
        </div>

        <div class="events-section">
            <div class="section-header">
                <h3>Lista de Eventos</h3>
                <form method="GET" class="search-box">
                    <input type="text"
                        class="search-input"
                        name="buscar"
                        placeholder="Buscar por título ou local..."
                        value="<?php echo htmlspecialchars($termo_busca); ?>">
                    <button type="submit" class="btn-search">🔍</button>
                </form>
            </div>

            <?php if (empty($eventos_filtrados)): ?>
                <div class="empty-state">
                    <h3>
                        <?php if (!empty($termo_busca)): ?>
                            Nenhum evento encontrado para "<?php echo htmlspecialchars($termo_busca); ?>"
                        <?php else: ?>
                            Nenhum evento cadastrado
                        <?php endif; ?>
                    </h3>
                    <p>
                        <?php if (!empty($termo_busca)): ?>
                            Nenhum evento foi encontrado com esses termos.
                        <?php else: ?>
                            Comece cadastrando o primeiro evento no sistema.
                        <?php endif; ?>
                    </p>
                    <?php if (empty($termo_busca)): ?>
                        <a href="cadastroevento.php" class="btn btn-primary">Cadastrar Primeiro Evento</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php if (!empty($termo_busca)): ?>
                    <div class="search-results-info">
                        <p>Encontrados <strong><?php echo count($eventos_filtrados); ?></strong> evento(s) para "<?php echo htmlspecialchars($termo_busca); ?>"</p>
                    </div>
                <?php endif; ?>

                <table class="events-table">
                    <thead>
                        <tr>
                            <th>Evento</th>
                            <th>Data e Hora</th>
                            <th>Local</th>
                            <th>Tipo</th>
                            <th>Responsável</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eventos_filtrados as $evento): ?>
                            <tr>
                                <td>
                                    <div class="event-info">
                                        <div class="event-avatar <?php echo $evento['tipo_evento']; ?>">
                                            <?php 
                                            $icones = [
                                                'cultural' => '🎭',
                                                'esportivo' => '⚽',
                                                'academico' => '🎓',
                                                'empresarial' => '💼',
                                                'palestra' => '📢',
                                                'festa' => '🎉',
                                                'reuniao' => '🤝'
                                            ];
                                            echo $icones[$evento['tipo_evento']] ?? '📅';
                                            ?>
                                        </div>
                                        <div class="event-details">
                                            <h4><?php echo htmlspecialchars($evento['titulo']); ?></h4>
                                            <p>ID: <?php echo $evento['id']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong>Data:</strong> <?php echo date('d/m/Y', strtotime($evento['data'])); ?><br>
                                    <strong>Hora:</strong> <?php echo $evento['hora']; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($evento['local']); ?>
                                </td>
                                <td>
                                    <span class="event-type <?php echo $evento['tipo_evento']; ?>">
                                        <?php
                                        $tipos = [
                                            'cultural' => 'Cultural',
                                            'esportivo' => 'Esportivo',
                                            'academico' => 'Acadêmico',
                                            'empresarial' => 'Empresarial',
                                            'palestra' => 'Palestra',
                                            'festa' => 'Festa',
                                            'reuniao' => 'Reunião'
                                        ];
                                        echo $tipos[$evento['tipo_evento']] ?? 'Outro';
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($evento['responsavel']); ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="editar_evento.php?id=<?php echo $evento['id']; ?>" class="btn btn-edit">
                                            ✏️ Editar
                                        </a>
                                        <a href="visualizar_evento.php?id=<?php echo $evento['id']; ?>" class="btn btn-view">
                                            👁️ Visualizar
                                        </a>
                                        <a href="admin_eventos.php?excluir=<?php echo $evento['id']; ?><?php echo !empty($termo_busca) ? '&buscar=' . urlencode($termo_busca) : ''; ?>"
                                            class="btn btn-delete"
                                            onclick="return confirm('Tem certeza que deseja excluir o evento \"<?php echo htmlspecialchars($evento['titulo']); ?>\"? Esta ação não pode ser desfeita!')">
                                            🗑️ Excluir
                                        </a>
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