<?php
/**
 * MySQLTeamRepository: Class that implements TeamRepository interface, to access a database with Team instances
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 28/04/2023
 * @updated: 18/05/2023
 */
namespace Salle\PuzzleMania\Repository;

use PDO;
use Salle\PuzzleMania\Model\Team;
use Salle\PuzzleMania\Model\User;

class MySQLTeamRepository implements TeamRepository
{

    private PDO $databaseConnection;

    public function __construct(PDO $database)
    {
        $this->databaseConnection = $database;
    }

    //==========================================================================================
    // TEAM RELATED QUERIES
    //==========================================================================================

    /**
     * Searches the team in the database by a user ID, and returns the team if found
     * @param int $user_id ID of the user whose team wants to be returned
     * @return Team The Team where the user forms part (if it doesn't form part in any, returns a null team)
     */
    public function getTeamByUserId(int $user_id): Team
    {
        // Build the SQL query
        $query = <<<'QUERY'
            SELECT * FROM teams WHERE user_id_1 = :id OR user_id_2 = :id
        QUERY;

        return $this->getTeam($query, $user_id);
    }

    /**
     * Searches the team in the database by its ID, and returns the team if found
     * @param int $id ID of the team that wants to be returned
     * @return Team The team with the queried ID (if not found, returns a null team)
     */
    public function getTeamById(int $id): Team
    {
        // Build the SQL query
        $query = <<<'QUERY'
            SELECT * FROM teams WHERE team_id = :id
        QUERY;

        return $this->getTeam($query, $id);
    }

    /**
     * Searches the team in the database by its name, and returns the team if found
     * @param string $name Name of the team that wants to be returned
     * @return Team The team with the queried name (if not found, returns a null team)
     */
    public function getTeamByName(string $name): Team
    {
        // Build the SQL query
        $query = <<<'QUERY'
            SELECT * FROM teams WHERE team_name = :name
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Introduce 'id' variable in statement query
        $statement->bindParam('name', $name, PDO::PARAM_STR);

        // Execute the query
        $statement->execute();

        // Check if the user provided belongs to a team
        $count = $statement->rowCount();
        if ($count > 0) {
            // Extract the first team (theoretically, the user can only form part of 1 team)
            $row = $statement->fetch(PDO::FETCH_OBJ);
            return $this->createTeamVariable($row);
        }
        // Return null Team
        return new Team();
    }

    /**
     * Persist in database that a team has generated its QR
     * @param int $id ID of the team that has generated its QR
     * @return void -
     */
    public function setQRToTeam(int $id): void
    {
        // Build the SQL query
        $query = <<<'QUERY'
            UPDATE teams
            SET QR_generated = 1
            WHERE team_id = :team_id;
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Introduce variables in statement query
        $statement->bindParam('team_id', $id, PDO::PARAM_INT);

        // Execute query
        $statement->execute();
    }

    /**
     * Saves the Team passed as parameter to the database
     * @param Team $team Team that wants to be created in database
     * @return void -
     */
    public function createTeam(Team $team): void
    {
        // Build the SQL query
        $query = <<<'QUERY'
            INSERT INTO teams (team_name, num_members, user_id_1, total_score, QR_generated) VALUES
            (:team_name, 1, :user_id_1, 0, 0);
        QUERY;

        // Extract the variables we want to upload to database
        $team_name = $team->getTeamName();
        $user_id_1 = $team->getUserId1();

        $statement = $this->databaseConnection->prepare($query);

        // Introduce variables in statement query
        $statement->bindParam(':team_name', $team_name, PDO::PARAM_STR);
        $statement->bindParam(':user_id_1', $user_id_1, PDO::PARAM_INT);

        // Execute query
        $statement->execute();
    }

