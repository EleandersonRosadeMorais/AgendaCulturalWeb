<?php
session_start();



$errors = [];
$success = '';
$formData = [
    'titulo' => '',
    'data' => '',
    'hora' => '',
    'local' => '',
    'descricao' => '',
    'tipo_evento' => '',
    'responsavel' => ''
];

// Processa o formulário de cadastro de evento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulação de processamento
    $formData['titulo'] = htmlspecialchars(trim($_POST['titulo']));
    $formData['data'] = htmlspecialchars(trim($_POST['data']));
    $formData['hora'] = htmlspecialchars(trim($_POST['hora']));
    $formData['local'] = htmlspecialchars(trim($_POST['local']));
    $formData['descricao'] = htmlspecialchars(trim($_POST['descricao']));
    $formData['tipo_evento'] = htmlspecialchars(trim($_POST['tipo_evento']));
    $formData['responsavel'] = htmlspecialchars(trim($_POST['responsavel']));
    
    // Simulação de sucesso no cadastro
    $success = 'Evento cadastrado com sucesso!';
    
    // Limpa o formulário após sucesso
    if ($success) {
        $formData = [
            'titulo' => '',
            'data' => '',
            'hora' => '',
            'local' => '',
            'descricao' => '',
            'tipo_evento' => '',
            'responsavel' => ''
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Evento - Sistema de Eventos</title>
    <link rel="stylesheet" href="css/cadastroEvento.css">
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>Cadastrar Evento</h1>
                <p>Preencha os dados do evento</p>
            </div>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form class="register-form" method="POST" action="" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="titulo">Título do Evento *</label>
                        <input type="text" id="titulo" name="titulo" 
                               value="<?php echo htmlspecialchars($formData['titulo']); ?>" 
                               placeholder="Digite o título do evento">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="data">Data *</label>
                        <input type="date" id="data" name="data" 
                               value="<?php echo htmlspecialchars($formData['data']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="hora">Hora *</label>
                        <input type="time" id="hora" name="hora" 
                               value="<?php echo htmlspecialchars($formData['hora']); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="local">Local *</label>
                        <input type="text" id="local" name="local" 
                               value="<?php echo htmlspecialchars($formData['local']); ?>" 
                               placeholder="Local do evento">
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo_evento">Tipo de Evento *</label>
                        <select id="tipo_evento" name="tipo_evento" class="form-select">
                            <option value="">Selecione o tipo</option>
                            <option value="cultural" <?php echo $formData['tipo_evento'] === 'cultural' ? 'selected' : ''; ?>>Cultural</option>
                            <option value="esportivo" <?php echo $formData['tipo_evento'] === 'esportivo' ? 'selected' : ''; ?>>Esportivo</option>
                            <option value="academico" <?php echo $formData['tipo_evento'] === 'academico' ? 'selected' : ''; ?>>Acadêmico</option>
                            <option value="empresarial" <?php echo $formData['tipo_evento'] === 'empresarial' ? 'selected' : ''; ?>>Empresarial</option>
                            <option value="social" <?php echo $formData['tipo_evento'] === 'social' ? 'selected' : ''; ?>>Social</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="responsavel">Responsável *</label>
                        <input type="text" id="responsavel" name="responsavel" 
                               value="<?php echo htmlspecialchars($formData['responsavel']); ?>" 
                               placeholder="Nome do responsável pelo evento">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="descricao">Descrição *</label>
                        <textarea id="descricao" name="descricao" 
                                  placeholder="Descreva o evento"
                                  rows="4"><?php echo htmlspecialchars($formData['descricao']); ?></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="banner">Banner do Evento</label>
                        <input type="file" id="banner" name="banner" 
                               accept="image/*" class="file-input">
                        <small class="file-help">Formatos: JPG, PNG, GIF (Max: 2MB)</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="register-btn">Cadastrar Evento</button>
                    <a href="index.php" class="cancel-btn">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>