<?php
header('Content-Type: application/json');
include("conexao.php");

// Garante que o método da requisição seja POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "erro", "mensagem" => "Método não permitido."]);
    exit;
}

$usuario_id = $_POST['usuario_id'] ?? 0;

if ($usuario_id <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "erro", "mensagem" => "ID de usuário inválido."]);
    exit;
}

// Prepara e executa a chamada para a Stored Procedure
$stmt = $conn->prepare("CALL SP_FinalizarCompraCarrinho(?)");
$stmt->bind_param("i", $usuario_id);

if ($stmt->execute()) {
    echo json_encode(["status" => "ok", "mensagem" => "Compra finalizada com sucesso!"]);
} else {
    http_response_code(500);
    // Retorna a mensagem de erro específica do banco de dados (ex: "Carrinho está vazio")
    echo json_encode(["status" => "erro", "mensagem" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>