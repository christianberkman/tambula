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
				$requestPath, 			// path via parseUrl();
				$requestQuery, 			// query via parseUrl();
				$routes = [],			// route array
				$filters = [],			// array of filters to apply
				
				$languageCodes = [],	// language code(s) from HTTP_ACCEPT_LANG
				$countryCode,			// country Code
				$geoPlugin = [],		// results from geoplugin.net

				$enableDebug = false,			// enable debug instead of redirect
				$debugFlag = 'debug=true',		// debug flag
				$debugStart;
	
	/**
	 * Constructor
	 * @return void
	 */
	public function __construct(){	
		$this->debugStart = microtime(true);
		$this->setRequestUrl($_SERVER['REQUEST_URI']);
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
		if(is_null($decodedJson)) throw new Exception('Json file seems invalid');

		// Store to $this->routes
		if($append){	# Append
			$this->routes = array_merge( ($this->routes ?? array()), $decodedJson);
		} else{ 		# Overwrite
			$this->routes = $decodedJson;
		}
	}

	/**
	 * Set the filter, make an array if a single string is presented
	 * @param string|array $filter Filter or filters to set
	 */
	public function setFilters($filters = null){
		// If array, re-index array and return
		if(is_array($filters)){
			$this->filters = array_values($filters);
			return;
		}

		// If not, make into an array with a single element
		$this->filters = [$filters];
	}

	/**
	 * Find route in $this->route
	 * @param bool $doRegex perform regex matching after normal search
	 * @return null|string null on no route, string on single route, array on multilingual route
	 */
	public function findRoute(){
		// Return null if $this->routes is not an array
		if(!is_array($this->routes)) return null;
		
		// Loop through all routes
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
					$filteredRoute = $this->filterRoute($route);
					return $this->compileRoute($pathPattern, $filteredRoute);
				}
			} # if()
		} # foreach()

		// No route found
		return null;
	} #

	/**
	 * Filter the route, find wildcard or fallback and return single array element (string)
	 * @param array $route Routes defined for each language
	 * @return string
	 */
	private function filterRoute(array $route){
		// Loop through all elements of filters
		foreach($this->filters as $filter){
			// Search for filter
			if(array_key_exists($filter, $route)){
				return $route[$filter];
			}
		} # foreach

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
		// Show debug if enabled and query equals the flag
		if($this->enableDebug){
			if($this->requestQuery == $this->debugFlag) $this->showDebug();
		}
		
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

	/**
	 * Find language codes from browser
	 * @param bool $findFirst Only use first language code
	 * @return array Found language code(s)
	 */
	public function findLanguageCodes(bool $findFirst = false){
		// Find all two letter language codes in HTTP_ACCEPT_LANGUAGE string
		$acceptedLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		$pattern = '/,([a-z]{2})/';
		preg_match_all($pattern, $acceptedLang, $matches);

		// Find only first
		if($findFirst) $this->languageCodes = [$matches[1][0]];
		else $this->languageCodes = $matches[1];
		
		// Return language codes
		return $this->languageCodes;
	}

	/**
	 * Find the client's two letter country code using it's IP address using geoplugin.net
	 * @param string $ip Override IP address
	 * @return string|null
	 */
	public function findCountryCode(string $ip = null){
		// From geoplugin.net/quickstart
		$url = 'http://www.geoplugin.net/php.gp?ip=' . ($ip ?? $_SERVER['REMOTE_ADDR']);
		$geoPlugin = unserialize(file_get_contents($url));
		$this->geoPlugin = $geoPlugin;
		$this->countryCode = $geoPlugin['geoplugin_countryCode'];
		return $this->countryCode;
	}

	/**
	 * Report execution time
	 * @return int Execution time in milliseconds
	 */
	public function execTime(){
		return number_format( ((microtime(true) - $this->debugStart) * 1000), 3, '.', '');
	}

	/**
	 * Enable debug flag
	 * @param string $debugFlag Debug flag to trigger debug view
	 */
	public function enableDebug(string $debugFlag){ 
		$this->enableDebug = true; 
		$this->debugFlag = $debugFlag;
	}

	/**
	 * Require the debug.php file and stop all execution
	 */
	private function showDebug(){
		require_once 'debug.php';
		exit;
	}
}
