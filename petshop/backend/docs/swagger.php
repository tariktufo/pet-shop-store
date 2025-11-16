<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../../vendor/autoload.php';

// Definiši BASE_URL
if($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1'){
    define('BASE_URL', 'http://localhost/petshop/backend');
} else {
    define('BASE_URL', 'https://your-production-domain.com/backend');
}

// Skeniraj fajlove za Swagger anotacije
$openapi = \OpenApi\Generator::scan([
    __DIR__ . '/doc_setup.php',
    __DIR__ . '/../routes'
]);

header('Content-Type: application/json');
echo $openapi->toJson();
?>