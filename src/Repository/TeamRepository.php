<?php
/**
 * TeamRepository: Interface that defines the methods to access data sources that contain Team instances
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 28/04/2023
 * @updated: 18/05/2023
 */
namespace Salle\PuzzleMania\Repository;

use Salle\PuzzleMania\Model\Team;
use Salle\PuzzleMania\Model\User;

interface TeamRepository
{
    /**
     * Searches the team in the database by a user ID, and returns the team if found
     * @param int $user_id ID of the user whose team wants to be returned
     * @return Team The Team where the user forms part (if it doesn't form part in any, returns a null team)
     */
    public function getTeamByUserId(int $user_id): Team;

    /**
     * Searches the team in the database by its ID, and returns the team if found
     * @param int $id ID of the team that wants to be returned
     * @return Team The team with the queried ID (if not found, returns a null team)
     */
    public function getTeamById(int $id): Team;

    /**
     * Searches the team in the database by its name, and returns the team if found
     * @param string $name Name of the team that wants to be returned
     * @return Team The team with the queried name (if not found, returns a null team)
     */
    public function getTeamByName(string $name): Team;

    /**
     * Persist in database that a team has generated its QR
     * @param int $id ID of the team that has generated its QR
     * @return void -
     */
    public function setQRToTeam(int $id): void;

    /**
     * Saves the Team passed as parameter to the database
     * @param Team $team Team that wants to be created in database
     * @return void -
     */
    public function createTeam(Team $team): void;

    /**
     * Adds a user ID to a Team, meaning this user has joined the team
     * @param int $team_id ID of the team the user is trying to join
     * @param User $user User that is trying to join the team
     * @return void -
     */
    public function addUserToTeam(int $team_id, User $user): void;

    /**
     * Function that returns all the incomplete Teams of the database (the one that has less than 2 members)
     * @return array Array containing Team instances that have less than two members
     */
    public function getIncompleteTeams(): array;

    /**
     * Function that adds a new score to a team
     * @param int $team_id ID of the team where the new score has to be set
     * @param int $score The new score of the team
     * @return void -
     */
    public function addScoreToTeam(int $team_id, int $score): void;
}