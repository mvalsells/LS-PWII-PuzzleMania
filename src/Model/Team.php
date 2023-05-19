<?php
/**
 * Team: models the Team structure where users can form groups to compete.
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 20/04/2023
 * @updated: 18/05/2023
 */
namespace Salle\PuzzleMania\Model;

use JsonSerializable;

class Team implements JsonSerializable
{
    //------------------------------------------------------------------------------------------
    // VARIABLES
    //------------------------------------------------------------------------------------------

    private int $team_id;
    private string $team_name;
    private int $num_members;
    private int $user_id_1;
    private int $user_id_2;
    private int $total_score;
    private int $last_score;
    private int $QR_generated;

    //------------------------------------------------------------------------------------------
    // CONSTRUCTORS
    //------------------------------------------------------------------------------------------

    public function __construct() {
    }

    //------------------------------------------------------------------------------------------
    // OTHER METHODS
    //------------------------------------------------------------------------------------------
    /**
     * @return void -
     */
    public function addMember (int $user_id): void
    {
        $this->user_id_2 = $user_id;
    }

    /**
     * @return void -
     */
    public function addNewScore (int $score): void
    {
        $this->last_score = $score;
        $this->total_score += $score;
    }

    /**
     * @return array Json array of the team data
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    /**
     * @return bool Variable that indicates whether the team is NULL (=true) or not (=false)
     */
    public function isNullTeam(): bool
    {
        if (!isset($this->team_name)) {
            return true;
        }
        return false;
    }

    /**
     * @return bool Variable that indicates whether the team has a last score (=true) or not (=false)
     */
    public function isLastScoreRegistered(): bool
    {
        if (!isset($this->last_score)) {
            return false;
        }
        return true;
    }

    //------------------------------------------------------------------------------------------
    // GETTERS
    //------------------------------------------------------------------------------------------

    /**
     * @return int Team ID
     */
    public function getTeamId(): int
    {
        return $this->team_id;
    }

    /**
     * @return string Team name
     */
    public function getTeamName(): string
    {
        return $this->team_name;
    }

    /**
     * @return int Number of members
     */
    public function getNumMembers(): int
    {
        return $this->num_members;
    }

    /**
     * @return int ID of the first user
     */
    public function getUserId1(): int
    {
        return $this->user_id_1;
    }

    /**
     * @return int ID of the second user
     */
    public function getUserId2(): int
    {
        return $this->user_id_2;
    }

    /**
     * @return int Total score of the team
     */
    public function getTotalScore(): int
    {
        return $this->total_score;
    }

    /**
     * @return int Last score of the team
     */
    public function getLastScore(): int
    {
        return $this->last_score;
    }

    /**
     * @return int Variable that indicates whether the QR of the team has been generated (=1) or not (=0)
     */
    public function isQRGenerated(): int
    {
        return $this->QR_generated;
    }

    //------------------------------------------------------------------------------------------
    // SETTERS
    //------------------------------------------------------------------------------------------

    /**
     * @param int $team_id Team ID
     */
    public function setTeamId(int $team_id): void
    {
        $this->team_id = $team_id;
    }

    /**
     * @param string $team_name Team name
     */
    public function setTeamName(string $team_name): void
    {
        $this->team_name = $team_name;
    }

    /**
     * @param int $num_members Number of members
     */
    public function setNumMembers(int $num_members): void
    {
        $this->num_members = $num_members;
    }

    /**
     * @param int $user_id_1 ID of the first user
     */
    public function setUserId1(int $user_id_1): void
    {
        $this->user_id_1 = $user_id_1;
    }

    /**
     * @param int $user_id_2 ID of the second user
     */
    public function setUserId2(int $user_id_2): void
    {
        $this->user_id_2 = $user_id_2;
    }

    /**
     * @param int $last_score Last score of the team
     */
    public function setLastScore(int $last_score): void
    {
        $this->last_score = $last_score;
    }

    /**
     * @param int $total_score Total score of the team
     */
    public function setTotalScore(int $total_score): void
    {
        $this->total_score = $total_score;
    }

    /**
     * @param int $QR_generated Variable that indicates whether the QR of the team has been generated (=1) or not (=0)
     */
    public function setQRGenerated(int $QR_generated): void
    {
        $this->QR_generated = $QR_generated;
    }

}