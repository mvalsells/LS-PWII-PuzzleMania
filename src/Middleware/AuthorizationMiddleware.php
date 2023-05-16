<?php
//We use this instead of the checking if the user is logged in every single controller.
//To avoid using that function in every call of our application
//We create a middleware that will start the session for us.
namespace Salle\PuzzleMania\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Flash\Messages;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

final class AuthorizationMiddleware
{

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
            ];

    public function __construct(private Messages $flash)
    {
    }

    //TODO: Si s'entra després d'haver borrat la BBDD peta
    public function __invoke(Request $request, RequestHandler $next): Response
    {
        if (!isset($_SESSION['user_id'])) {
            $route = RouteContext::fromRequest($request)->getRoute();

            // Get the team ID and store it in the session if it is an /invite request
            if ($route->getName() === 'invite_get') {
                $_SESSION["idTeam"] = (int) filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_NUMBER_INT);
            }

            // Get flash message and add it to response
            $page = self::FLASH_MESSAGES[$route->getName()] ?? 'Unknown page';
            $this->flash->addMessage("notifications", $this->buildMessage($page));
            $response = new Response();
            return $response->withHeader('Location','/sign-in')->withStatus(301);
        }
        return $next->handle($request);
    }

    private function buildMessage(string $page): string {
        return "You must be logged in to " . $page;
    }
}