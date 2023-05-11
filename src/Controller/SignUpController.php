<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Controller;

use Salle\PuzzleMania\Repository\TeamRepository;
use Salle\PuzzleMania\Service\ValidatorService;
use Salle\PuzzleMania\Repository\UserRepository;
use Salle\PuzzleMania\Model\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteContext;
use Slim\Views\Twig;

use DateTime;

final class SignUpController
{
    private ValidatorService $validator;

    public function __construct(
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

            // If the user has to join a team (used invite)
            if(!empty($_SESSION["idTeam"])){

                // In order to join a team we need a user with an ID associated.
                // The ID is associated after the creation of the user (in the DB), that's why we look up the same user that we have just created.
                $userT = $this->userRepository->getUserByEmail($user->getEmail());

                // Joining user to the team
                $this->teamRepository->addUserToTeam($_SESSION["idTeam"], $userT);

                $_SESSION["idTeam"] = null;
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
