<?php
/**
 * Landing page Controller: Manages all the landing page logic
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 20/04/2023
 * @updated: 19/05/2023
 */
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

    /**
     * Function that renders the landing page with the correct information
     * @param Request $request Unused variable
     * @param Response $response Variable that will contain the render twig
     * @return Response The twig view to render with the different parameters.
     */
    public function show(Request $request, Response $response): Response
    {
        // Check if the user from the session is logged in
        if (!isset($_SESSION['user_id'])) {
            return $this->twig->render($response, 'home.twig', [
                "username" => "stranger"
            ]);
        } else {
            $user = $this->userRepository->getUserById(intval($_SESSION['user_id']));
            $username = $user->getUsername();
            return $this->twig->render($response, 'home.twig', [
                "username" => $username,
                "email" => $_SESSION['email'],
                "team" => $_SESSION['team_id'] ?? null
            ]);
        }

    }

}