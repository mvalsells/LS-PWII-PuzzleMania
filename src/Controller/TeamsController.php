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

        $u = new User("prova", "pass", new \DateTime(),new \DateTime());
        $u2 = new User("prova3", "pass", new \DateTime(),new \DateTime());
        $u3 = new User("prova4", "pass", new \DateTime(),new \DateTime());

        print_r($this->userRepository->getUserByEmail("prova"));
        print_r("<br>");

        $this->userRepository->createUser($u2);
        $this->userRepository->createUser($u3);

        $this->userRepository->createSoloTeam($u2);

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