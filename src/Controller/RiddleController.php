<?php

namespace Salle\PuzzleMania\Controller;

use PDO;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Salle\PuzzleMania\Model\Riddle;
use Salle\PuzzleMania\Repository\MySQLRiddleRepository;
use Slim\Views\Twig;

class RiddleController
{

    private $twig;
    private $riddleRepository;

    public function __construct(
        Twig $twig,
        PDO $PDO,
    )
    {
        $this->twig = $twig;
        $this->riddleRepository = new MySQLRiddleRepository($PDO);
    }
    public function show(Request $request, Response $response): Response
    {

        print_r("Proves Riddles");


        $r = new Riddle(1, "p1", "p1", "p1");

        $this->riddleRepository->addRiddle($r);
        $this->riddleRepository->addRiddle($r);

        print_r($this->riddleRepository->getAllRiddles());

        return $this->twig->render(
            $response,
            'base.twig',
            [
            ]
        );
    }
    public function showID(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response,
            'base.twig',
            [
            ]
        );
    }
}