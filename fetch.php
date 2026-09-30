<?php
header('Content-Type: application/json; charset=utf-8');
define('API_KEY', 'kulcs');

function checkAuthorization(){
    $headers = getallheaders();
    if(!isset($headers['Authorization'])){
        http_response_code(401);
        echo json_encode(['packet' => 'error', 'details' => 'Missing Authorization header', 'message' => '']);
        exit;
    }
    $auth_header = $headers['Authorization'];
    $expected = 'Bearer ' . API_KEY;

    if(trim($auth_header) !== $expected){
        http_response_code(403);
        echo json_encode(['packet' => 'error', 'details' => 'Wrong API key', 'message' => '']);
        exit;
    }
}

checkAuthorization();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $input = json_decode(file_get_contents('php://input'), true);
    if($input === null){
        http_response_code(400);
        echo json_encode(['packet' => 'error', 'details' => 'Invalid JSON', 'message' => json_last_error_msg()]);
        exit;
    }

    switch($input['packet']){
        case 'query':
            if(!isset($input['data']['query'],$input['host'],$input['uname'],$input['psswd'],$input['table'])){
                http_response_code(400);
                echo json_encode(['packet' => 'error', 'details' => 'Missing variables', 'message' => ""]);
                exit;
            }
            $mysqli_instance = new mysqli($input['host'],$input['uname'],$input['psswd'],$input['table']);
            if(!$mysqli_instance){
                http_response_code(400);
                echo json_encode(['packet' => 'error', 'details' => 'Cant connect to database', 'message' => $mysqli_instance->error]);
                exit;
            }
            mysqli_set_charset($mysqli_instance, "utf8mb4");
            $sql = $input['data']['query'];
            $result = mysqli_query($mysqli_instance, $sql);

            if ($result === false) {
                echo json_encode(['packet' => 'error', 'details' => 'Query failed', 'message' => mysqli_error($mysqli_instance)]);
                exit;
            }
            
            if (stripos(trim($sql), 'SELECT') === 0) {
                $data = [];
                if ($result->num_rows === 0) {
                    echo json_encode(['packet' => 'response', 'details' => json_encode([])]);
                    exit;
                }
            
                while ($row = mysqli_fetch_assoc($result)) {
                    foreach ($row as $key => $value) {
                        if (!is_string($value) && !is_numeric($value) && !is_null($value)) {
                            $row[$key] = strval($value);
                        }
                    }
                    $data[] = $row;
                }
            
                echo json_encode(['packet' => 'response', 'details' => json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE)]);
            } else {
                // Nem SELECT típusú lekérdezés – csak visszajelzünk, hogy lefutott
                echo json_encode(['packet' => 'response', 'details' => json_encode(['affected_rows' => $mysqli_instance->affected_rows])]);
            }

            break;
        default:
            http_response_code(400);
            echo json_encode(['packet' => 'error', 'details' => 'Invalid packet', 'message' => 'This packet type doesnt exists!']);
            exit;
    }
}

?>
