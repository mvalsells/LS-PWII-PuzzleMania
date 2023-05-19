<?php
declare(strict_types=1);
/**
 * MySQLGameRepository: Class that implements RiddleRepository interface, to access a database with Riddle instances
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 20/04/2023
 * @updated: 18/05/2023
 */
namespace Salle\PuzzleMania\Repository;

use PDO;
use Salle\PuzzleMania\Model\Riddle;

class MySQLRiddleRepository implements RiddleRepository
{

    private PDO $databaseConnection;

    public function __construct(PDO $database)
    {
        $this->databaseConnection = $database;
    }


    //==========================================================================================
    // RIDDLES RELATED QUERIES
    //==========================================================================================

    /**
     * Returns an array of Model/Riddle objects with all the available riddles.
     * @return array Array of Riddle instances
     */
    public function getAllRiddles(): array
    {
        // Build the SQL query
        $query = <<<'QUERY'
            SELECT * FROM riddles;
        QUERY;

        // Prepare query
        $statement = $this->databaseConnection->prepare($query);

        // Execute query
        $statement->execute();

        // Fetch query results and create array with Riddle instances
        $rawData =  $statement->fetchAll(PDO::FETCH_ASSOC);
        $riddles = [];
        foreach ($rawData as $row) {
            $riddles[] = new Riddle($row['riddle_id'], $row['user_id'], $row['riddle'], $row['answer']);
        }
        return $riddles;
    }

    /**
     * Adds the Riddle object to the repository, and return the 'ID' of the added riddle.
     * @param Riddle $r Riddle object to persist, where the 'id' and 'userId' attributes can be null.
     * @return int The ID associated to the added riddle
     */
    public function addRiddle(Riddle $r): int
    {
        // Check if optional ID field is set
        if ($r->getId() == null) {
            // Build SQL query
            $query = <<<'QUERY'
                INSERT INTO riddles (user_id, riddle, answer) VALUES (:user_id, :riddle, :answer);
            QUERY;

            // Prepare query
            $statement = $this->databaseConnection->prepare($query);
        } else {
            // Build SQL query
            $query = <<<'QUERY'
                INSERT INTO riddles (riddle_id, user_id, riddle, answer) VALUES (:id, :user_id, :riddle, :answer);
            QUERY;

            // Prepare query and bind 'id' parameter
            $statement = $this->databaseConnection->prepare($query);
            $id = $r->getId();
            $statement->bindParam('id', $id, PDO::PARAM_INT);
        }

        // Extract the variables we want to upload to database
        $idUser = $r->getUserId();
        $riddle = $r->getRiddle();
        $answer = $r->getAnswer();

        // Introduce variables in statement query
        $statement->bindParam('user_id', $idUser, PDO::PARAM_INT);
        $statement->bindParam('riddle', $riddle, PDO::PARAM_STR);
        $statement->bindParam('answer', $answer, PDO::PARAM_STR);

        // Execute query
        $statement->execute();

        // Get the ID of the riddle in the Riddle table and return it
        return intval($this->databaseConnection->lastInsertId());
    }

    /**
     * Given an id of a riddle, a Model/Riddle object with all the information is returned. If the riddle is not found
     * in the repository null is returned.
     * @param int $id id of the riddle to get from repository
     * @return Riddle|null The riddle associated to the 'id' provided, or null if no riddle is associated to the 'id'
     */
    public function getOneRiddleById(int $id): ?Riddle
    {
        // Build the SQL query
        $query = <<<'QUERY'
            SELECT * FROM riddles WHERE riddle_id = :riddleId LIMIT 1;
        QUERY;

        // Prepare query and bind 'riddleId' parameter
        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam(':riddleId', $id, PDO::PARAM_INT);

        // Execute query
        $statement->execute();

        // Fetch query results
        $rawData =  $statement->fetchAll(PDO::FETCH_ASSOC);

        // Check if at least one row has been returned. If not, return null
        if ( count($rawData) <= 0) {
            return null;
        }

        // Check if the returned row has all the expected columns. If so, create Riddle object and return it
        if (array_key_exists('riddle_id', $rawData[0]) && array_key_exists('user_id', $rawData[0]) && array_key_exists('riddle', $rawData[0]) && array_key_exists('answer', $rawData[0]) ){
            return new Riddle($rawData[0]['riddle_id'], $rawData[0]['user_id'], $rawData[0]['riddle'], $rawData[0]['answer']);
        } else {
            return null;
        }
    }

    /**
     * Updates the riddle with ID 'originalId' in the repository with the 'newRiddle' data
     * @param int $originalId 'id' of the riddle to update
     * @param Riddle $newRiddle Riddle instance where info to update is contained: 'id' attribute CANNOT be null, while
     *                          'userId' can be null. The unchanged fields must have the original information.
     * @return void -
     */
    public function updateRiddle(int $originalId, Riddle $newRiddle): void
    {

        // Build the SQL query
        $query = <<<'QUERY'
            UPDATE riddles SET riddle_id = :id, user_id = :userId, riddle = :riddle, answer = :answer  WHERE riddle_id = :originalId;
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Extract the variables we want to upload to database
        $id = $newRiddle->getId();
        $userId = $newRiddle->getUserId();
        $riddle = $newRiddle->getRiddle();
        $answer = $newRiddle->getAnswer();

        // Introduce variables in statement query
        $statement->bindParam('id', $id, PDO::PARAM_INT);
        $statement->bindParam('userId', $userId, PDO::PARAM_INT);
        $statement->bindParam('riddle', $riddle, PDO::PARAM_STR);
        $statement->bindParam('answer', $answer, PDO::PARAM_STR);
        $statement->bindParam('originalId', $originalId, PDO::PARAM_INT);

        // Execute query
        $statement->execute();
    }

    /**
     * Given an 'id' of a riddle this one is deleted from the repository
     * @param int $id 'id' of the riddle to delete from the repository
     * @return void -
     */
    public function deleteRiddle(int $id): void {
        // Build the SQL query
        $query = <<<'QUERY'
            DELETE FROM riddles WHERE riddle_id = :id;
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Add 'id' parameter to the query
        $statement->bindParam('id', $id, PDO::PARAM_INT);

        // Execute query
        $statement->execute();
    }
}