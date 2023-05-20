<?php
/**
 * Sign-up Controller: Manages the sign-in logic and view
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 18/04/2023
 * @updated: 19/05/2023
 */
declare(strict_types=1);

namespace Salle\PuzzleMania\Controller;

use Salle\PuzzleMania\Service\InviteService;
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
        private Twig $twig,
        private UserRepository $userRepository,
        private InviteService  $inviteService,
        private Messages       $flash,
    )
    {
        $this->validator = new ValidatorService();
    }

    /**
     * Render the sign-up view
     * @param Request $request Used for route parser of the 'sign-up_get' link
     * @param Response $response View information will be written into this response body.
     * @return Response Returns the response with the twig view to render
     */
    public function show(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user_id'])) {
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            return $this->twig->render(
                $response,
                'sign-up.twig',
                [
                    'formAction' => $routeParser->urlFor('sign-up_get')
                ]
            );
        } else {
            $this->flash->addMessage("notifications", "You are already logged in an account. Log out first to sign into an another account.");
            return $response->withHeader('Location', '/')->withStatus(302);
        }

    }

    /**
     * Function that handles the sign-up view form, where users can create their accounts
     * @param Request $request Used for route parser, to determine the 'sign-up_get' link
     * @param Response $response Variable that will contain the render twig or redirect info.
     * @return Response The twig view to render with the different parameters or the redirect page.
     */
    public function handleForm(Request $request, Response $response): Response
    {
        // Get data from request
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        // Validate form variables
        $errors = $this->validateForm($data['email'], $data['password'], $data['repeatPassword']);

        // If no errors arisen, proceed with creating account
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

                // Login automatically since it joined through /invite endpoint, so set the session variables
                $_SESSION['user_id'] = $user->getId();
                $_SESSION['email'] = $user->getEmail();
                if ($user->hasPicture()) {
                    $_SESSION['profilePicturePath'] = $user->getProfilePicturePath();
                }

                // Call InviteService class to handle the invite logic
                return $this->inviteService->handleInviteLogic($response, $user);
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

    /**
     * Function that checks the inputs with the database info
     * @param string $email Email entered by the user
     * @param string $password Password entered by the user
     * @param string $repeat_password Repeated password entered by the user
     * @return array Errors' array updated
     */
    private function validateForm(string $email, string $password, string $repeat_password): array
    {
        $errors = [];

        // Validate email and password fields
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
        if ($savedUser != null) {
            $errors['email'] = "User already exists!";
        }

        return $errors;
    }
}
