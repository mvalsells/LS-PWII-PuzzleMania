<?php
/**
 * Invite Service: Manages the /invite logic of teams
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 19/05/2023
 * @updated: 19/05/2023
 */
namespace Salle\PuzzleMania\Service;

use Psr\Http\Message\ResponseInterface as Response;
use Salle\PuzzleMania\Model\User;
use Salle\PuzzleMania\Repository\TeamRepository;
use Slim\Flash\Messages;

class InviteService
{
    /**
     * Constructor for a InviteService object
     */
    public function __construct(
        private TeamRepository $teamRepository,
        private Messages $flash
    )
    {
    }

    /**
     * Handle /invite logic and redirect user to the suitable page
     * @param Response $response View information will be written into this response body.
     * @param User $user User that is trying to join the team through /invite endpoint
     * @return Response Returns the response with the corresponding redirection
     */
    public function handleInviteLogic(Response $response, User $user): Response
    {
        // Check the team the user is trying to join is not already full, it exists and has the /invite endpoint habilitated (has created a QR)
        $team = $this->teamRepository->getTeamById($_SESSION["idTeam"]);
        if (!$team->isNullTeam() and $team->getNumMembers() !== 2 and $team->isQRGenerated() !== 0) {
            // Joining user to the team
            $this->teamRepository->addUserToTeam($_SESSION["idTeam"], $user);

            // Set session team_id variable
            $_SESSION["team_id"] = $_SESSION["idTeam"];

            // Unset the variable used for the /invite logic
            unset($_SESSION["idTeam"]);

            // Redirect to the /team-stats page
            $this->flash->addMessage("success", "You joined the team successfully.");
            return $response->withHeader('Location', '/team-stats')->withStatus(302);
        } else {
            $this->flash->addMessage("notifications", "The team you're trying to join is full, doesn't exist or does not have the /invite endpoint activated.");
            // Unset the variable used for the /invite logic
            unset($_SESSION["idTeam"]);
            return $response->withHeader('Location', '/join')->withStatus(302);
        }
    }
}