<?php
//We use this instead of the checking if the user is logged in every single controller.
//To avoid using that function in every call of our application
////We create a middleware that will do a pre-check for us.
namespace Salle\PuzzleMania\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Flash\Messages;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

final class AuthorizationMiddleware
{
    // Definition of an array of messages to show depending on target route
    private const FLASH_MESSAGES = [
                'profile_get' => 'access the Profile page.',
                'profile_post' => 'access the Profile page.',
                'join_get' => 'join a team.',
                'join_post' => 'join a team.',
                'invite_get' => 'join a team.',
                'stats_get' => 'access the Team Stats page.',
                'game_get' => 'access the Game page.',
                'game_post' => 'access the Game page.',
                'game_riddle_get' => 'access the Game page.',
                'game_riddle_post' => 'access the Game page.',
                'riddle_get' => 'access the riddles page.',
                'riddle_id_get' => 'access a riddle page.',
            ];

    public function __construct(private Messages $flash)
    {
    }

    public function __invoke(Request $request, RequestHandler $next): Response
    {
        // Check if the user id logged in by checking if the session 'user_id' variable is set
        if (!isset($_SESSION['user_id'])) {
            $route = RouteContext::fromRequest($request)->getRoute();

            // Check if it is an /invite request, and if so save the team id in a session variable
            if ($route->getName() === 'invite_get') {
                $_SESSION["idTeam"] = (int) filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_NUMBER_INT);
            }

            // Build flash message and add it to response
            $page = self::FLASH_MESSAGES[$route->getName()] ?? 'access unknown page.';
            $this->flash->addMessage("notifications", $this->buildMessage($page));

            // The user needs authorization to access this resource, redirect to /sign-in page
            $response = new Response();
            return $response->withHeader('Location','/sign-in')->withStatus(301);
        }
        // If the user is logged in, passes request to next layer
        return $next->handle($request);
    }

    /**
     * Creates the error message to show
     * @param string $page message associated with the user's target route
     * @return string Returns the message generated
     */
    private function buildMessage(string $page): string {
        return "You must be logged in to " . $page;
    }
}