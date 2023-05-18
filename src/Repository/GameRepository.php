<?php

namespace Salle\PuzzleMania\Repository;

use Salle\PuzzleMania\Model\Game;

interface GameRepository
{
    public function createGame(Game $game): int;
}