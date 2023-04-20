<?php

namespace Salle\PuzzleMania\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class GameController
{
    private $twig;

    public function __construct(
        Twig $twig,
    )
    {
        $this->twig = $twig;
    }
    public function show(Request $request, Response $response): Response
    {

        return $this->twig->render(
            $response,
            'game.twig',
            [
            ]
        );
    }
    public function handleForm(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response,
            'game.twig',
            [
            ]
        );
    }
    public function showRiddle(Request $request, Response $response): Response
    {

        return $this->twig->render(
            $response,
            'riddle.twig',
            [
            ]
        );
    }
    public function handleFormRiddle(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response,
            'riddle.twig',
            [
            ]
        );
    }
}