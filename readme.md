# Tambula Simple Redirect Class
Tambula is a simple redirect class written in PHP that allows URL redirects stored in JSON files with multilingual capabilities. Written as a code excercise and for a specific redirect situation.

This class was written for a specific situation where the only functionality needed was redirecting but .htaccess had some limitations. This class is not written to integrate with any other framework or platform. It runs as the only thing on a (sub)domain, e.g. `go.mydomain.com/shortlink`  

## Requirements
Webserver that routes all requests to `index.php`, the `Tambula.php` class file and at least one JSON file with routes readable by the class script.

## JSON file with routes
The JSON file is a simple array with paths and routes. For each path multiple routes can be listed depending on the language. `*`  is used as the default route in case there is no route for the language specified.
```json
{
    "path/as/given/in/browser": "route/to/redirect",

    "welcome":
    {
        "*": "my/global/page",
        "nl": "my/dutch/page",
        "fr": "my/fr/page"
    }
}
````

JSON file may be kept out of the public accessible folders if desired, however it proably will contain public information anyway.

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

For a more advanced example see the provided `index-advanced.php` file included in this repository.

## Public methods
### class constructor
The class constructor sets `$requestUrl` to `$_SERVER['REQUEST_URI']` and sets the language from `$_SERVER['HTTP_ACCEPT_LANGUAGE']` but otherwise peforms no actions.

### setDefaultRoute
* string `$route` default route
Set the default route if no other route if found

### setRequestUrl
* string `$url` url
Parses the provided url and sets `$this->requestUrl`, `$this->requestPath` and `$this->requestQuery`. The class constructor calls this method with `$_SERVER['REQUEST_URI']`

### setLanguage
* string `$language` two letter language code or browsers accepted language string
Sets `$this->language` to the first two characters of the `$language` provided. The class constructor calls this method with `$_SERVER['HTTP_ACCEPTED_LANGUAGE']`

### loadRoutesFromJson
* string `$file` path to accessible JSON file
* bool `$append` append to routes
Loads routes from a specified json file. If `$append` is true the contents will be appended to `$this->routes` otherwise it will be replaced

### findRoute
* bool `$doRegex` find routes using regex matching
* return null|string found route
Finds the route in the route list or returns `null` if not found any

### redirect
* string `$route` Route to redirect to
* int `$code` Redirect code, default 301
Will redirect to `$route` and exit the script.

## Properties
All properties are private but can be accessed via `__get()`
* string `$defaultRoute` the default / fallback route, set by `setDefaultRoute()`
* string `$requestUrl` the given request url, set by setRequestUrl()
* string `$requestPath` path portion of the request url, cannot be set from outside class
* string `$language` language given or stored, set by `setLanguage()`
* string `$requestQuery` query portion of the request url, cannot be set from outside class
* array `$routes` available paths and routes, set by `loadRoutesFromJson()`

## Private functions
`findLanguageKey` Find language key, wildcard or fallback and return single array element (string)
`compileRoute` Replace regex groups from the request url into the route and appends $this->requestQuery
`appendQuery` Appends the request query to the route (if not null)