<?php
// Configuração básica do banco de dados
$servername = "localhost";
$database = "atividade";
$username = "root";
$password = "";
$cdn = "mysql:host=$servername;dbname=$database";

try {
    // Conexão com o banco de dados usando PDO
    $conn = new PDO($cdn, $username, $password);
    
    // Definindo o cabeçalho da resposta como JSON
    header('Content-Type: application/json');
    
    // Verifica o método HTTP
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Manipula a requisição
    switch ($method) {
        case 'GET':
            // Endpoint para buscar todas as atividades
            $stmt = $conn->prepare("SELECT * FROM atividades");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                echo json_encode($result);
            } else {
                echo json_encode(array('message' => 'Nenhuma atividade encontrada.'));
            }
            break;
        case 'POST':
            // Endpoint para criar uma nova atividade
            $nome = $_POST['nome'];
            $duvida = $_POST['duvida'];
            $natividade = $_POST['natividade'];
            $arquivo = $_FILES['arquivo'];

            // Gera um nome único para o arquivo
            $nome_arquivo = $arquivo['name'];
            $extensao_arquivo = pathinfo($nome_arquivo, PATHINFO_EXTENSION);
            $nome_arquivo_uniq = md5(uniqid($nome_arquivo . microtime(), true)) . "." . $extensao_arquivo;

            // Move o arquivo para o diretório de destino
            move_uploaded_file($arquivo['tmp_name'], "uploads/" . $nome_arquivo_uniq);

            // Prepara a query SQL para inserção dos dados na tabela atividades
            $sql = "INSERT INTO atividades (nome, duvida, natividade, arquivo, dataCad, dataAlt) 
                    VALUES (:nome, :duvida, :natividade, :arquivo, NOW(), NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':duvida', $duvida);
            $stmt->bindParam(':natividade', $natividade);
            $stmt->bindParam(':arquivo', $nome_arquivo_uniq);

            // Executa a query preparada
            $stmt->execute();

            // Retorna uma mensagem de sucesso
            echo json_encode(array('message' => 'Atividade criada com sucesso.'));
            break;
        case 'PUT':
            // Endpoint para atualizar uma atividade
            parse_str(file_get_contents('php://input'), $put_vars); // Parse do corpo da requisição PUT
            $id = $put_vars['id'];
            $nome = $put_vars['nome'];
            $duvida = $put_vars['duvida'];
            $natividade = $put_vars['natividade'];
            $arquivo = $_FILES['arquivo'];

            // Verifica se um novo arquivo foi enviado
            if ($arquivo['size'] > 0) {
                $nome_arquivo = $arquivo['name'];
                $extensao_arquivo = pathinfo($nome_arquivo, PATHINFO_EXTENSION);
                $nome_arquivo_uniq = md5(uniqid($nome_arquivo . microtime(), true)) . "." . $extensao_arquivo;
                move_uploaded_file($arquivo['tmp_name'], "uploads/" . $nome_arquivo_uniq);
            } else {
                $stmt_check_file = $conn->prepare("SELECT arquivo FROM atividades WHERE id = :id");
                $stmt_check_file->bindParam(':id', $id);
                $stmt_check_file->execute();
                $current_file = $stmt_check_file->fetchColumn();
                $nome_arquivo_uniq = $current_file;
            }

            // Prepara a query SQL para atualização dos dados na tabela atividades
            $sql = "UPDATE atividades SET nome = :nome, duvida = :duvida, natividade = :natividade, arquivo = :arquivo, dataAlt = NOW() WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':duvida', $duvida);
            $stmt->bindParam(':natividade', $natividade);
            $stmt->bindParam(':arquivo', $nome_arquivo_uniq);

            // Executa a query preparada
            $stmt->execute();

            // Retorna uma mensagem de sucesso
            echo json_encode(array('message' => 'Atividade atualizada com sucesso.'));
            break;
        case 'DELETE':
            // Endpoint para deletar uma atividade
            parse_str(file_get_contents('php://input'), $delete_vars); // Parse do corpo da requisição DELETE
            $id = $delete_vars['id'];

            // Prepara a query SQL para exclusão da atividade
            $stmt = $conn->prepare("DELETE FROM atividades WHERE id = :id");
            $stmt->bindParam(':id', $id);

            // Executa a query preparada
            if ($stmt->execute()) {
                echo json_encode(array('message' => 'Atividade deletada com sucesso.'));
            } else {
                echo json_encode(array('message' => 'Erro ao deletar atividade.'));
            }
            break;
        default:
            // Método não suportado
            http_response_code(405);
            echo json_encode(array('message' => 'Método não suportado.'));
            break;
    }
} catch(PDOException $e) {
    echo json_encode(array('message' => 'Erro de conexão: ' . $e->getMessage()));
}

// Fecha a conexão com o banco de dados
$conn = null;
?>