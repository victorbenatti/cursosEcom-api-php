<?php
header('Content-Type: application/json; charset=utf-8');
include("conexao.php");

// NOTA: Em um app real, você pegaria o ID do usuário de uma sessão ou token de autenticação.
// Para simplificar, vamos recebê-lo como um parâmetro na URL (ex: perfil.php?usuario_id=5)
$usuario_id = $_GET['usuario_id'] ?? 0;

if ($usuario_id <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID do usuário inválido.']);
    exit;
}

// Prepara a query para buscar os dados do usuário de forma segura
// Usamos os nomes das colunas que criamos no banco de dados.
$stmt = $conn->prepare("SELECT id_usuario, nome_completo, email, url_foto_perfil FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    http_response_code(404); // Not Found
    echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário não encontrado.']);
    exit;
}

// Retorna os dados do usuário como JSON
echo json_encode($usuario);

$stmt->close();
$conn->close();
?>