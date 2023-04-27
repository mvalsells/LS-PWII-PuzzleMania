<?php
declare(strict_types=1);

namespace Salle\PuzzleMania\Controller\API;


use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class RiddlesAPIController
{
    public function __construct(
    ){}

    public function getAllRiddles(Request $request, Response $response): Response {
        $data = [
            [
                "id" => 1,
                "userId" => 1,
                "riddle" => "What has to be broken before you can use it?",
                "answer" => "Egg"
            ]
        ];
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader("content-type", "application/json")
            ->withStatus(200);
    }
}