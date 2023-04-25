<?php

namespace Salle\PuzzleMania\Controller;

use PDO;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Salle\PuzzleMania\Model\User;
use Salle\PuzzleMania\Repository\MySQLUserRepository;
use Salle\PuzzleMania\Repository\UserRepository;
use Slim\Views\Twig;

class TeamsController
{
    private $twig;
    private $userRepository;

    public function __construct(
        Twig $twig,
        PDO $PDO
    )
    {
        $this->twig = $twig;
        $this->userRepository = new MySQLUserRepository($PDO);
    }
    public function show(Request $request, Response $response): Response
    {

        print_r("Probes BBDD" . "<br>");

        $u2 = new User("prova2", "pass", new \DateTime(),new \DateTime());
        $u = new User("prova", "pass", new \DateTime(),new \DateTime());


        //$this->userRepository->createUser($u4);

        print_r($this->userRepository->getScore($u));

        return $this->twig->render(
            $response,
            'join.twig',
            [
            ]
        );
    }
    public function handleForm(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response,
            'join.twig',
            [
            ]
        );
    }

    public function handleInviteForm(Request $request, Response $response): Response
    {

        return $this->twig->render(
            $response,
            'join.twig',
            [
            ]
        );
    }
    public function showStats(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response,
            'stats.twig',
            [
            ]
        );
    }
}