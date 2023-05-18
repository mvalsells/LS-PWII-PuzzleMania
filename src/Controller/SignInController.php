<?php
declare(strict_types=1);

namespace Salle\PuzzleMania\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Salle\PuzzleMania\Repository\TeamRepository;
use Salle\PuzzleMania\Service\ValidatorService;
use Salle\PuzzleMania\Repository\UserRepository;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;

class SignInController
{
    private ValidatorService $validator;

    public function __construct(
        private Twig           $twig,
        private UserRepository $userRepository,
        private TeamRepository $teamRepository,
        private Messages       $flash
    )
    {
        $this->validator = new ValidatorService();
    }

    public function show(Request $request, Response $response): Response
    {
        $messages = $this->flash->getMessages();

        $notifications = $messages['notifications'] ?? [];

        return $this->twig->render($response, 'sign-in.twig', ["notifs" => $notifications]);
    }

    public function handleForm(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $errors = [];

        $errors['email'] = $this->validator->validateEmail($data['email']);
        $errors['password'] = $this->validator->validatePassword($data['password']);

        if ($errors['email'] == '') {
            unset($errors['email']);
        }
        if ($errors['password'] == '') {
            unset($errors['password']);
        }
        if (count($errors) == 0) {
            $errors = $this->checkFormWithDatabase($data['email'], $data['password'], $errors);
            if (!isset($errors['email']) and !isset($errors['password'])) {
                // Check if it has accessed this page through /invite endpoint
                if(isset($_SESSION["idTeam"])){
                    // Check the user doesn't belong to a team already
                    if(!isset($_SESSION["team_id"])) {
                        // Check the team the user is trying to join is not already full
                        $team = $this->teamRepository->getTeamById($_SESSION["idTeam"]);
                        if (!$team->isNullTeam() and $team->getNumMembers() !== 2 and $team->isQRGenerated() !== 0) {
                            // In order to join a team we need the user Id
                            $user = $this->userRepository->getUserByEmail($data['email']);

                            // Joining user to the team
                            $this->teamRepository->addUserToTeam($_SESSION["idTeam"], $user);

                            // Set session team_id variable
                            $_SESSION["team_id"] = $_SESSION["idTeam"];

                            // Unset the variable used for the /invite logic
                            unset($_SESSION["idTeam"]);

                            // Redirect to the /team-stats page
                            return $response->withHeader('Location', '/team-stats')->withStatus(302);
                        } else {
                            $this->flash->addMessage("notifications", "The team you're trying to join is full, doesn't exist or does not have the /invite endpoint activated.");
                            // Unset the variable used for the /invite logic
                            unset($_SESSION["idTeam"]);
                            return $response->withHeader('Location', '/join')->withStatus(302);
                        }
                    } else {
                        $this->flash->addMessage("notifications", "You can't join another team, you already belong to one.");
                        // Unset the variable used for the /invite logic
                        unset($_SESSION["idTeam"]);
                        return $response->withHeader('Location', '/team-stats')->withStatus(302);
                    }
                }
                return $response->withHeader('Location', '/')->withStatus(302);
            }
        }
        return $this->twig->render(
            $response,
            'sign-in.twig',
            [
                'formErrors' => $errors,
                'formData' => $data,
                'formAction' => $routeParser->urlFor('sign-in_get')
            ]
        );
    }

    private function checkFormWithDatabase($email, $password, $errors): array
    {
        // Check if the credentials match the user information saved in the database
        $user = $this->userRepository->getUserByEmail($email);
        if ($user->isNullUser()) {
            $errors['email'] = 'User with this email address does not exist.';
        } else if ($user->getPassword() != md5($password)) {
            $errors['password'] = 'Your email and/or password are incorrect.';
        } else {
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['email'] = $user->getEmail();
            if ($user->hasPicture()) {
                $_SESSION['profilePicturePath'] = $user->getProfilePicturePath();
            }
            $team = $this->teamRepository->getTeamByUserId($user->getId());
            if (!$team->isNullTeam()) {
                $_SESSION['team_id'] = $team->getTeamId();
            }
        }

        return $errors;
    }
}
