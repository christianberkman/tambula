<?php
/**
 * Tambula Simple Redireting Class
 * Christian Berkman 2022
 *
 * index file
*/

use Tambula\Tambula;

require 'Tambula.php';

// Load Tambula Class
$tambula = new Tambula();
$tambula->enableDebug("debug=true");

// Set default / fallback route
$tambula->setDefaultRoute('https://www.mydomain.com/');

// Load routes from json
$tambula->loadRoutesFromJson('routes.json');

// Apply language filter
$tambula->setFilters( $tambula->findLanguageCodes(true) );

// Find route and redirect
$tambula->go();
