<?php

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

    //------------------------------------------------------------------------------------------
    // CONSTRUCTORS
    //------------------------------------------------------------------------------------------

    public function __construct() {
    }

    //------------------------------------------------------------------------------------------
    // OTHER METHODS
    //------------------------------------------------------------------------------------------

    public function addMember (int $user_id)
    {
        $this->user_id_2 = $user_id;
    }

    public function addNewScore (int $score)
    {
        $this->last_score = $score;
        $this->total_score += $score;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public function isNullTeam(): bool
    {
        if (!isset($this->team_name)) {
            return true;
        }
        return false;
    }

    //------------------------------------------------------------------------------------------
    // GETTERS
    //------------------------------------------------------------------------------------------

    /**
     * @return int
     */
    public function getTeamId(): int
    {
        return $this->team_id;
    }

    /**
     * @return string
     */
    public function getTeamName(): string
    {
        return $this->team_name;
    }

    /**
     * @return int
     */
    public function getNumMembers(): int
    {
        return $this->num_members;
    }

    /**
     * @return int
     */
    public function getUserId1(): int
    {
        return $this->user_id_1;
    }

    /**
     * @return int
     */
    public function getUserId2(): int
    {
        return $this->user_id_2;
    }

    /**
     * @return int
     */
    public function getTotalScore(): int
    {
        return $this->total_score;
    }

    /**
     * @return int
     */
    public function getLastScore(): int
    {
        return $this->last_score;
    }

    //------------------------------------------------------------------------------------------
    // SETTERS
    //------------------------------------------------------------------------------------------

    /**
     * @param int $team_id
     */
    public function setTeamId(int $team_id): void
    {
        $this->team_id = $team_id;
    }

    /**
     * @param string $team_name
     */
    public function setTeamName(string $team_name): void
    {
        $this->team_name = $team_name;
    }

    /**
     * @param int $num_members
     */
    public function setNumMembers(int $num_members): void
    {
        $this->num_members = $num_members;
    }

    /**
     * @param int $user_id_1
     */
    public function setUserId1(int $user_id_1): void
    {
        $this->user_id_1 = $user_id_1;
    }

    /**
     * @param int $user_id_2
     */
    public function setUserId2(int $user_id_2): void
    {
        $this->user_id_2 = $user_id_2;
    }

    /**
     * @param int $last_score
     */
    public function setLastScore(int $last_score): void
    {
        $this->last_score = $last_score;
    }

    /**
     * @param int $total_score
     */
    public function setTotalScore(int $total_score): void
    {
        $this->total_score = $total_score;
    }

}