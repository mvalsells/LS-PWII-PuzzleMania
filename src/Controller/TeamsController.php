<?php

namespace Salle\PuzzleMania\Controller;

use http\Client\Request;
use http\Client\Response;
use Slim\Views\Twig;

class TeamsController
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
            'base.twig',
            [
            ]
        );
    }
    public function handleForm(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response,
            'base.twig',
            [
            ]
        );
    }

    public function handleInviteForm(Request $request, Response $response): Response
    {

        return $this->twig->render(
            $response,
            'base.twig',
            [
            ]
        );
    }
    public function showStats(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response,
            'base.twig',
            [
            ]
        );
    }
}