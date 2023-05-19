<?php
//We use this instead of the checking if the user logged belongs to a team in every single controller.
//To avoid using that function in every call of our application
//We create a middleware that will do a pre-check for us.
namespace Salle\PuzzleMania\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Flash\Messages;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

final class TeamAuthorizationMiddleware
{

    public function __construct(private Messages $flash)
    {
    }

    public function __invoke(Request $request, RequestHandler $next): Response
    {
        $route = RouteContext::fromRequest($request)->getRoute();
        // Check if the user belongs to a team by checking if the session 'team_id' variable is set
        // If the session variable is set and the user tries to access a /join page, redirect to /team-stats
        if (isset($_SESSION['team_id']) and !$this->isATeamRoute($route->getName())) {
            // Set flash message and add it to response
            $this->flash->addMessage("notifications", "You have already joined a team.");
            $response = new Response();
            return $response->withHeader('Location','/team-stats')->withStatus(301);
        // If the session variable is not set and the user tries to access a page that requires belonging to a team,
        // redirect to /team-stats
        } elseif (!isset($_SESSION['team_id']) and $this->isATeamRoute($route->getName())) {
            // Set flash message and add it to response
            $this->flash->addMessage("notifications", "You haven't joined a team yet.");
            $response = new Response();
            return $response->withHeader('Location','/join')->withStatus(301);
        }
        // If none of the previous cases, pass the request to next level
        return $next->handle($request);
    }

    /**
     * Checks if the target route requires belonging to a team or not
     * @param string $page target route of the user request
     * @return bool Variable that indicates if the target route requires belonging to a team (=true) or not (=false)
     */
    private function isATeamRoute (string $page): bool
    {
        if (strcmp($page, 'join_get') == 0 or strcmp($page, 'join_post') == 0 or strcmp($page, 'invite_get') == 0) {
            return false;
        }
        return true;
    }

}