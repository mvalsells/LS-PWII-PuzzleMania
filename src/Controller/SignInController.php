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

                // If the user has to join a team (used invite)
                if(!empty($_SESSION["idTeam"])){

                    // In order to join a team we need a user with an ID associated.
                    // The ID is associated after the creation of the user (in the DB), that's why we look up the same user that we have just created.
                    $user = $this->userRepository->getUserByEmail($data['email']);

                    // Joining user to the team
                    $this->teamRepository->addUserToTeam($_SESSION["idTeam"], $user);

                    $_SESSION["team_id"] = $_SESSION["idTeam"];

                    unset($_SESSION["idTeam"]);

                    return $response->withHeader('Location', '/team-stats')->withStatus(302);
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
