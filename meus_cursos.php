<?php
header('Content-Type: application/json; charset=utf-8');
include("conexao.php");

$usuario_id = $_GET['usuario_id'] ?? 0;

if ($usuario_id <= 0) {
    http_response_code(400);
    echo json_encode([]);
    exit;
}

// Usar a VIEW vw_meuscursosativos é a forma mais limpa e eficiente!
// Ela já contém o percentual de progresso calculado.
$stmt = $conn->prepare("SELECT * FROM vw_meuscursosativos WHERE aluno_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$cursos = [];
while ($row = $result->fetch_assoc()) {
    // Converte os tipos para o que o app espera e renomeia se necessário
    $row['id_curso'] = (int)$row['id_curso'];
    $row['percentualConcluido'] = (int)$row['percentual_concluido'];
    // Ajusta os nomes das colunas para bater com a data class `Curso`
    $row['urlImagem'] = $row['url_imagem_capa_curso'];

    // Opcional: Adiciona um campo de preço fixo ou busca na tabela de cursos se precisar
    $row['preco'] = 0.0; 

    $cursos[] = $row;
}

echo json_encode($cursos);

$stmt->close();
$conn->close();
?>