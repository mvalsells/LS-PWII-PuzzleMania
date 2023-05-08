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
        // Get team for showing team name
        $team = $this->teamRepository->getTeamById($_SESSION['team_id']);
        return $this->twig->render(
            $response,
            'game.twig',
            [
                "teamName" => $team->getTeamName(),
                "start" => 1
            ]
        );
    }

    public function handleForm(Request $request, Response $response): Response
    {
        // Get riddles from repository and randomly choose 3
        $riddles = $this->riddleRepository->getAllRiddles();
        $chosenRiddles = array_slice(shuffle($riddles), 0, 3);

        // Set chosen riddles as a session variable
        $_SESSION['riddles'] = $chosenRiddles;

        // Get team for showing team name
        $team = $this->teamRepository->getTeamById($_SESSION['team_id']);

        // Set actual riddle
        $_SESSION['actual_riddle'] = 1;

        // Randomly generate game ID
        $gameId = rand(1000, 9999); // TODO: generate correctly this random number
        $_SESSION['gameId'] = $gameId;

        return $this->twig->render(
            $response,
            'game.twig',
            [
                "teamName" => $team->getTeamName(),
                "start" => 0,
                "actualRiddle" => $chosenRiddles[0],
                "nextRiddle" => 2
            ]
        );
    }

    public function showRiddle(Request $request, Response $response): Response
    {

        return $this->twig->render(
            $response,
            'riddle.twig',
            [
            ]
        );
    }
    public function handleFormRiddle(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response,
            'riddle.twig',
            [
            ]
        );
    }
}