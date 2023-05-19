<?php
/**
 * Teams Controller: Manages the logic and view of all team-related pages (/game, /join, /team-stats)
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 18/04/2023
 * @updated: 19/05/2023
 */
namespace Salle\PuzzleMania\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Salle\PuzzleMania\Model\Team;
use Salle\PuzzleMania\Repository\TeamRepository;
use Salle\PuzzleMania\Repository\UserRepository;
use Salle\PuzzleMania\Service\BarcodeService;
use Salle\PuzzleMania\Service\InviteService;
use Salle\PuzzleMania\Service\ValidatorService;
use Slim\Flash\Messages;
use Slim\Views\Twig;

class TeamsController
{
    // Services used
    private BarcodeService $barcode;
    private ValidatorService $validator;

    // Path where the default Team Picture is stored
    private const DEFAULT_TEAM_IMAGE = 'assets/images/teamPicture.png';

    public function __construct(
        private Twig           $twig,
        private UserRepository $userRepository,
        private TeamRepository $teamRepository,
        private Messages       $flash,
        private InviteService  $inviteService
    )
    {
        $this->barcode = new BarcodeService();
        $this->validator = new ValidatorService();
    }

    /**
     * Render the join view
     * @param Request $request Not used since the necessary information from the request is handled by routing.php.
     * @param Response $response View information will be written into this response body.
     * @return Response Returns the response with the twig view to render
     */
    public function showJoin(Request $request, Response $response): Response
    {
        // Get flash messages
        $messages = $this->flash->getMessages();
        $notifications = $messages['notifications'] ?? [];
        $notifications_create = $messages['notifications_create'] ?? [];
        $notifications_join = $messages['notifications_join'] ?? [];

        // Get incomplete teams from repository
        $teams = $this->teamRepository->getIncompleteTeams();

        // Render view
        return $this->twig->render($response, 'join.twig', [
            "notifs" => $notifications,
            "notifs_create" => $notifications_create,
            "notifs_join" => $notifications_join,
            "email" => $_SESSION["email"],
            "teams" => $teams
        ]);
    }

