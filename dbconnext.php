<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$servername = "localhost";
$username = "demo";
$pwd = "abc123";
$dbname = "demo";
// $servername = "localhost";
// $username = "demo";
// $pwd = "abc123";
// $dbname = "demo";

$dbconn = new mysqli($servername, $username, $pwd, $dbname);

if($dbconn->connect_error){
    die("Conection Failed: " . $conn->connect_error);
}