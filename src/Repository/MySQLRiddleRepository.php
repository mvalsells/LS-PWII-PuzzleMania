<?php
declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use PDO;
use Salle\PuzzleMania\Model\Riddle;

/**
 *  TODO: Coses que cal fer a la BBDD (✔✖)
 *
 *      Riddles:
 *      · Llegir tots els riddles --> ✔
 *      · Afegir riddles --> ✔
 *
 *
 *  TODO: FLASH MESSAGES
 */

class MySQLRiddleRepository implements RiddleRepository
{

    private const DATE_FORMAT = 'Y-m-d H:i:s';

    private PDO $databaseConnection;

    public function __construct(PDO $database)
    {
        $this->databaseConnection = $database;
    }


    //==========================================================================================
    // RIDDLES RELATED QUERIES
    //==========================================================================================

    /**
     * Returns an array of Model/Riddle objects with all the available riddles in the 'riddles' table of the database.
     * @return array
     */
    public function getAllRiddles(): array
    {

        // Fem la query
        $query = <<<'QUERY'
            SELECT * FROM riddles;
        QUERY;

        // Preparem la query
        $statement = $this->databaseConnection->prepare($query);

        // Executem la query
        $statement->execute();

        // Retornem els resultats de la query
        $rawData =  $statement->fetchAll(PDO::FETCH_ASSOC);
        $riddles = [];
        foreach ($rawData as $row) {
            $riddles[] = new Riddle($row['riddle_id'], $row['user_id'], $row['riddle'], $row['answer']);
        }
        return $riddles;
    }

    /**
     * Adds the $r object to the 'riddles' table in the database, the ID of the added riddle is returned.
     * @param Riddle $r The 'id' and 'userId' attributes can be null.
     * @return int
     */
    public function addRiddle(Riddle $r): int
    {
        // Check if optional ID field is set
        if ($r->getId() == null) {
            // Fem la query
            $query = <<<'QUERY'
                INSERT INTO riddles (user_id, riddle, answer) VALUES (:user_id, :riddle, :answer);
            QUERY;

            // Preparem la query
            $statement = $this->databaseConnection->prepare($query);
        } else {
            // Fem la query
            $query = <<<'QUERY'
                INSERT INTO riddles (riddle_id, user_id, riddle, answer) VALUES (:id, :user_id, :riddle, :answer);
            QUERY;
            // Preparem la query
            $statement = $this->databaseConnection->prepare($query);
            $id = $r->getId();
            $statement->bindParam('id', $id, PDO::PARAM_INT);
        }



        // Inserim els paràmetres que volem a la query
        $idUser = $r->getUserId();
        $riddle = $r->getRiddle();
        $answer = $r->getAnswer();

        $statement->bindParam('user_id', $idUser, PDO::PARAM_INT);
        $statement->bindParam('riddle', $riddle, PDO::PARAM_STR);
        $statement->bindParam('answer', $answer, PDO::PARAM_STR);

        // Executem la query
        $statement->execute();

        return intval($this->databaseConnection->lastInsertId());

    }
    /**
     * Given an id of a riddle, a Model/Riddle object with all the information is returned. If the riddle is not found
     * in the 'riddles' table of the database null is returned.
     * @param int $id
     * @return Riddle|null
     */
    public function getOneRiddleById(int $id): ?Riddle
    {
        // Fem la query
        $query = <<<'QUERY'
            SELECT * FROM riddles WHERE riddle_id = :riddleId LIMIT 1;
        QUERY;

        // Preparem la query
        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam(':riddleId', $id, PDO::PARAM_INT);

        // Executem la query
        $statement->execute();

        // Retornem els resultats de la query
        $rawData =  $statement->fetchAll(PDO::FETCH_ASSOC);

        // Check if at least one row has been returned
        if ( count($rawData) <= 0) {
            return null;
        }

        // Check if the returned row has all the expected columns
        if (array_key_exists('riddle_id', $rawData[0]) && array_key_exists('user_id', $rawData[0]) && array_key_exists('riddle', $rawData[0]) && array_key_exists('answer', $rawData[0]) ){
            return new Riddle($rawData[0]['riddle_id'], $rawData[0]['user_id'], $rawData[0]['riddle'], $rawData[0]['answer']);
        } else {
            return null;
        }
    }
    /**
     * Updates the riddle with ID $originalId in the 'riddles' table of the database with the $newRiddle data
     * @param int $originalId
     * @param Riddle $newRiddle 'id' attribute CANNOT be null, 'userId' can be null. The unchanged fields must have the
     *                          original information.
     * @return void
     */
    public function updateRiddle(int $originalId, Riddle $newRiddle): void
    {

        // Create query
        $query = <<<'QUERY'
            UPDATE riddles SET riddle_id = :id, user_id = :userId, riddle = :riddle, answer = :answer  WHERE riddle_id = :originalId;
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Add parameters to the query

        $id = $newRiddle->getId();
        $userId = $newRiddle->getUserId();
        $riddle = $newRiddle->getRiddle();
        $answer = $newRiddle->getAnswer();

        $statement->bindParam('id', $id, PDO::PARAM_INT);
        $statement->bindParam('userId', $userId, PDO::PARAM_INT);
        $statement->bindParam('riddle', $riddle, PDO::PARAM_STR);
        $statement->bindParam('answer', $answer, PDO::PARAM_STR);
        $statement->bindParam('originalId', $originalId, PDO::PARAM_INT);

        // Execute query
        $statement->execute();
    }
    /**
     * Given an id of a riddle this one is deleted from the 'riddles' table fo the database.
     * @param int $id
     * @return void
     */
    public function deleteRiddle(int $id): void {
        // Create query
        $query = <<<'QUERY'
            DELETE FROM riddles WHERE riddle_id = :id;
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Add parameters to the query
        $statement->bindParam('id', $id, PDO::PARAM_INT);

        // Execute query
        $statement->execute();
    }
}