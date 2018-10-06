<?php require dirname(__DIR__).'/vendor/autoload.php';
// DI
$builder = (new \DI\ContainerBuilder())
    ->useAutowiring(false)
    ->useAnnotations(false)
    ->enableCompilation(dirname(__DIR__).'/di.cache')
    ->addDefinitions([
        \App\Controller\Home::class => \DI\create(\App\Controller\Home::class)
    ]);
$app = $builder->build();

// FastRoute
$dispatcher = FastRoute\cachedDispatcher(function(FastRoute\RouteCollector $r) {
    $r->get('/users', \App\Controller\Home::class.'@index');
    $r->get('/user/{id:\d+}', \App\Controller\Home::class.'@index');
    $r->get('/articles/{id:\d+}[/{title}]', \App\Controller\Home::class.'@index');
},[
    'cacheFile' => dirname(__DIR__) . '/route.cache', /* required */
    'cacheDisabled' => false, 
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
        list($class, $method) = explode('@',$routeInfo[1]);
        $vars = $routeInfo[2];
        $app->get($class)->$method($vars);
        break;
}