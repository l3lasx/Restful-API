<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/authentication', function (Request $request, Response $response, $args) {
    //hash password
    //$hash = password_hash($pwd, PASSWORD_DEFAULT);

    $conn = $GLOBALS["dbconn"];

    $body = $request->getParsedBody();
    $email = $body["email"];
    $pwd = $body["password"];

    function getPasswordIndB($conn, $email)
    {
        $stmt = $conn->prepare("SELECT * FROM employees WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            return $row["password"];
        } else {
            return "";
        }
    }

    $pwdInDb = getPasswordIndB($conn, $email);

    if (password_verify($pwd, $pwdInDb)) {
        $response->getBody()->write(json_encode(["message" => "Login Successfylly"]));
        return $response->withHeader("Content-Type", "application/json")->withStatus(201);
    }

    $response->getBody()->write(json_encode(["message" => "Login Failed"]));
    return $response->withHeader("Content-Type", "application/json")->withStatus(201);
});
