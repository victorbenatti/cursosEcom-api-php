<?php
header('Content-Type: application/json; charset=utf-8');
include("conexao.php");

$usuario_id = $_GET['usuario_id'] ?? 0;

if ($usuario_id <= 0) {
    http_response_code(400);
    echo json_encode([]);
    exit;
}

// Vamos usar um JOIN para buscar os detalhes dos cursos que estão no carrinho
$stmt = $conn->prepare("
    SELECT c.*, u.nome_completo as nome_instrutor
    FROM carrinhoitens ci
    JOIN cursos c ON ci.curso_id = c.id_curso
    JOIN usuarios u ON c.instrutor_id = u.id_usuario
    WHERE ci.aluno_id = ?
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$cursos = [];
while ($row = $result->fetch_assoc()) {
    $row['id_curso'] = (int)$row['id_curso'];
    $row['preco_curso'] = (float)$row['preco_curso'];
    // Renomeia as colunas para bater com a data class `Curso` no Kotlin
    $row['titulo_curso'] = $row['titulo_curso'];
    $row['subtitulo_curso'] = $row['subtitulo_curso'];
    $row['url_imagem_capa_curso'] = $row['url_imagem_capa_curso'];
    $cursos[] = $row;
}

echo json_encode($cursos);

$stmt->close();
$conn->close();
?>