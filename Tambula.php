<?php 
/**
 * Tambula Simple Redirecting Class
 * Christian Berkman 2022
 * 
 * Tambula class file
 */

namespace Tambula;
use \Exception;

/**
 * Tambula Class
 */
class Tambula{
	/**
	 * Properties
	 * Private but all accessible through magic getter
	 * $defaultRoute, $requestUrl and $language have a set function
	 * $routes are set via loadRoutesfromJson()
	 * 
	 */
	 private	$defaultRoute,			// Default / fallback route
	 			$requestUrl,			// Request URL
				$language,				// detected language
	  			$requestPath, 			// path via parseUrl();
				$requestQuery, 			// query via parseUrl();
				$routes;				// route array
	
	/**
	 * Constructor
	 * @return void
	 */
	public function __construct(){	
		$this->setRequestUrl($_SERVER['REQUEST_URI']);
		$this->setLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE']);
	}

	/**
	 * Magic Getter
	 * @param string $property Property to get
	 * @return mixed $this->$property
	 */
	public function __get(string $property){
		if(isset($this->$property)) return $this->$property;
		else return null;
	}

	/**
	 * Set the default / fallback route
	 * @param string $route default route
	 */
	public function setDefaultRoute(string $route){
		$this->defaultRoute = $route;
	}

	/**
	 * Parse and set URL
	 * 
	 * @param string $url URL to parse, null for REQUEST_URI
	 * @return void
	 */
	public function setRequestUrl(string $url){
		$this->requestUrl = $url;
		$parsedUrl = parse_url($url);
		
		// Path
		$this->requestPath = $parsedUrl['path'];

		// Query
		if(array_key_exists('query', $parsedUrl)) $this->requestQuery = $parsedUrl['query'];
	}

	/**
	 * Set the language property
	 * @param string $language Language to set, null for auto-detect
	 * @return void
	 */
	public function setLanguage(string $language = null){
		$this->language = strtolower(substr($language, 0, 2));
	}

	/**
	 * Load routes from json file
	 * 
	 * @param string $file json file
	 * @param bool $append Appends route if TRUE, replaces routes if FALSE
	 */
	public function loadRoutesFromJson(string $file, $append = TRUE){
		// Check if file exits and is accaessbile
		if(!is_file($file)) throw new Exception('Cannot open json file');

	 	// Read file
		$fileContents = file_get_contents($file);

		// Decode json
		$decodedJson = json_decode($fileContents, TRUE);
		if($decodedJson == null) throw new Exception('Json file seems invalid');

		// Store to $this->routes
		if($append){	# Append
			$this->routes = array_merge( ($this->routes ?? array()), $decodedJson);
		} else{ 		# Overwrite
			$this->routes = $decodedJson;
		}
	}


	/**
	 * Find route in $this->route
	 * @param bool $doRegex perform regex matching after normal search
	 * @return null|string null on no route, string on single route, array on multilingual route
	 */
	public function findRoute($doRegex = true){
		// Return null if $this->routes is not an array
		if(!is_array($this->routes)) return null;
		
		// Simple CHeck
		if(!$doRegex){
			// Check if array key exists
			if(array_key_exists($this->requestPath, $this->routes)){
				// Single language route
				// Array element is a string
				// return single string
				return $this->appendQuery($this->routes[$this->requestPath]);

				// Multilingual route
				// Array element is an array
				// Find language key
				$languageRoute = $this->findLanguageKey($routes[$this->requestPath]);
				return $this->appendQuery($languageRoute);
			}

			// No match, return null
			return null;
		}
		
		// Regex Check
		foreach($this->routes as $path => $route){	# Loop through array
			$pathEscaped= str_replace('/', '\/', $path);
			$pathPattern = '/^'. $pathEscaped .'$/';
			$match = preg_match($pathPattern, $this->requestPath);
			if($match){
				// Single language route
				// $route is a string
				// return single string
				if(!is_array($route)){
					$this->route = $route;
					return $this->compileRoute($pathPattern, $route);
				}

				// Multilingual routes
				// $route is an array
				// Find language key, return single string
				if(is_array($route)){
					$languageRoute = $this->findLanguageKey($route);
					return $this->compileRoute($pathPattern, $languageRoute);
				}
			} # if()
		} # foreach()

		// No route found
		return null;
	} #

	/**
	 * Find language key, wildcard or fallback and return single array element (string)
	 * @param array $route Routes defined for each language
	 * @return string
	 */
	private function findLanguageKey(array $route){
		// Search for language
		if(array_key_exists($this->language, $route)){
			return $route[$this->language];
		}

		// Search for wildcard ('*')
		if(array_key_exists('*', $route)){
			return $route['*'];
		}

		// Fallback: return first element in array
		var_dump($route);
		return array_values($route)[0];
	}

	/**
	 * Replace regex groups from the request url into the route and appends $this->requestQuery
	 * @param string $path Path defined in routes
	 * @param string $route Route defined in routes
	 * @return string compiled route with appended query
	 */
	private function compileRoute(string $path, string $route){
		// Regex replace
		$replaced =  preg_replace($path, $route, $this->requestPath);

		// Append query and return
		if(empty($this->requestQuery)) return $replaced;
		else return "{$replaced}?{$this->requestQuery}";
	}

	/**
	 * Append $this->query to route
	 * @param string $route Route to append query to
	 * @return string $route with appended query if not null
	 */
	private function appendQuery(string $route){
		if(empty($this->requestQuery)) return $route;
		else return "{$route}?{$this->requestQuery}";
	}

	/**
	 * Redirect
	 * @param string $route Route to redirect to
	 * @param int $code redirect code if not 301
	 * @return void
	 */
	public function redirect(string $route, int $code = 301){
		header("Location: {$route}", TRUE, $code);
		exit();
	}

	/**
	 * Shorthand function for ->findRoute and ->redirect() with default fallback
	 * @return void
	 */
	public function go(){
		$route = $this->findRoute() ?? $this->defaultRoute;
		$this->redirect($route);
	}
}
