<?php
session_start();

// Configurações do MySQL - AJUSTE CONFORME SEU XAMPP
define('DB_HOST', 'localhost');
define('DB_NAME', 'bdAgendaCultural');
define('DB_USER', 'root');  // Padrão do XAMPP
define('DB_PASS', '');      // Padrão do XAMPP (vazio)

// Conexão com o MySQL
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "✅ Conectado ao MySQL com sucesso!";
} catch (PDOException $e) {
    die("❌ Erro na conexão com o MySQL: " . $e->getMessage());
}

// Função para verificar login
function verificarLogin($email, $senha)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // Verificar senha (usando password_verify para senhas hasheadas)
            if (password_verify($senha, $usuario['senha'])) {
                // Login bem-sucedido
                $_SESSION['usuario'] = [
                    'id' => $usuario['id_pk'],
                    'nome' => $usuario['nome'],
                    'email' => $usuario['email'],
                    'tipo' => $usuario['tipo'],
                    'dataNasc' => $usuario['dataNascimento'],
                    'cpf' => $usuario['cpf']
                ];
                return true;
            }
        }
        return false;
    } catch (PDOException $e) {
        error_log("Erro no login: " . $e->getMessage());
        return false;
    }
}

// Função para obter usuário atual
function getUsuarioAtual()
{
    return $_SESSION['usuario'] ?? null;
}

// Função para verificar se é admin
function isAdmin()
{
    return ($_SESSION['usuario']['tipo'] ?? '') === 'admin';
}

// Função simples para debug
function debug($data)
{
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

// Funções auxiliares para cores e ícones
function getCorPorTipo($tipo)
{
    $cores = [
        'música' => '#FF6B6B',
        'educação' => '#4ECDC4',
        'cultura' => '#45B7D1',
        'esportes' => '#96CEB4',
        'feira' => '#FFA726',
        'teatro' => '#AB47BC',
        'workshop' => '#26A69A',
        'palestra' => '#7E57C2'
    ];
    return $cores[strtolower($tipo)] ?? '#02416D';
}

function getIconePorTipo($tipo)
{
    $icones = [
        'música' => '<i class="fas fa-music"></i>',
        'educação' => '<i class="fas fa-graduation-cap"></i>',
        'cultura' => '<i class="fas fa-landmark"></i>',
        'esportes' => '<i class="fas fa-running"></i>',
        'feira' => '<i class="fas fa-store"></i>',
        'teatro' => '<i class="fas fa-theater-masks"></i>',
        'workshop' => '<i class="fas fa-laptop-code"></i>',
        'palestra' => '<i class="fas fa-chalkboard-teacher"></i>'
    ];
    return $icones[strtolower($tipo)] ?? '<i class="fas fa-calendar-alt"></i>';
}
