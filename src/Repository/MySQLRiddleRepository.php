<?php
declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use PDO;
use Salle\PuzzleMania\Model\Riddle;

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



    public function exists(Riddle $r){



    }

    public function getRiddles(){

    }

    public function addRiddle(Riddle $r){

        // Fem la query
        $query = <<<'QUERY'
            INSERT INTO riddles (user_id, riddle, answer) VALUES (:id, :riddle, :answer);
        QUERY;

        // Preparem la query
        $statement = $this->databaseConnection->prepare($query);

        // Inserim els paràmetres que volem a la query

        $idUser = $r->getIdUser();
        $riddle = $r->getRiddle();
        $answer = $r->getAnswer();

        $statement->bindParam('id', $idUser, PDO::PARAM_INT);
        $statement->bindParam('riddle', $riddle, PDO::PARAM_STR);
        $statement->bindParam('answer', $answer, PDO::PARAM_STR);

        // Executem la query
        $statement->execute();

    }
}