<?php
header('Content-Type: application/json; charset=utf-8');
include("conexao.php");

// 1. Pega o ID do curso da URL (ex: .../detalhes_curso.php?id=5)
$curso_id = $_GET['id'] ?? 0;

if ($curso_id <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID do curso inválido.']);
    exit;
}

// 2. Prepara a query para buscar os detalhes do curso de forma segura
$stmt_curso = $conn->prepare("SELECT * FROM vw_cursosdisponiveis WHERE id_curso = ?");
$stmt_curso->bind_param("i", $curso_id);
$stmt_curso->execute();
$result_curso = $stmt_curso->get_result();
$curso = $result_curso->fetch_assoc();

// Se não encontrou o curso, retorna um erro 404
if (!$curso) {
    http_response_code(404); // Not Found
    echo json_encode(['status' => 'erro', 'mensagem' => 'Curso não encontrado.']);
    exit;
}

// 3. Prepara a query para buscar as aulas associadas a esse curso
$stmt_aulas = $conn->prepare("SELECT id_aula, titulo_aula, ordem_aula FROM aulas WHERE curso_id = ? ORDER BY ordem_aula ASC");
$stmt_aulas->bind_param("i", $curso_id);
$stmt_aulas->execute();
$result_aulas = $stmt_aulas->get_result();

$aulas = [];
while ($row = $result_aulas->fetch_assoc()) {
    $aulas[] = $row;
}

// 4. Junta os detalhes do curso e a lista de aulas em uma única resposta
$curso['aulas'] = $aulas;

// 5. Retorna o JSON completo
echo json_encode($curso);

$stmt_curso->close();
$stmt_aulas->close();
$conn->close();
?>