<?php
/**
 * GameRepository: Interface that defines the methods to access data sources that contain Game instances
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 16/05/2023
 * @updated: 18/05/2023
 */
namespace Salle\PuzzleMania\Repository;

use Salle\PuzzleMania\Model\Game;

interface GameRepository
{
    /**
     * Persists the game provided in the database
     * @param Game $game Game to persist in database
     * @return int The id associated to the game created
     */
    public function createGame(Game $game): int;
}