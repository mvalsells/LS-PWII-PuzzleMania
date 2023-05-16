<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Controller;

use Salle\PuzzleMania\Repository\TeamRepository;
use Salle\PuzzleMania\Service\ValidatorService;
use Salle\PuzzleMania\Repository\UserRepository;
use Salle\PuzzleMania\Model\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Flash\Messages;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

use DateTime;

final class SignUpController
{
    private ValidatorService $validator;

    public function __construct(
        private Messages $flash,
        private Twig $twig,
        private UserRepository $userRepository,
        private TeamRepository $teamRepository
    )
    {
        $this->validator = new ValidatorService();
    }

    /**
     * Renders the form
     */
    public function show(Request $request, Response $response): Response
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        return $this->twig->render(
            $response,
            'sign-up.twig',
            [
                'formAction' => $routeParser->urlFor('sign-up_get')
            ]
        );
    }

    public function handleForm(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $errors = $this->validateForm($data['email'], $data['password'], $data['repeatPassword']);

        if (count($errors) == 0) {
            // Create user variable
            $user = new User();
            $user->setEmail($data['email']);
            $user->setPassword(md5($data['password']));
            $user->setCreatedAt(new DateTime());
            $user->setUpdatedAt(new DateTime());
            // Upload user to repository
            $this->userRepository->createUser($user);

            // Check if it has accessed this page through /invite endpoint
            if(isset($_SESSION["idTeam"])){
                // In order to join a team we need the user Id (which was assigned after creation)
                $user = $this->userRepository->getUserByEmail($data['email']);

                // Login automatically since it joined through /invite endpoint
                $_SESSION['user_id'] = $user->getId();
                $_SESSION['email'] = $user->getEmail();
                if ($user->hasPicture()) {
                    $_SESSION['profilePicturePath'] = $user->getProfilePicturePath();
                }

                // Check the team the user is trying to join is not already full
                $team = $this->teamRepository->getTeamById($_SESSION["idTeam"]);
                if (!$team->isNullTeam() and $team->getNumMembers() !== 2 and $team->isQRGenerated() !== 0) {
                    // Joining user to the team
                    $this->teamRepository->addUserToTeam($_SESSION["idTeam"], $user);

                    // Set session team_id variable
                    $_SESSION["team_id"] = $_SESSION["idTeam"];

                    // Unset the variable used for the /invite logic
                    unset($_SESSION["idTeam"]);

                    // Redirect to the /sign-in page
                    return $response->withHeader('Location', '/team-stats')->withStatus(302);
                } else {
                    $this->flash->addMessage("notifications", "The team you're trying to join is full, doesn't exist or does not have the /invite endpoint activated.");
                    // Unset the variable used for the /invite logic
                    unset($_SESSION["idTeam"]);
                    return $response->withHeader('Location', '/join')->withStatus(302);
                }
            }
            // Redirect to sign-in page
            return $response->withHeader('Location', '/sign-in')->withStatus(302);
        }
        return $this->twig->render(
            $response,
            'sign-up.twig',
            [
                'formErrors' => $errors,
                'formData' => $data,
                'formAction' => $routeParser->urlFor('sign-up_get')
            ]
        );
    }

    private function validateForm($email, $password, $repeat_password): array
    {
        $errors = [];

        $errors['email'] = $this->validator->validateEmail($email);
        $errors['password'] = $this->validator->validatePassword($password);

        // Check that passwords match
        if ($password != $repeat_password) {
            $errors['password'] = "Passwords do not match.";
        }

        // Unset variables if there are no errors
        if ($errors['email'] == '') {
            unset($errors['email']);
        }
        if ($errors['password'] == '') {
            unset($errors['password']);
        }

        // Check if user with this email already exists
        $savedUser = $this->userRepository->getUserByEmail($email);
        if (!$savedUser->isNullUser()) {
            $errors['email'] = "User already exists!";
        }

        return $errors;
    }
}
