<?php

namespace Spyframe\lib;

/**
 * Router class for handling static and dynamic routes in PHP.
 *
 * This class facilitates the definition and management of both static and dynamic routes
 * for your PHP application. It supports routing to anonymous functions as well as to
 * controller methods. Ensure you have a `resources/views` folder with a `404.php` file to handle
 * errors when routes are not found.
 *
 * Usage examples:
 *
 * 1. Routing to a Closure:
 * ```php
 * Route::get('/', function () {
 * 			return json_encode(['message' => 'Hello World']);
 * });
 * ```
 *
 * 2. Routing to a Controller Method:
 * ```php
 * Route::get('/', [HomeController::class, 'index']);
 * ```
 *
 * Call `Route::start()` to initiate the routing process.
 *
 * @author https://github.com/Ferchupessoadev
 * @license MIT
 * @version 1.0
 */
class Route
{
	/*
     * * routes
     * @var array
     */
	private static $routes = [
		'GET' => [],
		'POST' => [],
		'PUT' => [],
		'DELETE' => []
	];

	protected static $request;

	/*
     * * create route GET
     * @param $uri
     * @param $callback
     */

	public static function get($uri, $callback): void
	{
		$uri = trim($uri, '/');
		self::$routes['GET'][$uri] = $callback;
	}

	/*
     * * create route POST
     * @param $uri
     * @param $callback
     */
	public static function post($uri, $callback): void
	{
		$uri = trim($uri, '/');
		self::$routes['POST'][$uri] = $callback;
	}

	/**
	 * * create route PUT
	 * @param $uri
	 * @param $callback
	 * @return void 
	 */
	public static function put($uri, $callback): void
	{
		$uri = trim($uri, '/');
		self::$routes['PUT'][$uri] = $callback;
	}


	/**
	 * * create route DELETE
	 * @param $uri
	 * @param $callback
	 * @return void 
	 */
	public static function delete($uri, $callback): void
	{
		$uri = trim($uri, '/');
		self::$routes['DELETE'][$uri] = $callback;
	}

	/*
     * * Response
     * @param $response
     */
	private static function Response($response): void
	{
		if (is_array($response) || is_object($response)) {
			header('Content-Type: application/json');
			echo json_encode($response);
		} else {
			echo $response;
		}
	}

	private function resolve(string $uri, string $method): bool
	{
		foreach (self::$routes[$method] as $route => $callback) {
			if (strpos($route, '{')) {
				$pattern = '#^' . preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $route) . '$#';
				if (preg_match($pattern, $uri, $matches)) {
					array_shift($matches);
					if (is_callable($callback)) {
						$response = $callback(...$matches);
					} else if (is_array($callback) || is_object($callback)) {
						$controller = new $callback[0](self::$request);
						$response = $controller->{$callback[1]}(...$matches);
					}
					self::Response($response);
					return true;
				}
			}

			if ($uri == $route) {
				if (is_callable($callback)) {
					$response = $callback();
				} else if (is_array($callback) || is_object($callback)) {
					$controller = new $callback[0](self::$request);
					$response = $controller->{$callback[1]}();
				}
				self::Response($response);
				return true;
			}
		}

		return false;
	}

	private function setBody(string $method): void
	{
		if ($method === 'POST') {
			if ($input = file_get_contents('php://input')) {
				$jsonData = json_decode($input, true);
				if (json_last_error() === JSON_ERROR_NONE) {
					$_POST = array_merge($_POST, $jsonData);
				}
			}
			self::$request = $_POST;
		} else {
			self::$request = $_GET;
		}
	}

	/*
     * * start route
     */
	public static function start(): void
	{
		$uri = $_SERVER['REQUEST_URI'];
		$method = $_SERVER['REQUEST_METHOD'];
		$uri = trim($uri, '/');
		$uri = explode('?', $uri)[0];

		// validate method
		if (!isset(self::$routes[$method])) {
			http_response_code(405);
			self::Response(['message' => 'Method Not Allowed']);
			return;
		}

		// set body
		self::setBody($method);

		// resolve
		if (self::resolve($uri, $method))
			return;

		// not found
		self::notFound();
	}

	private static function notFound(): void
	{
		http_response_code(404);
		if (!file_exists('../resources/views/404.php')) {
			self::Response(['message' => '404 - Not Found']);
			return;
		}

		ob_start();
		include '../resources/views/404.php';
		$content = ob_get_clean();
		echo $content;
	}

	/*
     * * Redirect to a different URL
     * @param string $url The URL to redirect to
     * @param int $statusCode The HTTP status code (default is 302)
     */
	public static function redirect($url, $statusCode = 302): void
	{
		header("Location: $url", true, $statusCode);
	}
}
