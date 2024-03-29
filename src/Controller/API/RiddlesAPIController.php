<?php
/**
 * Riddles API Controller: Manages all the petitions to the Riddle API
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 27/04/2023
 * @updated: 19/05/2023
 */

declare(strict_types=1);

namespace Salle\PuzzleMania\Controller\API;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Salle\PuzzleMania\Model\Riddle;
use Salle\PuzzleMania\Repository\RiddleRepository;
use Salle\PuzzleMania\Repository\UserRepository;
use Salle\PuzzleMania\Service\ValidatorService;

class RiddlesAPIController
{
    private ValidatorService $validator;

    /**
     * Constructor for the RiddleAPIController
     * @param UserRepository $userRepository Interface to access Users database
     * @param RiddleRepository $riddleRepository Interface to access Riddles database
     */
    public function __construct(
        private UserRepository $userRepository,
        private RiddleRepository $riddleRepository
    ){
        $this->validator = new ValidatorService();
    }

    /**
     * Fetch all riddles available at the Riddle Repository and encode them in JSON.
     * @param Request $request Not used since the necessary information from the request is handled by routing.php
     * @param Response $response All the riddles will be written in this response body.
     * @return Response The received by arguments response with updated header and body content.
     */
    public function getAllRiddles(Request $request, Response $response): Response {
        $response->getBody()->write(json_encode($this->riddleRepository->getAllRiddles()));
        return $response
            ->withHeader("content-type", "application/json")
            ->withStatus(200);
    }

    /**
     * Given a Riddle in JSON format this is added to the Riddle repository.
     * @param Request $request The new riddle must be in JSON format inside the body of the request. The userId, riddle
     *                         and answer fields are required while the id filed is optional.
     * @param Response $response Once the Riddle is added to the repository, a petition with the new ID is done to
     *                           the repository which is added to the body of the response.
     * @return Response The received by arguments response with updated header and body content.
     */
    public function addARiddle(Request $request, Response $response): Response {

        // Get request body as associative array
        $input = json_decode($request->getBody()->getContents(), true);

        // Check if required parameters exist before adding the riddle
        if ($input != null && array_key_exists('userId', $input) && array_key_exists('riddle', $input) && array_key_exists('answer', $input)) {
            // Check if the body contains a riddle id
            $id = null;
            if (array_key_exists('id', $input) && is_numeric($input['id'])) {
                // Check the id is not taken by another riddle
                $id = intval($input['id']);
                $riddle = $this->riddleRepository->getOneRiddleById($id);
                if ($riddle != null) {
                    $response->getBody()->write('{ "message": "\'id\' is already in use for another riddle"}');
                    return $response
                        ->withHeader("content-type", "application/json")
                        ->withStatus(400);
                }
            }

            // Check the 'riddle' or 'answer' fields aren't too long
            if($this->validator->checkIfInputTooLong($input['riddle']) || $this->validator->checkIfInputTooLong($input['answer'])){
                $response->getBody()->write('{ "message": "\'riddle\' and/or \'answer\' are too long."}');

                return $response
                    ->withHeader("content-type", "application/json")
                    ->withStatus(400);
            }


                // If a user id was provided, check if it exists
                if (isset($input['userId'])){
                    $user = $this->userRepository->getUserById($input['userId']);
                    if ($user == null) {
                        $response->getBody()->write('{ "message": "\'user_id\' does not correspond to any registered user"}');
                        return $response
                            ->withHeader("content-type", "application/json")
                            ->withStatus(400);
                    }
                }

            // If no errors have arisen, add Riddle to database and return its content in a json response
            $addId = $this->riddleRepository->addRiddle(new Riddle($id, $input['userId'], $input['riddle'], $input['answer']));
            $riddle = $this->riddleRepository->getOneRiddleById($addId);
            if ($riddle != null) {
                $response->getBody()->write(json_encode($riddle));
                return $response
                    ->withHeader("content-type", "application/json")
                    ->withStatus(201);
            }
        }

        // Inform the user that some field is missing
        $response->getBody()->write('{ "message": "\'riddle\' and/or \'answer\' and/or \'userId\' key missing"}');
        return $response
            ->withHeader("content-type", "application/json")
            ->withStatus(400);
    }

    /**
     * Given a riddle id by arguments returns the information about the requested riddles in the repository
     * @param Request $request Not used since the necessary information from the request is handled by routing.php and $args.
     * @param Response $response Requested riddle information will be written into this response body.
     * @param array $args Requested riddle id will be retrieved from 'id' key of the arguments array.
     * @return Response The received by arguments response with updated header and body content.
     */
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

