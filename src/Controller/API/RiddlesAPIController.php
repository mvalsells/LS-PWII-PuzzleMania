<?php
declare(strict_types=1);

namespace Salle\PuzzleMania\Controller\API;


use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
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
}