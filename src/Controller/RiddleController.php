<?php
/**
 * Riddle controller: handles requests to /riddle and /riddle/{id} routes making requests to the API and rendering the twig templates.
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 20/04/2023
 * @updated: 16/05/2023
 */
namespace Salle\PuzzleMania\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Salle\PuzzleMania\Repository\UserRepository;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class RiddleController
{

    private Twig $twig;
    private UserRepository $userRepository;

    /**
     * RiddleController constructor.
     * @param UserRepository $userRepository Repository used to get the username of the user that created the riddle.
     * @param Twig $twig Twig templating engine used to render the templates.
     */
    public function __construct(
        UserRepository $userRepository,
        Twig $twig
    )
    {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    /**
     * It will ask for the information of all the riddles to the API and will render the twig template with the information.
     * @param Request $request Not used since the necessary information from the request is handled by routing.php.
     * @param Response $response Requested riddle information will be written into this response body.
     * @return Response Returns the response with all the riddles' information.
     * @throws LoaderError When the template cannot be found
     * @throws RuntimeError When an error occurred during rendering
     * @throws SyntaxError When an error occurred during compilation
     */
    public function show(Request $request, Response $response): Response
    {
        // Define API URL to query
        $API_URL = "http://nginx/api/riddle";

        $client = new Client();
        $notifications = [];
        $riddles = [];

        // Tries to get the riddles from the database through the Riddles API
        try {
            $answer = $client->request("GET", $API_URL);
            $riddles = json_decode($answer->getBody()->getContents());
            if (empty($riddles)) {
                $notifications[] = "Riddles are not found.";
            }
        } catch (GuzzleException $e) {
            $notifications[] = "Unexpected error when communicating with API.";
        }

        // Render twig view with all the parameters
        return $this->twig->render(
            $response,
            'riddle.twig',
            [
                'notifs' => $notifications,
                'oneRiddlePage' => false,
                'riddleCount' => count($riddles),
                'riddles' => $riddles,
                "email" => $_SESSION['email'] ?? null,
                "team" => $_SESSION['team_id'] ?? null
            ]
        );
    }

    /**
     * Given a Riddles ID in the argument, it will ask for the information of the riddle to the API and will render the
     * twig template with the information.
     * @param Request $request Not used since the necessary information from the request is handled by routing.php and $args.
     * @param Response $response Requested riddle information will be written into this response body.
     * @param array $args Requested riddle id will be retrieved from 'id' key of the arguments array.
     * @return Response Returns the response with the requested riddle information.
     * @throws LoaderError When the template cannot be found
     * @throws RuntimeError When an error occurred during rendering
     * @throws SyntaxError When an error occurred during compilation
     */
    public function showID(Request $request, Response $response, array $args): Response
    {

        // Save the Riddle ID passed as a parameter of the URL
        $idRiddle = (int) filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);

        // Define API URL to query
        $API_URL = "http://nginx/api/riddle/{$idRiddle}";

        $client = new Client();
        $temp = array();
        $notifications = [];

        // Tries to get the requested riddle from the database through the Riddles API
        try {
            $answer = $client->request("GET", $API_URL);
            $riddles = json_decode($answer->getBody()->getContents());
            // Check the user ID exists and a user is linked to that id in the database
            if (isset($riddles->userId)) {
                $user = $this->userRepository->getUserById($riddles->userId);
                $userName = isset($user) ? $user->getUsername() : "-";
            } else {
                $userName = "-";
            }
            // Set the riddle and the number of riddles to 1
            $temp[0] = $riddles;
            $riddleCount = 1;
        } catch (GuzzleException $e) {
            // Set error variables
            $userName = "-";
            $notifications[] = "Riddle not found";
            $riddleCount = 0;
        }

        // Render twig view with all the parameters
        return $this->twig->render(
            $response,
            'riddle.twig',
            [
                'oneRiddlePage' => true,
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