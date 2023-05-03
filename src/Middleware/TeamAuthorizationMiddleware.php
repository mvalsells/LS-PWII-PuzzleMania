<?php

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
        if (isset($_SESSION['team_id']) and !$this->hasATeamRoute($route->getName())) {
            // Set flash message and add it to response
            $this->flash->addMessage("notifications", "You have already joined a team.");
            $response = new Response();
            return $response->withHeader('Location','/team-stats')->withStatus(301);
        } elseif (!isset($_SESSION['team_id']) and $this->hasATeamRoute($route->getName())) {
            // Set flash message and add it to response
            $this->flash->addMessage("notifications", "You haven't joined a team yet.");
            $response = new Response();
            return $response->withHeader('Location','/join')->withStatus(301);
        }
        return $next->handle($request);
    }

    private function hasATeamRoute (string $page): bool {
        if (strcmp($page, 'join_get') == 0 or strcmp($page, 'join_post') == 0 or strcmp($page, 'invite_get') == 0) {
            return false;
        }
        return true;
    }

}