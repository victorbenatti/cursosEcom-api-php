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

// 1. VERIFICAÇÃO: Checa se o usuário já possui o curso (em um pedido concluído)
$stmt_check = $conn->prepare("
    SELECT COUNT(*) as total
    FROM pedidos p
    JOIN itenspedido ip ON p.id_pedido = ip.pedido_id
    WHERE p.aluno_id = ? AND ip.curso_id = ? AND p.status_pedido = 'Concluído'
");
$stmt_check->bind_param("ii", $usuario_id, $curso_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$row_check = $result_check->fetch_assoc();
$stmt_check->close();

if ($row_check['total'] > 0) {
    // Se o usuário já possui o curso, retorna a mensagem de erro específica
    echo json_encode(["status" => "erro", "mensagem" => "Você já possui este curso!"]);
    exit;
}


// 2. Se não possui, tenta adicionar ao carrinho
// INSERT IGNORE não insere se a combinação já existir no carrinho, evitando duplicatas.
$stmt = $conn->prepare("INSERT IGNORE INTO carrinhoitens (aluno_id, curso_id) VALUES (?, ?)");
$stmt->bind_param("ii", $usuario_id, $curso_id);

if ($stmt->execute()) {
    // Verifica se uma nova linha foi realmente inserida
    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => "ok", "mensagem" => "Curso adicionado ao carrinho."]);
    } else {
        echo json_encode(["status" => "ok", "mensagem" => "Este curso já está no seu carrinho."]);
    }
} else {
    http_response_code(500);
    echo json_encode(["status" => "erro", "mensagem" => "Erro ao adicionar ao carrinho."]);
}

$stmt->close();
$conn->close();
?>