        // If id is incorrect or there is no riddle in database with that id, return error message
        $response->getBody()->write('{"message": "Riddle with id '.$args['id'].' does not exist"}');
        return $response
            ->withHeader("content-type", "application/json")
            ->withStatus(404);
    }

    /**
     * Given a riddle id by argument this will be updated in the repository with the JSON provided in the request body.
     * @param Request $request The body of the request must contain encoded in JSON the updated riddle. At least the
     *                         'answer' or the 'riddle' field must be present.
     * @param Response $response Once the Riddle is updated to the repository, a petition with the new ID is done to
     *                           the repository which is added to the body of the response.
     * @param array $args Requested riddle id will be updated from 'id' key of the arguments array.
     * @return Response The received by arguments response with updated header and body content.
     */
    public function updateARiddle(Request $request, Response $response, array $args): Response
    {
        // Check if field 'id' was provided
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
                // Check if required parameters exist before adding the riddle
                if (array_key_exists('riddle', $input) || array_key_exists('answer', $input)) {
                    // Update riddle object with the new values
                    if (array_key_exists('id', $input) && is_numeric($input['id'])) {
                        // Check the riddle id doesn't exist
                        $riddle_aux = $this->riddleRepository->getOneRiddleById($input['id']);
                        if (!is_null($riddle_aux)) {
                            $response->getBody()->write('{ "message": "The new riddle \'id\' does already correspond to another riddle"}');
                            return $response
                                ->withHeader("content-type", "application/json")
                                ->withStatus(400);
                        } else {
                            $riddle->setId($input['id']);
                        }
                    }

                    // Check the user id exists
                    if (array_key_exists('userId', $input) && is_numeric($input['userId'])) {
                        // Check the user id exists
                        $user = $this->userRepository->getUserById($input['userId']);
                        if ($user == null) {
                            $response->getBody()->write('{ "message": "\'user_id\' does not correspond to any registered user"}');
                            return $response
                                ->withHeader("content-type", "application/json")
                                ->withStatus(400);
                        } else {
                            $riddle->setUserId($input['userId']);
                        }
                    }

                    // Check the riddle is not empty and is not too long
                    if (array_key_exists('riddle', $input)) {
                        // Checking length of input
                        if ($this->validator->checkIfInputTooLong($input['riddle'])){
                            $response->getBody()->write('{ "message": "\'riddle\' is too long."}');
                            return $response
                                ->withHeader("content-type", "application/json")
                                ->withStatus(400);
                        } elseif ($input['riddle'] === "") {
                            $response->getBody()->write('{ "message": "The riddle and/or answer cannot be empty"}');
                            return $response
                                ->withHeader("content-type", "application/json")
                                ->withStatus(400);
                        }

                        $riddle->setRiddle($input['riddle']);
                    }

                    // Check the answer is not empty and is not too long
                    if (array_key_exists('answer', $input)){
                        // Checking length of input
                        if ($this->validator->checkIfInputTooLong($input['answer'])){
                            $response->getBody()->write('{ "message": "\'answer\' is too long."}');
                            return $response
                                ->withHeader("content-type", "application/json")
                                ->withStatus(400);
                        } elseif ($input['answer'] === "") {
                            $response->getBody()->write('{ "message": "The riddle and/or answer cannot be empty"}');
                            return $response
                                ->withHeader("content-type", "application/json")
                                ->withStatus(400);
                        }

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

        // If id is incorrect or no riddle register has that id, return error message
        $response->getBody()->write('{"message": "Riddle with id '.$args['id'].' does not exist"}');
        return $response
            ->withHeader("content-type", "application/json")
            ->withStatus(404);
    }

    /**
     * Deletes from the repository the riddle with the id given by argument
     * @param Request $request Not used since the necessary information from the request is handled by routing.php and $args
     * @param Response $response A message, encoded in JSON, about the deletion operation is added in the body.
     * @param array $args Requested riddle id will be deleted from 'id' key of the arguments array.
     * @return Response The received by arguments response with updated header and body content.
     */
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

        // If id is incorrect or no riddle has that id, return error message
        $response->getBody()->write('{"message": "Riddle with id '.$args['id'].' does not exist"}');
        return $response
            ->withHeader("content-type", "application/json")
            ->withStatus(404);
    }

}