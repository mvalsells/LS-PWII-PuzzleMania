<?php

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

    public function getTeamByUserId(int $user_id): Team
    {
        // Build the SQL query
        $query = <<<'QUERY'
            SELECT * FROM teams WHERE user_id_1 = :id OR user_id_2 = :id
        QUERY;

        return $this->getTeam($query, $user_id);
    }


    public function getTeamById(int $id): Team
    {
        // Build the SQL query
        $query = <<<'QUERY'
            SELECT * FROM teams WHERE team_id = :id
        QUERY;

        return $this->getTeam($query, $id);
    }

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

    public function createTeam(Team $team): void
    {
        // Build the SQL query
        $query = <<<'QUERY'
            INSERT INTO teams (team_name, num_members, user_id_1, total_score, QR_generated) VALUES
            (:teamName, 1, :email1, 0, 0);
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

    public function addUserToTeam(int $team_id, User $user): void
    {
        // Build the SQL query
        $query = <<<'QUERY'
            UPDATE teams
            SET num_members = 2
            SET user_id_2 = :user_id
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

    public function addScoreToTeam(int $team_id, int $score): void
    {
        // We get the team data
        $team = $this->getTeamById($team_id);

        // Build the SQL query
        $query = <<<'QUERY'
            UPDATE teams
            SET total_score = :total_score
            SET last_score = :last_score
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

        // Executem la query
        $statement->execute();
    }

    //==========================================================================================
    // OTHER FUNCTIONS
    //==========================================================================================

    private function createTeamVariable($row): Team
    {
        // Create team variable
        $team = new Team();
        $team->setTeamId($row->team_id);
        $team->setTeamName($row->team_name);
        $team->setNumMembers($row->num_members);
        if ($row->user_id_1 != null) {
            $team->setUserId1($row->user_id_1);
        }
        if ($row->user_id_2 != null) {
            $team->setUserId2($row->user_id_2);
        }
        $team->setTotalScore($row->total_score);
        if ($row->last_score != null) {
            $team->setLastScore($row->last_score);
        }
        $team->setQRGenerated($row->QR_generated);
        return $team;
    }

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