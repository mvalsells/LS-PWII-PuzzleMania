<?php

namespace Salle\PuzzleMania\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PDO;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Salle\PuzzleMania\Model\Riddle;
use Salle\PuzzleMania\Repository\MySQLRiddleRepository;
use Slim\Routing\RouteContext;
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
        
        $API_URL = "http://nginx/api/riddle";

        $client = new Client();
        try {
            $resposta = $client->request("GET", $API_URL);
            $riddles = json_decode($resposta->getBody()->getContents());

            $temp = array();
            for ($i = 0; $i < count($riddles); $i++) {
                $temp[] = $riddles[$i]->riddle;
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

        // Guarem el id de la riddle
        $idRiddle = (int) filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_NUMBER_INT);

        print_r($idRiddle);

        $API_URL = "http://nginx/api/riddle?id=$idRiddle";

        $client = new Client();

        try {
            $resposta = $client->request("GET", $API_URL);
            $riddles = json_decode($resposta->getBody()->getContents());

            $temp = array();

            $temp[0] = $riddles[0]->riddle;

            print_r($temp);

        } catch (GuzzleException $e) {
            print_r($e->getCode());
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
}