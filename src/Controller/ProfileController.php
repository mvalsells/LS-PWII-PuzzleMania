<?php

namespace Salle\PuzzleMania\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class ProfileController
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
            'profile.twig',
            [
            ]
        );
    }
    public function handleForm(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response,
            'profile.twig',
            [
            ]
        );
    }
}