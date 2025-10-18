<?php
require 'flight/Flight.php'; // uključuje FlightPHP framework

// Ruta za glavnu stranicu backend-a
Flight::route('/', function(){
    echo 'Welcome to Pet Shop Backend!';
});

// Test API ruta
Flight::route('GET /api/ping', function(){
    echo json_encode(['status' => 'ok']);
});

// Pokreće FlightPHP
Flight::start();
?>
