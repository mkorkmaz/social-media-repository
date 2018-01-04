<?php
declare(strict_types=1);

chdir(dirname(__DIR__));

require_once  './vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$config = require './config/global.php';
$container = require './config/container.php';

$app = new Slim\App($container);

$app->get('/', function (Request $request, Response $response, array $args) {
    $status = 200;
    $return = ['status' => 200, 'message'=>'Welcome to Social Media Data Repository'];
    return $response->withJson($return, $status);
});


$app->get('/posts/{platform}/{hashtag}', function (Request $request, Response $response, array $args) {
    $status = 200;
    $platform  = $args['platform'];
    $hashtag = $args['hashtag'];
    $minId = $request->getParam('minId', '504e8fda395fdc23c226a323');
    $serviceName = ucfirst($platform).'Service';
    if (!$this->has($serviceName)) {
        return $response->withJson(['status' => 404, 'message' => 'Service '.$serviceName.' not found'], 404);
    }
    $service = $this->get($serviceName);
    $posts = $service->getPosts($hashtag, $minId);
    $return = [
        'platform' => $platform,
        'hashtag' => $hashtag,
        'total' => $posts['total'],
        'data' => $posts['data']
    ];
    return $response->withJson($return, $status);
});

$app->run();