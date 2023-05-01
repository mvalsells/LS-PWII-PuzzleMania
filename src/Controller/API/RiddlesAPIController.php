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
            if ($input != null && array_key_exists('userId', $input) && array_key_exists('riddle', $input) && array_key_exists('answer', $input)) {
                $id = null;
                if (array_key_exists('id', $input) && is_numeric($input['id'])) {
                    $id = intval($input['id']);
                }
                $addId = $this->riddleRepository->addRiddle(new Riddle($id, $input['userId'], $input['riddle'], $input['answer']));
                $riddle = $this->riddleRepository->getOneRiddleById($addId);
                if ($riddle != null) {
                    $response->getBody()->write(json_encode($riddle));
                    return $response
                        ->withHeader("content-type", "application/json")
                        ->withStatus(201);
                }
            }


        $response->getBody()->write('{ "message": "\'riddle\' and/or \'answer\' and/or \'userId\' key missing"}');
        return $response
            ->withHeader("content-type", "application/json")
            ->withStatus(400);
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

            // Check if riddle is in the DB
            $riddle = $this->riddleRepository->getOneRiddleById($id);
            if (!is_null($riddle)) {

                // Check if required parameters exists before adding the riddle
                if (array_key_exists('riddle', $input) || array_key_exists('answer', $input)) {

                    // Update riddle object with the new values
                    if (array_key_exists('id', $input) && is_numeric($input['id'])) {
                        $riddle->setId($input['id']);
                    }

                    if (array_key_exists('userId', $input) && is_numeric($input['userId'])) {
                        $riddle->setUserId($input['userId']);
                    }

                    if (array_key_exists('riddle', $input)) {
                        $riddle->setRiddle($input['riddle']);
                    }

                    if (array_key_exists('answer', $input)) {
                        $riddle->setAnswer($input['answer']);
                    }

                    // Send new values to the repository
                    $this->riddleRepository->updateRiddle($id, $riddle);

                    // Answer with the new riddle
                    $riddle = $this->riddleRepository->getOneRiddleById($riddle->getId());
                    if ($riddle != null) {
                        $response->getBody()->write(json_encode($riddle));
                        return $response
                            ->withHeader("content-type", "application/json")
                            ->withStatus(200);
                    }
                } else {
                    $response->getBody()->write('{ "message": "\'riddle\' and/or \'answer\' key missing"}');
                    return $response
                        ->withHeader("content-type", "application/json")
                        ->withStatus(400);
                }
            }

        }

        // If id is incorrect return error message
        $response->getBody()->write('{"message": "Riddle with id '.$args['id'].' does not exist"}');
        return $response
            ->withHeader("content-type", "application/json")
            ->withStatus(404);
    }


    public function deleteARiddle(Request $request, Response $response, array $args): Response{
        // Check if argument 'id' was provided
        if (!array_key_exists('id', $args)) {
            $args['id'] = '<not provided>';
        }

        // Check if the 'id' is a valid number, if not doesn't make sense requesting to db
        if (is_numeric($args['id']) && ctype_digit($args['id'])) {
            $id = intval($args['id']);

            // Check if riddle is in the DB
            $riddle = $this->riddleRepository->getOneRiddleById($id);
            if (!is_null($riddle)) {
                $this->riddleRepository->deleteRiddle($id);
                $response->getBody()->write('{ "message": "Riddle with id '.$id.' was successfully deleted"}');
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