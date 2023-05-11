<?php

namespace Salle\PuzzleMania\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Salle\PuzzleMania\Repository\RiddleRepository;
use Salle\PuzzleMania\Repository\TeamRepository;
use Slim\Views\Twig;

class GameController
{

    public function __construct(
        private Twig           $twig,
        private TeamRepository $teamRepository,
        private RiddleRepository $riddleRepository
    )
    {
    }
    public function show(Request $request, Response $response): Response
    {
        if (isset($_SESSION['gameId'])) {
            // Set end game and button link to /team-stats
            unset($_SESSION['actual_riddle']);
            unset($_SESSION['riddles']);
            unset($_SESSION['gameId']);
            $points = $_SESSION['points'];
            if ($_SESSION["points"] > 0) {
                $this->teamRepository->addScoreToTeam($_SESSION["team_id"], $points);
            }
            unset($_SESSION['points']);
            $link = "/team-stats";
            $buttonName = "Finish";

            return $this->twig->render(
                $response,
                'game.twig',
                [
                    "teamName" => $this->teamRepository->getTeamById($_SESSION['team_id'])->getTeamName(),
                    "start" => 0,
                    "endGame" => 1,
                    "formAction" => $link,
                    "guessRiddle" => 1,
                    "buttonName" => $buttonName,
                    "points" => $points
                ]
            );
        } else {
            // Randomly generate game ID
            $gameId = rand(1000, 9999); // TODO: generate correctly this random number
            $_SESSION['gameId'] = $gameId;

            // Get riddles from repository and randomly choose 3
            $riddles = $this->riddleRepository->getAllRiddles();
            shuffle($riddles);
            $chosenRiddles = array_slice($riddles, 0, 3);

            // Set chosen riddles as a session variable
            $_SESSION['riddles'] = $chosenRiddles;

            // Get team for showing team name
            $team = $this->teamRepository->getTeamById($_SESSION['team_id']);

            // Set actual riddle
            $_SESSION['actual_riddle'] = 0;

            // Set start points
            $_SESSION["points"] = 10;

            return $this->twig->render(
                $response,
                'game.twig',
                [
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
        $link = '';
        $buttonName = "Submit answer";
        $endGame = 0;

        // Increment actual riddle
        $_SESSION['actual_riddle']++;
        $link = '/game/' . $_SESSION['gameId'] . "/riddle/" . ($_SESSION['actual_riddle']);

        return $this->twig->render(
            $response,
            'game.twig',
            [
                "teamName" => $this->teamRepository->getTeamById($_SESSION['team_id'])->getTeamName(),
                "start" => 0,
                "endGame" => $endGame,
                "actualRiddle" => ($_SESSION['riddles'][$_SESSION['actual_riddle']-1]) ?? [],
                "formAction" => $link,
                "guessRiddle" => 1,
                "buttonName" => $buttonName,
                "totalPoints" => $_SESSION["points"]
            ]
        );
    }
    public function handleFormRiddle(Request $request, Response $response): Response
    {
        $endGame = 0;
        $link = '/game/' . $_SESSION['gameId'] . "/riddle/" . ($_SESSION['actual_riddle']+1);
        $buttonName = "Next";
        $points = 10;

        $data = $request->getParsedBody();
        $riddle = $_SESSION['riddles'][$_SESSION['actual_riddle']-1];

        if ($data["answer"] !== $riddle->getAnswer()) {
            $points = -10;
        }

        $_SESSION["points"] += $points;

        if ($_SESSION['actual_riddle'] == 3 or $_SESSION["points"] < 0) {
            $link = "/game";
        }

        return $this->twig->render(
            $response,
            'game.twig',
            [
                "teamName" => $this->teamRepository->getTeamById($_SESSION['team_id'])->getTeamName(),
                "start" => 0,
                "guessRiddle" => 0,
                "endGame" => $endGame,
                "actualRiddle" => $_SESSION['riddles'][$_SESSION['actual_riddle']-1] ?? [],
                "formAction" => $link,
                "buttonName" => $buttonName,
                "points" => $points,
                "totalPoints" => $_SESSION["points"]
            ]
        );
    }
}