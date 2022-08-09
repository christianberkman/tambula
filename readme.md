# Tambula Simple Redirect Class
Tambula is a simple redirect class written in PHP that allows URL redirects stored in JSON files with multilingual capabilities.

## Requirements
`.htaccess` file that redirects all requests to `index.php`, the `Tambula.php` class file and at least one JSON file with routes.

## How to use
### Short form
```php
<?php
use Tambula\Tambula;
require 'Tambula.php';

// Load Tambula Class
$tambula = new Tambula();

// Set default / fallback route
$tambula->setDefaultRoute('https://www.mydomain.com/');

// Load routes from json
$tambula->loadRoutesFromJson('routes.json');

// Find route and redirect
$tambula->go();
```

### Long form
```php
<?php
use Tambula\Tambula;
require 'Tambula.php';

// Load Tambula Class
$tambula = new Tambula();

// Load routes from json
$tambula->loadRoutesFromJson('routes.json');

// Find route
$route = $tambula->findRoute();

// Redirect if not null
if($route != null) $tambula->redirect($route);
else{
    echo "404 not found";
}
```