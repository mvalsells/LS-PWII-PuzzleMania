<?php

declare(strict_types=1);

use DI\Container;
use Salle\PuzzleMania\Controller\GameController;
use Salle\PuzzleMania\Controller\LandingPageController;
use Salle\PuzzleMania\Controller\ProfileController;
use Salle\PuzzleMania\Controller\RiddleController;
use Salle\PuzzleMania\Controller\TeamsController;
use Salle\PuzzleMania\Middleware\AuthorizationMiddleware;
use SallezzleMania\Controller\API\RiddlesAPIController;
use Salle\PuzzleMania\Controller\API\UsersAPIController;
use Salle\PuzzleMania\Controller\SignUpController;
use Salle\PuzzleMania\Controller\SignInController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

function addRoutes(App $app, Container $container): void
{
    $app->get('/', LandingPageController::class . ':show')->setName('loading');

    $app->get('/join', TeamsController::class . ':show')->setName('join_get');
    $app->post('/join', TeamsController::class . ':handleForm')->setName('join_post');

    //TODO: Mirar lo del ID.
    $app->get('/invite/join/{id}', TeamsController::class . ':handleInviteForm')->setName('invite_get');

    $app->get('/team-stats', TeamsController::class . ':showStats')->setName('stats_get');

    $app->get('/profile', ProfileController::class . ':show')->setName('profile_get');
    $app->post('/profile', ProfileController::class . ':handleForm')->setName('profile_post');


    $app->group('/game', function (RouteCollectorProxy $group) {

        $group->get(
            '',
            GameController::class . ":show"
        )->setName('game_get');

        $group->post(
            '',
            GameController::class . ":handleForm"
        )->setName('game_post');

        $group->get(
            '/{gameID}/riddle/{riddleID}',
            GameController::class . ":showRiddle"
        )->setName('riddle_get');

        $group->post(
            '/{gameID}/riddle/{riddleID}',
            GameController::class . ":handleFormRiddle"
        )->setName('riddle_post');

    })->add(AuthorizationMiddleware::class);

    $app->group('/riddle', function (RouteCollectorProxy $group) {

        $group->get(
            '',
            RiddleController::class . ":show"
        )->setName('game_get');

        $group->get(
            '/{id}',
            RiddleController::class . ":showID"
        )->setName('game_post');

    });

}
