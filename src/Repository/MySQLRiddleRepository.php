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
     * Funció que agafa totes les riddles de la BBDD i les retorna en forma d'array.
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
     * Funció que afegeix un riddle a la BBDD.
     * @param Riddle $r
     * @return void
     */
    public function addRiddle(Riddle $r): void
    {

        // Fem la query
        $query = <<<'QUERY'
            INSERT INTO riddles (user_id, riddle, answer) VALUES (:id, :riddle, :answer);
        QUERY;

        // Preparem la query
        $statement = $this->databaseConnection->prepare($query);

        // Inserim els paràmetres que volem a la query

        $idUser = $r->getUserId();
        $riddle = $r->getRiddle();
        $answer = $r->getAnswer();

        $statement->bindParam('id', $idUser, PDO::PARAM_INT);
        $statement->bindParam('riddle', $riddle, PDO::PARAM_STR);
        $statement->bindParam('answer', $answer, PDO::PARAM_STR);

        // Executem la query
        $statement->execute();

    }

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
}