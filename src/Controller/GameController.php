<?php

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
    public function show(Request $request, Response $response): Response
    {
        // Check if there was a game running that has ended
        if (isset($_SESSION['gameId']) and $_SESSION['endGame'] == 1) {
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
                    "teamName" => $this->teamRepository->getTeamById($_SESSION['team_id'])->getTeamName(),
                    "start" => 0,
                    "endGame" => 1,
                    "formAction" => "/team-stats",
                    "guessRiddle" => 1,
                    "buttonName" => "Finish",
                    "points" => $points
                ]
            );

        } else {
            $notifications = [];
            if (isset($_SESSION['gameId'])) {
                // Set a notification error and unset game variables
                $notifications = "The last game wasn't finished and has been erased from memory";
                unset($_SESSION['actual_riddle']);
                unset($_SESSION['riddles']);
                unset($_SESSION['gameId']);
                unset($_SESSION['points']);
                unset($_SESSION['endGame']);
            }

            // Get riddles from repository and randomly choose 3
            $riddles = $this->riddleRepository->getAllRiddles();
            shuffle($riddles);
            $chosenRiddles = array_slice($riddles, 0, 3);

            // Randomly generate game ID
            $game = new Game($_SESSION["user_id"], $chosenRiddles[0]->getId(), $chosenRiddles[1]->getId(), $chosenRiddles[2]->getId());
            $gameId = $this->gameRepository->createGame($game);
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
                    "start" => 1,
                    "formAction" => '/game',
                    "buttonName" => "Start"
                ]
            );
        }
    }

    public function handleForm(Request $request, Response $response): Response
    {
        // Redirect to riddle page
        return $response
            ->withHeader('Location', '/game/' . $_SESSION['gameId'] . "/riddle/1")
            ->withStatus(200);

    }

    public function showRiddle(Request $request, Response $response): Response
    {
        // Get the riddleID and the gameID from the URL
        $riddleID = $request->getAttribute('riddleID');
        $gameID = $request->getAttribute('gameID');

        // Check there is a game running
        if (!isset($_SESSION['gameId'])) {
            $this->flash->addMessage("notifications", "There isn't a current game running yet.");
            return $response
                ->withHeader('Location', '/game')
                ->withStatus(200);
        }

        // Check the IDs match the current game ones
        if ($riddleID != $_SESSION['actual_riddle'] or $gameID != $_SESSION['gameId']) {
            // Check whether the game has already ended or not
            if ($_SESSION['endGame'] == 0) {
                $this->flash->addMessage("notifications", "You can't access this page of the game.");
                return $response
                    ->withHeader('Location', '/game/' . $_SESSION['gameId'] . "/riddle/" . $_SESSION['actual_riddle'])
                    ->withStatus(200);
            } else {
                $this->flash->addMessage("notifications", "The game has already ended.");
                return $response
                    ->withHeader('Location', '/game')
                    ->withStatus(200);
            }
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
                "start" => 0,
                "endGame" => 0,
                "actualRiddle" => ($_SESSION['riddles'][$_SESSION['actual_riddle']-1]) ?? [],
                "formAction" => $link,
                "guessRiddle" => 1,
                "buttonName" => "Submit answer",
                "totalPoints" => $_SESSION["points"]
            ]
        );
    }
    public function handleFormRiddle(Request $request, Response $response): Response
    {
        // Get the riddleID and the gameID from the URL
        $riddleID = $request->getAttribute('riddleID');
        $gameID = $request->getAttribute('gameID');

        // Check there is a game running
        if (!isset($_SESSION['gameId'])) {
            $this->flash->addMessage("notifications", "There isn't a current game running yet.");
            return $response
                ->withHeader('Location', '/game')
                ->withStatus(200);
        }

        // Check the IDs match the current game ones
        if ($riddleID != $_SESSION['actual_riddle'] or $gameID != $_SESSION['gameId']) {
            // Check whether the game has already ended or not
            if ($_SESSION['endGame'] == 0) {
                $this->flash->addMessage("notifications", "You can't access this page of the game.");
                return $response
                    ->withHeader('Location', '/game/' . $_SESSION['gameId'] . "/riddle/" . $_SESSION['actual_riddle'])
                    ->withStatus(200);
            } else {
                $this->flash->addMessage("notifications", "The game has already ended.");
                return $response
                    ->withHeader('Location', '/game')
                    ->withStatus(200);
            }
        }

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

        // Determine button link and actual riddle
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
                "start" => 0,
                "guessRiddle" => 0,
                "endGame" => 0,
                "actualRiddle" => $_SESSION['riddles'][$_SESSION['actual_riddle']-2] ?? [],
                "userAnswer" => $data["answer"],
                "formAction" => $link,
                "buttonName" => "Next",
                "points" => $_SESSION["last_points"],
                "totalPoints" => $_SESSION["points"]
            ]
        );
    }
}