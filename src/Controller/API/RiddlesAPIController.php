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

    public function updateARiddle(Request $request, Response $response, array $args): Response
    {

        // Check if argument 'id' was provided
        if (!array_key_exists('id', $args)) {
            $args['id'] = '<not provided>';
        }

        // Check if the 'id' is a valid number, if not doesn't make sense requesting to db
        if (is_numeric($args['id']) && ctype_digit($args['id'])) {
            $id = intval($args['id']);

            // Get request body as associative array
            $input = json_decode($request->getBody()->getContents(), true);

            // Check if required parameters exists before adding the riddle
            if (array_key_exists('riddle', $input[0]) || array_key_exists('answer', $input[0])) {
                $riddle = $this->riddleRepository->getOneRiddleById($id);

                // Check if riddle is in the DB
                if (is_null($riddle)) {
                    $response->getBody()->write('{"message": "Riddle with id '.$args['id'].' does not exist"}');
                    return $response
                        ->withHeader("content-type", "application/json")
                        ->withStatus(404);
                }

                // Update riddle object with the new values
                if (array_key_exists('id', $input[0]) &&  is_numeric($input[0]['id'])) {
                    $riddle->setId($input[0]['id']);
                }

                if (array_key_exists('userId', $input[0]) &&  is_numeric($input[0]['userId'])) {
                    $riddle->setUserId($input[0]['userId']);
                }

                if (array_key_exists('riddle', $input[0])) {
                    $riddle->setRiddle($input[0]['riddle']);
                }

                if (array_key_exists('answer', $input[0])) {
                    $riddle->setAnswer($input[0]['answer']);
                }

                // Send new values to the repository
                $this->riddleRepository->updateRiddle($id, $riddle);

                return $response
                    ->withHeader("content-type", "application/json")
                    ->withStatus(200);

            } else {
                $response->getBody()->write('{ "message": "The riddle and/or answer cannot be empty"}');
                return $response
                    ->withHeader("content-type", "application/json")
                    ->withStatus(400);
            }

        } else {
            // If id is incorrect return error message
            $response->getBody()->write('{"message": "Riddle with id '.$args['id'].' does not exist"}');
            return $response
                ->withHeader("content-type", "application/json")
                ->withStatus(404);
        }
    }
}