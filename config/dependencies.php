<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Salle\PuzzleMania\Controller\API\RiddlesAPIController;
use Salle\PuzzleMania\Controller\API\UsersAPIController;
use Salle\PuzzleMania\Controller\GameController;
use Salle\PuzzleMania\Controller\LandingPageController;
use Salle\PuzzleMania\Controller\LogoutController;
use Salle\PuzzleMania\Controller\ProfileController;
use Salle\PuzzleMania\Controller\RiddleController;
use Salle\PuzzleMania\Controller\SignUpController;
use Salle\PuzzleMania\Controller\SignInController;
use Salle\PuzzleMania\Controller\TeamsController;
use Salle\PuzzleMania\Middleware\AuthorizationMiddleware;
use Salle\PuzzleMania\Middleware\TeamAuthorizationMiddleware;
use Salle\PuzzleMania\Repository\MySQLRiddleRepository;
use Salle\PuzzleMania\Repository\MySQLTeamRepository;
use Salle\PuzzleMania\Repository\MySQLUserRepository;
use Salle\PuzzleMania\Repository\PDOConnectionBuilder;
use Slim\Flash\Messages;
use Slim\Views\Twig;

function addDependencies(ContainerInterface $container): void
{
    $container->set(
        'view',
        function () {
            return Twig::create(__DIR__ . '/../templates', ['cache' => false]);
        }
    );

    $container->set('db', function () {
        $connectionBuilder = new PDOConnectionBuilder();
        return $connectionBuilder->build(
            $_ENV['MYSQL_ROOT_USER'],
            $_ENV['MYSQL_ROOT_PASSWORD'],
            $_ENV['MYSQL_HOST'],
            $_ENV['MYSQL_PORT'],
            $_ENV['MYSQL_DATABASE']
        );
    });

    $container->set(
        'flash',
        function () {
            return new Messages();
        }
    );

    /*
     *  Declare the AuthorizationMiddleware dependency, to show the flash message if the user access specific
     *  pages without being logged in
    */
    $container->set(
        'authorizationMiddleware',
        function (ContainerInterface $c) {
            return new AuthorizationMiddleware($c->get('flash'));
        }
    );

    $container->set(
        'teamAuthorizationMiddleware',
        function (ContainerInterface $c) {
            return new TeamAuthorizationMiddleware($c->get('flash'));
        }
    );

    $container->set('user_repository', function (ContainerInterface $container) {
        return new MySQLUserRepository($container->get('db'));
    });

    $container->set('team_repository', function (ContainerInterface $container) {
        return new MySQLTeamRepository($container->get('db'));
    });

    $container->set('riddle_repository', function (ContainerInterface $container) {
        return new MySQLRiddleRepository($container->get('db'));
    });

    // Controller dependencies
    $container->set(
        SignInController::class,
        function (ContainerInterface $c) {
            return new SignInController($c->get('view'), $c->get('user_repository'), $c->get('team_repository'), $c->get("flash"));
        }
    );

    $container->set(
        SignUpController::class,
        function (ContainerInterface $c) {
            return new SignUpController($c->get('view'), $c->get('user_repository'));
        }
    );

    $container->set(
        LogOutController::class,
        function (ContainerInterface $c) {
            return new LogOutController();
        }
    );

    $container->set(
        GameController::class,
        function (ContainerInterface $c) {
            return new GameController($c->get('view'));
        }
    );

    $container->set(
        LandingPageController::class,
        function (ContainerInterface $c) {
            return new LandingPageController($c->get('view'), $c->get('user_repository'));
        }
    );

    $container->set(
        ProfileController::class,
        function (ContainerInterface $c) {
            return new ProfileController($c->get('view'), $c->get('user_repository'));
        }
    );

    $container->set(
        RiddleController::class,
        function (ContainerInterface $c) {
            return new RiddleController($c->get('view'), $c->get('db'));
        }
    );

    $container->set(
        TeamsController::class,
        function (ContainerInterface $c) {
            return new TeamsController($c->get('view'), $c->get('db'));
        }
    );


    // API dependencies
    $container->set(
        RiddlesAPIController::class,
        function (ContainerInterface $c) {
            return new RiddlesAPIController($c->get('view'));
        }
    );

    $container->set(
        UsersAPIController::class,
        function (ContainerInterface $c) {
            return new UsersAPIController($c->get('view'));
        }
    );


}
