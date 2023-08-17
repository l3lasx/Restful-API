<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function isCheckCustomersInDB($conn, $bodyArr)
{
    $checkSql = "SELECT COUNT(*) FROM customers WHERE customerNumber = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $bodyArr["customerNumber"]);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    return $checkResult->fetch_assoc()['COUNT(*)'];
}


/* insert json file use getBody() but form use getParsedBosy() */
$app->post('/customers/insert', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS["dbconn"];
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);

    $checkResult = isCheckCustomersInDB($conn, $bodyArr);

    if ($checkResult > 0) {
        // Data already exists, return an appropriate response
        $response->getBody()->write(json_encode(["message" => "Data already exists"]));
        return $response->withHeader("Content-Type", "application/json")->withStatus(400); // Bad Request
    }

    $sql = "INSERT INTO customers (customerNumber, customerName, contactLastName, contactFirstName, phone, addressLine1, addressLine2, city, state, postalCode, country, salesRepEmployeeNumber, creditLimit) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssssssid", $bodyArr["customerNumber"], $bodyArr["customerName"], $bodyArr["contactLastName"], $bodyArr["contactFirstName"], $bodyArr["phone"], $bodyArr["addressLine1"], $bodyArr["addressLine2"], $bodyArr["city"], $bodyArr["state"], $bodyArr["postalCode"], $bodyArr["country"], $bodyArr["salesRepEmployeeNumber"], $bodyArr["creditLimit"]);
    $stmt->execute();

    // Return a success response
    $response->getBody()->write(json_encode(["message" => "Data inserted successfully"]));
    return $response->withHeader("Content-Type", "application/json")->withStatus(201); // Created
});

/* query customer condition*/
$app->get('/customers/{query}', function (Request $request, Response $response, $args) {

    $query = $args["query"];
    $conn = $GLOBALS["dbconn"];
    $sql = "SELECT * FROM customers WHERE customerNumber LIKE ? OR customerName LIKE ? OR contactLastName LIKE ? OR contactFirstName LIKE ? OR phone LIKE ? OR addressLine1 LIKE ? OR addressLine2 LIKE ? OR city LIKE ? OR state LIKE ? OR postalCode LIKE ? OR country LIKE ? OR salesRepEmployeeNumber LIKE ? OR creditLimit LIKE ?";
    $stmt = $conn->prepare($sql);

    $searchTerm = "%" . $query . "%";

    $stmt->bind_param("issssssssssid", $query, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $query, $query);
    $stmt->execute();

    $result = $stmt->get_result();

    //creat data array
    $data = array();
    while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }

    $json = json_encode($data);

    $response->getBody()->write($json);
    return $response->withHeader("Content-Type", "application/json");
});

/* delete customer*/
$app->post('/customers/delete', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS["dbconn"];
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);

    $checkResult = isCheckCustomersInDB($conn, $bodyArr);

    if ($checkResult > 0) {
        $sql = "DELETE FROM customers WHERE customerNumber = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $bodyArr["customerNumber"]);
        $stmt->execute();

        $response->getBody()->write(json_encode(["message" => "Delete data successfully"]));
        return $response->withHeader("Content-Type", "application/json");
    }

    // Return a success response
    $response->getBody()->write(json_encode(["message" => "Data not found"]));
    return $response->withHeader("Content-Type", "application/json")->withStatus(201); // Created
});

// update date
$app->post('/customers/update', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS["dbconn"];
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);

    $checkResult = isCheckCustomersInDB($conn, $bodyArr);

    if ($checkResult > 0) {
        $sql = "UPDATE customers SET customerNumber = ? , customerName = ? , contactLastName = ? , contactFirstName = ? , phone = ? , addressLine1 = ? , addressLine2 = ? , city = ? , state = ? , postalCode = ? , country = ? , salesRepEmployeeNumber = ? , creditLimit = ? WHERE customerNumber = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssssssidi", $bodyArr["customerNumber"], $bodyArr["customerName"], $bodyArr["contactLastName"], $bodyArr["contactFirstName"], $bodyArr["phone"], $bodyArr["addressLine1"], $bodyArr["addressLine2"], $bodyArr["city"], $bodyArr["state"], $bodyArr["postalCode"], $bodyArr["country"], $bodyArr["salesRepEmployeeNumber"], $bodyArr["creditLimit"], $bodyArr["customerNumber"]);
        $stmt->execute();

        $response->getBody()->write(json_encode(["message" => "Update Data Successfylly"]));
        return $response->withHeader("Content-Type", "application/json")->withStatus(201);
    }

    $response->getBody()->write(json_encode(["message" => "Not found data"]));
    return $response->withHeader("Content-Type", "application/json")->withStatus(201);
});
