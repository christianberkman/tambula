<?php
/**
 * Tambula Simple Redireting Class
 * Christian Berkman 2022
 *
 * index file, advanced example
*/

use Tambula\Tambula;

require 'Tambula.php';

// Load Tambula Class
$tambula = new Tambula();

// Find 301 routes
$tambula->loadRoutesFromJson('301-routes.json');
$route = $tambula->findRoute();
if($route != null) $tambula->redirect($route, 301);

// If no route is found, find 302 routes
$tambula->loadRoutesFromJson('302-routes.jsoon', FALSE); // replace all routes
if(!is_null($route)) $tambula->redirect($route, 302);

// No route found
http_response_code(404);
echo "404 Route not found";
exit();
