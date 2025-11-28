<?php
session_start();

// Simulação de dados do evento (em um sistema real, viria do banco de dados)
$evento = [
    'id' => 1,
    'titulo' => 'Festival de Música',
    'data' => '2024-01-15',
    'hora' => '18:00',
    'local' => 'Parque Central',
    'tipo_evento' => 'cultural',
    'responsavel' => 'João Silva',
    'descricao' => 'Um incrível festival de música com diversas atrações locais e nacionais.'
];

$errors = [];
$success = '';

// Processa o formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação e processamento dos dados
    $titulo = htmlspecialchars(trim($_POST['titulo']));
    $data = htmlspecialchars(trim($_POST['data']));
    $hora = htmlspecialchars(trim($_POST['hora']));
    $local = htmlspecialchars(trim($_POST['local']));
    $tipo_evento = htmlspecialchars(trim($_POST['tipo_evento']));
    $responsavel = htmlspecialchars(trim($_POST['responsavel']));
    $descricao = htmlspecialchars(trim($_POST['descricao']));

    // Validações básicas
    if (empty($titulo)) {
        $errors[] = 'Título do evento é obrigatório';
    }
    if (empty($data)) {
        $errors[] = 'Data do evento é obrigatória';
    }
    if (empty($hora)) {
        $errors[] = 'Hora do evento é obrigatória';
    }
    if (empty($local)) {
        $errors[] = 'Local do evento é obrigatório';
    }
    if (empty($descricao)) {
        $errors[] = 'Descrição do evento é obrigatória';
    }
    if (empty($tipo_evento)) {
        $errors[] = 'Tipo de evento é obrigatório';
    }
    if (empty($responsavel)) {
        $errors[] = 'Responsável pelo evento é obrigatório';
    }

    // Se não há erros, simula sucesso
    if (empty($errors)) {
        $success = 'Evento atualizado com sucesso!';
        
        // Atualiza os dados do evento (em um sistema real, salvaria no banco)
        $evento['titulo'] = $titulo;
        $evento['data'] = $data;
        $evento['hora'] = $hora;
        $evento['local'] = $local;
        $evento['tipo_evento'] = $tipo_evento;
        $evento['responsavel'] = $responsavel;
        $evento['descricao'] = $descricao;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Evento - Sistema de Eventos</title>
    <link rel="stylesheet" href="css/cadastroEvento.css">
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>Editar Evento</h1>
                <p>Atualize os dados do evento</p>
            </div>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form class="register-form" method="POST" action="" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="titulo">Título do Evento *</label>
                        <input type="text" id="titulo" name="titulo" 
                               value="<?php echo htmlspecialchars($evento['titulo']); ?>" 
                               placeholder="Digite o título do evento" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="data">Data *</label>
                        <input type="date" id="data" name="data" 
                               value="<?php echo htmlspecialchars($evento['data']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hora">Hora *</label>
                        <input type="time" id="hora" name="hora" 
                               value="<?php echo htmlspecialchars($evento['hora']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="local">Local *</label>
                        <input type="text" id="local" name="local" 
                               value="<?php echo htmlspecialchars($evento['local']); ?>" 
                               placeholder="Local do evento" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo_evento">Tipo de Evento *</label>
                        <select id="tipo_evento" name="tipo_evento" class="form-select" required>
                            <option value="">Selecione o tipo</option>
                            <option value="palestra" <?php echo $evento['tipo_evento'] === 'palestra' ? 'selected' : ''; ?>>Palestra</option>
                            <option value="festa" <?php echo $evento['tipo_evento'] === 'festa' ? 'selected' : ''; ?>>Festa</option>
                            <option value="esporte" <?php echo $evento['tipo_evento'] === 'esporte' ? 'selected' : ''; ?>>Esporte</option>
                            <option value="reuniao" <?php echo $evento['tipo_evento'] === 'reuniao' ? 'selected' : ''; ?>>Reunião</option>
                            <option value="cultural" <?php echo $evento['tipo_evento'] === 'cultural' ? 'selected' : ''; ?>>Cultural</option>
                            <option value="academico" <?php echo $evento['tipo_evento'] === 'academico' ? 'selected' : ''; ?>>Acadêmico</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="responsavel">Responsável *</label>
                        <input type="text" id="responsavel" name="responsavel" 
                               value="<?php echo htmlspecialchars($evento['responsavel']); ?>" 
                               placeholder="Nome do responsável pelo evento" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="descricao">Descrição *</label>
                        <textarea id="descricao" name="descricao" 
                                  placeholder="Descreva o evento"
                                  rows="4" required><?php echo htmlspecialchars($evento['descricao']); ?></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="banner">Banner do Evento</label>
                        <input type="file" id="banner" name="banner" 
                               accept="image/*" class="file-input">
                        <small class="file-help">Formatos: JPG, PNG, GIF (Max: 2MB)</small>
                        <div class="current-file">
                            <small>Banner atual: <strong>festival_musica.jpg</strong></small>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="cadastrarEvento-btn">Atualizar Evento</button>
                    <a href="admin_eventos.php" class="voltar-btn">Voltar</a>
                </div>

                <div class="event-info">
                    <p><strong>ID do Evento:</strong> <?php echo $evento['id']; ?></p>
                    <p><strong>Data de Criação:</strong> 01/01/2024 10:00</p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>