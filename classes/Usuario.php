<?php

class Usuario
{
    // Propriedades
    private $uid;
    private $nome;
    private $email;
    private $tipo;
    private $idade;
    private $cpf;
    private $dataCriacao;
    private $dataAtualizacao;
    private $favoritos;

    // Construtor
    public function __construct($uid = null, $nome = '', $email = '', $tipo = 'usuario', $idade = null, $cpf = '', $dataCriacao = null, $dataAtualizacao = null, $favoritos = [])
    {
        $this->uid = $uid;
        $this->nome = $nome;
        $this->email = $email;
        $this->tipo = $tipo;
        $this->idade = $idade;
        $this->cpf = $cpf;
        $this->dataCriacao = $dataCriacao ?: new DateTime();
        $this->dataAtualizacao = $dataAtualizacao;
        $this->favoritos = $favoritos;
    }

    // Getters
    public function getUid()
    {
        return $this->uid;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getTipo()
    {
        return $this->tipo;
    }

    public function getIdade()
    {
        return $this->idade;
    }

    public function getCpf()
    {
        return $this->cpf;
    }

    public function getDataCriacao()
    {
        return $this->dataCriacao;
    }

    public function getDataAtualizacao()
    {
        return $this->dataAtualizacao;
    }

    public function getFavoritos()
    {
        return $this->favoritos;
    }

    // Setters
    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
        return $this;
    }

    public function setIdade($idade)
    {
        $this->idade = $idade;
        return $this;
    }

    public function setCpf($cpf)
    {
        $this->cpf = $cpf;
        return $this;
    }

    public function setDataCriacao($dataCriacao)
    {
        $this->dataCriacao = $dataCriacao;
        return $this;
    }

    public function setDataAtualizacao($dataAtualizacao)
    {
        $this->dataAtualizacao = $dataAtualizacao;
        return $this;
    }

    public function setFavoritos($favoritos)
    {
        $this->favoritos = $favoritos;
        return $this;
    }

    // Métodos estáticos para conversão

    /**
     * Converte array para objeto Usuario
     */
    public static function fromArray($data)
    {
        return new Usuario(
            $data['uid'] ?? $data['id'] ?? null,
            $data['nome'] ?? '',
            $data['email'] ?? '',
            $data['tipo'] ?? 'usuario',
            $data['idade'] ?? null,
            $data['cpf'] ?? '',
            isset($data['dataCriacao']) ? new DateTime($data['dataCriacao']) : null,
            isset($data['dataAtualizacao']) ? new DateTime($data['dataAtualizacao']) : null,
            $data['favoritos'] ?? []
        );
    }

    /**
     * Converte objeto Usuario para array
     */
    public function toArray()
    {
        return [
            'uid' => $this->uid,
            'nome' => $this->nome,
            'email' => $this->email,
            'tipo' => $this->tipo,
            'idade' => $this->idade,
            'cpf' => $this->cpf,
            'dataCriacao' => $this->dataCriacao ? $this->dataCriacao->format('Y-m-d H:i:s') : null,
            'dataAtualizacao' => $this->dataAtualizacao ? $this->dataAtualizacao->format('Y-m-d H:i:s') : null,
            'favoritos' => $this->favoritos
        ];
    }

    /**
     * Converte objeto Usuario para array (compatível com Firestore)
     */
    public function toFirestoreArray()
    {
        $data = [
            'nome' => $this->nome,
            'email' => $this->email,
            'tipo' => $this->tipo,
            'idade' => $this->idade,
            'cpf' => $this->cpf,
            'dataCriacao' => $this->dataCriacao ? $this->dataCriacao->format('c') : null,
            'favoritos' => $this->favoritos
        ];

        // Remove valores nulos
        return array_filter($data, function ($value) {
            return $value !== null;
        });
    }

    // Métodos de validação

    public function validar()
    {
        $erros = [];

        if (empty($this->nome)) {
            $erros[] = "Nome é obrigatório";
        }

        if (empty($this->email) || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $erros[] = "E-mail inválido";
        }

        if (!empty($this->idade) && ($this->idade < 0 || $this->idade > 150)) {
            $erros[] = "Idade inválida";
        }

        if (!empty($this->cpf) && !$this->validarCPF($this->cpf)) {
            $erros[] = "CPF inválido";
        }

        return $erros;
    }

    private function validarCPF($cpf)
    {
        // Remove caracteres especiais
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Verifica se tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se não é uma sequência repetida
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Validação do CPF (algoritmo)
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    // Métodos de negócio

    public function isAdmin()
    {
        return $this->tipo === 'admin';
    }

    public function adicionarFavorito($eventoId)
    {
        if (!in_array($eventoId, $this->favoritos)) {
            $this->favoritos[] = $eventoId;
        }
        return $this;
    }

    public function removerFavorito($eventoId)
    {
        $key = array_search($eventoId, $this->favoritos);
        if ($key !== false) {
            unset($this->favoritos[$key]);
            $this->favoritos = array_values($this->favoritos); // Reindexa o array
        }
        return $this;
    }

    public function temFavorito($eventoId)
    {
        return in_array($eventoId, $this->favoritos);
    }

    public function getQuantidadeFavoritos()
    {
        return count($this->favoritos);
    }

    // Método para atualizar data de atualização
    public function atualizar()
    {
        $this->dataAtualizacao = new DateTime();
        return $this;
    }

    // Método mágico para conversão em string
    public function __toString()
    {
        return "Usuario: {$this->nome} ({$this->email}) - {$this->tipo}";
    }

    // Método para debug
    public function debug()
    {
        return [
            'uid' => $this->uid,
            'nome' => $this->nome,
            'email' => $this->email,
            'tipo' => $this->tipo,
            'idade' => $this->idade,
            'cpf' => $this->cpf,
            'dataCriacao' => $this->dataCriacao ? $this->dataCriacao->format('d/m/Y H:i:s') : null,
            'dataAtualizacao' => $this->dataAtualizacao ? $this->dataAtualizacao->format('d/m/Y H:i:s') : null,
            'favoritos_count' => count($this->favoritos),
            'is_admin' => $this->isAdmin()
        ];
    }
}

// Exemplo de uso:
/*
// Criar usuário a partir de array
$dadosUsuario = [
    'uid' => 'abc123',
    'nome' => 'João Silva',
    'email' => 'joao@email.com',
    'tipo' => 'usuario',
    'idade' => 25,
    'cpf' => '123.456.789-00',
    'dataCriacao' => '2024-01-01 10:00:00',
    'favoritos' => ['evento1', 'evento2']
];

$usuario = Usuario::fromArray($dadosUsuario);

// Converter para array Firestore
$firestoreData = $usuario->toFirestoreArray();

// Validar usuário
$erros = $usuario->validar();
if (empty($erros)) {
    echo "Usuário válido!";
} else {
    echo "Erros: " . implode(', ', $erros);
}
*/
