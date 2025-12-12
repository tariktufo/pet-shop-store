<?php
// Učitaj autoloader
require __DIR__ . '/vendor/autoload.php';

// Učitaj servise
require __DIR__ . '/services/BaseService.php';
require __DIR__ . '/services/AppointmentService.php';
require __DIR__ . '/services/OrderService.php';
require __DIR__ . '/services/PetService.php';
require __DIR__ . '/services/ProductService.php';
require __DIR__ . '/services/UserService.php';

// Učitaj sve rute iz foldera routes
foreach (glob(__DIR__ . '/routes/*.php') as $routeFile) {
    require $routeFile;
}

// Pokreni Flight
Flight::start();
