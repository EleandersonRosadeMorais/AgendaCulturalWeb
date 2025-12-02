<?php
session_start();
require_once 'config.php';

// DEBUG: Verificar se config.php foi carregado
if (!function_exists('getEventoById')) {
    die("ERRO: Função getEventoById não encontrada no config.php");
}

// Verificar se o ID do evento foi passado
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$eventoId = intval($_GET['id']);

// Buscar o evento
$evento = getEventoById($eventoId);

if (!$evento) {
    echo "Evento não encontrado! ID: " . $eventoId;
    exit();
}

// Dados básicos para exibir
$cor = getCorPorTipo($evento['tipo']);
$icone = getIconePorTipo($evento['tipo']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($evento['titulo']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/index.css">
    <style>
        .evento-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .evento-header {
            border-bottom: 3px solid <?php echo $cor; ?>;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .evento-titulo {
            color: #02416D;
            margin: 0 0 10px 0;
        }
        
        .evento-tipo {
            background: <?php echo $cor; ?>;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-item {
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .info-label {
            font-weight: bold;
            color: #02416D;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>
    
    <div class="evento-container">
        <div class="evento-header">
            <div class="evento-tipo">
                <?php echo $icone; ?>
                <?php echo htmlspecialchars(ucfirst($evento['tipo'])); ?>
            </div>
            <h1 class="evento-titulo"><?php echo htmlspecialchars($evento['titulo']); ?></h1>
        </div>
        
        <div class="info-item">
            <div class="info-label">Descrição:</div>
            <div><?php echo nl2br(htmlspecialchars($evento['descricao'])); ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label">Data:</div>
            <div><?php echo date('d/m/Y', strtotime($evento['data'])); ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label">Hora:</div>
            <div><?php echo $evento['hora']; ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label">Local:</div>
            <div><?php echo htmlspecialchars($evento['local']); ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label">Organizador:</div>
            <div><?php echo htmlspecialchars($evento['responsavel']); ?></div>
        </div>
        
        <div style="margin-top: 30px;">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Voltar para Eventos
            </a>
        </div>
        
        <!-- DEBUG: Mostrar dados completos -->
        <?php if (isset($_GET['debug'])): ?>
            <div style="margin-top: 30px; background: #f0f0f0; padding: 15px; border-radius: 8px;">
                <h3>DEBUG - Dados do Evento:</h3>
                <pre><?php print_r($evento); ?></pre>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>