<?php

namespace Salle\PuzzleMania\Controller;

use PDO;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Salle\PuzzleMania\Repository\TeamRepository;
use Salle\PuzzleMania\Repository\UserRepository;
use Salle\PuzzleMania\Service\BarcodeService;
use Slim\Flash\Messages;
use Slim\Views\Twig;

class TeamsController
{
    private BarcodeService $barcode;

    private const DEFAULT_TEAM_IMAGE = 'assets/images/teamPicture.png';

    public function __construct(
        private Twig           $twig,
        private UserRepository $userRepository,
        private TeamRepository $teamRepository,
        private Messages       $flash
    )
    {
        $this->barcode = new BarcodeService();
    }

    public function showJoin(Request $request, Response $response): Response
    {
        // Get flash messages
        $messages = $this->flash->getMessages();
        $notifications = $messages['notifications'] ?? [];

        // Get incomplete teams from repository
        $teams = $this->teamRepository->getIncompleteTeams();

        // Render view
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
        // Get flash messages
        $messages = $this->flash->getMessages();
        $notifications = $messages["notifications"] ?? [];

        // Render team stats view again
        return $this->showTeamStatsView($notifications, $response);
    }

    public function createQR(Request $request, Response $response): Response
    {
        // Get team from repository
        $team = $this->teamRepository->getTeamById($_SESSION['team_id']);

        // Check if QR has already been generated
        if ($team->isQRGenerated()) {
            $this->flash->addMessage("notifications", "QR code was already generated for this team.");
            return $response->withHeader('Location','/team-stats')->withStatus(301);
        }

        // Contact API to generate the QR
        $generated = $this->barcode->simpleQRBase64("localhost:8030/invite/join/" . $_SESSION["team_id"], "Join '" . $team->getTeamName() . "'");

        // Check if QR has been generated correctly
        if (!$generated) {
            $this->flash->addMessage("notifications", "Unexpected error while generating QR code.");
        } else {
            // Persist in DB that QR has been generated
            $this->teamRepository->setQRToTeam($_SESSION['team_id']);
        }

        // Render team stats view again
        return $response->withHeader('Location','/team-stats')->withStatus(200);
    }

    public function downloadQR(Request $request, Response $response): Response
    {
        // Get team from repository
        $team = $this->teamRepository->getTeamById($_SESSION['team_id']);

        // Check QR for that team has been generated
        if ($team->isQRGenerated()) {
            // Attach file to header for download
            $filePath = $this->barcode->getQRFilePath($_SESSION['team_id']);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            flush();
            readfile($filePath);
        } else {
            $this->flash->addMessage("notifications", "Unexpected error while downloading QR code.");
        }

        // Render team stats view again
        return $response->withHeader('Location','/team-stats')->withStatus(200);
    }

    private function showTeamStatsView(array $notifications, Response $response): Response
    {
        // Get team information from repository
        $team = $this->teamRepository->getTeamById($_SESSION['team_id']);

        // Get user's information from repository and subtract usernames
        $user1 = explode('@', $this->userRepository->getUserById($team->getUserId1())->getEmail())[0];
        if ($team->getNumMembers() == 2) {
            $user2 = explode('@', $this->userRepository->getUserById($team->getUserId2())->getEmail())[0];
        }

        // Check if last score has been registered
        if ($team->isLastScoreRegistered()) {
            $lastScore = $team->getLastScore();
        } else {
            $lastScore = "-";
        }

        // Render team stats view with the necessary parameters
        return $this->twig->render(
            $response,
            'team-stats.twig',
            [
                "notifs" => $notifications ?? [],
                "email" => $_SESSION['email'],
                "team" => $_SESSION['team_id'],
                "TeamFull" => intval($team->getNumMembers()/2),
                "QRGenerated" => $team->isQRGenerated(),
                "teamPicture" => self::DEFAULT_TEAM_IMAGE,
                "teamName" => $team->getTeamName(),
                "teamMembers" => $team->getNumMembers(),
                "lastScore" => $lastScore,
                "totalScore" => $team->getTotalScore(),
                "user1" => $user1,
                "user2" => $user2 ?? null
            ]
        );
    }
}