<?php require dirname(__DIR__).'/vendor/autoload.php';

$dispatcher = FastRoute\cachedDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/users', 'App\Controller\Home@index');
    $r->addRoute('GET', '/user/{id:\d+}', 'App\Controller\Home@index');
    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'App\Controller\Home@index');
},[
    'cacheFile' => __DIR__ . '/route.cache', /* required */
    'cacheDisabled' => true, 
]);

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        readfile(dirname(__DIR__).'/view/error/404.html');
        exit;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        http_response_code(405);
        readfile(dirname(__DIR__).'/view/error/405.html');
        exit;
    case FastRoute\Dispatcher::FOUND:
        list( $class, $method) = explode('@',$routeInfo[1]);
        $vars = $routeInfo[2];
        echo (new $class())->index($vars);
        break;
}