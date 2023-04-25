<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use Salle\PuzzleMania\Model\User;

interface UserRepository
{
    public function createUser(User $user): void;

    public function getUserByEmail(string $email);

    public function getUserById(int $id);

    public function getAllUsers();

    public function getTeamByID(int $id);

    /***
     * Function that creates a team given 2 users.
     * @param User $u1
     * @param User $u2
     * @return void
     */
    public function createTeam(User $u1, User $u2);

    public function createSoloTeam(User $u1): void;

    /***
     * Function that checks if a user is registered on a tema or not
     * @param User $u
     * @return bool
     */
    public function hasTeam(User $u);

    public function addToTeamByID(int $teamId, User $u);

    /**
     * Funció que afegeix un usuari a un equip ja existent.
     * @param User $oldUser
     * @param User $newUser
     * @return void
     */
    public function addToTeam(User $oldUser, User $newUser);

    public function getTeamID(User $u);

}