    /**
     * Private function that handles the logic of a user creating a new team
     * @param Response $response View information will be written into this response body.
     * @return Response Returns the response with the twig view to render
     */
    private function createTeamLogic(Response $response): Response
    {
        // The "Create Team" button was clicked, check the input name is set and not empty
        if (isset($_POST['teamName']) and $_POST['teamName'] !== "") {
            // Check if the input is too long
            if($this->validator->checkIfInputTooLong($_POST['teamName'])){
                $this->flash->addMessage("notifications_create", "The team name is too long.");
                return $response->withHeader('Location','/join')->withStatus(301);
            }

            // Check the name is not already taken by another team
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
                $this->flash->addMessage("success", "You created the team successfully.");
                return $response->withHeader('Location','/team-stats')->withStatus(301);
            } else {
                $this->flash->addMessage("notifications_create", "The team name is already in use.");
            }
        } else {
            $this->flash->addMessage("notifications_create", "The team name can not be empty.");
        }
        // If reached here means the creation of team failed, so we must redirect to the 'join' page again
        return $response->withHeader('Location','/join')->withStatus(301);
    }

    /**
     * Private function that handles the logic of a user joining a new team
     * @param Response $response View information will be written into this response body.
     * @return Response Returns the response with the twig view to render
     */
    private function joinTeamLogic(Response $response): Response
    {
        // The "Join Team" button was clicked, check the input team is set
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
                $this->flash->addMessage("notifications_join", "The team selected doesn't exist.");
            } elseif ($team->getNumMembers() == 2) {
                // The team selected is already full
                $this->flash->addMessage("notifications_join", "The team selected is already full.");
            }
        } else {
            // No team was selected.
            $this->flash->addMessage("notifications_join", "You didn't select a team to join.");
        }
        // If reached here means the creation of team failed, so we must show the 'join' page again
        return $response->withHeader('Location','/join')->withStatus(301);
    }

    /**
     * Function that handles the forms from /join view
     * @param Request $request Not used since the necessary information from the request is handled by routing.php.
     * @param Response $response View information will be written into this response body.
     * @return Response Returns the response with the twig view to render
     */
    public function handleJoinForm(Request $request, Response $response): Response
    {
        // Check if the user used the 'Join' form or the 'Create Team' form
        if (isset($_POST['joinTeam'])) {
            return $this->joinTeamLogic($response);
        } elseif (isset($_POST['createTeam'])) {
            return $this->createTeamLogic($response);
        }

        $this->flash->addMessage("notifications", "An unexpected error happened.");
        // If reached here means the creation of team failed, so we must show the 'join' page again
        return $response->withHeader('Location','/join')->withStatus(301);

    }

    /**
     * Function that handles the invite endpoint
     * @param Request $request Used to get the id of the team that the user is trying to join
     * @param Response $response View information will be written into this response body.
     * @return Response Returns the response with the twig view to render
     */
    public function handleInviteForm(Request $request, Response $response): Response
    {
        // Get the teamID from the URL
        $team_id = $request->getAttribute('id');
        $_SESSION["idTeam"] = $team_id;

        // In order to join a team we need the user Id
        $user = $this->userRepository->getUserByEmail($_SESSION['email']);

        // Call InviteService class to handle the invite logic
        return $this->inviteService->handleInviteLogic($response, $user);
    }

    /**
     * Function that handles the team-stats endpoint
     * @param Request $request Not used since the necessary information from the request is handled by routing.php.
     * @param Response $response View information will be written into this response body.
     * @return Response Returns the response with the twig view to render
     */
    public function showTeamStats(Request $request, Response $response): Response
    {
        // Get flash messages
        $messages = $this->flash->getMessages();
        $notifications = $messages["notifications"] ?? [];
        $success_notifications = $messages["success"] ?? [];

        // Render team stats view
        return $this->showTeamStatsView($notifications, $success_notifications, $response);
    }

    /**
     * Function that handles the QR creation
     * @param Request $request Not used since the necessary information from the request is handled by routing.php.
     * @param Response $response View information will be written into this response body.
     * @return Response Returns the response with the redirect info
     */
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

    /**
     * Function that handles the QR download
     * @param Request $request Not used since the necessary information from the request is handled by routing.php.
     * @param Response $response View information will be written into this response body.
     * @return Response Returns the response with the redirect info
     */
    public function downloadQR(Request $request, Response $response): Response
    {
        // Get team from repository
        $team = $this->teamRepository->getTeamById($_SESSION['team_id']);

        // Check QR for that team has been generated
        if ($team->isQRGenerated()) {
            // Attach file to header for download
            $filePath = $this->barcode->getQRFilePath($_SESSION['team_id']);
            // Check if the image is found
            if (file_exists($filePath)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filePath));
                flush();
                readfile($filePath);
            }
        } else {
            $this->flash->addMessage("notifications", "Unexpected error while downloading QR code.");
        }

        // Render team stats view again
        return $response->withHeader('Location','/team-stats')->withStatus(200);
    }

    /**
     * Function that handles the view of the team-stats to show
     * @param array $notifications Array containing error notifications to show.
     * @param array $success_notifications Array containing success notifications to show.
     * @param Response $response View information will be written into this response body.
     * @return Response Returns the response with the twig view info
     */
    private function showTeamStatsView(array $notifications, array $success_notifications, Response $response): Response
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

        // Check if the QR code is located in server (in case it exists)
        $found = true;
        if ($team->isQRGenerated() === 1 and !file_exists($this->barcode->getQRFilePath($_SESSION['team_id']))) {
            $notifications[] = "The QR code of the team was previously generated but is not found in the server.";
            $found = false;
        }

        // Render team stats view with the necessary parameters
        return $this->twig->render(
            $response,
            'team-stats.twig',
            [
                "notifs" => $notifications ?? [],
                "success_notifs" => $success_notifications ?? [],
                "email" => $_SESSION['email'],
                "team" => $_SESSION['team_id'],
                "TeamFull" => ($team->getNumMembers() == 2),
                "QRGenerated" => $team->isQRGenerated(),
                "QRFound" => $found,
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