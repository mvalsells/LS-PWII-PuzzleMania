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
        $response->getBody()->write(json_encode($this->riddleRepository->getRiddles()));
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
}