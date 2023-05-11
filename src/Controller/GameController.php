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
        // Randomly generate game ID
        $gameId = rand(1000, 9999); // TODO: generate correctly this random number
        $_SESSION['gameId'] = $gameId;

        // Get team for showing team name
        $team = $this->teamRepository->getTeamById($_SESSION['team_id']);
        return $this->twig->render(
            $response,
            'game.twig',
            [
                "teamName" => $team->getTeamName(),
                "start" => 1,
                "button" => '/game/' . $gameId . "/riddle/1",
                "buttonName" => "Start answer"
            ]
        );
    }

    public function handleForm(Request $request, Response $response): Response
    {
        // Get riddles from repository and randomly choose 3
        $riddles = $this->riddleRepository->getAllRiddles();
        shuffle($riddles);
        $chosenRiddles = array_slice($riddles, 0, 3);

        // Set chosen riddles as a session variable
        $_SESSION['riddles'] = $chosenRiddles;

        // Get team for showing team name
        $team = $this->teamRepository->getTeamById($_SESSION['team_id']);

        // Set actual riddle
        $_SESSION['actual_riddle'] = 1;

        return $this->twig->render(
            $response,
            'game.twig',
            [
                "teamName" => $team->getTeamName(),
                "start" => 0,
                "endGame" => 0,
                "actualRiddle" => $chosenRiddles[0],
                "button" => '/game/' . $_SESSION['gameId'] . "/riddle/1",
                "buttonName" => "Submit answer"
            ]
        );
    }

    public function showRiddle(Request $request, Response $response): Response
    {
        // Increment actual riddle
        $_SESSION['actual_riddle']++;

        return $this->twig->render(
            $response,
            'game.twig',
            [
                "teamName" => $this->teamRepository->getTeamById($_SESSION['team_id'])->getTeamName(),
                "start" => 0,
                "endGame" => 0,
                "actualRiddle" => $_SESSION['riddles'][$_SESSION['actual_riddle']-1],
                "button" => '/game/' . $_SESSION['gameId'] . "/riddle/" . ($_SESSION['actual_riddle']),
                "buttonName" => "Submit answer"
            ]
        );
    }
    public function handleFormRiddle(Request $request, Response $response): Response
    {
        $endGame = 0;
        $link = '/game/' . $_SESSION['gameId'] . "/riddle/" . ($_SESSION['actual_riddle']);
        $buttonName = "Next riddle";

        // Increment actual riddle if it's not the last
        if ($_SESSION['actual_riddle'] != 3) {
            $_SESSION['actual_riddle']++;
        } else {
            // Set end game and button link to /team-stats
            $endGame = 1;
            unset($_SESSION['actual_riddle']);
            unset($_SESSION['riddles']);
            unset($_SESSION['gameId']);
            $link = "/team-stats";
            $buttonName = "Finish";
        }

        return $this->twig->render(
            $response,
            'game.twig',
            [
                "teamName" => $this->teamRepository->getTeamById($_SESSION['team_id'])->getTeamName(),
                "start" => 0,
                "endGame" => $endGame,
                "actualRiddle" => $_SESSION['riddles'][$_SESSION['actual_riddle']-1] ?? [],
                "button" => $link
            ]
        );
    }
}