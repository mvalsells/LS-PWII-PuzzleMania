<?php

declare(strict_types=1);

use DI\Container;
use Salle\PuzzleMania\Controller\GameController;
use Salle\PuzzleMania\Controller\LandingPageController;
use Salle\PuzzleMania\Controller\LogoutController;
use Salle\PuzzleMania\Controller\ProfileController;
use Salle\PuzzleMania\Controller\RiddleController;
use Salle\PuzzleMania\Controller\TeamsController;
use Salle\PuzzleMania\Middleware\AuthorizationMiddleware;
use Salle\PuzzleMania\Middleware\TeamAuthorizationMiddleware;
use Salle\PuzzleMania\Controller\API\RiddlesAPIController;
use Salle\PuzzleMania\Controller\API\UsersAPIController;
use Salle\PuzzleMania\Controller\SignUpController;
use Salle\PuzzleMania\Controller\SignInController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

function addRoutes(App $app, Container $container): void
{
    $app->get('/', LandingPageController::class . ':show')->setName('home');

    $app->get('/sign-up', SignUpController::class . ':show')->setName('sign-up_get');
    $app->post('/sign-up', SignUpController::class . ':handleForm')->setName('sign-up_post');

    $app->get('/log-out', LogOutController::class . ':handle')->setName('log-out_get');

    $app->get('/sign-in', SignInController::class . ':show')->setName('sign-in_get');
    $app->post('/sign-in', SignInController::class . ':handleForm')->setName('sign-in_post');

    $app->get('/join',
        TeamsController::class . ':showJoin')
        ->setName('join_get')->add(TeamAuthorizationMiddleware::class)
        ->add(AuthorizationMiddleware::class);

    $app->post('/join',
        TeamsController::class . ':handleJoinForm')
        ->setName('join_post')->add(TeamAuthorizationMiddleware::class)
        ->add(AuthorizationMiddleware::class);


    $app->get('/invite/join/{id}',
        TeamsController::class . ':handleInviteForm')
        ->setName('invite_get')->add(TeamAuthorizationMiddleware::class)
        ->add(AuthorizationMiddleware::class);

    $app->group('/team-stats', function (RouteCollectorProxy $group) {

        $group->get(
            '',
            TeamsController::class . ':showTeamStats')
            ->setName('stats_get');

        $group->get(
            '/QR_create',
            TeamsController::class . ":createQR"
        )->setName('game_riddle_get');

        $group->get(
            '/QR_download',
            TeamsController::class . ":downloadQR"
        )->setName('game_riddle_post');

    })->add(TeamAuthorizationMiddleware::class)->add(AuthorizationMiddleware::class);

    $app->get('/profile', ProfileController::class . ':show')->setName('profile_get')->add(AuthorizationMiddleware::class);
    $app->post('/profile', ProfileController::class . ':handleForm')->setName('profile_post')->add(AuthorizationMiddleware::class);


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
        )->setName('game_riddle_get');

        $group->post(
            '/{gameID}/riddle/{riddleID}',
            GameController::class . ":handleFormRiddle"
        )->setName('game_riddle_post');

    })->add(TeamAuthorizationMiddleware::class)->add(AuthorizationMiddleware::class);

    $app->group('/riddle', function (RouteCollectorProxy $group) {

        $group->get(
            '',
            RiddleController::class . ":show"
        )->setName('riddle_get');

        $group->get(
            '/{id}',
            RiddleController::class . ":showID"
        )->setName('riddle_post');

    });

    // Riddles API
    $app->group('/api/riddle', function (RouteCollectorProxy $group) {

        // Gets all riddles
        $group->get(
            '',
            RiddlesAPIController::class . ":getAllRiddles"
        );

        // Adds a riddle
        $group->post(
            '',
            RiddlesAPIController::class . ":addARiddle"
        );

        // Get one riddle
        $group->get(
            '/{id}',
            RiddlesAPIController::class . ":getOneRiddle"
        );

        // Update a riddle
        $group->put(
            '/{id}',
            RiddlesAPIController::class . ":updateARiddle"
        );

        // Delete a riddle
        $group->delete(
            '/{id}',
            RiddlesAPIController::class . ":deleteARiddle"
        );
    });

}
