<?php

namespace Salle\PuzzleMania\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Salle\PuzzleMania\Repository\UserRepository;
use Slim\Views\Twig;

class LandingPageController
{
    private $twig;
    private $userRepository;

    public function __construct(
        Twig $twig,
        UserRepository $userRepository
    )
    {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function show(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->twig->render($response, 'home.twig', [
                "username" => "stranger"
            ]);
        } else {
            $user = $this->userRepository->getUserById(intval($_SESSION['user_id']));
            $username = explode('@', $user->getEmail())[0];
            return $this->twig->render($response, 'home.twig', [
                "username" => $username,
                "email" => $_SESSION['email'],
                "team" => $_SESSION['team_id'] ?? null
            ]);
        }

    }

}