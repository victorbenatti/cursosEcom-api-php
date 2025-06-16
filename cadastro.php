<?php
// Define o cabeçalho da resposta como JSON
header('Content-Type: application/json');

// Configurações de erro (ideal para desenvolvimento, desabilitar display_errors em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclui o arquivo de conexão com o banco de dados
include("conexao.php");

// Garante que a requisição seja do tipo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Encerra o script se o método não for POST
    echo json_encode(["status" => "erro", "mensagem" => "Método de requisição inválido."]);
    exit;
}

// Recebe e valida os dados de entrada
$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

// Verifica se todos os campos obrigatórios foram preenchidos
if (empty($nome) || empty($email) || empty($senha)) {
    echo json_encode(["status" => "erro", "mensagem" => "Todos os campos são obrigatórios."]);
    exit;
}

// 1. CORREÇÃO DE SEGURANÇA: Prevenir SQL Injection com Prepared Statements
// Primeiro, verifica se o e-mail já existe de forma segura
$stmt_verifica = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
$stmt_verifica->bind_param("s", $email); // "s" indica que a variável é uma string
$stmt_verifica->execute();
$result = $stmt_verifica->get_result();

if ($result->num_rows > 0) {
    // Se encontrou um usuário, retorna erro
    echo json_encode(["status" => "erro", "mensagem" => "Este e-mail já está cadastrado."]);
    $stmt_verifica->close();
    $conn->close();
    exit;
}
$stmt_verifica->close();


// 2. CORREÇÃO DE SEGURANÇA: Usar password_hash para proteger a senha
$hash_senha = password_hash($senha, PASSWORD_DEFAULT);


// 3. CORREÇÃO DOS NOMES DAS COLUNAS E USO DE PREPARED STATEMENTS PARA INSERÇÃO
// Prepara a query de inserção usando os nomes corretos das colunas: `nome_completo` e `hash_senha`
$stmt_insert = $conn->prepare("INSERT INTO usuarios (nome_completo, email, hash_senha) VALUES (?, ?, ?)");
$stmt_insert->bind_param("sss", $nome, $email, $hash_senha); // "sss" -> três strings

// Executa a query e verifica o resultado
if ($stmt_insert->execute()) {
    echo json_encode(["status" => "ok", "mensagem" => "Cadastro realizado com sucesso!"]);
} else {
    // Em caso de erro, retorna uma mensagem genérica e loga o erro real (ideal para produção)
    error_log("Erro no cadastro: " . $stmt_insert->error); // Escreve o erro no log do servidor
    echo json_encode(["status" => "erro", "mensagem" => "Ocorreu um erro ao realizar o cadastro."]);
}

// Fecha a declaração e a conexão
$stmt_insert->close();
$conn->close();

?>