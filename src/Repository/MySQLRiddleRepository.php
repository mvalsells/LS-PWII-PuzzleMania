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
    public function getRiddles(): array
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
}