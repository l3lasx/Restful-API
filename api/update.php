<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/employee/update', function (Request $request, Response $response, $args) {

    $conn = $GLOBALS["dbconn"];

    $body = $request->getParsedBody();
    $email = $body["email"];
    $pwd = $body["old_password"];
    $new_pwd = $body["new_password"];
    $confirm_pwd = $body["confirm_password"];

    function getPasswordIndB($conn, $email)
    {
        $stmt = $conn->prepare("SELECT * FROM employees WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            return $row;
        } else {
            return "";
        }
    }

    //asociative array
    $pwdInDb = getPasswordIndB($conn, $email);

    if (password_verify($pwd, $pwdInDb["password"])) {
        if (password_verify($new_pwd, $pwdInDb["password"]) && password_verify($confirm_pwd, $pwdInDb["password"])) {
            $response->getBody()->write(json_encode(["message" => "Duplicate old password"]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(201);
        } else {
            if ($new_pwd != $confirm_pwd) {
                $response->getBody()->write(json_encode(["message" => "The password does not match!"]));
                return $response->withHeader("Content-Type", "application/json")->withStatus(201);
            } else {
                //hash password
                $hash_pwd = password_hash($new_pwd, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE employees SET password = ? WHERE employeeNumber = ?");
                $stmt->bind_param("si", $hash_pwd, $pwdInDb["employeeNumber"]);
                $stmt->execute();

                $response->getBody()->write(json_encode(["message" => "Update password Successfylly"]));
                return $response->withHeader("Content-Type", "application/json")->withStatus(201);
            }
        }
    }

    $response->getBody()->write(json_encode(["message" => "Old password is incorrect"]));
    return $response->withHeader("Content-Type", "application/json")->withStatus(201);
});
