<?php

namespace Salle\PuzzleMania\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
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

        print_r("Proves Riddles");


        $API_URL = "http://nginx/api/riddle";

        $client = new Client();
        try {
            $resposta = $client->request("GET", $API_URL);
            $riddles = json_decode($resposta->getBody()->getContents());

            $temp = array();
            for ($i = 0; $i < count($riddles); $i++) {
                $temp[] = $riddles[$i]->answer;
            }
        } catch (GuzzleException $e) {
            exit();
        }

        return $this->twig->render(
            $response,
            'riddle.twig',
            [
                'riddles' => $temp
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