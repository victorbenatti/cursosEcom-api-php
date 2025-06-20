<?php
header('Content-Type: application/json');
include("conexao.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "erro", "mensagem" => "Método não permitido."]);
    exit;
}

$usuario_id = $_POST['usuario_id'] ?? 0;
$curso_id = $_POST['curso_id'] ?? 0;

if ($usuario_id <= 0 || $curso_id <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "erro", "mensagem" => "IDs de usuário e curso são obrigatórios."]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM carrinhoitens WHERE aluno_id = ? AND curso_id = ?");
$stmt->bind_param("ii", $usuario_id, $curso_id);

if ($stmt->execute()) {
    echo json_encode(["status" => "ok", "mensagem" => "Curso removido do carrinho."]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "erro", "mensagem" => "Erro ao remover do carrinho."]);
}

$stmt->close();
$conn->close();
?>