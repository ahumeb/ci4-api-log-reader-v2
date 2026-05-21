<?php

use Encender\LogsAPI\Controllers\LogEntriesController;

/** @var \CodeIgniter\Router\RouteCollection $routes */
$routes->group('api/logs', ['namespace' => 'AppFactory\LogsAPI\Controllers'], function($routes) {
    $routes->get("disponibles", [ LogEntriesController::class, 'listAvailableLogs' ]);
    $routes->post('entradas', [ LogEntriesController::class, 'listLogEntries' ]);
});