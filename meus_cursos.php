<?php
header('Content-Type: application/json; charset=utf-8');
include("conexao.php");

// Recebe o ID do usuário como parâmetro da URL
$usuario_id = $_GET['usuario_id'] ?? 0;

if ($usuario_id <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode([]); // Retorna um array vazio em caso de erro
    exit;
}

// Prepara a query para buscar os dados da nossa view, filtrando pelo aluno
$stmt = $conn->prepare("SELECT * FROM vw_meuscursosativos WHERE aluno_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$cursos = [];
while ($row = $result->fetch_assoc()) {
    // Faz o cast dos tipos de dados para garantir consistência no JSON
    $row['id_curso'] = (int)$row['id_curso'];
    // A view VW_MeusCursosAtivos não tem preco_curso, então podemos adicionar um valor padrão ou buscar da tabela Cursos se necessário.
    // Para simplificar, vamos adicionar um valor padrão. Em um caso real, o ideal seria adicionar o preço na view.
    $row['preco_curso'] = (float)($row['preco_curso'] ?? 0.0); 
    $row['percentual_concluido'] = (int)$row['percentual_concluido'];
    $cursos[] = $row;
}

echo json_encode($cursos);

$stmt->close();
$conn->close();
?>