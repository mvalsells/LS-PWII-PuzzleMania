<?php
/**
 * UserRepository: Interface that defines the methods to access data sources that contain User instances
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 18/04/2023
 * @updated: 19/05/2023
 */
declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use Salle\PuzzleMania\Model\User;

interface UserRepository
{
    /**
     * Saves the user in the database
     * @param User $user User to save in the database
     * @return void -
     */
    public function createUser(User $user): void;

    /**
     * Searches the user in the database by its email, and returns the user if found
     * @param string $email Email of the user that wants to be returned
     * @return ?User The team with the queried email (if not found, returns null)
     */
    public function getUserByEmail(string $email): ?User;

    /**
     * Function to update the profile picture of a user in the database
     * @param int $id ID of the user that has changed of profile picture
     * @param string $profilePicturePath The new path of the user's profile picture
     * @return void -
     */
    public function updateProfilePicture(int $id, string $profilePicturePath): void;

    /**
     * Searches the user in the database by its id, and returns the user if found
     * @param int $id ID of the user that wants to be returned
     * @return ?User The team with the queried id (if not found, returns null)
     */
    public function getUserById(int $id): ?User;

    /**
     * Function that returns all the Users of the database
     * @return array Array containing all User instances contained in the database
     */
    public function getAllUsers(): array;
}
