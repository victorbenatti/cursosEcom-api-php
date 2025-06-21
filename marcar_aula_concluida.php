<?php
header('Content-Type: application/json');
include("conexao.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "erro", "mensagem" => "Método não permitido."]);
    exit;
}

$usuario_id = $_POST['usuario_id'] ?? 0;
$aula_id = $_POST['aula_id'] ?? 0;
$status_conclusao = isset($_POST['status_conclusao']) ? (int)$_POST['status_conclusao'] : 0; // 1 para concluído, 0 para não

if ($usuario_id <= 0 || $aula_id <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "erro", "mensagem" => "IDs de usuário e aula são obrigatórios."]);
    exit;
}

// Primeiro, precisamos do curso_id. Vamos buscar na tabela de aulas.
$stmt_curso = $conn->prepare("SELECT curso_id FROM aulas WHERE id_aula = ?");
$stmt_curso->bind_param("i", $aula_id);
$stmt_curso->execute();
$result_curso = $stmt_curso->get_result();
if($result_curso->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["status" => "erro", "mensagem" => "Aula não encontrada."]);
    exit;
}
$curso_id = $result_curso->fetch_assoc()['curso_id'];
$stmt_curso->close();


// Agora, insere ou atualiza o progresso.
// INSERT ... ON DUPLICATE KEY UPDATE é perfeito para isso.
// Ele tenta inserir; se a chave única (aluno_id, aula_id) já existir, ele executa a parte do UPDATE.
$stmt = $conn->prepare("
    INSERT INTO progressoalunoaula (aluno_id, aula_id, curso_id, status_conclusao, data_conclusao)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
    status_conclusao = VALUES(status_conclusao),
    data_conclusao = VALUES(data_conclusao)
");

$data_conclusao = $status_conclusao == 1 ? date('Y-m-d H:i:s') : null;

$stmt->bind_param("iiiis", $usuario_id, $aula_id, $curso_id, $status_conclusao, $data_conclusao);

if ($stmt->execute()) {
    echo json_encode(["status" => "ok", "mensagem" => "Progresso da aula atualizado."]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "erro", "mensagem" => "Erro ao atualizar progresso: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>