<?php
// Se a constante de segurança não foi definida, impede a execução do script
if (!defined('ACESSO_PERMITIDO')) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Acesso direto negado.';
    exit;
}

define("HOST", "db_vdfvdfv,dl,lfd,.br"); 
define("USER", "db_da111defrferdos_azur"); 
define("PASSWORD", "a8"); 
define("DATABASE", "db"); 

$mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);

if ($mysqli->connect_error) {
    echo json_encode(["erro" => "Falha na conexão com o banco de dados."]);
    exit;
}

$mysqli->set_charset("utf8mb4");
?>