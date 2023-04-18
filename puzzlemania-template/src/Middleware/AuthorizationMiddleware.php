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

    public function __construct(private Messages $flash)
    {
    }

    public function __invoke(Request $request, RequestHandler $next): Response
    {
        if (!isset($_SESSION['user_id'])) {
            $route = RouteContext::fromRequest($request)->getRoute();

            $this->flash->addMessage("notifications", $this->buildMessage($route->getName()));
            $response = new Response();
            return $response->withHeader('Location','/sign-in')->withStatus(401);
        }
        return $next->handle($request);
    }

    private function buildMessage(string $page): string {
        return "You must be logged in to access the " . $page . " page.";
    }
}