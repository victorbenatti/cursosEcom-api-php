<?php
header('Content-Type: application/json; charset=utf-8');
include("conexao.php");

$curso_id = $_GET['id'] ?? 0;
$usuario_id = $_GET['usuario_id'] ?? 0;

if ($curso_id <= 0) {
    http_response_code(400); 
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID do curso inválido.']);
    exit;
}

$stmt_curso = $conn->prepare("SELECT * FROM vw_cursosdisponiveis WHERE id_curso = ?");
$stmt_curso->bind_param("i", $curso_id);
$stmt_curso->execute();
$result_curso = $stmt_curso->get_result();
$curso = $result_curso->fetch_assoc();

if (!$curso) {
    http_response_code(404);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Curso não encontrado.']);
    exit;
}

// ALTERADO: A query de aulas agora também seleciona a URL do vídeo
$sql_aulas = "
    SELECT
        a.id_aula,
        a.titulo_aula,
        a.ordem_aula,
        a.url_video_aula, -- <-- COLUNA ADICIONADA AQUI
        COALESCE(paa.status_conclusao, 0) AS concluida
    FROM aulas a
    LEFT JOIN progressoalunoaula paa ON a.id_aula = paa.aula_id AND paa.aluno_id = ?
    WHERE a.curso_id = ?
    ORDER BY a.ordem_aula ASC
";

$stmt_aulas = $conn->prepare($sql_aulas);
$stmt_aulas->bind_param("ii", $usuario_id, $curso_id);
$stmt_aulas->execute();
$result_aulas = $stmt_aulas->get_result();

$aulas = [];
while ($row = $result_aulas->fetch_assoc()) {
    $row['concluida'] = (bool)$row['concluida'];
    $aulas[] = $row;
}

$curso['aulas'] = $aulas;
echo json_encode($curso);

$stmt_curso->close();
$stmt_aulas->close();
$conn->close();
?>