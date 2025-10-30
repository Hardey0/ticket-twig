<?php
require __DIR__ . '/vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

// Start session
session_start();

// === SETUP CONTAINER ===
$container = new Container();

// === TWIG SETUP ===
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader, ['cache' => false]);

// Register Twig in the container
$container->set('view', $twig);

// === CREATE APP ===
AppFactory::setContainer($container);
$app = AppFactory::create();

// === ERROR MIDDLEWARE (SHOW ERRORS IN DEV) ===
$app->addErrorMiddleware(true, true, true);

// === PROTECTED ROUTES MIDDLEWARE ===
$app->add(function ($request, $handler) {
    $uri = $request->getUri()->getPath();
    $protected = ['/dashboard', '/tickets'];

    foreach ($protected as $path) {
        if (strpos($uri, $path) === 0 && empty($_SESSION['user'])) {
            $response = new \Slim\Psr7\Response();
            return $response->withHeader('Location', '/auth')->withStatus(302);
        }
    }
    return $handler->handle($request);
});


// === INCLUDE ROUTES ===
require __DIR__ . '/routes.php';

// === RUN APP ===
$app->run();
