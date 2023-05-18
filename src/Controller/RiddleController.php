<?php

namespace Salle\PuzzleMania\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PDO;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Salle\PuzzleMania\Model\Riddle;
use Salle\PuzzleMania\Repository\MySQLRiddleRepository;
use Salle\PuzzleMania\Repository\RiddleRepository;
use Salle\PuzzleMania\Repository\UserRepository;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use function DI\add;

class RiddleController
{

    private $twig;
    private $riddleRepository;
    private $userRepository;

    public function __construct(
        RiddleRepository $riddleRepository,
        UserRepository $userRepository,
        Twig $twig
    )
    {
        $this->twig = $twig;
        $this->riddleRepository = $riddleRepository;
        $this->userRepository = $userRepository;
    }
    public function show(Request $request, Response $response): Response
    {
        
        $API_URL = "http://nginx/api/riddle";

        $client = new Client();
        $notifications = [];
        $riddles = [];

        try {
            $resposta = $client->request("GET", $API_URL);
            $riddles = json_decode($resposta->getBody()->getContents());
            if (empty($riddles)) {
                $notifications[] = "Riddles are not found.";
            }
        } catch (GuzzleException $e) {
            $notifications[] = "Unexpected error when communicating with API.";
        }

        return $this->twig->render(
            $response,
            'riddle.twig',
            [
                'notifs' => $notifications,
                'riddleCount' => 999, // It can be any value as long as it's not 1.
                'riddles' => $riddles,
                "email" => $_SESSION['email'] ?? null,
                "team" => $_SESSION['team_id'] ?? null
            ]
        );
    }

    public function showID(Request $request, Response $response): Response
    {

        // Guarem el id de la riddle
        $idRiddle = (int) filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_NUMBER_INT);

        $API_URL = "http://nginx/api/riddle/{$idRiddle}";

        $client = new Client();
        $temp = array();
        $notifications = [];

        try {
            $resposta = $client->request("GET", $API_URL);
            $riddles = json_decode($resposta->getBody()->getContents());
            if (isset($riddles->userId)) {
                $userName = $this->userRepository->getUserById($riddles->userId)->getUsername();
            } else {
                $userName = "-";
            }
            $temp[0] = $riddles;
            $riddleCount = 1;

        } catch (GuzzleException $e) {
            $userName = "-";
            $notifications[] = "Riddle not found";
            $riddleCount = 0;
        }

        return $this->twig->render(
            $response,
            'riddle.twig',
            [
                'notifs' => $notifications,
                'idRiddle' => $idRiddle,
                'riddleCount' => $riddleCount, // We indicate that there's just one riddle
                'riddles' => $temp,
                'user' => $userName,
                "email" => $_SESSION['email'] ?? null,
                "team" => $_SESSION['team_id'] ?? null
            ]
        );
    }
}