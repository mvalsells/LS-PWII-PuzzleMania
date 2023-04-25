<?php
declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use PDO;

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


    public function getRiddles()
    {

    }

    public function addRiddle(){

    }
}