<?php
/**
 * Game Controller: Manages all the game logic and redirections
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 20/04/2023
 * @updated: 19/05/2023
 */
namespace Salle\PuzzleMania\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Salle\PuzzleMania\Model\Game;
use Salle\PuzzleMania\Repository\GameRepository;
use Salle\PuzzleMania\Repository\RiddleRepository;
use Salle\PuzzleMania\Repository\TeamRepository;
use Slim\Flash\Messages;
use Slim\Views\Twig;

class GameController
{

    public function __construct(
        private Twig           $twig,
        private TeamRepository $teamRepository,
        private RiddleRepository $riddleRepository,
        private GameRepository $gameRepository,
        private Messages       $flash
    )
    {
    }

    /**
     * Responsible for configuring the End Game view
     * @param Response $response Response variable where the twig render will be contained
     * @param array $notifications Notifications that may contain error messages that need to be displayed
     * @return Response Render twig response of the End Game view
     */
    private function showEndGameView(Response $response, array $notifications): Response
    {
        // Set session points to maximum 0
        if ($_SESSION['points'] < 0) {
            $_SESSION['points'] = 0;
        }
        // Save this session points and upload them to database
        $points = $_SESSION['points'];
        $this->teamRepository->addScoreToTeam($_SESSION["team_id"], $points);

        // Unset session variables
        unset($_SESSION['points']);
        unset($_SESSION['actual_riddle']);
        unset($_SESSION['riddles']);
        unset($_SESSION['gameId']);
        unset($_SESSION['endGame']);

        // Render the 'game.twig' variable, with a button to redirect to /team-stats
        return $this->twig->render(
            $response,
            'game.twig',
            [
                "notifs" => $notifications,
                "teamName" => $this->teamRepository->getTeamById($_SESSION['team_id'])->getTeamName(),
                "start" => false,
                "endGame" => true,
                "formAction" => "/team-stats",
                "guessRiddle" => true,
                "buttonName" => "Finish",
                "points" => $points
            ]
        );
    }

    /**
     * Responsible for configuring the Start Game view
     * @param Response $response Response variable where the twig render will be contained
     * @param array $notifications Notifications that may contain error messages that need to be displayed
     * @return Response Render twig response of the Start Game view
     */
    private function showStartGameView(Response $response, array $notifications): Response
    {
        // If a previous game wasn't finished, set a notification error and unset game variables
        if (isset($_SESSION['gameId'])) {
            $notifications[] = "The last game wasn't finished and has been erased from memory";
            unset($_SESSION['actual_riddle']);
            unset($_SESSION['riddles']);
            unset($_SESSION['gameId']);
            unset($_SESSION['points']);
            unset($_SESSION['endGame']);
        }

        // Get riddles from repository and randomly choose 3
        $riddles = $this->riddleRepository->getAllRiddles();
        shuffle($riddles);

        // Check that we have at least 3 riddles to begin the game
        if(count($riddles) < 3){
            $this->flash->addMessage("notifications", "There aren't enough riddles in database to begin a game.");
            return $response->withHeader('Location','/')->withStatus(301);
        }

        $chosenRiddles = array_slice($riddles, 0, 3);

        // Randomly generate game ID by uploading it to database
        $game = new Game($_SESSION["user_id"], $chosenRiddles[0]->getId(), $chosenRiddles[1]->getId(), $chosenRiddles[2]->getId());
        $gameId = $this->gameRepository->createGame($game);

        // Set game variables
        $_SESSION['gameId'] = $gameId;
        $_SESSION['endGame'] = 0;

        // Set chosen riddles as a session variable
        $_SESSION['riddles'] = $chosenRiddles;

        // Get team for showing team name
        $team = $this->teamRepository->getTeamById($_SESSION['team_id']);

        // Set actual riddle
        $_SESSION['actual_riddle'] = 1;

        // Set start points
        $_SESSION["points"] = 10;

        return $this->twig->render(
            $response,
            'game.twig',
            [
                "notifs" => $notifications,
                "teamName" => $team->getTeamName(),
                "start" => true,
                "formAction" => '/game',
                "buttonName" => "Start"
            ]
        );
    }

    /**
     * Responsible for determining what view to show if user accesses /game page
     * @param Response $response Response variable where the twig render will be contained
     * @param Request $request Unused variable
     * @return Response Render twig response of the suitable view
     */
    public function show(Request $request, Response $response): Response
    {
        // Get the flash messages
        $messages = $this->flash->getMessages();
        $notifications = $messages['notifications'] ?? [];

        // Check if there was a game running that has ended
        if (isset($_SESSION['gameId']) and $_SESSION['endGame'] == 1) {
            return $this->showEndGameView($response, $notifications);
        } else {
            return $this->showStartGameView($response, $notifications);
        }
    }

    /**
     * Handles form of /game page, which only consists on redirecting to first riddle page
     * @param Response $response Response variable where the redirection will be contained
     * @param Request $request Unused variable
     * @return Response Redirect response to riddle page
     */
    public function handleForm(Request $request, Response $response): Response
    {
        // Redirect to riddle page
        return $response
            ->withHeader('Location', '/game/' . $_SESSION['gameId'] . "/riddle/1")
            ->withStatus(301);

    }

