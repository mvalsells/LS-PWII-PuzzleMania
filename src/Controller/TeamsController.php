<?php

namespace Salle\PuzzleMania\Controller;

use PDO;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Salle\PuzzleMania\Repository\TeamRepository;
use Salle\PuzzleMania\Repository\UserRepository;
use Slim\Flash\Messages;
use Slim\Views\Twig;

class TeamsController
{

    private const DEFAULT_TEAM_IMAGE = 'assets/images/teamPicture.png';

    public function __construct(
        private Twig           $twig,
        private UserRepository $userRepository,
        private TeamRepository $teamRepository,
        private Messages       $flash
    )
    {
    }

    public function showJoin(Request $request, Response $response): Response
    {
        $messages = $this->flash->getMessages();

        $notifications = $messages['notifications'] ?? [];

        $teams = $this->teamRepository->getIncompleteTeams();


        return $this->twig->render($response, 'join.twig', ["notifs" => $notifications]);
    }
    public function handleJoinForm(Request $request, Response $response): Response
    {


        return $this->twig->render(
            $response,
            'join.twig',
            [
            ]
        );
    }

    public function handleInviteForm(Request $request, Response $response): Response
    {

        return $this->twig->render(
            $response,
            'join.twig',
            [
            ]
        );
    }

    public function showTeamStats(Request $request, Response $response): Response
    {
        $messages = $this->flash->getMessages();

        $notifications = $messages["notifications"] ?? [];
        echo $notifications[0];
        $team = $this->teamRepository->getTeamById($_SESSION['team_id']);

        $user1 = explode('@', $this->userRepository->getUserById($team->getUserId1())->getEmail())[0];
        if ($team->getNumMembers() == 2) {
            $user2 = explode('@', $this->userRepository->getUserById($team->getUserId2())->getEmail())[0];
        }

        return $this->twig->render(
            $response,
            'team-stats.twig',
            [
                "notifs" => $notifications,
                "email" => $_SESSION['email'],
                "team" => $_SESSION['team_id'],
                "teamPicture" => self::DEFAULT_TEAM_IMAGE,
                "teamName" => $team->getTeamName(),
                "teamMembers" => $team->getNumMembers(),
                "lastScore" => $team->getLastScore(),
                "totalScore" => $team->getTotalScore(),
                "user1" => $user1,
                "user2" => $user2 ?? null
            ]
        );
    }
}