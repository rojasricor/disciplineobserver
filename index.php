<?php

require_once __DIR__ . '/vendor/autoload.php';

use Aura\Router\RouterContainer;
use Laminas\Diactoros\ServerRequestFactory;

// Crear una instancia del contenedor de enrutamiento
$routerContainer = new RouterContainer();

$map = $routerContainer->getMap();

// Rutas
$map->get('login.page', '/', [
  'App\Controllers\AuthController',
  'show'
]);

$map->post('login.auth', '/', [
  'App\Controllers\AuthController',
  'authenticate'
]);

// Obtener la ruta coincidente de las variables globales
$route = $routerContainer->getMatcher()->match(
  ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
  )
);

// Ejecutar la acción de la ruta coincidente
if ($route) {
  // Obtener el manejador de la ruta
  $handler = $route->handler;
  
  // Separar el nombre de la clase y el método
  list($controller, $method) = $handler;

  // Crear una instancia de la clase controladora
  $controllerInstance = new $controller();

  // Ejecutar el método de la clase controladora
  $controllerInstance->$method();
} else {
  // Ruta no encontrada
  echo 'Ruta no encontrada';
}
