<?php
/**
 * Logout page Controller: Manages the logout logic
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 27/04/2023
 * @updated: 19/05/2023
 */
declare(strict_types=1);

namespace Salle\PuzzleMania\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

// Controller to deal with the 'log-out' logic
final class LogoutController
{
    // Constructor of the class
    public function __construct()
    {
    }

    // Public method that handles the log-out logic
    public function handle(Request $request, Response $response): Response
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        // Unset session variables
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
            unset($_SESSION);
        }

        // Redirect to home page
        return $response
            ->withHeader('Location', $routeParser->urlFor("home"))
            ->withStatus(301);
    }
}
