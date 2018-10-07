<?php 
// DIRECTORY
define( 'APP_DIR', dirname(__DIR__));
define( 'VENDOR_DIR', APP_DIR.'/vendor');
define( 'VIEW_DIR', APP_DIR.'/view');

// VENDORs
require VENDOR_DIR.'/autoload.php';

// ERROR HANDLER
$old_error_handler = set_error_handler('\App\Error::handler');

// DI
try {
  $builder = (new \DI\ContainerBuilder())
  ->useAutowiring(false)
  ->useAnnotations(false)
  ->addDefinitions([
    \App\Controller\Home::class => \DI\create(\App\Controller\Home::class),
    \App\Error::class => \DI\create(\App\Error::class)
  ]);
  $builder->enableCompilation(dirname(__DIR__).'/di.cache');
  $app = $builder->build();
}catch(Throwable $e) {
  \App\Error::render(501, \App\Error\Context::thrown('DI', $e));
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
      \App\Error::render(404);
      exit;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
      $allowedMethods = $routeInfo[1];
      \App\Error::render(405);
      exit;
    case FastRoute\Dispatcher::FOUND:
      list($class, $method) = explode('@',$routeInfo[1]);
      $vars = $routeInfo[2];
      $app->get($class)->$method($vars);
      exit;
  }
}catch(Throwable $e) {
  \App\Error::render(500, \App\Error\Context::thrown('FR', $e));
  exit;
}