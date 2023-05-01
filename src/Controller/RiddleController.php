<?php

namespace Salle\PuzzleMania\Controller;

use PDO;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Salle\PuzzleMania\Model\Riddle;
use Salle\PuzzleMania\Repository\MySQLRiddleRepository;
use Slim\Views\Twig;
use function DI\add;

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

        print_r("Show Riddles");

        $riddles = $this->riddleRepository->getRiddles();
        print_r($riddles);

        $riddle = array();
        for ($i = 0; $i < count($riddles); $i++) {
                $riddle[$i] = $riddles[$i]["riddle"];
        }


        print_r($riddles);

        return $this->twig->render(
            $response,
            'riddle.twig',
            [
                'riddles' => $riddle
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