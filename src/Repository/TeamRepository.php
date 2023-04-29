<?php

namespace Salle\PuzzleMania\Repository;

use Salle\PuzzleMania\Model\Team;
use Salle\PuzzleMania\Model\User;

interface TeamRepository
{
    public function getTeamByUserId(int $user_id): Team;

    public function getTeamById(int $id): Team;

    public function setQRToTeam(int $id): void;

    public function createTeam(Team $team): void;

    public function addUserToTeam(int $team_id, User $user): void;

    public function getIncompleteTeams(): array;

    public function addScoreToTeam(int $team_id, int $score): void;
}