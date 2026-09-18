<?php
// 1. FORÇA A EXIBIÇÃO DE TODOS OS ERROS EM TELA
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('ACESSO_PERMITIDO', true);

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");

// 2. TESTE DE CONEXÃO DIRETO
if (isset($_GET['acao']) && $_GET['acao'] === 'teste') {
    echo json_encode([
        "status" => "sucesso", 
        "mensagem" => "Servidor online e acessível!"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 3. VERIFICA SE O ARQUIVO BANCO.PHP EXISTE
if (!file_exists('banco.php')) {
    echo json_encode([
        "sucesso" => false,
        "erro_diagnostico" => "O arquivo 'banco.php' não foi encontrado na mesma pasta do api.php!"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

include_once 'banco.php';

// 4. VERIFICA SE A VARIÁVEL DE CONEXÃO DO BANCO EXISTE
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    echo json_encode([
        "sucesso" => false,
        "erro_diagnostico" => "A variável \$mysqli não foi encontrada ou não é uma conexão MySQLi válida."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 5. TESTE SE HOUVE ERRO NA CONEXÃO COM O MYSQL
if ($mysqli->connect_error) {
    echo json_encode([
        "sucesso" => false,
        "erro_diagnostico" => "Falha na conexão com o banco MySQL: " . $mysqli->connect_error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Captura o código enviado pelo Android ou Navegador
$inputJSON = json_decode(file_get_contents('php://input'), true);
$rawBusca = $_GET['cod_barra'] ?? $_GET['codigo'] ?? $_GET['busca'] ?? $_POST['cod_barra'] ?? $_POST['codigo'] ?? $inputJSON['cod_barra'] ?? $inputJSON['codigo'] ?? '';

$busca = trim($rawBusca);

if (!empty($busca)) {
    // Consulta SQL buscando o codigo_barras, nome e preco
    $stmt = $mysqli->prepare("SELECT codigo_barras, nome, preco FROM produtos WHERE codigo_barras = ? AND ativo = 1 LIMIT 1");
    
    if (!$stmt) {
        echo json_encode([
            "sucesso" => false,
            "erro_diagnostico" => "Erro na Query SQL: " . $mysqli->error
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt->bind_param("s", $busca);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // RETORNA OS DADOS EXATOS PARA O ANDROID PROCESSAR
        echo json_encode([
            "sucesso"       => true,
            "codigo_barras" => $row['codigo_barras'],
            "nome"          => $row['nome'],
            "preco"         => (float)$row['preco']
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Produto não encontrado no banco de dados do servidor."
        ], JSON_UNESCAPED_UNICODE);
    }
    $stmt->close();
} else {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Código enviado está vazio ou em branco."
    ], JSON_UNESCAPED_UNICODE);
}

$mysqli->close();
?>