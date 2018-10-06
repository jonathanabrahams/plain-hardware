<?php 
// DEFINE
define( 'APP_DIR', dirname(__DIR__));
define( 'VENDOR_DIR', APP_DIR.'/vendor');
define( 'VIEW_DIR', APP_DIR.'/view');
define( 'HTTP_404', VIEW_DIR.'/error/404.html');
define( 'HTTP_405', VIEW_DIR.'/error/405.html');
define( 'HTTP_500', VIEW_DIR.'/error/500.html');

// VENDORs
require VENDOR_DIR.'/autoload.php';

// DI
try {
  $builder = (new \DI\ContainerBuilder())
  ->useAutowiring(false)
  ->useAnnotations(false)
  // ->enableCompilation(dirname(__DIR__).'/di.cache')
  ->addDefinitions([
    \App\Controller\Home::class => \DI\create(\App\Controller\Home::class)
  ]);
  $app = $builder->build();
}catch(Error $e) {
  http_response_code(500);
  readfile(HTTP_500);
  exit;
}catch(Exception $e){
  http_response_code(500);
  readfile(HTTP_500);
  exit;
}

// FastRoute
try {
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
      readfile(HTTP_404);
      exit;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
      $allowedMethods = $routeInfo[1];
      http_response_code(405);
      readfile(HTTP_405);
      exit;
    case FastRoute\Dispatcher::FOUND:
      list($class, $method) = explode('@',$routeInfo[1]);
      $vars = $routeInfo[2];
      $app->get($class)->$method($vars);
      exit;
  }
}catch(Error $e) {
  http_response_code(500);
  readfile(HTTP_500);
  exit;
}catch(Exception $e){
  http_response_code(500);
  readfile(HTTP_500);
  exit;
}