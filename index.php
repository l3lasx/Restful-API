<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/dbconnext.php';
require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->setBasePath('/Restful-API');

require __DIR__ . '/api/customers.php';
require __DIR__ . '/api/authentication.php';
require __DIR__ . '/api/update.php';

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->run();