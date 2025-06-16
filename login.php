<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("conexao.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "erro", "mensagem" => "Método inválido"]);
    exit;
}

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

if (empty($email) || empty($senha)) {
    echo json_encode(["status" => "erro", "mensagem" => "Preencha todos os campos"]);
    exit;
}

// Usa Prepared Statement para buscar o usuário de forma segura
$stmt = $conn->prepare("SELECT id_usuario, hash_senha FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
    $hash_armazenado = $usuario['hash_senha'];

    // Verifica se a senha fornecida corresponde ao hash armazenado
    if (password_verify($senha, $hash_armazenado)) {
        // Sucesso! A senha está correta.
        echo json_encode(["status" => "ok", "mensagem" => "Login realizado com sucesso"]);
    } else {
        // Senha incorreta
        echo json_encode(["status" => "erro", "mensagem" => "E-mail ou senha incorretos."]);
    }
} else {
    // Usuário não encontrado
    echo json_encode(["status" => "erro", "mensagem" => "E-mail ou senha incorretos."]);
}

$stmt->close();
$conn->close();
?>