    /**
     * Adds a user ID to a Team, meaning this user has joined the team
     * @param int $team_id ID of the team the user is trying to join
     * @param User $user User that is trying to join the team
     * @return void -
     */
    public function addUserToTeam(int $team_id, User $user): void
    {
        // Build the SQL query
        $query = <<<'QUERY'
            UPDATE teams
            SET num_members = 2, user_id_2 = :user_id
            WHERE team_id = :team_id;
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Extract the 'user_id' variable we want to upload to database
        $user_id = $user->getId();

        // Introduce variables in statement query
        $statement->bindParam('user_id', $user_id, PDO::PARAM_INT);
        $statement->bindParam('team_id', $team_id, PDO::PARAM_INT);

        // Execute query
        $statement->execute();
    }

    /**
     * Function that returns all the incomplete Teams of the database (the one that has less than 2 members)
     * @return array Array containing Team instances that have less than two members
     */
    public function getIncompleteTeams(): array
    {
        // Build the SQL query
        $query = <<<'QUERY'
            SELECT * FROM teams WHERE user_id_1 IS NULL OR user_id_2 IS NULL;
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Execute the query
        $statement->execute();

        $teams = [];

        // Count the number of teams
        $count = $statement->rowCount();

        // Fetch all the teams and add them to array
        for ($i = 0; $i < $count; $i++) {
            $row = $statement->fetch(PDO::FETCH_OBJ);
            $teams[] = $this->createTeamVariable($row);
        }

        return $teams;
    }

    /**
     * Function that adds a new score to a team
     * @param int $team_id ID of the team where the new score has to be set
     * @param int $score The new score of the team
     * @return void -
     */
    public function addScoreToTeam(int $team_id, int $score): void
    {
        // We get the team data
        $team = $this->getTeamById($team_id);

        // Build the SQL query
        $query = <<<'QUERY'
            UPDATE teams
            SET total_score = :total_score, last_score = :last_score
            WHERE team_id = :team_id;
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Extract the variables we want to upload to database
        $total_score = $team->getTotalScore();
        if ($score > 0) {
            $total_score += $score;
        }

        // Introduce variables in statement query
        $statement->bindParam('total_score', $total_score, PDO::PARAM_INT);
        $statement->bindParam('last_score', $score, PDO::PARAM_INT);
        $statement->bindParam('team_id', $team_id, PDO::PARAM_INT);

        // Execute query
        $statement->execute();
    }

    //==========================================================================================
    // OTHER FUNCTIONS
    //==========================================================================================

    /**
     * Converts a row fetched from the database into a Team
     * @param mixed $row fetched from the database
     * @return Team The Team created from the data contained in the $row
     */
    private function createTeamVariable(mixed $row): Team
    {
        // Create team variable
        $team = new Team();

        // Set the variables that always appear
        $team->setTeamId($row->team_id);
        $team->setTeamName($row->team_name);
        $team->setNumMembers($row->num_members);
        $team->setTotalScore($row->total_score);
        $team->setQRGenerated($row->QR_generated);

        // Check the variables that can be NULL
        if (isset($row->user_id_1)) {
            $team->setUserId1($row->user_id_1);
        }
        if (isset($row->user_id_2)) {
            $team->setUserId2($row->user_id_2);
        }
        if (isset($row->last_score)) {
            $team->setLastScore($row->last_score);
        }

        return $team;
    }

    /**
     * General function that gets a Team from the database
     * @param string $query Query built that has to be executed
     * @param int $id ID that needs to be included in the query statement
     * @return Team The Team that has been got from the database (if not found, returns a null team)
     */
    private function getTeam(string $query, int $id): Team
    {
        $statement = $this->databaseConnection->prepare($query);

        // Introduce 'id' variable in statement query
        $statement->bindParam('id', $id, PDO::PARAM_INT);

        // Execute the query
        $statement->execute();

        // Check if the user provided belongs to a team
        $count = $statement->rowCount();
        if ($count > 0) {
            // Extract the first team (theoretically, the user can only form part of 1 team)
            $row = $statement->fetch(PDO::FETCH_OBJ);
            return $this->createTeamVariable($row);
        }
        // Return null Team
        return new Team();
    }

}