<?php
header('Content-Type: application/json; charset=utf-8');
include("conexao.php");

// Usar a VIEW que já une as tabelas é muito mais eficiente!
$sql = "SELECT * FROM vw_cursosdisponiveis";

$result = $conn->query($sql);

if (!$result) {
    // Se a query falhar, retorne um erro
    http_response_code(500); // Internal Server Error
    echo json_encode(["status" => "erro", "mensagem" => "Erro ao consultar o banco de dados: " . $conn->error]);
    exit;
}

$cursos = [];

// O fetch_assoc já pega os nomes corretos das colunas da VIEW
while ($row = $result->fetch_assoc()) {
    // Converte os tipos de dados para o que o app espera (ex: preço para float)
    $row['id_curso'] = (int)$row['id_curso'];
    $row['preco_curso'] = (float)$row['preco_curso'];
    $row['total_avaliacoes'] = (int)$row['total_avaliacoes'];
    $row['media_avaliacoes'] = (float)$row['media_avaliacoes'];
    $cursos[] = $row;
}

// Retorna o array de cursos como JSON
echo json_encode($cursos);

$conn->close();
?>