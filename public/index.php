<?php
declare(strict_types=1);

use DI\Container;
use Salle\PuzzleMania\ErrorHandler\HttpErrorHandler;
use Salle\PuzzleMania\Middleware\AuthorizationMiddleware;
use Salle\PuzzleMania\Middleware\StartSessionMiddleware;
use Slim\Factory\AppFactory;
use Slim\Views\TwigMiddleware;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv();

$dotenv->load(__DIR__ . '/../.env');

$container = new Container();

require_once __DIR__ . '/../config/dependencies.php';

addDependencies($container);

AppFactory::setContainer($container);

$app = AppFactory::create();

$callableResolver = $app->getCallableResolver();
$responseFactory = $app->getResponseFactory();

$app->add(StartSessionMiddleware::class);

$app->addBodyParsingMiddleware();

$app->add(TwigMiddleware::createFromContainer($app));

$app->addRoutingMiddleware();

$app->addErrorMiddleware(true, false, false);

require_once __DIR__ . '/../config/routing.php';

addRoutes($app, $container);

$app->run();
