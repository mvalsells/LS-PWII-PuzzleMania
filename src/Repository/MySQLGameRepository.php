<?php
declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use PDO;
use Salle\PuzzleMania\Model\Game;

class MySQLGameRepository implements GameRepository
{
    private PDO $databaseConnection;

    public function __construct(PDO $database)
    {
        $this->databaseConnection = $database;
    }

    public function createGame(Game $game): int
    {
        // Build the SQL query
        $query = <<<'QUERY'
            INSERT INTO games(user_id, riddle_1, riddle_2, riddle_3)
            VALUES(:user_id, :riddle_1, :riddle_2, :riddle_3)
        QUERY;

        // Extract the variables we want to upload to database
        $user_id = $game->getUserId();
        $riddle1 = $game->getRiddle1();
        $riddle2 = $game->getRiddle2();
        $riddle3 = $game->getRiddle3();

        $statement = $this->databaseConnection->prepare($query);

        // Introduce variables in statement query
        $statement->bindParam('user_id', $user_id, PDO::PARAM_INT);
        $statement->bindParam('riddle_1', $riddle1, PDO::PARAM_INT);
        $statement->bindParam('riddle_2', $riddle2, PDO::PARAM_INT);
        $statement->bindParam('riddle_3', $riddle3, PDO::PARAM_INT);

        // Execute query
        $statement->execute();

        // Get the ID of the game in the Game table and return it
        return intval($this->databaseConnection->lastInsertId());
    }
}