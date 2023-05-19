<?php
/**
 * Sign-in Controller: Manages the sign-in logic and view
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 18/04/2023
 * @updated: 19/05/2023
 */
declare(strict_types=1);

namespace Salle\PuzzleMania\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Salle\PuzzleMania\Repository\TeamRepository;
use Salle\PuzzleMania\Service\InviteService;
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
        private Messages       $flash,
        private InviteService  $inviteService
    )
    {
        $this->validator = new ValidatorService();
    }

    /**
     * Render the sign-in view
     * @param Request $request Not used since the necessary information from the request is handled by routing.php.
     * @param Response $response View information will be written into this response body.
     * @return Response Returns the response with the twig view to render
     */
    public function show(Request $request, Response $response): Response
    {
        // Get possible flash messages and rend the view with them
        $messages = $this->flash->getMessages();
        $notifications = $messages['notifications'] ?? [];
        return $this->twig->render($response, 'sign-in.twig', ["notifs" => $notifications]);
    }

    /**
     * Function that handles the sign-in view form, where users can log into their accounts
     * @param Request $request Used for route parser, to determine the 'sign-in_get' link
     * @param Response $response Variable that will contain the render twig or redirect info.
     * @return Response The twig view to render with the different parameters or the redirect page.
     */
    public function handleForm(Request $request, Response $response): Response
    {
        // Get data from request
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $errors = [];

        // Validate inputs from the form
        $errors['email'] = $this->validator->validateEmail($data['email']);
        $errors['password'] = $this->validator->validatePassword($data['password']);

        // Initialise 'errors' variable if no errors have arisen in validation
        if ($errors['email'] == '') {
            unset($errors['email']);
        }
        if ($errors['password'] == '') {
            unset($errors['password']);
        }

        // If no errors in validation, check with database
        if (count($errors) == 0) {
            $errors = $this->checkFormWithDatabase($data['email'], $data['password'], $errors);
            if (!isset($errors['email']) and !isset($errors['password'])) {
                // Check if it has accessed this page through /invite endpoint
                if(isset($_SESSION["idTeam"])){
                    // Check the user doesn't belong to a team already
                    if(!isset($_SESSION["team_id"])) {
                        // In order to join a team we need the user Id
                        $user = $this->userRepository->getUserByEmail($data['email']);
                        // Call InviteService class to handle the invite logic
                        return $this->inviteService->handleInviteLogic($response, $user);
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

    /**
     * Function that checks the inputs with the database info
     * @param string $email Email entered by the user
     * @param string $password Password entered by the user
     * @param array $errors Array containing the previously detected errors
     * @return array Errors' array updated
     */
    private function checkFormWithDatabase(string $email, string $password, array $errors): array
    {
        // Check if the credentials match the user information saved in the database
        $user = $this->userRepository->getUserByEmail($email);
        if ($user == null) {
            $errors['email'] = 'User with this email address does not exist.';
        } else if ($user->getPassword() != md5($password)) {
            $errors['password'] = 'Your email and/or password are incorrect.';
        } else {
            // If they match, set session variables
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
