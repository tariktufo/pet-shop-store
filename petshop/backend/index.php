<?php

// Enable error reporting za development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers za CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Load Flight framework
require 'flight/Flight.php';

// Load database connection
require 'db.php';

// Load services
require_once 'services/BaseService.php';
require_once 'services/UserService.php';
require_once 'services/PetService.php';
require_once 'services/ProductService.php';
require_once 'services/OrderService.php';
require_once 'services/AppointmentService.php';

// Root endpoint
Flight::route('GET /', function() {
    Flight::json([
        'message' => 'Pet Shop API',
        'version' => '1.0.0',
        'endpoints' => [
            'users' => '/api/users',
            'pets' => '/api/pets',
            'products' => '/api/products',
            'orders' => '/api/orders',
            'appointments' => '/api/appointments',
            'documentation' => '/docs'
        ]
    ]);
});

// API Documentation redirect
Flight::route('GET /docs', function() {
    Flight::redirect('/petshop/backend/docs/');
});

// Load all routes
require 'routes/users.php';
require 'routes/pets.php';
require 'routes/products.php';
require 'routes/orders.php';
require 'routes/appointments.php';

// 404 Handler
Flight::map('notFound', function() {
    Flight::json(['error' => 'Endpoint nije pronađen'], 404);
});

// Error Handler
Flight::map('error', function(Exception $ex) {
    Flight::json(['error' => 'Server error: ' . $ex->getMessage()], 500);
});

// Start the application
Flight::start();
?>