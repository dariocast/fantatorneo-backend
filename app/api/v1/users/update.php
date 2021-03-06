<?php
//headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type");

include_once '../../../config/database.php';
include_once '../../../models/user.php';
include_once '../../../models/error_response.php';
include_once '../../../inc/basic_auth.php';

$id = $_GET['id'];
$data = json_decode(file_get_contents("php://input"));

if (!BasicAuth::authenticated()) {
    //401 unauthorized
    http_response_code(401);
    $response = new ErrorResponse(
        "Accesso non autorizzato.",
        "auth/unauthorized"
    );
    echo json_encode(
        $response
    );

} elseif (!BasicAuth::is_admin()) {
    //401 unauthorized
    http_response_code(401);
    $response = new ErrorResponse(
        "Operazione consentita solo ad utenti amministratori.",
        "auth/not_admin"
    );
    echo json_encode(
        $response
    );
} else {

    // instantiate database and product object
    $database = new Database();
    $db = $database->getConnection();

// initialize object
    $user = new User($db);
    $user->id = $id;

    if (
        !empty($data->firstname) &&
        !empty($data->lastname) &&
        !empty($data->email) &&
        !empty($data->username) &&
        !empty($data->password) &&
        isset($data->admin)
    ) {
        $user->firstname = $data->firstname;
        $user->lastname = $data->lastname;
        $user->email = $data->email;
        $user->username = $data->username;
        $user->password = $data->password;
        $user->admin = (bool)$data->admin;

        // query products
        $stmt = $user->update();
        if ($stmt) {
            // set response code - 200 OK
            http_response_code(200);

            $response = $user;
        } else {

            // set response code - 400 Bad Request
            http_response_code(400);

            $response = new ErrorResponse(
                "Errore durante l'aggiornamento dell'utente.",
                "user_update/unknown_error"
            );
        }
    }
    else {
        //400 bad request
        http_response_code(400);
        $response = new ErrorResponse(
            "Impossibile aggiornare il User, i dati sono incompleti.",
            "user_update/incomplete_data"
        );
    }
    echo json_encode(
        $response
    );

}