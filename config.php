<?php
session_start();

// Inicializar dados se não existirem
// No array de eventos, substitua os 'banner' por URLs reais:
if (!isset($_SESSION['eventos'])) {
    $_SESSION['eventos'] = [
        [
            'id' => 1,
            'titulo' => 'Palestra: Profissões do Futuro',
            'data' => '2025-12-15',
            'hora' => '14:00',
            'local' => 'Auditório Principal',
            'descricao' => 'Venha descobrir as carreiras que estarão em alta nos próximos anos com especialistas do mercado.',
            'tipo' => 'palestra',
            'responsavel' => 'Prof. Carlos Silva - Orientação Vocacional',
            'banner' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80',
            'cor' => '#FF6B6B',
            'destaque' => true
        ],
        [
            'id' => 2,
            'titulo' => 'Feira de Ciências 2024',
            'data' => '2024-06-20',
            'hora' => '09:00',
            'local' => 'Quadra Coberta',
            'descricao' => 'Exposição dos melhores projetos científicos desenvolvidos pelos alunos. Venha se inspirar!',
            'tipo' => 'feira',
            'responsavel' => 'Coord. Maria Santos - Departamento de Ciências',
            'banner' => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80',
            'cor' => '#4ECDC4',
            'destaque' => true
        ],
        [
            'id' => 3,
            'titulo' => 'Torneio de Vôlei Interclasses',
            'data' => '2024-06-18',
            'hora' => '16:00',
            'local' => 'Quadra Poliesportiva',
            'descricao' => 'Final do campeonato de vôlei entre as turmas do ensino médio. Venha torcer!',
            'tipo' => 'jogos',
            'responsavel' => 'Prof. Rodrigo Lima - Educação Física',
            'banner' => 'https://images.unsplash.com/photo-1612872087720-bb876e2e67d1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80',
            'cor' => '#45B7D1',
            'destaque' => false
        ],
        [
            'id' => 4,
            'titulo' => 'Reunião de Pais e Mestres - 2º Bimestre',
            'data' => '2024-06-22',
            'hora' => '19:00',
            'local' => 'Salas de Aula',
            'descricao' => 'Reunião para entrega de boletins e discussão do desempenho dos alunos.',
            'tipo' => 'reuniao',
            'responsavel' => 'Diretora Ana Oliveira - Direção',
            'banner' => 'https://images.unsplash.com/photo-1582213782179-e0d53f98f2ca?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80',
            'cor' => '#96CEB4',
            'destaque' => false
        ],
        [
            'id' => 5,
            'titulo' => 'Workshop de Robótica',
            'data' => '2024-05-10',
            'hora' => '13:30',
            'local' => 'Laboratório de Informática',
            'descricao' => 'Workshop prático de introdução à robótica para alunos do ensino médio.',
            'tipo' => 'palestra',
            'responsavel' => 'Prof. João Mendes - Tecnologia',
            'banner' => 'https://images.unsplash.com/photo-1581091226825-c6b00e2a31c0?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80',
            'cor' => '#FF6B6B',
            'destaque' => false
        ]
    ];
}

if (!isset($_SESSION['favoritos'])) {
    $_SESSION['favoritos'] = [];
}

// Inicializar dados de usuários se não existirem
if (!isset($_SESSION['usuarios'])) {
    $_SESSION['usuarios'] = [
        [
            'id' => 1,
            'nome' => 'Administrador',
            'email' => 'admin@escola.com',
            'tipo' => 'admin',
            'senha' => '123456'
        ],
        [
            'id' => 2,
            'nome' => 'João Silva',
            'email' => 'aluno@escola.com',
            'tipo' => 'usuario',
            'senha' => '123456'
        ]
    ];
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
        $_SESSION['favoritos'] = array_values($_SESSION['favoritos']);
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

function getEventosFuturos()
{
    $hoje = date('Y-m-d');
    $eventosFuturos = [];

    foreach ($_SESSION['eventos'] as $evento) {
        if ($evento['data'] >= $hoje) {
            $eventosFuturos[] = $evento;
        }
    }

    // Ordenar por data mais próxima
    usort($eventosFuturos, function ($a, $b) {
        return strcmp($a['data'], $b['data']);
    });

    return $eventosFuturos;
}

function getEventosPassados()
{
    $hoje = date('Y-m-d');
    $eventosPassados = [];

    foreach ($_SESSION['eventos'] as $evento) {
        if ($evento['data'] < $hoje) {
            $eventosPassados[] = $evento;
        }
    }

    return $eventosPassados;
}

function getCorPorTipo($tipo)
{
    switch ($tipo) {
        case 'palestra':
            return '#FF6B6B';
        case 'feira':
            return '#4ECDC4';
        case 'jogos':
            return '#45B7D1';
        case 'reuniao':
            return '#96CEB4';
        default:
            return '#CDD071';
    }
}

function getIconePorTipo($tipo)
{
    switch ($tipo) {
        case 'palestra':
            return '🎤';
        case 'feira':
            return '🎪';
        case 'jogos':
            return '⚽';
        case 'reuniao':
            return '👥';
        default:
            return '📅';
    }
}

// Função para obter usuário atual
function getUsuarioAtual()
{
    return $_SESSION['usuario_logado'] ?? null;
}

// Função para verificar se é admin
function isAdmin()
{
    $usuario = getUsuarioAtual();
    return $usuario && $usuario['tipo'] === 'admin';
}

// Função auxiliar para ajustar brilho da cor
function adjustBrightness($hex, $steps)
{
    $steps = max(-255, min(255, $steps));
    $hex = str_replace('#', '', $hex);

    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
    }

    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));

    $r_hex = str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
    $g_hex = str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
    $b_hex = str_pad(dechex($b), 2, '0', STR_PAD_LEFT);

    return '#' . $r_hex . $g_hex . $b_hex;
}