    /**
     * Checks if the target page of the user is correct, taking into account the state of the game
     * @param Response $response Response variable where the redirection will be contained
     * @param Request $request Request variable where the game ID and the riddle ID are contained
     * @return ?Response Redirect response to another page if some issue arose, if not null
     */
    private function checkIfCorrectGamePage (Request $request, Response $response): ?Response
    {
        // Get the riddleID and the gameID from the URL
        $riddleID = $request->getAttribute('riddleID');
        $gameID = $request->getAttribute('gameID');

        // Check there is a game running
        if (!isset($_SESSION['gameId'])) {
            $this->flash->addMessage("notifications", "There isn't a current game running yet.");
            return $response
                ->withHeader('Location', '/game')
                ->withStatus(301);
        }

        // Check the gameID and riddleID values are numeric
        // If the values are numeric, check the IDs match the current game ones
        if (!is_numeric($riddleID) or !ctype_digit($riddleID) or !is_numeric($gameID) or !ctype_digit($gameID) or
            $riddleID != $_SESSION['actual_riddle'] or $gameID != $_SESSION['gameId']) {
            if ($_SESSION['endGame'] == 0) {
                $this->flash->addMessage("notifications", "You can't access this page of the game.");
                return $response
                    ->withHeader('Location', '/game/' . $_SESSION['gameId'] . "/riddle/" . $_SESSION['actual_riddle'])
                    ->withStatus(301);
            } else {
                $this->flash->addMessage("notifications", "The game has already ended.");
                return $response
                    ->withHeader('Location', '/game')
                    ->withStatus(301);
            }
        }
        return null;
    }

    /**
     * Function that manages what to render if the user accesses a riddle page in a game
     * @param Response $response Response variable where the redirection or render will be contained
     * @param Request $request Request variable where the game ID and the riddle ID are contained
     * @return Response Redirect response to another page if some issue arose, if not a render of the requested riddle
     */
    public function showRiddle(Request $request, Response $response): Response
    {
        // Check if the targeted page is correct
        $answer = $this->checkIfCorrectGamePage($request, $response);
        if ($answer !== null) {
            return $answer;
        }

        // Get the flash messages
        $messages = $this->flash->getMessages();
        $notifications = $messages['notifications'] ?? [];

        // Set link of the submit button
        $link = '/game/' . $_SESSION['gameId'] . "/riddle/" . ($_SESSION['actual_riddle']);

        return $this->twig->render(
            $response,
            'game.twig',
            [
                "notifs" => $notifications,
                "teamName" => $this->teamRepository->getTeamById($_SESSION['team_id'])->getTeamName(),
                "start" => false,
                "endGame" => false,
                "actualRiddle" => ($_SESSION['riddles'][$_SESSION['actual_riddle']-1]) ?? [],
                "formAction" => $link,
                "guessRiddle" => true,
                "buttonName" => "Submit answer",
                "totalPoints" => $_SESSION["points"]
            ]
        );
    }

    /**
     * Function that handles the post method of a riddle
     * @param Response $response Response variable where the redirection or render will be contained
     * @param Request $request Request variable where the game ID, riddle ID and user's answer are contained
     * @return Response Redirect response to another page if some issue arose, if not a render the result of the riddle
     */
    public function handleFormRiddle(Request $request, Response $response): Response
    {
        // Check if the targeted page is correct
        $answer = $this->checkIfCorrectGamePage($request, $response);
        if ($answer !== null) {
            return $answer;
        }

        // Get data from the POST
        $data = $request->getParsedBody();
        $riddle = $_SESSION['riddles'][$_SESSION['actual_riddle'] - 1];

        // Determine the last points got
        if ($data["answer"] !== $riddle->getAnswer()) {
            $_SESSION["last_points"] = -10;
        } else {
            $_SESSION["last_points"] = 10;
        }

        // Update session points if the game has not ended
        if ($_SESSION['endGame'] == 0) {
            $_SESSION["points"] += $_SESSION["last_points"];
        }

        // Determine button link and actual riddle, depending on the total points and the next riddle
        if ($_SESSION['actual_riddle'] == 3 or $_SESSION["points"] <= 0) {
            $link = "/game";
            $_SESSION['endGame'] = 1;
        } else {
            $_SESSION['actual_riddle']++;
            $link = '/game/' . $_SESSION['gameId'] . "/riddle/" . $_SESSION['actual_riddle'];
        }

        return $this->twig->render(
            $response,
            'game.twig',
            [
                "teamName" => $this->teamRepository->getTeamById($_SESSION['team_id'])->getTeamName(),
                "start" => false,
                "guessRiddle" => false,
                "endGame" => false,
                "actualRiddle" => $riddle,
                "userAnswer" => $data["answer"],
                "formAction" => $link,
                "buttonName" => "Next",
                "points" => $_SESSION["last_points"],
                "totalPoints" => $_SESSION["points"]
            ]
        );
    }
}