<?php

namespace Salle\PuzzleMania\Controller;

use PDO;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Salle\PuzzleMania\Model\Team;
use Salle\PuzzleMania\Repository\TeamRepository;
use Salle\PuzzleMania\Repository\UserRepository;
use Salle\PuzzleMania\Service\BarcodeService;
use Salle\PuzzleMania\Service\ValidatorService;
use Slim\Flash\Messages;
use Slim\Views\Twig;

class TeamsController
{
    private BarcodeService $barcode;

    private const DEFAULT_TEAM_IMAGE = 'assets/images/teamPicture.png';
    private ValidatorService $validator;

    public function __construct(
        private Twig           $twig,
        private UserRepository $userRepository,
        private TeamRepository $teamRepository,
        private Messages       $flash
    )
    {
        $this->barcode = new BarcodeService();
        $this->validator = new ValidatorService();
    }

    public function showJoin(Request $request, Response $response): Response
    {
        // Get flash messages
        $messages = $this->flash->getMessages();
        $notifications = $messages['notifications'] ?? [];

        // Get incomplete teams from repository
        $teams = $this->teamRepository->getIncompleteTeams();

        // Render view
        return $this->twig->render($response, 'join.twig', [
            "notifs" => $notifications,
            "email" => $_SESSION["email"],
            "teams" => $teams
        ]);
    }
    public function handleJoinForm(Request $request, Response $response): Response
    {
        if (isset($_POST['joinTeam'])) {
            // The "Join Team" button was clicked
            if (isset($_POST['team'])) {
                // Join this team in DB
                $user = $this->userRepository->getUserById($_SESSION["user_id"]);
                $team = $this->teamRepository->getTeamById($_POST['team']);
                // Check the team exists and is not full
                if (!$team->isNullTeam() and $team->getNumMembers() !== 2) {
                    $this->teamRepository->addUserToTeam($_POST['team'], $user);
                    // Set SESSION variable
                    $_SESSION['team_id'] = $_POST['team'];
                    // Redirect to team-stats
                    $this->flash->addMessage("success", "You joined the team successfully.");
                    return $response->withHeader('Location','/team-stats')->withStatus(301);
                } elseif ($team->isNullTeam()) {
                    // The team selected doesn't exist
                    $this->flash->addMessage("notifications", "The team selected doesn't exist.");
                } elseif ($team->getNumMembers() == 2) {
                    // The team selected is already full
                    $this->flash->addMessage("notifications", "The team selected is already full.");
                }
            } else {
                // No team was selected.
                $this->flash->addMessage("notifications", "You didn't select a team to join.");
            }
        } elseif (isset($_POST['createTeam'])) {
            // The "Create Team" button was clicked

            if (isset($_POST['teamName'])) {

                // Check if the input is too long
                if($this->validator->checkIfInputTooLong($_POST['teamName'])){
                    $this->flash->addMessage("notifications", "The team name is too long.");
                    return $response->withHeader('Location','/join')->withStatus(301);
                }

                $name = $_POST['teamName'];
                $team_aux = $this->teamRepository->getTeamByName($name);
                if ($team_aux->isNullTeam()) {
                    // Create team and upload to DB
                    $team = new Team();
                    $team->setTeamName($name);
                    $team->setUserId1($_SESSION['user_id']);
                    $this->teamRepository->createTeam($team);
                    // Set SESSION VARIABLE
                    $team = $this->teamRepository->getTeamByName($name);
                    $_SESSION['team_id'] = $team->getTeamId();
                    // Redirect to team-stats
                    $this->flash->addMessage("success", "You joined the team successfully.");
                    return $response->withHeader('Location','/team-stats')->withStatus(301);
                } else {
                    $this->flash->addMessage("notifications", "The team name is already in use.");
                }
            } else {
                $this->flash->addMessage("notifications", "The team name can not be empty.");
            }
            // If reached here means the creation of team failed, so we must show the 'join' page again
            return $response->withHeader('Location','/join')->withStatus(301);
        }

        // If reached here means the creation of team failed, so we must show the 'join' page again
        return $response->withHeader('Location','/join')->withStatus(301);
    }

    public function handleInviteForm(Request $request, Response $response): Response
    {
        // Get the teamID from the URL
        $team_id = $request->getAttribute('id');

        // Get team from repository
        $team = $this->teamRepository->getTeamById($team_id);
        if (!$team->isNullTeam() and $team->getNumMembers() !== 2 and $team->isQRGenerated() !== 0) {
            // In order to join a team we need the user Id
            $user = $this->userRepository->getUserByEmail($_SESSION['email']);

            // Joining user to the team
            $this->teamRepository->addUserToTeam($team_id, $user);

            // Set session team_id variable
            $_SESSION["team_id"] = $team_id;

            // Redirect to the /team-stats page
            return $response->withHeader('Location', '/team-stats')->withStatus(302);
        } else {
            $this->flash->addMessage("notifications", "The team you're trying to join is full, doesn't exist or does not have the /invite endpoint activated.");
            return $response->withHeader('Location', '/join')->withStatus(302);
        }
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
        $generated = $this->barcode->generateSimpleQR("http://localhost:8030/invite/join/" . $_SESSION["team_id"], "Join '" . $team->getTeamName() . "'");

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