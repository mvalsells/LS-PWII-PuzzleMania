<?php
/**
 * User: models the User structure that contains relevant info (id, email, password, timestamps and profile picture path).
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 18/04/2023
 * @updated: 18/05/2023
 */
declare(strict_types=1);

namespace Salle\PuzzleMania\Model;

use DateTime;
use JsonSerializable;

class User implements JsonSerializable
{
    //------------------------------------------------------------------------------------------
    // VARIABLES
    //------------------------------------------------------------------------------------------

    private int $id;
    private string $email;
    private string $password;
    private string $profilePicturePath;
    private Datetime $createdAt;
    private Datetime $updatedAt;


    //------------------------------------------------------------------------------------------
    // CONSTRUCTORS
    //------------------------------------------------------------------------------------------

    public function __construct () {
    }

    //------------------------------------------------------------------------------------------
    // OTHER METHODS
    //------------------------------------------------------------------------------------------

    /**
     * @return array Json array of the user data
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    /**
     * @return bool Variable that indicates whether the user is NULL (=true) or not (=false)
     */
    public function isNullUser(): bool
    {
        if (!isset($this->email)) {
            return true;
        }
        return false;
    }

    /**
     * @return bool Variable that indicates whether the user has a profile picture (=true) or not (=false)
     */
    public function hasPicture(): bool
    {
        if (isset($this->profilePicturePath)) {
            return true;
        }
        return false;
    }

    //------------------------------------------------------------------------------------------
    // GETTERS
    //------------------------------------------------------------------------------------------

    /**
     * @return int User ID
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string User email
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string Username of the user
     */
    public function getUsername(): string
    {
        return explode('@', $this->email)[0];
    }

    /**
     * @return string Password of the user
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string Path of the user's profile picture
     */
    public function getProfilePicturePath(): string
    {
        return $this->profilePicturePath;
    }

    /**
     * @return DateTime Date of when the account was created
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return DateTime Date of when the account was updated
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    //------------------------------------------------------------------------------------------
    // SETTERS
    //------------------------------------------------------------------------------------------

    /**
     * @param int $id User ID
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param string $email User email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @param string $password Password of the user
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @param string $profilePicturePath Path of the user's profile picture
     */
    public function setProfilePicturePath(string $profilePicturePath): void
    {
        $this->profilePicturePath = $profilePicturePath;
    }

    /**
     * @param DateTime $createdAt Date when the account was created
     */
    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @param DateTime $updatedAt Date when the account was updated
     */
    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }


}
