<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Salle\PuzzleMania\Controller\API\RiddlesAPIController;
use Salle\PuzzleMania\Controller\API\UsersAPIController;
use Salle\PuzzleMania\Controller\SignUpController;
use Salle\PuzzleMania\Controller\SignInController;
use Salle\PuzzleMania\Repository\MySQLRiddleRepository;
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

    $container->set('user_repository', function (ContainerInterface $container) {
        return new MySQLUserRepository($container->get('db'));
    });

    $container->set(
        SignInController::class,
        function (ContainerInterface $c) {
            return new SignInController($c->get('view'), $c->get('user_repository'), $c->get("flash"));
        }
    );

    $container->set(
        SignUpController::class,
        function (ContainerInterface $c) {
            return new SignUpController($c->get('view'), $c->get('user_repository'));
        }
    );
}
