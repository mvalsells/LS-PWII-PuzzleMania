<?php
declare(strict_types=1);

namespace Salle\PuzzleMania\Controller\API;


use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Salle\PuzzleMania\Model\Riddle;
use Salle\PuzzleMania\Repository\MySQLRiddleRepository;

class RiddlesAPIController
{
    public function __construct(
        private MySQLRiddleRepository $riddleRepository
    ){}

    public function getAllRiddles(Request $request, Response $response): Response {
        $response->getBody()->write(json_encode($this->riddleRepository->getAllRiddles()));
        return $response
            ->withHeader("content-type", "application/json")
            ->withStatus(200);
    }

    public function addARiddle(Request $request, Response $response): Response{

        // Get request body as associative array
        $input = json_decode($request->getBody()->getContents(), true);

        // Check if required parameters exists before adding the riddle
        if (array_key_exists('id', $input[0]) && array_key_exists('userId', $input[0]) && array_key_exists('riddle', $input[0]) && array_key_exists('answer', $input[0])) {
            $this->riddleRepository->addRiddle(new Riddle($input[0]['id'], $input[0]['userId'], $input[0]['riddle'], $input[0]['answer']));
            return $response
                ->withHeader("content-type", "application/json")
                ->withStatus(200);
        } else {
            $response->getBody()->write('{ "message": "\'riddle\' and/or \'answer\' and/or \'userId\' key missing"}');
            return $response
                ->withHeader("content-type", "application/json")
                ->withStatus(400);
        }
    }

    public function getOneRiddle(Request $request, Response $response, array $args): Response {

        // Check if argument 'id' was provided
        if (!array_key_exists('id', $args)) {
            $args['id'] = '<not provided>';
        }

        // Check if the 'id' is a valid number, if not doesn't make sense requesting to db
        if (is_numeric($args['id']) && ctype_digit($args['id'])) {
            $id = intval($args['id']);
            $riddle = $this->riddleRepository->getOneRiddleById($id);
            if ($riddle != null) {
                $response->getBody()->write(json_encode($riddle));
                return $response
                    ->withHeader("content-type", "application/json")
                    ->withStatus(200);
            }
        }

        // If id is incorrect return error message
        $response->getBody()->write('{"message": "Riddle with id '.$args['id'].' does not exist"}');
        return $response
            ->withHeader("content-type", "application/json")
            ->withStatus(404);

    }
}